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

namespace Gishiki\ActiveRecord\Adapter;

/**
 * This is the sqlite database adapter
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class SqliteAdapter implements \Gishiki\ActiveRecord\DatabaseAdapter {
    //this is the native PDO driver
    private $native_connection = null;
    
    public function __construct($connection_query) {
        if (!in_array("sqlite", \PDO::getAvailableDrivers()))
        {   throw new \Gishiki\ActiveRecord\DatabaseException("No sqlite driver available: install sqlite PDO driver", 5);  }
        
        try {
            $this->native_connection = new \PDO("sqlite:" . $connection_query);
            $this->native_connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $ex) {
            throw new \Gishiki\ActiveRecord\DatabaseException("Unable to open a connection to the sqlite db, PDO reports: " . $ex->getMessage(), 2);
        }
    }
    
    public function Insert($collection_name, $collection_values) {
        try {
            //generate values collection and placeholders
            $values = array();
            $placeholders = array();
            foreach ($collection_values as &$current) {
                $placeholders[] = '?';
                $values[] = $current;
            }
            
            $sql = "INSERT INTO " . $collection_name . " ( " . implode(', ', array_keys($collection_values)) . " ) VALUES ( " . implode(', ', $placeholders) . ")";
            
            //create the statement for execution
            $statement = $this->native_connection->prepare($sql);
            
            //execute the statement
            $statement->execute($values);
        
            //give the result back
            return $this->native_connection->lastInsertId();
        } catch (\PDOException $ex) {
            var_dump($ex->getMessage());
            throw new \Gishiki\ActiveRecord\DatabaseException("unable to continue with insertion, PDO reports: " . $ex->getCode());
        }
    }
    
    public function Update($collection_name, $collection_values, $id, $id_table_name) {
        try {
            $statement = $this->native_connection->prepare("UPDATE");

            foreach ($collection_values as $value_placeholder => $value_literal) {
                $statement->bindValue(":".$value_placeholder, $value_literal);
            }
            
            //execute the statement
            $statement->execute();
        } catch (\PDOException $ex) {
            throw new \Gishiki\ActiveRecord\DatabaseException("unable to continue with insertion cannot, PDO reports: " . $ex->getCode());
        }
    }
}