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

use Gishiki\Database\SelectionCriteria;
use Gishiki\Database\ResultModifier;
use Gishiki\Database\FieldOrdering;

/**
 * This utility is useful to create sql queries for various RDBMS.
 *
 * An example of usage can be:
 * <code>
 * $queryBuilder = new SQLBuilder();
 * $queryBuilder->insertInto("users")->values([
 *      "name" => "Mario",
 *      "surname" => "Rossi",
 *      "password" => Hashing\Algorithms::hash(" __= N0 r4inb0w t4bl3 ==__" . "Mario's password", Hashing\Algorithms::SHA512);
 *      "sex" => 0,
 *      "height" => 1.70
 * ]);
 *
 * //INSERT INTO "users" (name, surname, password, sex, height) VALUES (?, ?, ?, ?, ?)
 * $sql = $queryBuilder->exportQuery();
 *
 * // array( "Mario", "Rossi", ".....", 0, 1.70 )
 * $params = $queryBuilder->exportParams();
 * </code>
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class SQLBuilder
{
    
    /**
     * @var string the SQL query that contains placeholders
     */
    protected $sql = '';
    
    /**
     * @var array a list of values to be escaped and inserted in place of sql placeholders
     */
    protected $params = [];
    
    protected function appendToQuery($sql)
    {
        $this->sql .= $sql;
    }
    
    protected function appendToParams($newParams)
    {
        if (is_array($newParams)) {
            foreach ($newParams as $currentParam) {
                $this->params[] = $currentParam;
            }
        } else {
            $this->params[] = $newParams;
        }
    }
    
    public function __construct()
    {
    }
    
    public function update($table)
    {
        $this->appendToQuery('UPDATE "'.$table.'" ');
        
        //chain functions calls
        return $this;
    }
    
    public function set(array $values)
    {
        $this->appendToQuery('SET ');

        //create the sql placeholder resolver
        $first = true;
        foreach ($values as $columnName => $columnValue) {
            $this->appendToParams($columnValue);
            if (!$first) {
                $this->appendToQuery(',');
            }
            $this->appendToQuery($columnName.' = ? ');
            
            $first = false;
        }
        
        //chain functions calls
        return $this;
    }
    
    public function where(SelectionCriteria $where)
    {
        $this->appendToQuery('WHERE ');
        
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
            $qmarks = '';
            $parentOpen = '';
            $parentClose = '';
            if (is_array($fieldValue)) {
                $qmarks = str_repeat(' ?,', count($fieldValue) - 1);
                $parentOpen = '(';
                $parentClose = ')';
            }
            $this->appendToQuery($conjunction.$fieldName.' '.$fieldRelationship.' '.$parentOpen.$qmarks.' ?'.$parentClose.' ');
            $this->appendToParams($fieldValue);
            
            $first = false;
        }
        
        //chain functions calls
        return $this;
    }
    
    public function insertInto($table)
    {
        $this->appendToQuery('INSERT INTO "'.$table.'" ');
        
        //chain functions calls
        return $this;
    }
    
    public function values(array $values)
    {
        $this->appendToQuery("(".implode(', ', array_keys($values)).") VALUES (");
        
        //create the sql placeholder resolver
        $first = true;
        foreach ($values as $columnValue) {
            $this->appendToParams($columnValue);
            if (!$first) {
                $this->appendToQuery(',');
            }
            $this->appendToQuery('?');
            $first = false;
        }
        
        $this->appendToQuery(')');
        
        //chain functions calls
        return $this;
    }
    
    public function limitOffsetOrderBy(ResultModifier $mod)
    {
        //execute the private function 'export'
        $exportMethod = new \ReflectionMethod($mod, 'export');
        $exportMethod->setAccessible(true);
        $resultModifierExported = $exportMethod->invoke($mod);
        
        //append limit if needed
        if ($resultModifierExported['limit'] > 0) {
            $this->appendToQuery('LIMIT '.$resultModifierExported['limit'].' ');
        }
        
        //append offset if needed
        if ($resultModifierExported['skip'] > 0) {
            $this->appendToQuery('OFFSET '.$resultModifierExported['skip'].' ');
        }
        
        //append order if needed
        if (count($resultModifierExported['order']) > 0) {
            $this->appendToQuery('ORDER BY ');
            $first = true;
            foreach ($resultModifierExported['order'] as $column => $order) {
                if (!$first) {
                    $this->appendToQuery(', ');
                }
                
                $orderStr = ($order == FieldOrdering::ASC) ? 'ASC' : 'DESC';
                $this->appendToQuery($column.' '.$orderStr);
                
                $first = false;
            }
        }
        
        //chain functions calls
        return $this;
    }
    
    public function selectAllFrom($table)
    {
        $this->appendToQuery('SELECT * FROM "'.$table.'" ');
        
        //chain functions calls
        return $this;
    }
    
    public function selectFrom($table, array $fields)
    {
        $this->appendToQuery('SELECT '.  implode(', ', $fields).' FROM "'.$table.'" ');
        
        //chain functions calls
        return $this;
    }
    
    public function deleteFrom($table)
    {
        $this->appendToQuery('DELETE FROM "'.$table.'" ');
        
        //chain functions calls
        return $this;
    }
    
    /**
     * Export the SQL query string with ? in place of actual parameters.
     *
     * @return string the SQL query without values
     */
    public function exportQuery()
    {
        return $this->sql;
    }
    
    /**
     * Export the list of parameters that will replace ? in the SQL query.
     *
     * @return array the list of params
     */
    public function exportParams()
    {
        return $this->params;
    }
}
