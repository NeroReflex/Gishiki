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
 * This is the MySQL database adapter
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class MysqlAdapter implements \Gishiki\ActiveRecord\DatabaseAdapter
{
    //this is the native PDO driver
    private $native_connection = null;
    
    public function __construct($connection_query, $ssl_key = null, $ssl_certificate = null, $ssl_ca = null)
    {
        if (!in_array("mysql", \PDO::getAvailableDrivers())) {
            throw new \Gishiki\ActiveRecord\DatabaseException("No MySQL driver available: install the mysql PDO driver", 5);
        }
        
        //extract connection info from the connection query
        $db_conn = explode('@', $connection_query, 2);
        $user_and_password = explode(':', $db_conn[0], 2);
        $host_and_port = explode(':', explode('/', $db_conn[1], 2)[0], 2);
        $db_name = explode('/', $db_conn[1], 2)[1];
        
        //use the default port is nother one was not specified
        if (!isset($host_and_port[1])) {
            $host_and_port[1] = '3306';
        }
        
        $pdo_connection = array(
            \PDO::ATTR_PERSISTENT => false,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_STRINGIFY_FETCHES => false,
        );
        if (($ssl_key != null) && ($ssl_certificate != null) && ($ssl_ca != null)) {
            if ((file_exists($ssl_key)) && (file_exists($ssl_certificate)) && (file_exists($ssl_ca))) {
                $pdo_connection[\PDO::MYSQL_ATTR_SSL_KEY] = $ssl_key;
                $pdo_connection[\PDO::MYSQL_ATTR_SSL_CERT] = $ssl_certificate;
                $pdo_connection[\PDO::MYSQL_ATTR_SSL_CA] = $ssl_ca;
            } else {
                throw new \Gishiki\ActiveRecord\DatabaseException("Unable to find SSL key and/or certs: make sure php has read access to those files", 200);
            }
        }
        
        try {
            $this->native_connection = new \PDO("mysql:host=".$host_and_port[0].";port=".$host_and_port[1].";dbname=".$db_name, $user_and_password[0], $user_and_password[1], $pdo_connection);
        } catch (\PDOException $ex) {
            throw new \Gishiki\ActiveRecord\DatabaseException("Unable to open a connection to the MySQL db, PDO reports: ".$ex->getCode(), 2);
        }
    }
    
    private function getColumnInfo($collection_name)
    {
        try {
            //start building the query
            $sql = "SELECT * FROM ".$collection_name." LIMIT 1";
            
            //create the statement for execution
            $statement = $this->native_connection->prepare($sql);
            
            //execute the statement
            $statement->execute();
            
            //build columns metadata:
            $metadata = array();
            foreach (range(0, $statement->columnCount() - 1) as $column_index) {
                $metadata[$column_index] = $statement->getColumnMeta($column_index);
                if ($metadata[$column_index] === false) {
                    throw new \Gishiki\ActiveRecord\DatabaseException("Unable to detect table structure", 15);
                }
            }
            
            //return the column metadata
            return $metadata;
        } catch (\PDOException $ex) {
            throw new \Gishiki\ActiveRecord\DatabaseException("unable to continue with table introspection, PDO reports: ".$ex->getCode(), 16);
        }
    }
    
    private function filter_input_types($collection_name, $array)
    {
        try {
            //get table info
            $table_descriptors = $this->getColumnInfo($collection_name);

            //setup the datatype filter
            $filters = array();
            foreach ($table_descriptors as $column_info) {
                $filters[$column_info['name']] = $column_info['native_type'];
            }

            //perform the filtering
            $filtered_data = array();
            foreach ($filters as $column_name => $column_type) {
                if (in_array($column_name, array_keys($array))) {
                    $native_value = $array[$column_name];

                    switch (strtolower($column_type)) {
                        case 'integer':
                            $native_value = intval($native_value);
                            break;
                        case 'boolean':
                            $native_value = boolval($native_value);
                            break;
                        case 'float':
                        case 'double':
                            $native_value = floatval($native_value);
                            break;
                        default:
                            //leave it as is
                            break;
                    }

                    $filtered_data[$column_name] = $native_value;
                }
            }
            return $filtered_data;
        } catch (\Gishiki\ActiveRecord\DatabaseException $ex) {
            //on error perform a no-op
            return $array;
        }
    }
    
    private function where_compile(\Gishiki\ActiveRecord\RecordsSelector $where, &$sobstitution_table)
    {
        //setup the sobtitution table
        $sobstitution_table = array();
        
        //start compiling
        $where_clauses = " WHERE ";
        
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
            
            //HERE you should change relationship if the rdbms you are using doesn't support:
            // = != < <= >= > 

            $where_clauses .= $fieldname." ".$relationship." ?";
            
            //place comme if needed
            if ($i < count($fields) - 2) {
                $where_clauses .= ' AND ';
            }
            $sobstitution_table[] = $fields_selectors[$field];
            
            $i++;
        }
        
        //get the limit
        $limit_property = new \ReflectionProperty($where, "limit");
        $limit_property->setAccessible(true);
        
        $limit = $limit_property->getValue($where);
        if ($limit) {
            $sobstitution_table[] = $limit;
            $where_clauses .= " LIMIT ? ";
        }
        
        //get the offset
        $offset_property = new \ReflectionProperty($where, "offset");
        $offset_property->setAccessible(true);
        
        $offset = $offset_property->getValue($where);
        if ($offset) {
            $where_clauses .= " OFFSET ? ";
            $sobstitution_table[] = $offset;
        }
        
        return $where_clauses;
    }
    
    public function Create($collection_name, $collection_values, $id_column_name = null)
    {
        //cast data to correct types
        $collection_values = $this->filter_input_types($collection_name, $collection_values);
        
        try {
            //generate values collection and placeholders
            $values = array();
            $placeholders = array();
            foreach ($collection_values as &$current) {
                $placeholders[] = '?';
                $values[] = $current;
            }
            
            $sql = "INSERT INTO ".$collection_name." ( ".implode(', ', array_keys($collection_values))." ) VALUES ( ".implode(', ', $placeholders).") ";
            
            //create the statement for execution
            $statement = $this->native_connection->prepare($sql);
            
            //execute the statement
            $statement->execute($values);
        
            //give the result back
            return $this->native_connection->lastInsertId();
        } catch (\PDOException $ex) {
            throw new \Gishiki\ActiveRecord\DatabaseException("unable to continue with insertion, PDO reports: ".$ex->getCode(), 6);
        }
    }
    
    public function Update($collection_name, $collection_values, \Gishiki\ActiveRecord\RecordsSelector $where, $id_column_name = null)
    {
        $collection_values = $this->filter_input_types($collection_name, $collection_values);
        
        try {
            //generate values collection and placeholders
            $values = array();
            
            //start building the query
            $sql = "UPDATE ".$collection_name." SET ";
            
            //insert value that are going to be updated
            $value_count = count($collection_values);
            $i = 0;
            foreach ($collection_values as $key => $current) {
                $sql .= " $key = ?";
                $values[] = $current;
                
                //place the separator if another field is going to be updated
                if ($i < ($value_count - 1)) {
                    $sql .= ", ";
                }
                    
                $i++;
            }
            
            //add where clauses
            $where_subs = null;
            $sql .= $this->where_compile($where, $where_subs);
            
            //build the values and where array
            $values_and_where = array();
            foreach ($values as &$value) {
                $values_and_where[] = $value;
            }
            foreach ($where_subs as &$value) {
                $values_and_where[] = $value;
            }
            
            //create the statement for execution
            $statement = $this->native_connection->prepare($sql);
            
            //execute the statement
            $statement->execute($values_and_where);
            
            //return the number of affected rows
            return $statement->rowCount();
        } catch (\PDOException $ex) {
            throw new \Gishiki\ActiveRecord\DatabaseException("unable to continue database update, PDO reports: ".$ex->getCode(), 11);
        }
    }
    
    public function Delete($collection_name, \Gishiki\ActiveRecord\RecordsSelector $where, $id_column_name = null)
    {
        try {
            //start building the query
            $sql = "DELETE FROM ".$collection_name." ";
            
            //add where clauses
            $where_subs = null;
            $sql .= $this->where_compile($where, $where_subs);
            
            //create the statement for execution
            $statement = $this->native_connection->prepare($sql);
            
            //execute the statement
            $statement->execute($where_subs);
            
            //return the number of affected rows
            return $statement->rowCount();
        } catch (\PDOException $ex) {
            throw new \Gishiki\ActiveRecord\DatabaseException("unable to continue with deletion, PDO reports: ".$ex->getCode(), 9);
        }
    }
    
    public function Read($collection_name, \Gishiki\ActiveRecord\RecordsSelector $where, $id_column_name = null)
    {
        $metadata = false;
        try {
            //build columns metadata:
            $metadata = $this->getColumnInfo($collection_name);
        } catch (\Gishiki\ActiveRecord\DatabaseException $ex) {
        }
        
        try {
            
            //start building the query
            $sql = "SELECT * FROM ".$collection_name." ";
            
            //add where clauses
            $where_subs = null;
            $sql .= $this->where_compile($where, $where_subs);
            
            //create the statement for execution
            $statement = $this->native_connection->prepare($sql);
            
            //execute the statement
            $statement->execute($where_subs);
            
            //return the number of affected rows
            $raw_fetch = $statement->fetchAll(\PDO::FETCH_NUM);
            
            //build the native record collection
            $native_records = array();
            if ($metadata !== false) {
                foreach ($raw_fetch as $current_record) {
                    $current_record_native = array();
                    foreach ($current_record as $record_key => $record_value) {
                        $native_value = $record_value;
                        switch (strtolower($metadata[$record_key]["native_type"])) {
                            case 'integer':
                                $native_value = intval($record_value);
                                break;
                            case 'boolean':
                                $native_value = boolval($record_value);
                                break;
                            case 'float':
                            case 'double':
                                $native_value = floatval($record_value);
                                break;
                            default:
                                //leave it as is
                                break;
                        }

                        //build the current record (in native format)
                        $current_record_native[$metadata[$record_key]['name']] = $native_value;
                    }
                    $native_records[] = $current_record_native;
                }
            } else {
                return $raw_fetch;
            }
            
            return $native_records;
        } catch (\PDOException $ex) {
            throw new \Gishiki\ActiveRecord\DatabaseException("unable to continue with read, PDO reports: ".$ex->getCode(), 10);
        }
    }
}
