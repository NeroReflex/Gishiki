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
use Gishiki\Pipeline\RuntimeStatus;

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
    public $serializableCollection;

    private $uniqCode;
    private $status;
    private $type;
    private $priority;
    private $creationTime;
    private $completionReports = array();
    private $completedStages;
    private $pipeline;
    private $abortMessage = null;
    
    /**
     * Create a new pipeline executor and stop the execution, so that it
     * will be started on request.
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

        //generate a new serializable collection
        $this->serializableCollection = new SerializableCollection();

        //generate an unique ID for the current pipeline executor
        $this->uniqCode = uniqid();

        //save the priority of the current runtime
        $this->priority = $priority;

        //store the timestamp of the creation time
        $this->creationTime = time();

        //no completed stages (yet)
        $this->completedStages = 0;

        //store a reference to the pipeline
        $this->pipeline = &$pipeline;

        //end the execution IMMEDIATLY by executing zero stages
        $this(0);
    }

    /**
     * Execute a finite number of stages and end the execution.
     * If the number of stages to be executed are less than zero than the entire
     * pipeline is executed.
     * 
     * @param int   $steps number of stages to be passed before stopping the execution
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

        for ($i = $this->completedStages; ($i < ($this->completedStages + $stepsNumber)) && (($i + $this->completedStages) < $this->pipeline->countStages()) && ($this->status != RuntimeStatus::ABORTED); ++$i) {
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

                //a stage has been completed
                ++$this->completedStages;

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
        }
        
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
     * Get the status of the current pippeline executor.
     * 
     * @return int one of the RuntimeStatus constants
     */
    public function getStatus()
    {
        return $this->status;
    }
}
