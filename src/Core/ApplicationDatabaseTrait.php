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

namespace Gishiki\Core;

use Gishiki\Database\DatabaseException;
use Gishiki\Database\DatabaseManager;
/**
 * This is a working implementation of database connections handler for the Application class.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
trait ApplicationDatabaseTrait
{
    /**
     * @var DatabaseManager the group of database connections
     */
    protected $databaseConnections;

    /**
     * Get the collection of opened database handlers within the current application.
     *
     * @return DatabaseManager the collection of database handlers
     */
    public function &getDatabaseManager() : DatabaseManager
    {
        return $this->databaseConnections;
    }

    /**
     * Initialize the application internal database handler
     */
    private function initializeDatabaseHandler()
    {
        if (!$this->isInitializedDatabaseHandler()) {
            //setup the database manager
            $this->databaseConnections = new DatabaseManager();
        }
    }

    /**
     * Check if the database handler has been initialized.
     *
     * @return bool true if the database handler is initialized
     */
    private function isInitializedDatabaseHandler() : bool
    {
        return !is_null($this->databaseConnections);
    }

    /**
     * Prepare connections to databases.
     *
     * @param array $connections the array of connections
     * @throws DatabaseException error while connecting the database
     */
    public function connectDatabase(array $connections)
    {
        if (!$this->isInitializedDatabaseHandler()) {
            $this->initializeDatabaseHandler();
        }

        //connect every db connection
        foreach ($connections as $connection) {
            $this->databaseConnections->connect($connection['name'], $connection['query']);
        }
    }
}
