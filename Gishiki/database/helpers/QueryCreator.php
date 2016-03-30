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

require_once(ROOT."Gishiki".DS."database".DS."helpers".DS."native".DS."SQLiteHelper.php");
require_once(ROOT."Gishiki".DS."database".DS."helpers".DS."native".DS."MySQLHelper.php");
require_once(ROOT."Gishiki".DS."database".DS."helpers".DS."native".DS."PostgreSQLHelper.php");
require_once(ROOT."Gishiki".DS."database".DS."helpers".DS."native".DS."MicrosoftSQLHelper.php");

/**
 * This class is a sort of proxing to generate the proper query
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class QueryCreator {
    /**
     * Creates a query ready to be executed on the given database type
     * 
     * @param string $tableName the name of the table to affect
     * @param Clauses $clauses deleting conditions
     * @param integer $dbtype the database type
     * @return string the query string-encoded
     */
    static function PrepareForDataDeletion(&$tableName, Clauses &$clauses, $dbtype = DatabaseType::SQLite3) {
        //execute a native query creator
        if (($dbtype == DatabaseType::SQLite3) || ($dbtype == DatabaseType::SQLite))
        {   return SQLiteHelper::PrepareForDataDeletion($tableName, $clauses);   }
        else if ($dbtype == DatabaseType::MySQL)
        {   return MySQLHelper::PrepareForDataDeletion($tableName, $clauses);   }
        else if ($dbtype == DatabaseType::PostgreSQL)
        {   return PostgreSQLHelper::PrepareForDataDeletion($tableName, $clauses);   }
        else if ($dbtype == DatabaseType::MicrosoftSQL)
        {   return MicrosoftSQLHelper::PrepareForDataDeletion($tableName, $clauses);   }
        else {
            throw new DatabaseException("Unsupported query creation for the given database type", 100);
        }
    }
    
    /**
     * Creates a query ready to be executed on the given database type
     * 
     * @param string $tableName the name of the table to affect
     * @param Clauses $clauses fetching conditions
     * @param integer $dbtype the database type
     * @return string the query string-encoded
     */
    static function PrepareForFetching(&$tableName, Clauses &$clauses, $dbtype = DatabaseType::SQLite3) {
        //execute a native query creator
        if (($dbtype == DatabaseType::SQLite3) || ($dbtype == DatabaseType::SQLite))
        {   return SQLiteHelper::PrepareForFetching($tableName, $clauses);   }
        else if ($dbtype == DatabaseType::MySQL)
        {   return MySQLHelper::PrepareForFetching($tableName, $clauses);   }
        else if ($dbtype == DatabaseType::PostgreSQL)
        {   return PostgreSQLHelper::PrepareForFetching($tableName, $clauses);   }
        else if ($dbtype == DatabaseType::MicrosoftSQL)
        {   return MicrosoftSQLHelper::PrepareForFetching($tableName, $clauses);   }
        else {
            throw new DatabaseException("Unsupported query creation for the given database type", 100);
        }
    }
    
    /**
     * Creates a query ready to be executed on the given database type
     * 
     * @param string $tableName the name of the table to affect
     * @param array $data an array that relates the index (table column name) with data (column data)
     * @param integer $dbtype the database type
     * @return string the query string-encoded
     */
    static function PrepareForDataInsertion(&$tableName, &$data, $dbtype = DatabaseType::SQLite3) {
        
        //execute a native query creator
        if (($dbtype == DatabaseType::SQLite3) || ($dbtype == DatabaseType::SQLite))
        {   return SQLiteHelper::PrepareForDataInsertion($tableName, $data);   }
        else if ($dbtype == DatabaseType::MySQL)
        {   return MySQLHelper::PrepareForDataInsertion($tableName, $data);   }
        else if ($dbtype == DatabaseType::PostgreSQL)
        {   return PostgreSQLHelper::PrepareForDataInsertion($tableName, $data);   }
        else if ($dbtype == DatabaseType::MicrosoftSQL)
        {   return MicrosoftSQLHelper::PrepareForDataInsertion($tableName, $data);   }
        else {
            throw new DatabaseException("Unsupported query creation for the given database type", 100);
        }
    }
    
    /**
     * Creates a query ready to be executed on the given database type
     * 
     * @param string $tableName the name of the table to affect
     * @param array $data an array with the name of the columns (as index) and its type (as value)
     * @param integer $dbtype the database type
     * @return string the query string-encoded
     */
    static function PrepareForTableCreation(&$tableName, &$data, $dbtype = DatabaseType::SQLite3) {
        
        //execute a native query creator
        if (($dbtype == DatabaseType::SQLite3) || ($dbtype == DatabaseType::SQLite))
        {   return SQLiteHelper::PrepareForTableCreation($tableName, $data);   }
        else if ($dbtype == DatabaseType::MySQL)
        {   return MySQLHelper::PrepareForTableCreation($tableName, $data);   }
        else if ($dbtype == DatabaseType::PostgreSQL)
        {   return PostgreSQLHelper::PrepareForTableCreation($tableName, $data);   }
        else if ($dbtype == DatabaseType::MicrosoftSQL)
        {   return MicrosoftSQLHelper::PrepareForTableCreation($tableName, $data);   }
        else {
            throw new DatabaseException("Unsupported query creation for the given database type", 100);
        }
    }
    
    /**
     * Complete the finalization of the statement created from a semi-elaborated query
     * using an associatice array of $data[field] = value
     * 
     * @param array $fieldANDValue the data array
     * @param statement $statement a statement retrived from PDO or SQLite3
     * @param integer $dbtype the database type
     */
    static function FinalizeStatementPreparation(&$fieldANDValue, &$statement, &$dbtype = DatabaseType::SQLite3) {
        //start a cycle to scan each clause
        $clausesNumber = count($fieldANDValue);
        reset($fieldANDValue);

        //scan each clause
        for ($i = 0; $i < $clausesNumber; $i++) {
            //strict rules: only variables can be passed by reference
            $bindName = ":".key($fieldANDValue);
            $bindValue = current($fieldANDValue);

            $bindValueType = gettype($bindValue);
            if ($bindValueType == "string") {
                $explodingTest = explode(":", $bindValue);
                if (($explodingTest[0] != "jsn") && ($explodingTest[0] != "boo") && ($explodingTest[0] != "obj") && ($explodingTest[0] != "str") && (!class_exists($explodingTest[0]))){
                    $bindValue = "str:".$bindValue;
                }
            } else if ($bindValueType == "boolean") {
                if ($bindValue) {
                    $bindValue = "boo:true";
                } else {
                   $bindValue = "boo:false";
                }
            } else if ($bindValueType == "array") {
                $JSONEncodedArray = new JSON($bindValue);
                $bindValue = "jsn:".$JSONEncodedArray->Serialize();
            } else if ($bindValueType == "object") {
                $serializationStack = new Stack();
                Serialization::SerializeObject($bindValue, $serializationStack);
                $serializedObj = $serializationStack->TOP();
                $bindValue = "obj:".$serializedObj["ObjectDescriptor"];
            }

            //bind data to the query
            if ($dbtype == DatabaseType::SQLite3) {
                switch (gettype($bindValue)) {
                    case "string":
                        $statement->bindValue($bindName, $bindValue, SQLITE3_TEXT);
                        break;
                    case "double":
                        $statement->bindValue($bindName, $bindValue, SQLITE3_FLOAT);
                        break;
                    case "integer":
                        $statement->bindValue($bindName, $bindValue, SQLITE3_INTEGER);
                        break;
                    default:
                        $statement->bindValue($bindName, NULL, SQLITE3_NULL);
                        break;
                }
            } else {
                switch (gettype($bindValue)) {
                    case "string":
                        $statement->bindValue($bindName, $bindValue, PDO::PARAM_STR);
                        break;
                    case "double":
                        $statement->bindValue($bindName, $bindValue);
                        break;
                    case "integer":
                        $statement->bindValue($bindName, $bindValue, PDO::PARAM_INT);
                        break;
                    default:
                        $statement->bindValue($bindName, NULL, PDO::PARAM_NULL);
                        break;
                }
            }
                    
            //jump to the next clause
            next($fieldANDValue);
        }
    }
    
    /**
     * Complete the finalization of the statement created from a semi-elaborated query
     * using a clauses collection
     * 
     * @param Clauses $clauses the clauses collection
     * @param statement $statement a statement retrived from PDO or SQLite3
     * @param integer $dbtype the database type
     */
    static function FinalizeStatementPreparationWithClauses(Clauses &$clauses, &$statement, &$dbtype = DatabaseType::SQLite3) {
        //add clauses if any
        $getterReflected = new ReflectionMethod($clauses, "GetClauses");
        $getterReflected->setAccessible(TRUE);
        $clausesList = $getterReflected->invoke($clauses);

        //check for not-empty arrays
        if ((count($clausesList[0]) == count($clausesList[1])) && (!empty($clausesList))) {
            //complete the finalization of the statement created from a semi-elaborated query
            QueryCreator::FinalizeStatementPreparation($clausesList[0], $statement, $dbtype);
        }
    }
}
