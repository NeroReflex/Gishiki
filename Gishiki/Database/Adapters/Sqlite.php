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

use Gishiki\Database\DatabaseInterface;
use Gishiki\Database\DatabaseException;
use Gishiki\Database\SelectionCriteria;
use Gishiki\Database\ResultModifier;
use Gishiki\Algorithms\Collections\GenericCollection;

/**
 * Represent an sqlite database.
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class Sqlite implements DatabaseInterface {
    
    /**
     * @var boolean TRUE only if the connection is alive
     */
    private $connected;
    
    /**
     * @var \PDO the native pdo sqlite connection
     */
    private $connection;
    
    public function __construct($details) {
        $this->connection = array();
        $this->connected = false;
        
        //connect to the database
        $this->connect($details);
    }

    
    public function connect($details) {
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
    
    public function connected() {
        return $this->connected;
    }
    
    public function create($collection, $data) {
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
        
        //create a safe sql that contains placeholders for the given values
        $sql = "INSERT INTO \"".$collection."\" (".implode(', ', array_keys($adaptedData)).") VALUES (";
        
        //create the sql placeholder resolver
        $resolverArray = [];
        foreach ($adaptedData as $columnValue) {
            $resolverArray[] = $columnValue;
            $sql .= "?, ";
        }
        $sql = trim($sql, " \n\t\r\0\x0B,").")";
        
        //open a new statement and execute it
        try {
            //prepare a statement with that safe sql string
            $stmt = $this->connection->prepare($sql);

            //execute the statement resolving placeholders
            $stmt->execute($resolverArray);
        } catch (\PDOException $ex)  {
            throw new DatabaseException('Error while performing the creation operation: '.$ex->getMessage(), 3);
        }
    }
    
    public function update($collection, $data, SelectionCriteria $where) {
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
        
        //create a safe sql that contains placeholders for the given values
        $sql = "UPDATE \"".$collection."\" SET ";
        //create the sql placeholder resolver and build the sql safe query
        $resolverArray = [];
        foreach ($adaptedData as $columnName => $columnValue) {
            $resolverArray[] = $columnValue;
            
            $sql .= $columnName." = ?, ";
        }
        $sql = trim($sql, " \n\t\r\0\x0B,")." ";
        
        //build, append and bind params for the where statement
        $whereResolved = $this->whereBuilder($where);
        $sql .= $whereResolved['sql'];
        foreach ($whereResolved['resolver'] as $whereClauseData) {
            $resolverArray[] = $whereClauseData;
        }
        
        //open a new statement and execute it
        try {
            //prepare a statement with that safe sql string
            $stmt = $this->connection->prepare($sql);

            //execute the statement resolving placeholders
            $stmt->execute($resolverArray);
        } catch (\PDOException $ex)  {
            throw new DatabaseException('Error while performing the update operation: '.$ex->getMessage(), 4);
        }
    }
    
    public function delete($collection, SelectionCriteria $where) {
        
    }
    
    public function read($collection, SelectionCriteria $where, ResultModifier $mod) {
        
    }
    
    private function whereBuilder(SelectionCriteria $where) {
        $result = [
            'sql' => "WHERE ",
            'resolver' => [ ]
        ];
        
        //execute the private function 'export'
        $exportMethod = new \ReflectionMethod($where, 'export');
        $exportMethod->setAccessible(true);
        $resultModifierExported = $exportMethod->invoke($where);
        
        $first = true;
        foreach ($resultModifierExported['historic'] as $current) {
            $conjunction = "";
            
            $arrayIndex = $current & (~SelectionCriteria::AND_Historic_Marker);
            $arrayConjunction = '';
            
            if (($current & (SelectionCriteria::AND_Historic_Marker)) != 0) {
                $conjunction = (!$first) ? " AND " : " ";
                $arrayConjunction = 'and';
            } else {
                $conjunction = (!$first) ? " OR " : " ";
                $arrayConjunction = 'or';
            }
            
            $fieldName = $resultModifierExported['criteria'][$arrayConjunction][$arrayIndex][0];
            $fieldRelationship = $resultModifierExported['criteria'][$arrayConjunction][$arrayIndex][1];
            $fieldValue = $resultModifierExported['criteria'][$arrayConjunction][$arrayIndex][2];
            
            //assemble the query
            $result['sql'] .= $conjunction.$fieldName." ".$fieldRelationship." ? ";
            $result['resolver'][] = $fieldValue;
            
            $first = false;
        }
        
        return $result;
    }
}
