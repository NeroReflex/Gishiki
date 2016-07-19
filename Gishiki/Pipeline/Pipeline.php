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

/**
 * Represent a pipeline as a group of actions to be executed
 * in a specific order.
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class Pipeline
{
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
     *
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
     * @param string   $name the name of the stage
     * @param \Closure $func the function that represents the stage
     *
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
            if ($step['step_name'] == $name) {
                throw new \InvalidArgumentException('the given function name is already used in another step');
            }
        }

        //store the step
        $this->steps[] = array(
            'step_name' => ''.$name,
            'step_function' => $func,
        );
    }

    /**
     * Get the number of stages inside the pipeline.
     *
     * @return int the number of stages of the pipeline
     */
    public function countStages()
    {
        return count($this->steps);
    }

    /**
     * Get the name of the current pipeline.
     * 
     * @return string the name of the pipeline
     */
    public function getName()
    {
        return ''.$this->name;
    }

    /**
     * Retrieve the name of the function inside the current pipeline by its index.
     * 
     * @param int $index the index zero-based of the function
     *
     * @return string the name of the function
     *
     * @throws \InvalidArgumentException the given index is invalid
     * @throws PipelineException         a function with the given index doesn't exists
     */
    public function getFunctionNameByIndex($index)
    {
        //check for bad or invalid numbers
        if ((!is_int($index)) || ($index < 0)) {
            throw new \InvalidArgumentException('The given function index is not valid');
        }
        if ($index >= $this->countStages()) {
            throw new PipelineException('The pipeline doesn\'t have the requested function', 2);
        }

        //return the step name
        return $this->steps[$index]['step_name'];
    }

    /**
     * Retrieve the index of the function with the given name.
     * 
     * @param string $name the name of the function to be found
     *
     * @return int the index of the binded function with the given name
     *
     * @throws \InvalidArgumentException the name of the function is invalid
     * @throws PipelineException         a function with the given name cannot be found
     */
    public function getFunctionIndexByName($name)
    {
        //check for malformed input
        if ((!is_string($name)) || (strlen($name) <= 0)) {
            throw new \InvalidArgumentException('the given function name is not a valid string');
        }

        //search the requested function
        foreach ($this->steps as $index => $step) {
            if (strcmp($step['step_name'], $name) == 0) {
                return $index;
            }
        }

        //report the error
        throw new PipelineException('A function with the given name in not defined on the current pipeline', 3);
    }

    /**
     * Reflect the function with the given name from the current pipeline.
     * 
     * @param string $name the name of the function to be reflected
     *
     * @return \ReflectionFunction the reflected function
     *
     * @throws \InvalidArgumentException the given function name is invalid
     */
    public function reflectFunctionByName($name)
    {
        //check for invalid function names
        if ((!is_string($name)) || (strlen($name) <= 0)) {
            throw new \InvalidArgumentException('The given function name is not a valid string');
        }

        //get the function index
        $index = $this->getFunctionIndexByName($name);

        //and return the function using its index
        return $this->reflectFunctionByIndex($index);
    }

    /**
     * Reflect the function with the given index from the current pipeline.
     * 
     * @param string $index the name of the function to be reflected
     *
     * @return \ReflectionFunction the reflected function
     *
     * @throws \InvalidArgumentException the given function index is invalid
     * @throws PipelineException         the given function index is out of range
     */
    public function reflectFunctionByIndex($index)
    {
        //check for bad or invalid numbers
        if ((!is_int($index)) || ($index < 0)) {
            throw new \InvalidArgumentException('The given function index is not valid');
        }
        if ($index >= $this->countStages()) {
            throw new PipelineException('The pipeline doesn\'t have the requested function', 2);
        }

        //return the reflected function
        return new \ReflectionFunction($this->steps[$index]['step_function']);
    }
}
