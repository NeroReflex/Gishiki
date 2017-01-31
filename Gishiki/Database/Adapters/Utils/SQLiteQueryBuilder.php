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

namespace Gishiki\Database\Adapters\Utils;

use Gishiki\Database\Runtime\SelectionCriteria;
use Gishiki\Database\Runtime\ResultModifier;
use Gishiki\Database\Runtime\FieldOrdering;
use Gishiki\Database\Schema\Table;
use Gishiki\Database\Schema\ColumnType;

/**
 * This utility is useful to create sql queries for SQLite ONLY.
 *
 * It extends the SQLQueryBuilder and add SQLite-specific support.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class SQLiteQueryBuilder extends SQLQueryBuilder {
    
    /**
     * Add CREATE TABLE IF NOT EXISTS %tablename% to the SQL query.
     * 
     * @param  string $tableName the name of the table
     * @return \Gishiki\Database\Adapters\Utils\SQLiteQueryBuilder the updated sql builder
     */
    public function &createTable($tableName)
    {
        $this->appendToQuery('CREATE TABLE IF NOT EXISTS '.$tableName.' ');
        
        //chain functions calls
        return $this;
    }
    
    /**
     * Add (id INT PRIMARY KEY NUT NULL, name TEXT NOT NULL, ... ) to the SQL query.
     * 
     * @param array $columns a collection of Gishiki\Database\Schema\Column
     * @return \Gishiki\Database\Adapters\Utils\SQLiteQueryBuilder the updated sql builder
     */
    public function &definedAs($columns)
    {
        $this->appendToQuery('(');
        
        $first = true;
        foreach ($columns as $column) {
            if (!$first) {
                $this->appendToQuery(',');
            }
            
            $this->appendToQuery($column->getName().' ');
            
            $typename = "";
            switch($column->getType()) {
                case ColumnType::INTEGER:
                    $typename = "INT";
                    break;
                    
                case ColumnType::TEXT:
                    $typename = "TEXT";
                    break;
                    
                case ColumnType::REAL:
                    $typename = "REAL";
                    break;
                
                default:
                    $typename = "INT";
            }
            
            $this->appendToQuery($typename.' ');
            
            if ($column->getNotNull()) {
                $this->appendToQuery('NOT NULL ');
            }
            
            $this->appendToQuery(', ');
            $first = false;
        }
        
        
        
        $this->appendToQuery(')');
        
        //chain functions calls
        return $this;
    }
    
    /**
     * Add DROP TABLE IF EXISTS %tablename% to the SQL query.
     * 
     * @param  string $tableName the name of the table
     * @return \Gishiki\Database\Adapters\Utils\SQLiteQueryBuilder the updated sql builder
     */
    public function &dropTable($tableName)
    {
        $this->appendToQuery('DROP TABLE IF EXISTS '.$tableName.' ');
        
        //chain functions calls
        return $this;
    }
}
