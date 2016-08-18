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
use Gishiki\Algorithms\Collections\SerializableCollection;

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
        self::$activeRuntime = $runtime;
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
     * Stop the current pipeline execution to continue the execution later on.
     * 
     * @param mixed $value the value to be used as function return
     */
    public static function Stop($value = null)
    {
        //throw the stop signal
        throw new PipelineStopSignal(serialize($value));
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
     * Change the type of the current pipeline, but doesn't immediatly reflect
     * changes to the database.
     * 
     * @param int $type can either be RuntimeType::ASYNCHRONOUS or RuntimeType::SYNCHRONOUS
     *
     * @throws \InvalidArgumentException the given type is not valid
     */
    public static function ChangeType($type)
    {
        //change the type
        self::$activeRuntime->ChangeType($type);
    }

    /**
     * Get the unique ID of the runtime currently executed.
     * 
     * @return string the unique ID of the loaded pipeline
     * @throws PipelineException no loaded runtimes
     */
    public static function GetUniqueID() {
        if (is_null(self::$activeRuntime)) {
            throw new PipelineException("The unique ID of an unloaded runtime cannot be retrieved", 8);
        }
        
        //return the unique ID
        return self::$activeRuntime->getUniqueID();
    }
    
    /**
     * Save the currently active PipelineRuntime.
     * 
     * @throws PipelineException no pipeline is going to be saved
     */
    public static function saveCurrentPipeline()
    {
        if (is_null(self::$activeRuntime)) {
            throw new PipelineException('No pipeline currently flagged as active', 1);
        }

        //generate the data to be saved
        $data = new \Gishiki\Algorithms\Collections\GenericCollection([
            'uniqID' => self::$activeRuntime->getUniqueID(),
            'status' => self::$activeRuntime->getStatus(),
            'type' => self::$activeRuntime->getType(),
            'priority' => self::$activeRuntime->getPriority(),
            'creationTime' => self::$activeRuntime->getCreationTime(),
            'completionReports' => self::$activeRuntime->getExecutionReport()->all(),
            'pipeline' => self::$activeRuntime->getPipelineName(),
            'abortMessage' => self::$activeRuntime->getAbortMessage(),
            'serializableCollection' => self::$activeRuntime->getDataCollection()->all(),
        ]);

        //identify the pipeline if already saved
        $selector = new \Gishiki\Database\SelectionCriteria();
        $selector->EqualThan('uniqID', self::$activeRuntime->getUniqueID());

        if (self::$connectionHandler->Fetch(self::$tableName, $selector)->count() > 0) {
            //save the pipeline status in an already existing record
             self::$connectionHandler->Update(self::$tableName, $data, $selector);

            return;
        }

        //save the pipeline status in a new record
        self::$connectionHandler->Insert(self::$tableName, $data);
    }

    /**
     * Get the next runtime to be executed giving priority and selecting
     * asychronous only runtimes.
     * 
     * @return PipelineRuntime|null the next runtime to be executed, or null
     */
    public static function getNextAsyncByPriority()
    {
        //identify the pipeline if already saved
        $selector = new \Gishiki\Database\SelectionCriteria();
        $selector->EqualThan('type', RuntimeType::ASYNCHRONOUS)
                ->EqualThan('status', RuntimeStatus::STOPPED);

        //what is going to be restored
        $toRestore = null;

        for ($i = RuntimePriority::URGENT; ($i <= RuntimePriority::LOWEST) && (is_null($toRestore)); ++$i) {
            //change searched priority
            $prioritySelector = clone $selector;
            $prioritySelector->EqualThan('priority', $i);

            //the the collection of runtimes to be completed
            $pipelineCollectionFetched = self::$connectionHandler->Fetch(self::$tableName, $prioritySelector);
            $resultsCount = $pipelineCollectionFetched->count();

            //if there is a runtime to be restored
            if ($resultsCount > 0) {
                //extract a random one
                srand(intval(uniqid(), 16));
                $random = rand(0, $resultsCount - 1);

                $toRestore = $pipelineCollectionFetched->get($random)->GetData();
            }
        }

        if ($toRestore instanceof \Gishiki\Algorithms\Collections\CollectionInterface) {
            return self::populateRestoredPipelineRuntime($toRestore);
        }

        return;
    }

    /**
     * Forward the request to PipelineSupport.
     * 
     * @param string $uniqueID the unique ID of the PipelineRuntime
     *
     * @return PipelineRuntime the restored runtime
     *
     * @throws PipelineException         the given unique ID is not valid
     * @throws \InvalidArgumentException the unique ID is not valid or the pipeline is executing
     */
    public static function Restore($uniqueID)
    {
        if ((!is_string($uniqueID)) || (strlen($uniqueID) <= 0)) {
            throw new \InvalidArgumentException('The unique pipeline ID is not a valid string');
        }

        //identify the pipeline if already saved
        $selector = new \Gishiki\Database\SelectionCriteria();
        $selector->EqualThan('uniqID', $uniqueID);

        //check for the unique ID
        $pipelineCollectionFetched = self::$connectionHandler->Fetch(self::$tableName, $selector);
        if ($pipelineCollectionFetched->count() != 1) {
            throw new \Gishiki\Pipeline\PipelineException("The given ID doesn't identify an unique pipeline runtime", 2);
        }

        //check the status of the pipeline
        $toRestore = $pipelineCollectionFetched->get(0)->GetData();
        if ($toRestore->get('status') == RuntimeStatus::WORKING) {
            throw new \Gishiki\Pipeline\PipelineException('The given ID matches a pipeline runtime that is executed elsewhere', 3);
        }

        return self::populateRestoredPipelineRuntime($toRestore);
    }

    private static function populateRestoredPipelineRuntime(\Gishiki\Algorithms\Collections\CollectionInterface $toRestore)
    {
        //create an empty pipeline
        $pipelineClassReflection = new \ReflectionClass('Gishiki\\Pipeline\\PipelineRuntime');
        $pipelineRuntime = $pipelineClassReflection->newInstanceWithoutConstructor();
        $pipelineReflection = new \ReflectionObject($pipelineRuntime);

        $uniqueIDProperty = $pipelineReflection->getProperty('uniqCode');
        $uniqueIDProperty->setAccessible(true);
        $uniqueIDProperty->setValue($pipelineRuntime, $toRestore->get('uniqID'));

        $statusProperty = $pipelineReflection->getProperty('status');
        $statusProperty->setAccessible(true);
        $statusProperty->setValue($pipelineRuntime, $toRestore->get('status'));

        $typeProperty = $pipelineReflection->getProperty('type');
        $typeProperty->setAccessible(true);
        $typeProperty->setValue($pipelineRuntime, $toRestore->get('type'));

        $priorityProperty = $pipelineReflection->getProperty('priority');
        $priorityProperty->setAccessible(true);
        $priorityProperty->setValue($pipelineRuntime, $toRestore->get('priority'));

        $creationTimeProperty = $pipelineReflection->getProperty('creationTime');
        $creationTimeProperty->setAccessible(true);
        $creationTimeProperty->setValue($pipelineRuntime, $toRestore->get('creationTime'));

        $completionReportsProperty = $pipelineReflection->getProperty('completionReports');
        $completionReportsProperty->setAccessible(true);
        $completionReportsProperty->setValue($pipelineRuntime, $toRestore->get('completionReports'));

        $abortMessageProperty = $pipelineReflection->getProperty('abortMessage');
        $abortMessageProperty->setAccessible(true);
        $abortMessageProperty->setValue($pipelineRuntime, $toRestore->get('abortMessage'));

        $pipelineProperty = $pipelineReflection->getProperty('pipeline');
        $pipelineProperty->setAccessible(true);
        $pipelineProperty->setValue($pipelineRuntime, PipelineCollector::getPipelineByName($toRestore->get('pipeline')));

        $serializableCollectionProperty = $pipelineReflection->getProperty('serializableCollection');
        $serializableCollectionProperty->setAccessible(true);
        $serializableCollectionProperty->setValue($pipelineRuntime, new SerializableCollection($toRestore->get('serializableCollection')));

        return $pipelineRuntime;
    }
}
