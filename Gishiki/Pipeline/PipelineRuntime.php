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
    public $serializableCollection;

    private $uniqCode;
    private $status;
    private $type;
    private $priority;
    private $creationTime;
    private $completionReports = array();
    private $completedStages;
    private $pipeline;

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
        $this([], 0);
    }

    /**
     * Execute a finite number of stages and end the execution.
     * If the number of stages to be executed are less than zero than the entire
     * pipeline is executed.
     * 
     * @param array $args  values to be passed to every pipeline function
     * @param int   $steps number of stages to be passed before stopping the execution
     *
     * @throws \InvalidArgumentException invalid arguments passed
     */
    public function __invoke($args = array(), $steps = -1)
    {
        //check the number of steps
        if (!is_int($steps)) {
            throw new \InvalidArgumentException('The number of steps to execute must be given as an integer number');
        }

        //check the list of arguments to be passed to the execution
        if (!is_array($args)) {
            throw new \InvalidArgumentException('The list of arguments must be given as an array');
        }
        
        //register the current runtime
        PipelineSupport::RegisterRuntime($this);
        
        //get the exact number of stages that should be executed
        $stepsNumber = ($steps < 0) ?
                $this->pipeline->countStages() : $steps;

        for ($i = $this->completedStages; ($i < ($this->completedStages + $stepsNumber)) && (($i + $this->completedStages) < $this->pipeline->countStages()); ++$i) {
            //the pipeline is working right now
            $this->status = RuntimeStatus::WORKING;

            //get the starting time
            $startTime = time();
            $start = microtime(true);

            //register the currently used runtime
            self::$currentExecution = &$this;

            //fetch & execute the pipeline stage
            $reflectedFunction = $this->pipeline->reflectFunctionByIndex($i);
            $executionResult = $reflectedFunction->invokeArgs($args);

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
        }
    }
}
