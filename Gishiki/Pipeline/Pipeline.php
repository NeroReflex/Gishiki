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

use Gishiki\Pipeline\PipelineCollector;

/**
 * Represent a pipeline as a group of actions to be executed
 * in a specific order.
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class Pipeline {
    /**
     * @var array The ordered collection of steps
     */
    private $steps = array();
	
    /**
     * @var string The name of the current pipeline
     */
    private $name;
	
    /**
     * Initialize an empty pipeline with the given name.
     *
     * @param string $name the name of the pipeline
     * @throws \InvalidArgumentException invalid pipeline name
     */
    public function __construct($name)
    {
        //check for the name
        if ((!is_string($name)) || (strlen($name) <= 0)) {
            throw new \InvalidArgumentException('the given pipeline name is not a valid string');
        }
		
        //store the pipeline name
        $this->name = $name;
        
        //register the pipeline
        PipelineCollector::registerPipeline($this);
    }
	
    /**
     * Bind a stage to the current pipeline.
     * The execution order of pipeline stages is the order they are binded.
     *
     * @param  string   $name   the name of the stage
     * @param  \Closure $func   the function that represents the stage
     * @throws \InvalidArgumentException invalid name or function
     */
    public function bindStage($name, $func)
    {
        if (!is_callable($func)) {
            throw new \InvalidArgumentException('the given function cannot be called');
        }
		
        if ((!is_string($name)) || (strlen($name) <= 0)) {
            throw new \InvalidArgumentException('the given function name is not a valid string');
        }
		
        //check for steps with the same name
        foreach ($this->steps as $step) {
            if ($step["step_name"] == $name) {
                throw new \InvalidArgumentException('the given function name is already used in another step');
            }
        }
		
        //store the step
        $this->steps[] = array(
            "step_name" => "".$name,
            "step_function" => $func,
        );
    }
	
    /**
     * Get the number of stages inside the pipeline
     *
     * @return int the number of stages of the pipeline
     */
    public function countStages()
    {
        return count($this->steps);
    }
        
    /**
     * Get the name of the current pipeline
     * 
     * @return string the name of the pipeline
     */
    public function getName()
    {
        return "".$this->name;
    }
}