<?php
/**************************************************************************
Copyright 2016 Benato Denis

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*****************************************************************************/

namespace Gishiki\Pipeline;

use Gishiki\Algorithms\Collections\SerializableCollection;

/**
 * Represent the executor of a pipeline.
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class PipelineRuntime
{
    private static $currentExecution = null;

    /**
     * @var SerializableCollection the data that the runtime will access with R/W permissions
     */
    private $serializableCollection;
    private $uniqCode;
    private $status;
    private $type;
    private $priority;
    private $creationTime;
    private $completionReports = array();
    private $pipeline;
    private $abortMessage = null;

    /**
     * Create a new pipeline executor and stop the execution, so that it
     * will be started on request or by the cronjob.
     * 
     * <code>
     * $pipeline = new Pipeline("first_splittest!");
     *  $pipeline->bindStage('firstStage', function (SerializableCollection &$collection)
     *  {
     *      $collection->set('value', 5);
     *      return "stringa";
     *  });
     *  $pipeline->bindStage('secondStage', function (SerializableCollection &$collection)
     *  {
     *      $collection->set('value', $collection->get('value') + 1);
     *      return 0x5A;
     *  });
     *  $pipeline->bindStage('thirdStage', function (SerializableCollection &$collection)
     *  {
     *      $collection->set('value', $collection->get('value') * 3);
     *      return 7.43;
     *  });
     *  $pipeline->bindStage('fourthStage', function (SerializableCollection &$collection)
     *  {
     *      $collection->set('value', $collection->get('value') + 2);
     *      return null;
     *  });
     *  
     *  //create the pipeline runtime
     *  $pipelineExecutor = new PipelineRuntime($pipeline, \Gishiki\Pipeline\RuntimeType::SYNCHRONOUS);
     * 
     *  //execute the pipeline entirely
     *  $pipelineExecutor(-1);
     * </code>
     * 
     * @param Pipeline $pipeline the pipeline to be executed
     * @param int      $priority the priority assigned to the pipeline executor
     *
     * @throws \InvalidArgumentException the given priority is not valid
     */
    public function __construct(Pipeline &$pipeline, $type = RuntimeType::ASYNCHRONOUS, $priority = RuntimePriority::LOWEST)
    {
        //check for invalid priority
        if (((!is_int($priority)) || ($priority > RuntimePriority::LOWEST)) || (($type != RuntimeType::ASYNCHRONOUS) && ($type != RuntimeType::SYNCHRONOUS))) {
            throw new \InvalidArgumentException('The given execution priority or pipeline type is not valid');
        }

        //store the type of the pipeline
        $this->type = $type;

        //the pipeline is stopped right now
        $this->status = RuntimeStatus::STOPPED;

        //generate a new serializable collection
        $this->serializableCollection = new SerializableCollection();

        //generate an unique ID for the current pipeline executor
        $this->uniqCode = uniqid();

        //save the priority of the current runtime
        $this->priority = $priority;

        //store the timestamp of the creation time
        $this->creationTime = time();

        //store a reference to the pipeline
        $this->pipeline = &$pipeline;

        //end the execution IMMEDIATLY by executing zero stages
        $this(0);
    }

    /**
     * Forward the request to PipelineSupport.
     * 
     * @param string $uniqueID the unique ID of the PipelineRuntime
     *
     * @return PipelineRuntime the restored runtime
     *
     * @throws PipelineException         the given unique ID is not valid
     * @throws \InvalidArgumentException the unique ID is not a valid string
     */
    public static function Restore($uniqueID)
    {
        return PipelineSupport::Restore($uniqueID);
    }

    /**
     * Execute a finite number of stages and end the execution.
     * If the number of stages to be executed are less than zero than the entire
     * pipeline is executed.
     * 
     * @param int $steps number of stages to be passed before stopping the execution
     *
     * @throws \InvalidArgumentException invalid arguments passed
     */
    public function __invoke($steps = -1)
    {
        //check the number of steps
        if (!is_int($steps)) {
            throw new \InvalidArgumentException('The number of steps to execute must be given as an integer number');
        }

        //register the current runtime
        PipelineSupport::RegisterRuntime($this);

        //get the exact number of stages that should be executed
        $stepsNumber = ($steps < 0) ?
                $this->pipeline->countStages() : $steps;

        for ($i = $this->getCompletedStagesCount(); ($stepsNumber > 0) && ($i < $this->pipeline->countStages()) && ($this->status != RuntimeStatus::ABORTED); ++$i) {
            try {
                //the pipeline is working right now
                $this->status = RuntimeStatus::WORKING;

                //get the starting time
                $startTime = time();
                $start = microtime(true);

                //register the currently used runtime
                self::$currentExecution = &$this;

                //fetch & execute the pipeline stage
                $reflectedFunction = $this->pipeline->reflectFunctionByIndex($i);
                $executionResult = $reflectedFunction->invokeArgs([&$this->serializableCollection]);

                //unregister the currently used runtime
                self::$currentExecution = null;

                //get the final time
                $time_elapsed_secs = microtime(true) - $start;
                $finalTime = time();

                //generate and store the report
                $this->completionReports[] = [
                    'start_time' => $startTime,
                    'end_time' => $finalTime,
                    'elapse_time' => $time_elapsed_secs,
                    'result' => $executionResult,
                ];

                //the pipeline is stopped right now
                $this->status = RuntimeStatus::STOPPED;
            } catch (\Gishiki\Pipeline\PipelineAbortSignal $abortSignal) {
                //the pipeline was aborted
                $this->status = RuntimeStatus::ABORTED;

                //the abort reason must be saved
                $this->abortMessage = $abortSignal->getMessage();
            }

            if ((is_null($this->abortMessage)) && ($this->status != RuntimeStatus::ABORTED) && ($i == ($this->pipeline->countStages() - 1))) {
                $this->status = RuntimeStatus::COMPLETED;
            }

            //register the currently active runtime
            PipelineSupport::saveCurrentPupeline();

            --$stepsNumber;
        }

        //register the currently active runtime
        PipelineSupport::saveCurrentPupeline();

        //runtime ended
        PipelineSupport::UnregisterRuntime();
    }

    /**
     * Get the reason of the pipeline processing forced abort.
     * 
     * @return null|string the reason for the project abort or null
     */
    public function getAbortMessage()
    {
        return $this->abortMessage;
    }

    /**
     * Get the status of the current pipeline executor.
     * 
     * @return int one of the RuntimeStatus constants
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get the type of the current pipeline executor.
     * 
     * @return int one of the RuntimeType constants
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get the priority of the current pipeline executor.
     * 
     * @return int one of the RuntimePriority constants
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Get the number of pipeline stages completed.
     * 
     * @return int completed stages
     */
    public function getCompletedStagesCount()
    {
        return count($this->completionReports);
    }

    /**
     * Get the data collection that the pipeline can use to work on.
     * 
     * @return SerializableCollection the data collection
     */
    public function &getDataCollection()
    {
        return $this->serializableCollection;
    }

    /**
     * Get the name of the pipeline that is executed by this runtime.
     * 
     * @return string the name of the pipeline
     */
    public function getPipelineName()
    {
        return $this->pipeline->getName();
    }

    /**
     * Get timestamp of the runtime creation moment.
     * 
     * @return int the timestamp of the creation time
     */
    public function getCreationTime()
    {
        return $this->creationTime;
    }

    /**
     * Get the unique ID of the pipeline runtime.
     * The given ID can be used to restore the runtime over the time.
     * 
     * @return string the unique ID
     */
    public function getUniqueID()
    {
        return $this->uniqCode;
    }

    /**
     * Get the report of the pipeline execution.
     * Each time a pipeline stage get executed completely a report is generated.
     * 
     * <code>
     * //this is an example of what is returned:
     * array([
     *          'start_time' => 1470481017,
     *          'end_time' => 1470481017,
     *          'elapse_time' => 2.09808349609375e-005,
     *          'result' => 4,
     *      ],
     *      [
     *          'start_time' => 1470481017,
     *          'end_time' => 1470481017,
     *          'elapse_time' => 3.09944152832031e-005,
     *          'result' => 7,
     *      ],
     * );
     * </code>
     * 
     * @return SerializableCollection the report of the result automatically generated
     */
    public function getExecutionReport()
    {
        return new SerializableCollection($this->completionReports);
    }
}
