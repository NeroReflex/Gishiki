<?php
/****************************************************************************
  Copyright 2015 Benato Denis

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

namespace Gishiki\Database {

    /**
     * A simple database manager used to manage the database connection 
     * related to the current environment
     *
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    class Database {
        
        /** the handler of the database */
        private $databaseHandler;
        
        /** the database type */
        private $databaseType;
        
        /**
         * Create an instance of the Gishiki database helper
         * 
         * @param string $connectionString a connection string (read specs)
         */
        public function __construct($connectionString = "")
        {
            //initialize an ampty database handler
            $this->databaseHandler = [];
            
            //check if a connection string was given (and should be used to execute the connection)
            if (gettype($connectionString) == "string") {
                if (strlen($connectionString) > 0) {
                    $this->Connect($connectionString);
                }
            }
        }
        
        /**
         * Check the availability of the database connection 
         * 
         * @return boolean TRUE if a connection is alive, FALSE otherwise
         */
        public function IsConnected() {
            return (($this->databaseHandler !== "NULL") && (count($this->databaseHandler) > 0));
        }
        
        /**
         * Connect the current instance of database to a real database using a 
         * connection string. The string must be conformant to the documentation
         * 
         * @param string $connectionString the string that identify the database to connect
         * @throws DatabaseException the error encountered while performing the requested operation
         */
        public function Connect($connectionString) {
            //parse the connection string
            $connectionDetails = ConnectionString::Parse($connectionString);
            
            if ($connectionDetails["database_type"] == "mongo") {
                //store the database type
                $this->databaseType = DatabaseType::MongoDB;
                
                //try performing a MongoDB connection
                try {
                    //build the connection string
                    $this->databaseHandler["connecting_string"] = sprintf('mongodb://%s:%s@%s:%d', $connectionDetails["userName"], $connectionDetails["userPassword"], $connectionDetails["dbHost"], intval($connectionDetails["dbHostPort"]));
                    
                    //use the connection string to perform a connenction to the given host
                    $this->databaseHandler["connection"] = new \MongoClient($this->databaseHandler["connecting_string"], ['journal' => true]);
                    
                    //select the database
                    $this->databaseHandler["database"] = $this->databaseHandler["connection"]->selectDB($connectionDetails["dbName"]);
                } catch (\MongoConnectionException $nativeEx) {
                    throw new DatabaseException("Error while connecting to the given MongoDB host. MongoDB reports: ".$nativeEx->getMessage(), 0);
                }
            } else {
                //store the database type
                $this->databaseType = DatabaseType::SQL;

                //try performing a SQL connection using medoo
                try {
                    //use the connection string to perform a connenction to the given host
                    $this->databaseHandler["database"] = new \medoo($connectionDetails);
                } catch (\Exception $nativeEx) {
                    throw new DatabaseException("Error while connecting to the given database. Database reports: ".$nativeEx->getMessage(), 0);
                }
            }
        }
        
        /**
         * Insert into a given table (or collection) a set of data. It is developer's 
         * work to check the table exists or that the DBMS creates it automatically
         * 
         * @param string $collectionName the name of collection/table of the database
         * @param array $collectionData the serilized and structured/schemed data to be saved
         * @throws DatabaseException the error encountered while performing the requested operation
         */
        public function Insert($collectionName, &$collectionData) {
            if ($this->IsConnected()) {
                if ($this->databaseType == DatabaseType::MongoDB) {
                    try {
                        //select the collection with the given name from the database
                        $currentCollection = $this->databaseHandler["database"]->selectCollection($collectionName);
                        
                        //insert the data inside the collection
                        $currentCollection->insert($collectionData);
                    } catch (\MongoException $nativeEx) {
                        throw new DatabaseException("Error while inserting data to the database: ".$nativeEx->getMessage(), 3);
                    }
                } else if ($this->databaseType == DatabaseType::SQL) {
                    try {
                        //insert the data inside the collection
                        $this->databaseHandler["database"]->insert($collectionName, $collectionData);
                    } catch (\Exception $nativeEx) {
                        throw new DatabaseException("Error while inserting data to the database: ".$nativeEx->getMessage(), 3);
                    }
                } else {
                    throw new DatabaseException("Unsupported database type", 2);
                }
            } else {
                throw new DatabaseException("Error while inserting data to the database: the database connection is closed", 1);
            }
        }
        
        /**
         * Remove one or more database entries. What will be deleted is everything that
         * matches the given criteria and is stored inside the given table/collection
         * 
         * @param string $collectionName the name of collection/table of the database
         * @param array $collectionDataCriteria the collection to be deleted (everything that matches the criteria will be removed)
         * @throws DatabaseException the error encountered while performing the requested operation
         */
        public function Remove($collectionName, &$collectionDataCriteria) {
            if ($this->IsConnected()) {
                if ($this->databaseType == DatabaseType::MongoDB) {
                    try {
                        //select the collection with the given name from the database
                        $currentCollection = $this->databaseHandler["database"]->selectCollection($collectionName);
                        
                        //remove data inside the collection
                        $currentCollection->remove($collectionDataCriteria);
                    } catch (\MongoException $nativeEx) {
                        throw new DatabaseException("Error while removing data from the database: ".$nativeEx->getMessage(), 4);
                    }
                } else if ($this->databaseType == DatabaseType::SQL) {
                    try {
                        //remove data inside the collection
                        $this->databaseHandler["database"]->delete($collectionName, $collectionDataCriteria);
                    } catch (\Exception $nativeEx) {
                        throw new DatabaseException("Error while inserting data to the database: ".$nativeEx->getMessage(), 3);
                    }
                } else {
                    throw new DatabaseException("Unsupported database type", 2);
                }
            } else {
                throw new DatabaseException("Error while removing data from the database: the database connection is closed", 5);
            }
        }
        
        /**
         * Update one or more database entries. What will be updated is everything that
         * matches the given criteria and is stored inside the given table/collection
         * 
         * @param string $collectionName the name of collection/table of the database
         * @param array $collectionData the serilized and structured/schemed data to be saved replacing the old one
         * @param array $collectionDataCriteria the collection to be updated (everything that matches the criteria will be removed)
         * @throws DatabaseException the error encountered while performing the requested operation
         */
        public function Update($collectionName, &$collectionData, &$collectionDataCriteria) {
            if ($this->IsConnected()) {
                if ($this->databaseType == DatabaseType::MongoDB) {
                    try {
                        //select the collection with the given name from the database
                        $currentCollection = $this->databaseHandler["database"]->selectCollection($collectionName);
                        
                        //insert the data inside the collection
                        $currentCollection->update($collectionDataCriteria, $collectionData);
                    } catch (\MongoException $nativeEx) {
                        throw new DatabaseException("Error while updating data on the database: ".$nativeEx->getMessage(), 6);
                    }
                } else if ($this->databaseType == DatabaseType::SQL) {
                    try {
                        //update data inside the collection
                        return $this->databaseHandler["database"]->update($collectionName, $collectionData, $collectionDataCriteria);
                    } catch (\Exception $nativeEx) {
                        throw new DatabaseException("Error while updating data on the database: ".$nativeEx->getMessage(), 6);
                    }
                } else {
                    throw new DatabaseException("Unsupported database type", 2);
                }
            } else {
                throw new DatabaseException("Error while updating data on the database: the database connection is closed", 7);
            }
        }
        
        /**
         * Fetch one or more database entries. What will be retrived is everything that
         * matches the given criteria and is stored inside the given table/collection
         * 
         * @param string $collectionName the name of collection/table of the database
         * @param array $collectionDataCriteria the collection to be updated (everything that matches the criteria will be returned)
         * @return array an array of everything that matched the given criteria
         * @throws DatabaseException the error encountered while performing the requested operation
         */
        public function Fetch($collectionName, &$collectionDataCriteria) {
            if ($this->IsConnected()) {
                if ($this->databaseType == DatabaseType::MongoDB) {
                    try {
                        //select the collection with the given name from the database
                        $currentCollection = $this->databaseHandler["database"]->selectCollection($collectionName);
                        
                        //fetch the data inside the collection
                        return $currentCollection->find($collectionDataCriteria);
                    } catch (\MongoException $nativeEx) {
                        throw new DatabaseException("Error while fetching data from the database: ".$nativeEx->getMessage(), 8);
                    }
                } else if ($this->databaseType == DatabaseType::SQL) {
                    try {
                        //fetch data inside the collection
                        return $this->databaseHandler["database"]->select($collectionName, "*", $collectionDataCriteria);
                    } catch (\Exception $nativeEx) {
                        throw new DatabaseException("Error while fetching data from the database: ".$nativeEx->getMessage(), 8);
                    }
                }  else {
                    throw new DatabaseException("Unsupported database type", 2);
                }
            } else {
                throw new DatabaseException("Error while fetching data from the database: the database connection is closed", 9);
            }
        }
    }
}
