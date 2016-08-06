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

use Gishiki\Database\DatabaseException;

/**
 * Give runtime support to the pipeline component.
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class PipelineSupport
{
    /**
     * @var \Gishiki\Database\DatabaseInterface the connection to the database
     */
    private static $connectionHandler = null;

    /**
     * @var string the name of the table
     */
    private static $tableName = null;

    /**
     * @var bool have the PipelineSupport being initialized?
     */
    private static $initialized = false;

    /**
     * @var PipelineRuntime|null the currently active runtime or null 
     */
    private static $activeRuntime = null;

    /**
     * Internal use ONLY!
     * Setup the PipelineSupport from the global Environment.
     * 
     * @param string $connectionName the name of the database connection
     * @param string $tableName      the name of the table inside the database connection
     *
     * @throws DatabaseException         the exception occurred while fetching the database connection
     * @throws \InvalidArgumentException given arguments are not valid strings
     */
    public static function Initialize($connectionName, $tableName)
    {
        //only works for the first time (prevent user from doing bad things)
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;

        if ((!is_string($connectionName)) || (!is_string($tableName))) {
            throw new \InvalidArgumentException('Database connection name and table must be given as strings');
        }

        //retrieve the database connection
        self::$connectionHandler = \Gishiki\Database\DatabaseManager::Retrieve($connectionName);

        //store the table name
        self::$tableName = $tableName;
    }

    /**
     * Internal use ONLY!
     * Flag the given PipelineRuntime as the currently active one.
     * 
     * @param PipelineRuntime $runtime
     */
    public static function RegisterRuntime(PipelineRuntime &$runtime)
    {
        //get the runtime
        self::$activeRuntime = &$runtime;
    }

    /**
     * Internal use ONLY!
     * The currently active PipelineRuntime is marked as not-active-anymore.
     */
    public static function UnregisterRuntime()
    {
        //get the runtime
        self::$activeRuntime = null;
    }

    /**
     * Abort the current pipeline execution saving the reason.
     * 
     * @param string $message the message to be saved as the reason of the abort
     *
     * @throws \InvalidArgumentException the given message is not a string
     */
    public static function Abort($message = '')
    {
        if (!is_string($message)) {
            throw new \InvalidArgumentException('The abort message must be given as a string');
        }

        //throw the abort signal
        throw new PipelineAbortSignal($message);
    }

    /**
     * Save the currently active PipelineRuntime.
     * 
     * @throws PipelineException no pipeline is going to be saved
     */
    public static function saveCurrentPupeline()
    {
        if (is_null(self::$activeRuntime)) {
            throw new PipelineException('No pipeline currently flagged as active', 1);
        }

        //reflect the PipelineRuntime currently active
        $pipelineRuntimeReflected = new \ReflectionObject(self::$activeRuntime);

        //reflect the unique ID of the pipeline
        $uniqProp = $pipelineRuntimeReflected->getProperty('uniqCode');
        $uniqProp->setAccessible(true);
        $uniqCode = $uniqProp->getValue(self::$activeRuntime);

        //reflect the status of the pipeline
        $statusProp = $pipelineRuntimeReflected->getProperty('status');
        $statusProp->setAccessible(true);
        $status = $statusProp->getValue(self::$activeRuntime);

        //reflect the type of the pipeline
        $typeProp = $pipelineRuntimeReflected->getProperty('type');
        $typeProp->setAccessible(true);
        $type = $typeProp->getValue(self::$activeRuntime);

        //reflect the priority of the pipeline
        $priorityProp = $pipelineRuntimeReflected->getProperty('priority');
        $priorityProp->setAccessible(true);
        $priority = $priorityProp->getValue(self::$activeRuntime);

        //reflect the creation time of the pipeline
        $creationTimeProp = $pipelineRuntimeReflected->getProperty('creationTime');
        $creationTimeProp->setAccessible(true);
        $creationTime = $creationTimeProp->getValue(self::$activeRuntime);

        //reflect completion reports of the pipeline
        $completionReportsProp = $pipelineRuntimeReflected->getProperty('completionReports');
        $completionReportsProp->setAccessible(true);
        $completionReports = $completionReportsProp->getValue(self::$activeRuntime);

        //reflect the completed stages list of the pipeline
        $completedStagesProp = $pipelineRuntimeReflected->getProperty('completedStages');
        $completedStagesProp->setAccessible(true);
        $completedStages = $completedStagesProp->getValue(self::$activeRuntime);

        //reflect the pipeline reference to the pipeline
        $pipelineProp = $pipelineRuntimeReflected->getProperty('pipeline');
        $pipelineProp->setAccessible(true);
        $pipeline = $pipelineProp->getValue(self::$activeRuntime);

        //reflect the abortMessage of the pipeline
        $abortMessageProp = $pipelineRuntimeReflected->getProperty('abortMessage');
        $abortMessageProp->setAccessible(true);
        $abortMessage = $abortMessageProp->getValue(self::$activeRuntime);

        //reflect the data collection of the pipeline
        $serializableCollectionProp = $pipelineRuntimeReflected->getProperty('serializableCollection');
        $serializableCollectionProp->setAccessible(true);
        $serializableCollection = $serializableCollectionProp->getValue(self::$activeRuntime);

        //generate the data to be saved
        $data = [
            'uniqID' => $uniqCode,
            'status' => $status,
            'type' => $type,
            'priority' => $priority,
            'creationTime' => $creationTime,
            'completionReports' => $completionReports,
            'completedStages' => $completedStages,
            'pipeline' => $pipeline->getName(),
            'abortMessage' => $abortMessage,
            'serializableCollection' => $serializableCollection->all(),
        ];

        //identify the pipeline if already saved
        $selector = new \Gishiki\Database\SelectionCriteria();
        $selector->EqualThan('uniqID', $uniqCode);

        if (self::$connectionHandler->Fetch(self::$tableName, $selector)->count() > 0) {
            //save the pipeline status in an already existing record
             self::$connectionHandler->Update(self::$tableName, $data, $selector);

            return;
        }

        //save the pipeline status in a new record
        self::$connectionHandler->Insert(self::$tableName, $data);
    }
}
