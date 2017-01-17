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

namespace Gishiki\Database\Adapters;

use Gishiki\Database\RelationalDatabaseInterface;
use Gishiki\Database\DatabaseException;
use Gishiki\Database\SelectionCriteria;
use Gishiki\Database\ResultModifier;
use Gishiki\Algorithms\Collections\GenericCollection;
use Gishiki\Database\Adapters\Utils\SQLBuilder;

/**
 * Represent an sqlite database.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class Sqlite implements RelationalDatabaseInterface
{
    
    /**
     * @var boolean TRUE only if the connection is alive
     */
    private $connected;
    
    /**
     * @var \PDO the native pdo sqlite connection
     */
    private $connection;
    
    /**
     * Create a new SQLite database connection using the given connection string.
     * 
     * The connect function is automatically called.
     * 
     * @param string $details the connection string
     */
    public function __construct($details)
    {
        $this->connection = array();
        $this->connected = false;
        
        //connect to the database
        $this->connect($details);
    }

    /**
     * {@inheritdoc}
     */
    public function connect($details)
    {
        //check for argument type
        if ((!is_string($details)) || (strlen($details) <= 0)) {
            throw new \InvalidArgumentException('The connection query must be given as a non-empty string');
        }
        
        //check for the pdo driver
        if (!in_array('sqlite', \PDO::getAvailableDrivers())) {
            throw new DatabaseException("No SQLite PDO driver", 0);
        }
        
        //open the connection
        try {
            $this->connection = new \PDO('sqlite:'.$details);
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            //the connection is opened
            $this->connected = true;
        } catch (\PDOException $ex) {
            throw new DatabaseException("Error while opening the database connection:".$ex->getMessage(), 1);
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function connected()
    {
        return $this->connected;
    }
    
    /**
     * {@inheritdoc}
     */
    public function create($collection, $data)
    {
        //check for invalid database name
        if ((!is_string($collection)) || (strlen($collection) <= 0)) {
            throw new \InvalidArgumentException('The name of the table must be given as a non-empty string');
        }
        
        //check for invalid data collection
        if ((!is_array($data)) && (!($data instanceof CollectionInterface))) {
            throw new \InvalidArgumentException('The data to be written on the database must be given as a collection');
        }
        
        //check for closed database connection
        if (!$this->connected()) {
            throw new DatabaseException('The database connection must be opened before executing any operation', 2);
        }
        
        //get an associative array of the input data
        $adaptedData = ($data instanceof GenericCollection) ? $data->all() : $data;
        
        //build the sql query
        $queryBuilder = new SQLBuilder();
        $queryBuilder->insertInto($collection)->values($adaptedData);
        
        //open a new statement and execute it
        try {
            //prepare a statement with that safe sql string
            $stmt = $this->connection->prepare($queryBuilder->exportQuery());

            //execute the statement resolving placeholders
            $stmt->execute($queryBuilder->exportParams());
        } catch (\PDOException $ex) {
            throw new DatabaseException('Error while performing the creation operation: '.$ex->getMessage(), 3);
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function update($collection, $data, SelectionCriteria $where)
    {
        //check for invalid database name
        if ((!is_string($collection)) || (strlen($collection) <= 0)) {
            throw new \InvalidArgumentException('The name of the table must be given as a non-empty string');
        }
        
        //check for invalid data collection
        if ((!is_array($data)) && (!($data instanceof CollectionInterface))) {
            throw new \InvalidArgumentException('The data to be written on the database must be given as a collection');
        }
        
        //check for closed database connection
        if (!$this->connected()) {
            throw new DatabaseException('The database connection must be opened before executing any operation', 2);
        }
        
        //get an associative array of the input data
        $adaptedData = ($data instanceof GenericCollection) ? $data->all() : $data;
        
        //build the sql query
        $queryBuilder = new SQLBuilder();
        $queryBuilder->update($collection)->set($adaptedData)->where($where);
        
        //open a new statement and execute it
        try {
            //prepare a statement with that safe sql string
            $sql = $queryBuilder->exportQuery();
            $stmt = $this->connection->prepare($sql);

            //execute the statement resolving placeholders
            $stmt->execute($queryBuilder->exportParams());
        } catch (\PDOException $ex) {
            throw new DatabaseException('Error while performing the update operation: '.$ex->getMessage(), 4);
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function delete($collection, SelectionCriteria $where)
    {
        //check for invalid database name
        if ((!is_string($collection)) || (strlen($collection) <= 0)) {
            throw new \InvalidArgumentException('The name of the table must be given as a non-empty string');
        }
        
        //check for closed database connection
        if (!$this->connected()) {
            throw new DatabaseException('The database connection must be opened before executing any operation', 2);
        }
        
        //build the sql query
        $queryBuilder = new SQLBuilder();
        $queryBuilder->deleteFrom($collection)->where($where);
        
        //open a new statement and execute it
        try {
            //prepare a statement with that safe sql string
            $stmt = $this->connection->prepare($queryBuilder->exportQuery());

            //execute the statement resolving placeholders
            $stmt->execute($queryBuilder->exportParams());
        } catch (\PDOException $ex) {
            throw new DatabaseException('Error while performing the delete operation: '.$ex->getMessage(), 5);
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function deleteAll($collection) {
        //check for invalid database name
        if ((!is_string($collection)) || (strlen($collection) <= 0)) {
            throw new \InvalidArgumentException('The name of the table must be given as a non-empty string');
        }
        
        //check for closed database connection
        if (!$this->connected()) {
            throw new DatabaseException('The database connection must be opened before executing any operation', 2);
        }
        
        //build the sql query
        $queryBuilder = new SQLBuilder();
        $queryBuilder->deleteFrom($collection);
        
        //open a new statement and execute it
        try {
            //prepare a statement with that safe sql string
            $stmt = $this->connection->prepare($queryBuilder->exportQuery());

            //execute the statement resolving placeholders
            $stmt->execute($queryBuilder->exportParams());
        } catch (\PDOException $ex) {
            throw new DatabaseException('Error while performing the delete operation: '.$ex->getMessage(), 5);
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function read($collection, SelectionCriteria $where, ResultModifier $mod)
    {
        //check for invalid database name
        if ((!is_string($collection)) || (strlen($collection) <= 0)) {
            throw new \InvalidArgumentException('The name of the table must be given as a non-empty string');
        }
        
        //check for closed database connection
        if (!$this->connected()) {
            throw new DatabaseException('The database connection must be opened before executing any operation', 2);
        }
        
        //build the sql query
        $queryBuilder = new SQLBuilder();
        $queryBuilder->selectAllFrom($collection)->where($where)->limitOffsetOrderBy($mod);
        
        //open a new statement and execute it
        try {
            //prepare a statement with that safe sql string
            $stmt = $this->connection->prepare($queryBuilder->exportQuery());

            //execute the statement resolving placeholders
            $stmt->execute($queryBuilder->exportParams());
            
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return $results;
        } catch (\PDOException $ex) {
            throw new DatabaseException('Error while performing the read operation: '.$ex->getMessage(), 6);
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function readSelective($collection, $fields, SelectionCriteria $where, ResultModifier $mod)
    {
        //check for invalid database name
        if ((!is_string($collection)) || (strlen($collection) <= 0)) {
            throw new \InvalidArgumentException('The name of the table must be given as a non-empty string');
        }
        
        //check for closed database connection
        if (!$this->connected()) {
            throw new DatabaseException('The database connection must be opened before executing any operation', 2);
        }
        
        //build the sql query
        $queryBuilder = new SQLBuilder();
        $queryBuilder->selectFrom($collection, $fields)->where($where)->limitOffsetOrderBy($mod);
        
        //open a new statement and execute it
        try {
            //prepare a statement with that safe sql string
            $stmt = $this->connection->prepare($queryBuilder->exportQuery());

            //execute the statement resolving placeholders
            $stmt->execute($queryBuilder->exportParams());
            
            //return the fetch result
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $ex) {
            throw new DatabaseException('Error while performing the read operation: '.$ex->getMessage(), 6);
        }
    }
}
