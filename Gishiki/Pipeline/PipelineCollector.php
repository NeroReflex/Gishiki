<?php
/**************************************************************************
Copyright 2017 Benato Denis

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
 * Represent the entire collection of pipelines currectly available.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class PipelineCollector
{
    /**
     * @var array the collection of registered pipelines
     */
    private static $pipelines = array();

    /**
     * Register a pipeline in the global pipeline collector.
     *
     * @param Pipeline $pipeline the pipeline to be registered
     *
     * @throws PipelineException a pipeline with the same name already eexists
     */
    public static function registerPipeline(Pipeline &$pipeline)
    {
        //register the current pipeline only if a pipeline with the same name doesn't exists
        if (self::checkPipelineByName($pipeline->getName())) {
            throw new PipelineException('A pipeline with the same name already exists', 0);
        }
        self::$pipelines[] = &$pipeline;
    }

    /**
     * Check if a pipeline with the given name already exists.
     *
     * @param string $name the name of the pipeline to be searched
     *
     * @return bool TRUE only is a pipeline with the given name already exists
     */
    public static function checkPipelineByName($name)
    {
        //check for the name
        if ((!is_string($name)) || (strlen($name) <= 0)) {
            throw new \InvalidArgumentException('the given pipeline name is not a valid string');
        }

        try {
            self::getPipelineByName($name);

            return true;
        } catch (PipelineException $ex) {
            return false;
        }
    }

    /**
     * Get a pipeline using its name.
     *
     * @param string $name the name of the desired pipeline
     *
     * @return Pipeline& the reference to the pipeline with the given name
     *
     * @throws \InvalidArgumentException the given pipeline name is not valid
     * @throws PipelineException         a pipeline with the given name cannot be found
     */
    public static function getPipelineByName($name)
    {
        //check for the name
        if ((!is_string($name)) || (strlen($name) <= 0)) {
            throw new \InvalidArgumentException('the given pipeline name is not a valid string');
        }

        //check for another pipeline with the same name
        foreach (self::$pipelines as &$pipelineToCheck) {
            if (strcmp($pipelineToCheck->getName(), $name) == 0) {
                return $pipelineToCheck;
            }
        }

        //if executing this than the correct pipeline was not returned
        throw new PipelineException("A pipeline with the given name doesn't exists", 1);
    }
}
