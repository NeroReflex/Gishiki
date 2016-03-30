<?php
/**************************************************************************
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

/**
 * The generic database manager class
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class Database {
    //the type of the opened connection
    private $databaseType;
    
    //the database connection
    private $connectionHandler;
    
    //connection details
    private $connectionDetails;
    
    /**
     * Create an empty database instance without connecting it
     *
     */
    public function __construct() {
        //setup an empty connection
        $this->connectionHandler = NULL;
        $this->databaseType = NULL;
        $this->connectionDetails = NULL;
    }

    /**
     * Check the status of the connection and return true if the connection is 
     * active, false otherwise
     * 
     * @return boolean true if the connection is currently opened
     */
    public function CheckConnection() {
	//check if the connection is open
        return ($this->connectionHandler != NULL) && (($this->connectionDetails != NULL));
    }
    
    /**
     * Use a string to open a database connection
     * 
     * @param string $connectionString the connection string
     * @throws DatabaseException the exception occurred while opening the database
     */
    public function CreateConnection($connectionString) {
        //parse the connection string
        $this->connectionDetails = ConnectionString::Parse($connectionString);
        
        //use the parsed result to open a connection
        $this->connectionHandler = DatabaseConnector::Connect($this->connectionDetails, $this->databaseType);
    }
    
    /**
     * Open a database connection from a previously stored one
     * 
     * @param string $connectionName the name of the connection (without path and extension)
     * @throws DatabaseException the exception occurred while opening the database
     */
    public function UseConnection($connectionName = "global") {
        //try loading an XML-stored connection
        $this->connectionDetails = XMLConnection::Parse($connectionName);
        
        //use the parsed result to open a connection
        $this->connectionHandler = DatabaseConnector::Connect($this->connectionDetails, $this->databaseType);
    }
    
    /**
     * Store the currently available database connection to a file 
     * @param string $connectionName the name of the connection (without extension)
     */
    public function SaveConnection($connectionName) {
        if ($this->CheckConnection()) {
            XMLConnection::Store($connectionName, $this->connectionDetails);
        } else {
            throw new DatabaseException("The database connection cannot be stored because the connection is closed", -8);
        }
    }
    
    /**
     * Create a table into the currently opened database to correctly store the
     * given associative array
     * 
     * @param string $tableName the name of the table to affect
     * @param array $data the associative array that relates the index (table column name) with data (column data)
     * @throws DatabaseException the exception while executing the query
     */
    private function CreateTableForData($tableName, &$data) {    
        //start building the table (because it might not exists)
        $columnDefinition = array();
                
        /*     create the table structure     */
                
        //get the number of affected columns
        $columnNumbers = count($data);
                
        //cycle each element from the first one
        reset($data);

        for ($counter = 0; $counter < $columnNumbers; $counter++)
        {
            $dataType = gettype(current($data));
            $filteredDataType = ColumnType::TEXT;
                
            if ($dataType == "integer") {
                $filteredDataType = ColumnType::INTEGER;
            } else if ($dataType == "double") {
                $filteredDataType = ColumnType::REAL;
            }
                    
            $columnDefinition[key($data)] = $filteredDataType;

            //jump to the next serialized field
            next($data);
        }
            
        //prepare the creation table query
        $ctquery = QueryCreator::PrepareForTableCreation($tableName, $columnDefinition, $this->databaseType);
            
        //initialize an invalid statement
        $stmt = FALSE;
        
        if ($this->databaseType == DatabaseType::SQLite3)
        {
            //start creating the table
            @$stmt = $this->connectionHandler->prepare($ctquery);
        } else {
            //shutdown autocommit mode
            $this->connectionHandler->beginTransaction();
            
            //prepare the statement
            @$stmt = $this->connectionHandler->prepare($ctquery);
        }
        
        //check for the statement
        if ($stmt == FALSE) {
            if ($this->databaseType == DatabaseType::SQLite3) {
                throw new DatabaseException($this->connectionHandler->lastErrorMsg(), $this->connectionHandler->lastErrorCode());
            } else {
				$errorInfo = $stmt->errorInfo();
                throw new DatabaseException($errorInfo[2], intval($this->connectionHandler->errorCode()));
            }
        }
            
        //try to create the needed table to store the serialized data
        
         //initialize an empty result
        $executionResult = FALSE;
        //that won't crash the script if
        //the statement execution fails

        //execute the statement
        if ($this->databaseType == DatabaseType::SQLite3) {
            //execute the statement
            $executionResult = $stmt->execute();
        } else {
            //execute the statement
            $executionResult = $stmt->execute();
            
            //commit changes to the database
            $this->connectionHandler->commit();
            
            //close the cursor to avoid bad things
            $stmt->closeCursor();
        }
            
        //execute the statement
        if ($executionResult == FALSE) {
            if ($this->databaseType == DatabaseType::SQLite3) {
                throw new DatabaseException("The table cannot be created due to an internal database error: ".$this->connectionHandler->lastErrorMsg(), $this->connectionHandler->lastErrorCode());
            } else {
				$errorinfo = $stmt->errorInfo();
                throw new DatabaseException($errorinfo[2], intval($this->connectionHandler->errorCode()));
            }
        }
    }
    
    /**
     * Insert into the currently opened database the given associative array
     * 
     * @param string $tableName the name of the table to affect
     * @param array $data the associative array that relates the index (table column name) with data (column data)
     * @throws DatabaseException the exception while executing the query
     */
    private function InsertData($tableName, &$data) {
        //table MUST already exists

        //LOOK FOR THE TABLE STRUCTURE AND ITS COLUMN AND DATATYPE
            
        /*          insert data             */
        
        
        //prepare the query
        $query = QueryCreator::PrepareForDataInsertion($tableName, $data, $this->databaseType);
        
        //initialize an invalid statement
        $stmt = FALSE;
           
        //prepare the statement for the query execution
        if ($this->databaseType == DatabaseType::SQLite3) {
            @$stmt = $this->connectionHandler->prepare($query);
        } else {
            //shutdown autocommit mode
            $this->connectionHandler->beginTransaction();
            
            //prepare the statement
            @$stmt = $this->connectionHandler->prepare($query);
        }
        
        //check for the statement
        if ($stmt == FALSE) {
            if ($this->databaseType == DatabaseType::SQLite3) {
                throw new DatabaseException($this->connectionHandler->lastErrorMsg(), $this->connectionHandler->lastErrorCode());
            } else {
				$errorinfo = $stmt->errorInfo();
                throw new DatabaseException($errorinfo[2], intval($this->connectionHandler->errorCode()));
            }
        }
        
        //bind each clausole
        QueryCreator::FinalizeStatementPreparation($data, $stmt, $this->databaseType);
            
        //initialize an empty result
        $executionResult = FALSE;
        //that won't crash the script if
        //the statement execution fails

        //execute the statement
        if ($this->databaseType == DatabaseType::SQLite3) {
            //execute the statement
            $executionResult = $stmt->execute();
        } else {
            //execute the statement
            $executionResult = $stmt->execute();
            
            //commit changes to the database
            $this->connectionHandler->commit();
            
            //close the cursor to avoid bad things
            $stmt->closeCursor();
        }
            
        //execute the statement
        if ($executionResult == FALSE) {
            if ($this->databaseType == DatabaseType::SQLite3) {
                throw new DatabaseException($this->connectionHandler->lastErrorMsg(), $this->connectionHandler->lastErrorCode());
            } else {
				$errorinfo = $stmt->errorInfo();
                throw new DatabaseException($errorinfo[2], intval($this->connectionHandler->errorCode()));
            }
        }
    }
    
    /**
     * Retrive data from the database using the given filter/clauses
     * 
     * @param string $tableName the name of the table
     * @param Clauses $clauses the filter to be applied
     * @return array an associative array with multiples arrays (one result => one array)
     * @throws DatabaseException the error that prevent the execution of the operation
     */
    private function FetchData($tableName, Clauses &$clauses) {
        //create the query
        $query = QueryCreator::PrepareForFetching($tableName, $clauses, $this->databaseType);

        //initialize an invalid statement
        $stmt = FALSE;
        
        if ($this->databaseType == DatabaseType::SQLite3)
        {
            //prepare the statement for the query execution
            @$stmt = $this->connectionHandler->prepare($query);
        } else {
            //prepare the statement
            @$stmt = $this->connectionHandler->prepare($query);
        }
        
        //check for the statement
        if ($stmt == FALSE) {
            if ($this->databaseType == DatabaseType::SQLite3) {
                $lastError = $this->connectionHandler->lastErrorMsg();
                if (strpos($lastError, "no such table") === FALSE) {
                    throw new DatabaseException($lastError, $this->connectionHandler->lastErrorCode());
                } else {
                    return NULL;
                }
            } else {
                $lastError = $stmt->errorInfo();
                if (strpos($lastError, "no such table") === FALSE) {
                    $errorinfo = $stmt->errorInfo();
                    throw new DatabaseException($errorinfo[2], intval($this->connectionHandler->errorCode()));
                } else {
                    return NULL;
                }
            }
        }
                
        //bind values to the query
        QueryCreator::FinalizeStatementPreparationWithClauses($clauses, $stmt, $this->databaseType);
            
        //initialize an empty result
        $executionResult = FALSE;
        //that won't crash the script if
        //the statement execution fails
            
        if ($this->databaseType == DatabaseType::SQLite3) {
            //execute the statement
            $executionResult = $stmt->execute();
        } else {
            //execute the statement
            $executionResult = $stmt->execute();
            
            //the cursor will be closed later
        }
            
        //check for the statement to be executed correctly
        if ($executionResult == FALSE) {
            if ($this->databaseType == DatabaseType::SQLite3) {
                throw new DatabaseException($this->connectionHandler->lastErrorMsg(), $this->connectionHandler->lastErrorCode());
            } else {
                $errorinfo = $stmt->errorInfo();
                throw new DatabaseException($errorinfo[2], intval($this->connectionHandler->errorCode()));
            }
        }
        
        //start building the result
        $result = array();
                
        //the number of fetched rows
        $nrows = 0;
        
        //retrive the number of rows fetched and the data
        if ($this->databaseType == DatabaseType::SQLite3) {
            $executionResult->reset();

            //while building the result cycling each row retrived from the database
            while ($currentResult = $executionResult->fetchArray(SQLITE3_ASSOC)) {
                $result[] = $currentResult;
                $nrows++;
            }
        } else {
            while ($currentResult = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $result[] = $currentResult;
                $nrows++;
            }
            
            //close the cursor to avoid bad things
            $stmt->closeCursor();
        }
                
        //register the number of of fetched results
        $result["numberOfResults"] = $nrows;
                
        //return the result
        return $result;
    }
    
    /**
     * Continue to build the stack used to deserialize an object. This function 
     * is important because it allows an object to be fully deserialized,
     * even if the object contains other objects that consains other objects and
     * so on.... 
     * 
     * @param Stack $stack the serialization stack to be completed
     * @throws DatabaseException the error occurred while building the serialization stack
     */
    private function CompleteDeserializationStack(Stack &$stack, $serializedObject = NULL) {
        if (gettype($serializedObject) == "NULL") {
            $backupOfObjectID = $stack->POP();
            $serializedObject = $stack->TOP();
            $stack->PUSH($backupOfObjectID);
        }
		
        //get the number of class property
        $serObjLen = count($serializedObject);
            
        //stat cycling from the fist one
        reset($serializedObject);
            
        for ($i = 0; $i < $serObjLen; $i++) {
            /* $currentlyExamitatingPropertyName = key($serializedObject); */
            $currentlyExamitatingPropertyValue = current($serializedObject);
            if (gettype($currentlyExamitatingPropertyValue) == "string") {
                $data = explode(":", $currentlyExamitatingPropertyValue);
                    
                //check if the current class property is an object
                if ($data[0] == "obj") {
                    $objClassName = $data[1];
                    $objDescriptorID = $data[2];
                        
                    //build the fetchClauses
                    $clauses = new Clauses();
                    $clauses->AddClause("ObjectDescriptor", Criteria::EQUAL, $objClassName.":".$objDescriptorID);
                        
                    $result = $this->FetchData($objClassName, $clauses);
                    if ($result["numberOfResults"] == 1) {
                        $this->CompleteDeserializationStack($stack, $result[0]);
                        $stack->PUSH($result[0]);
                    } else {
                        throw new DatabaseException("The object is damaged, probably one or more of its property are objects that were deleted", -41);
                    }
                }
             }
                
            //jump over the next element
            next($serializedObject);
        }
    }
    
    /**
     * Restore one or more objects from the database. If one object is found then
     * one object is returned. If two or more objects are found an array of object is returned.
     * If no objects are found NULL is returned
     * 
     * @param string $className the name of the class the object was instanced from, or an instance of that class
     * @param Clauses $criteria the filled criteria used to select the data to retrive
     * @return anytype the fetched object, array of objects or null
     * @throws DatabaseException the error that prevent the function to operate correctly
     */
    public function Restore($className, Clauses &$criteria) {
        //check if the connection is open
        if ($this->CheckConnection()) {
            //get the name of the class 
            if (is_object($className)) {
                $className = get_class($className);
            }

            //check if the class to exists
            if (class_exists($className)) {
                //retrive the list of object to build
                $result = $this->FetchData($className, $criteria);
                
                if ($result["numberOfResults"] > 1) {
                    //start building the list of stacks
                    $deserializedObjectsArray = array();
                    
                    for ($counter = 0; $counter < $result["numberOfResults"]; $counter++) {
                        //create a stack of serialized objects
                        $serializedObjectstack = new Stack();
                        
                        //fill the serialized stack
                        $serializedObjectstack->PUSH($result[$counter]);
                        $serializedObjectstack->PUSH("obj:".$result[$counter]["ObjectDescriptor"]);
                        
                        /*          insert into the stack every serialized object      */
                        //it is important to scan each field to retrive every object
                        //inside the current object in order to deserialize the result
                        $this->CompleteDeserializationStack($serializedObjectstack);
                        
                        //when deserializing the stack will be reverted, but it
                        //already is in the correct order
                        $serializedObjectstack->Revert();
                        //and a double reversion ends up in a null operation
                        
                        //save the stack
                        $deserializedObjectsArray[] = Serialization::DeserializeOject($serializedObjectstack);
                    }
                    
                    //return the resulting array
                    return $deserializedObjectsArray;
                } else if ($result["numberOfResults"] == 1) {
                        //create a stack of serialized objects
                        $serializedObjectstack = new Stack();
                        
                        //fill the serialized stack
                        $serializedObjectstack->PUSH($result[0]);
                        $serializedObjectstack->PUSH("obj:".$result[0]["ObjectDescriptor"]);
                        
                        /*          insert into the stack every serialized object      */
                        //it is important to scan each field to retrive every object
                        //inside the current object in order to deserialize the result
                        $this->CompleteDeserializationStack($serializedObjectstack);
                        
                        //when deserializing the stack will be reverted, but it
                        //already is in the correct order
                        $serializedObjectstack->Revert();
                        //and a double reversion ends up in a null operation
                        
                        //return the deserialized object
                        return Serialization::DeserializeOject($serializedObjectstack);
                } else {
                    //if no object are found return null
                    return NULL;
                }
            } else {
                throw new DatabaseException("The given class name doesn't exists in the current context", -40);
            }
        } else {
            throw new DatabaseException("The operation cannot be performed because the database connection is closed", -9);
        }
    }
    
    /**
     * Serialize and store an object into the database, if not found this function
     * create the table and necessary columns.
     * 
     * @param object $object the object to be stored on the database
     * @throws DatabaseException the error that prevent the function to operate correctly
     */
    public function Store($object) {
        //check if the connection can be used
        if ($this->CheckConnection())
        {
            //setup a new stack structure
            $stack = new Stack();

            //serialize the object and the contained objects recursively
            Serialization::SerializeObject($object, $stack);

            /*      reversed Stack structure:
             * 
             *      object to be stored
             *      object to be stored
             *      object to be stored
             *      object to be stored
             *      object to be stored
             *      object to be stored
             * 
             *      name of the serialized object
             * 
             *      serialized object
             */

            //the last object retrived from the stack
            $lastStackObject = NULL;

            while (!$stack->IsEmpty()) {
                //retrive the object or the object id from the stack
                $lastStackObject = $stack->POP();

                //get the tyoe of the object retrived from the stack
                $lastStackObjectType = gettype($lastStackObject);

                //if it is a string prepare to return it
                if ($lastStackObjectType == "string") {
                    $serializedObjectID = $lastStackObject;
                } else { //else
                    //get the serialized class name
                    $exploded = explode(":", $lastStackObject["ObjectDescriptor"]);
                    $objectClassName = $exploded[0];
                    
                    //build the table structure
                    $this->CreateTableForData($objectClassName, $lastStackObject);
                    
                    //check if the given object (or one exact copy) was already stored
                    $selection = new Clauses();
                    $selection->AddClause("ObjectDescriptor", Criteria::EQUAL, $lastStackObject["ObjectDescriptor"]);
                    /*$serachResult = array("numberOfResults" => 0);
                    try {*/
                        $serachResult = $this->FetchData($objectClassName, $selection);
                    /*} catch (DatabaseException $ex) {
                        //do nothing, probably the table doesn't exists.....
                    }
                    */
                    if ($serachResult["numberOfResults"] == 0) {
                        //and....... if the given object does NOT exists in the database...
                        //....store the serialized that object in a table that is
                        //called as the class name is
                        $this->InsertData($objectClassName, $lastStackObject);
                    }
                }
            }

            //return the serialized object unique ID
            return $serializedObjectID;
        } else {
            throw new DatabaseException("The Store operation cannot be executed because the database connection is closed", -11);
        }
    }
    
    /**
     * Delete one or more objects from the database.
     * 
     * @param string $className the name of the class the object was instanced from, or an instance of that class
     * @param Clauses $clauses the filled criteria used to select the data to be deleted
     * @return integer the number of removed elements
     * @throws DatabaseException the error that prevent the function to operate correctly
     */
    public function Remove($className, Clauses &$clauses) {
        //get the name of the class 
        if (is_object($className)) {
            $className = get_class($className);
        }

        //check if the class to exists
        if (!class_exists($className)) {
            throw new DatabaseException("The given class name doesn't exists in the current context", -40);
        }
        
        //check if the connection can be used
        if ($this->CheckConnection())
        {
            //this will hold the number of removed objects
            $removedItems = 0;
            
            //generate the query
            $deletionQuery = QueryCreator::PrepareForDataDeletion($className, $clauses, $this->databaseType);
            
            //initialize an invalid statement
            $stmt = FALSE;
            
            //use the best way to perform the operation
            if ($this->databaseType == DatabaseType::SQLite3) {
                //prepare the statement for the query execution
                @$stmt = $this->connectionHandler->prepare($deletionQuery);
            } else {
                //shutdown autocommit mode
                $this->connectionHandler->beginTransaction();

                //prepare the statement
                @$stmt = $this->connectionHandler->prepare($deletionQuery);
            }
            
            //check for the statement
            if ($stmt == FALSE) {
                if ($this->databaseType == DatabaseType::SQLite3) {
                    $lastError = $this->connectionHandler->lastErrorMsg();
                    if (strpos($lastError, "no such table") === FALSE) {
                        throw new DatabaseException($lastError, $this->connectionHandler->lastErrorCode());
                    } else {
                        return 0;
                    }
                } else {
                    $errorinfo = $stmt->errorInfo();
                    $lastError = $errorinfo[2];
                    if (strpos($lastError, "no such table") === FALSE) {
                        throw new DatabaseException($lastError, intval($this->connectionHandler->errorCode()));
                    } else {
                        return 0;
                    }
                }
            }
                
            //bind values to the query
            QueryCreator::FinalizeStatementPreparationWithClauses($clauses, $stmt, $this->databaseType);
            
            //initialize an empty result
            $executionResult = FALSE;
            //that won't crash the script if
            //the statement execution fails
            
            //execute the statement
            if ($this->databaseType == DatabaseType::SQLite3) {
                //execute the SQLite3Stmt statement
                $executionResult = $stmt->execute();
            } else {
                //execute the statement
                $executionResult = $stmt->execute();
            }

            //check for the statement to be executed correctly
            if ($executionResult == FALSE) {
                if ($this->databaseType == DatabaseType::SQLite3) {
                    throw new DatabaseException($this->connectionHandler->lastErrorMsg(), $this->connectionHandler->lastErrorCode());
                } else {
					$errorinfo = $stmt->errorInfo();
                    throw new DatabaseException($errorinfo[2], intval($this->connectionHandler->errorCode()));
                }
            }
                
            //close the statement and get the number of deleted rows
            if ($this->databaseType == DatabaseType::SQLite3) {
                //finalize the result
                $executionResult->finalize();

                //close the statement
                $stmt->close();

                //get the number of deleted items
                $removedItems = $this->connectionHandler->changes();
            } else {
                //commit changes to the database
                $this->connectionHandler->commit();

                //close the cursor to avoid bad things
                $stmt->closeCursor();
                
                //get the number of deleted items
                $removedItems = $stmt->rowCount();
            }
            
            //return the number of removed items
            return $removedItems;
        } else {
            throw new DatabaseException("The Store operation cannot be executed because the database connection is closed", -11);
        }
    }
    
    /**
     * Close the currently opened database connection (if there is an open connection)
     * 
     */
    public function CloseConnection() {
        //close the connection  if it is open
        if ($this->CheckConnection())
        {
            if ($this->databaseType == DatabaseType::SQLite3) {
                $this->connectionHandler->close();
                $this->connectionHandler= NULL;
            }
            
            //the connection is now closed and invalid
            $this->databaseType = NULL;
        }
    }
    
    /**
     * Close the database connection if it is still opened
     */
    public function __destruct() {
        //close the connection
        $this->CloseConnection();
    }
}
