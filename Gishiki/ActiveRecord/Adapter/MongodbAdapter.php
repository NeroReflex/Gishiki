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
 * This is the MongoDB database adapter
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class MongodbAdapter implements \Gishiki\ActiveRecord\DatabaseAdapter
{
    private $connection = null;
    private $dbname;
    
    public function __construct($connection_query, $ssl_key = null, $ssl_certificate = null, $ssl_ca = null)
    {
        if (!class_exists("MongoDB\\Driver\\Manager")) {
            throw new \Gishiki\ActiveRecord\DatabaseException("No MongoDB driver available: install the mongodb pecl extension", 5);
        }
        
        //get the database name
        $dbpath = explode('/', $connection_query, 2);
        if (count($dbpath) > 1) {
            $dbpath = $dbpath[1];
        }
        $lim = strpos($dbpath, '?');
        if ($lim !== false) {
            $dbpath = substr($dbpath, 0, $lim + 1);
        }
        $this->dbname = $dbpath;
        
        try {
            $this->connection = new \MongoDB\Driver\Manager("mongodb://".$connection_query);
        } catch (\MongoDB\Driver\Exception\InvalidArgumentException $ex) {
            throw new \Gishiki\ActiveRecord\DatabaseException("Unable to open a valid MongoDB connection (".$ex->getCode().")", 2);
        } catch (\MongoDB\Driver\Exception\RuntimeException $ex) {
            throw new \Gishiki\ActiveRecord\DatabaseException("Unable to open a valid MongoDB connection (".$ex->getCode().") invalid connection string", 3);
        }
    }
    
    public function Create($collection_name, $collection_values, $id_column_name = null)
    {
        //get the fully qualified namespace (e.g. "databaseName.collectionName")
        $namespace = $this->dbname.".".$collection_name;
        
        try {
            $statement = new \MongoDB\Driver\BulkWrite(/*['ordered' => true]*/);
            
            //attempt to create the insertion operation
            $ObjectID = $statement->insert($collection_values);
            
            //execute the write operation
            $this->connection->executeBulkWrite($namespace, $statement);
            
            //and return the object id
            return (string)$ObjectID;
        } catch (\MongoDB\Driver\Exception\BulkWriteException $ex) {
            throw new \Gishiki\ActiveRecord\DatabaseException("Unable to perform the creation operation (".$ex->getCode().")", 4);
        } catch (\MongoDB\Driver\Exception\InvalidArgumentException $ex) {
            throw new \Gishiki\ActiveRecord\DatabaseException("Unable to perform the creation operation (".$ex->getCode().")", 5);
        } catch (\MongoDB\Driver\Exception\ConnectionException $ex) {
            throw new \Gishiki\ActiveRecord\DatabaseException("Unable to connect to the given database (".$ex->getCode().")", 6);
        } catch (\MongoDB\Driver\Exception\AuthenticationException $ex) {
            throw new \Gishiki\ActiveRecord\DatabaseException("Unable to authenticate on the given database (".$ex->getCode().")", 7);
        } catch (\MongoDB\Driver\Exception\RuntimeException $ex) {
            throw new \Gishiki\ActiveRecord\DatabaseException("Unable to perform the creation operation (".$ex->getCode().")", 8);
        }
    }
    
    public function Update($collection_name, $collection_values, \Gishiki\ActiveRecord\RecordsSelector $where, $id_column_name = null)
    {
    }
    
    public function Delete($collection_name, \Gishiki\ActiveRecord\RecordsSelector $where, $id_column_name = null)
    {
    }
    
    public function Read($collection_name, \Gishiki\ActiveRecord\RecordsSelector $where, $id_column_name = null)
    {
        //get the fully qualified namespace (e.g. "databaseName.collectionName")
        $namespace = $this->dbname.".".$collection_name;
        
        try {
            $options = null;
            $filter = null;
            
            //compile the record selector
            self::where_compile($where, $filter, $options);
            
            //execute the write operation
            $cursor = $this->connection->executeQuery($namespace, new \MongoDB\Driver\Query($filter, $options));
            
            $result = [];
            foreach ($cursor as $current_data) {
                if (!is_array($current_data)) {
                    $result[] = get_object_vars($current_data);
                } else {
                    $result[] = $current_data;
                }
            }
            
            return $result;
        } catch (\MongoDB\Driver\Exception\InvalidArgumentException $ex) {
            throw new \Gishiki\ActiveRecord\DatabaseException("Unable to perform the read operation (".$ex->getCode().")", 5);
        } catch (\MongoDB\Driver\Exception\ConnectionException $ex) {
            throw new \Gishiki\ActiveRecord\DatabaseException("Unable to connect to the given database (".$ex->getCode().")", 6);
        } catch (\MongoDB\Driver\Exception\AuthenticationException $ex) {
            throw new \Gishiki\ActiveRecord\DatabaseException("Unable to authenticate on the given database (".$ex->getCode().")", 7);
        } catch (\MongoDB\Driver\Exception\RuntimeException $ex) {
            throw new \Gishiki\ActiveRecord\DatabaseException("Unable to perform the read operation (".$ex->getCode().")", 8);
        }
    }


    
    private static function where_compile(\Gishiki\ActiveRecord\RecordsSelector $where, &$filter, &$options)
    {
        $filter = array();
        $options = array();
        
        //get the list of selectors
        $selectors_property = new \ReflectionProperty($where, "selectors");
        $selectors_property->setAccessible(true);
        $fields_selectors = $selectors_property->getValue($where);
        
        //place selectors
        $i = 0;
        $fields = array_keys($fields_selectors);
        foreach ($fields as $field) {
            $relationship = \Gishiki\Algorithms\String::get_string_between($field, '[', ']');
            $fieldname = str_replace('['.$relationship.']', "", $field);
            $fieldvalue = $fields_selectors[$field];
            
            //HERE you should change relationship if the rdbms you are using doesn't support:
            // = != < <= >= > 

            $filter[$fieldname] = array();
            if ($relationship == "=") {
                $filter[$fieldname]['$eq'] = $fieldvalue;
            } elseif ($relationship == ">=") {
                $filter[$fieldname]['$gte'] = $fieldvalue;
            } elseif ($relationship == "<=") {
                $filter[$fieldname]['$lte'] = $fieldvalue;
            } elseif ($relationship == ">") {
                $filter[$fieldname]['$gt'] = $fieldvalue;
            } elseif ($relationship == "<") {
                $filter[$fieldname]['$lt'] = $fieldvalue;
            } elseif ($relationship == "!=") {
                $filter[$fieldname]['$ne'] = $fieldvalue;
            }
            
            $i++;
        }
        
        //get the limit
        $limit_property = new \ReflectionProperty($where, "limit");
        $limit_property->setAccessible(true);
        
        $limit = $limit_property->getValue($where);
        if ($limit != 0) {
            $options['limit'] = $limit;
        }
        
        //get the offset
        $offset_property = new \ReflectionProperty($where, "offset");
        $offset_property->setAccessible(true);
        
        $offset = $offset_property->getValue($where);
        if ($offset != 0) {
            $options['skip'] = $offset;
        }
    }
}
