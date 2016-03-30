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
 * PostgreSQL queries builder
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class PostgreSQLHelper {
    /**
     * Creates a query ready to be executed on the given database type
     * 
     * @param string $tableName the name of the table to affect
     * @param Clauses $clauses deleting conditions
     * @return string the query string-encoded
     */
    static function PrepareForDataDeletion(&$tableName, Clauses &$clauses) {
        //start building the query
        $query = "DELETE FROM ".$tableName;
        
        //add clauses if any
        if ($clauses != NULL) {
            $getterReflected = new ReflectionMethod($clauses, "GetClauses");
            $getterReflected->setAccessible(TRUE);
            $clausesList = $getterReflected->invoke($clauses);
            
            //check for not-empty arrays
            if ((count($clausesList[0]) == count($clausesList[1])) && (!empty($clausesList[0]))) {
                $query = $query." WHERE ";
                
                $fieldANDValueRalationship = $clausesList[1];
                
                //start a cycle to scan each clause
                $clausesNumber = count($fieldANDValueRalationship);
                reset($fieldANDValueRalationship);
                
                //scan each clause
                for ($i = 0; $i < $clausesNumber; $i++) {
                    //get every necessary information about the currently examinated clause
                    $field = key($fieldANDValueRalationship);
                    $valueComparation = current($fieldANDValueRalationship);

                    //encode as a string the relationship between the field and its data
                    $valueComparationStringEncoded = "=";
                    switch ($valueComparation) {
                        case Criteria::EQUAL: 
                            $valueComparationStringEncoded = "=";
                            break;
                        case Criteria::NOT_EQUAL:
                            $valueComparationStringEncoded = "!=";
                            break;
                        case Criteria::GREATER_EQUAL:
                            $valueComparationStringEncoded = ">=";
                            break;
                        case Criteria::GREATER_THAN:
                            $valueComparationStringEncoded = ">";
                            break;
                        case Criteria::LESS_EQUAL:
                            $valueComparationStringEncoded = "<=";
                            break;
                        case Criteria::LESS_THAN:
                            $valueComparationStringEncoded = "<";
                            break;
                        case Criteria::LIKE:
                            $valueComparationStringEncoded = "LIKE";
                            break;
                        case Criteria::NOT_LIKE:
                            $valueComparationStringEncoded = "NOT LIKE";
                            break;
                        case Criteria::IS_NULL:
                            $valueComparationStringEncoded = "IS";
                            break;
                        case Criteria::IS_NOT_NULL:
                            $valueComparationStringEncoded = "IS NOT";
                            break;
                        default:
                            throw new DatabaseException("The given relationship between a field and its value is not valid. Please, look at the Criteria class", -32);
                    }
                    
                    //concatenate the clause to the query
                    $query = $query." \"".$field."\" ".$valueComparationStringEncoded." ".":".$field;
                    
                    if ($i < ($clausesNumber - 1)) {
                        $query = $query." AND ";
                    }
                    
                    //jump to the next clause
                    next($fieldANDValueRalationship);
                }
                
            }
        }
        
        //return the query
        return $query;
    }
    
    /**
     * Creates a query ready to be executed on a SQLite3 database
     * 
     * @param string $tableName the name of the table to affect
     * @param Clauses $clauses fetching conditions
     * @param integer $dbtype the database type
     * @return string the query string-encoded
     */
    static function PrepareForFetching(&$tableName, Clauses &$clauses) {
        //start building the query
        $query = "SELECT * FROM ".$tableName;
        
        //add clauses if any
        if ($clauses != NULL) {
            $getterReflected = new ReflectionMethod($clauses, "GetClauses");
            $getterReflected->setAccessible(TRUE);
            $clausesList = $getterReflected->invoke($clauses);
            
            //check for not-empty arrays
            if ((count($clausesList[0]) == count($clausesList[1])) && (!empty($clausesList[0]))) {
                $query = $query." WHERE ";
                
                $fieldANDValueRalationship = $clausesList[1];
                
                //start a cycle to scan each clause
                $clausesNumber = count($fieldANDValueRalationship);
                reset($fieldANDValueRalationship);
                
                //scan each clause
                for ($i = 0; $i < $clausesNumber; $i++) {
                    //get every necessary information about the currently examinated clause
                    $field = key($fieldANDValueRalationship);
                    $valueComparation = current($fieldANDValueRalationship);

                    //encode as a string the relationship between the field and its data
                    $valueComparationStringEncoded = "=";
                    switch ($valueComparation) {
                        case Criteria::EQUAL: 
                            $valueComparationStringEncoded = "=";
                            break;
                        case Criteria::NOT_EQUAL:
                            $valueComparationStringEncoded = "!=";
                            break;
                        case Criteria::GREATER_EQUAL:
                            $valueComparationStringEncoded = ">=";
                            break;
                        case Criteria::GREATER_THAN:
                            $valueComparationStringEncoded = ">";
                            break;
                        case Criteria::LESS_EQUAL:
                            $valueComparationStringEncoded = "<=";
                            break;
                        case Criteria::LESS_THAN:
                            $valueComparationStringEncoded = "<";
                            break;
                        case Criteria::LIKE:
                            $valueComparationStringEncoded = "LIKE";
                            break;
                        case Criteria::NOT_LIKE:
                            $valueComparationStringEncoded = "NOT LIKE";
                            break;
                        case Criteria::IS_NULL:
                            $valueComparationStringEncoded = "IS";
                            break;
                        case Criteria::IS_NOT_NULL:
                            $valueComparationStringEncoded = "IS NOT";
                            break;
                        default:
                            throw new DatabaseException("The given relationship between a field and its value is not valid. Please, look at the Criteria class", -32);
                    }
                    
                    //concatenate the clause to the query
                    $query = $query."\"".$field."\" ".$valueComparationStringEncoded." ".":".$field;
                    
                    if ($i < ($clausesNumber - 1)) {
                        $query = $query." AND ";
                    }
                    
                    //jump to the next clause
                    next($fieldANDValueRalationship);
                }
                
            }
        }
        
        //return the query
        return $query;
    }
    
    /**
     * Creates a query ready to be executed on a SQLite3 database
     * 
     * @param string $tableName the name of the table to affect
     * @param array $data an array that relates the index (table column name) with data (column data)
     * @return string the query string-encoded
     */
    static function PrepareForDataInsertion(&$tableName, &$data) {
        //start building the query
        $query = "INSERT INTO ".$tableName." (";

        //cycle each element from the first one
        reset($data);

        //get the number of affected columns
        $columnNumbers = count($data);

        for ($counter = 0; $counter < $columnNumbers; $counter++)
        {
            $query = $query." \"".key($data)."\"";

            if ($counter != ($columnNumbers - 1))
                $query = $query.",";

            //jump to the next serialized field
            next($data);
        }

        $query = $query.") VALUES (";

        //cycle each element from the first one
        reset($data);

        for ($counter = 0; $counter < $columnNumbers; $counter++)
        {
            $query = $query." :".key($data);

            if ($counter != ($columnNumbers - 1)) {
                $query = $query.",";
            }

            //jump to the next serialized field
            next($data);
        }

        //end building the query
        $query = $query.")";
        
        //return the query
        return $query;
    }
    
    /**
     * Creates a query ready to be executed on the given database type
     * 
     * @param string $tableName the name of the table to affect
     * @param array $data an array with the name of the columns (as index) and its type (as value)
     * @param integer $dbtype the database type
     * @return string the query string-encoded
     */
    static function PrepareForTableCreation(&$tableName, &$data) {
        //start building the query
        $query = "CREATE TABLE IF NOT EXISTS ".$tableName." (";
        $query = $query."\"ID\" integer PRIMARY KEY, ";
        //get the number of columns
        $columnsNumber = count($data);
        
        //start cycling from the first column
        reset($data);
        
        for ($counter = 0; $counter < $columnsNumber; $counter++) {
            //set the column name
            $query = $query."\"".key($data)."\" ";
            
            //set the column type
            $columnType = current($data);
            if ($columnType == ColumnType::INTEGER) {
                $query = $query."integer";
            } else if ($columnType == ColumnType::REAL) {
                $query = $query."real";
            } else if ($columnType == ColumnType::TEXT) {
                $query = $query."text";
            } else {
                throw new DatabaseException("Unrecognized column type", -15);
            }
                
            //prepare for the next column (if the current one is not the last)
            if ($counter < ($columnsNumber - 1))
                $query = $query.",";
            
            //jump to the next column
            next($data);
        }
        
        //end building the query
        $query = $query.")";
        
        //return the created query
        return $query;
    }
}
