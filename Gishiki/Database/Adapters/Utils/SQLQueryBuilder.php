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

/**
 * This utility is useful to create sql queries for various RDBMS.
 *
 * An example of usage can be:
 * <code>
 * $queryBuilder = new SQLQueryBuilder();
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
class SQLQueryBuilder
{
    /**
     * @var string the SQL query that contains placeholders
     */
    protected $sql;

    /**
     * @var array a list of values to be escaped and inserted in place of sql placeholders
     */
    protected $params;

    /**
     * Beautify an SQL query.
     *
     * @param string $query the ugly SQL query
     */
    public static function Beautify($query)
    {
        $count = 1;
        while ($count > 0) {
            $query = str_replace('  ', ' ', $query, $count);
        }

        $count = 1;
        while ($count > 0) {
            $query = str_replace('( ', '(', $query, $count);
        }

        $count = 1;
        while ($count > 0) {
            $query = str_replace(' )', ')', $query, $count);
        }

        $count = 1;
        while ($count > 0) {
            $query = str_replace('?, ?', '?,?', $query, $count);
        }

        return trim($query);
    }

    /**
     * Append to the current SQL the given text.
     *
     * Placeholders are question marks: ? and the value must be registered using
     * the appendToParams function.
     *
     * @param string $sql the SQL with '?' placeholders
     */
    protected function appendToQuery($sql)
    {
        $this->sql .= $sql;
    }

    /**
     * This is a collection of raw values that the PDO will replace to ?
     * on the SQL query.
     *
     * @param mixed $newParams an array of values or the value to be replaced
     */
    protected function appendToParams($newParams)
    {
        //this is used to recreate an array that doesn't conflicts with the $this->params when merging them
        if (gettype($newParams) == "array") {
            $temp = [];
            
            foreach ($newParams as $currentKey => $currentValue) {
                $temp[$currentKey + count($this->params)] = $currentValue;
            }
            
            $newParams = $temp;
        }
        //the result will be something like:
        // [0] => x1, [1] => x2
        // [5] => x1, [6] => x2
        
        (is_array($newParams)) ? $this->params = array_merge($this->params, $newParams) : array_push($this->params, $newParams);
    }

    /**
     * Initialize an empty SQL query.
     */
    public function __construct()
    {
        $this->sql = '';
        $this->params = [];
    }

    /**
     * Add UPDATE %tablename% to the SQL query.
     *
     * @param string $table the name of the table to be updated
     *
     * @return \Gishiki\Database\Adapters\Utils\SQLQueryBuilder the updated sql builder
     */
    public function &update($table)
    {
        $this->appendToQuery('UPDATE "'.$table.'" ');

        //chain functions calls
        return $this;
    }

    /**
     * Add SET col1 = ?, col2 = ?, col3 = ? to the SQL query.
     *
     * @param array $values an associative array of columns => value to be changed
     *
     * @return \Gishiki\Database\Adapters\Utils\SQLQueryBuilder the updated sql builder
     */
    public function &set(array $values)
    {
        $this->appendToQuery('SET ');

        //create the sql placeholder resolver
        $first = true;
        foreach ($values as $columnName => $columnValue) {
            $this->appendToParams($columnValue);
            if (!$first) {
                $this->appendToQuery(', ');
            }
            $this->appendToQuery($columnName.' = ?');

            $first = false;
        }

        $this->appendToQuery(' ');

        //chain functions calls
        return $this;
    }

    /**
     * Add WHERE col1 = ? OR col2 <= ? ....... to the SQL query.
     *
     * @param SelectionCriteria $where the selection criteria
     *
     * @return \Gishiki\Database\Adapters\Utils\SQLQueryBuilder the updated sql builder
     */
    public function &where(SelectionCriteria $where)
    {
        //execute the private function 'export'
        $exportMethod = new \ReflectionMethod($where, 'export');
        $exportMethod->setAccessible(true);
        $resultExported = $exportMethod->invoke($where);

        if (count($resultExported['historic']) > 0) {
            $this->appendToQuery('WHERE ');

            $first = true;
            foreach ($resultExported['historic'] as $current) {
                $conjunction = '';

                $arrayIndex = $current & (~SelectionCriteria::AND_Historic_Marker);
                $arrayConjunction = '';

                //default is an OR
                $conjunction = (!$first) ? ' OR ' : ' ';
                $arrayConjunction = 'or';
                
                //change to AND where necessary
                if (($current & (SelectionCriteria::AND_Historic_Marker)) != 0) {
                    $conjunction = (!$first) ? ' AND ' : ' ';
                    $arrayConjunction = 'and';
                }

                $fieldName = $resultExported['criteria'][$arrayConjunction][$arrayIndex][0];
                $fieldRelation = $resultExported['criteria'][$arrayConjunction][$arrayIndex][1];
                $fieldValue = $resultExported['criteria'][$arrayConjunction][$arrayIndex][2];

                //assemble the query
                $qmarks = '';
                $parentOpen = '';
                $parentClose = '';
                if (is_array($fieldValue)) {
                    $qmarks = str_repeat(' ?,', count($fieldValue) - 1);
                    $parentOpen = '(';
                    $parentClose = ')';
                }
                $this->appendToQuery($conjunction.$fieldName.' '.$fieldRelation.' '.$parentOpen.$qmarks.' ?'.$parentClose.' ');
                $this->appendToParams($fieldValue);

                $first = false;
            }
        }

        //chain functions calls
        return $this;
    }

    /**
     * Add INSERT INTO %tablename% to the SQL query.
     *
     * @param string $table the name of the table to be affected
     *
     * @return \Gishiki\Database\Adapters\Utils\SQLQueryBuilder the updated sql builder
     */
    public function &insertInto($table)
    {
        $this->appendToQuery('INSERT INTO "'.$table.'" ');

        //chain functions calls
        return $this;
    }

    /**
     * Add (col1, col2, col3) VALUES (?, ?, ?, ?) to the SQL query.
     *
     * @param array $values an associative array of columnName => rowValue
     *
     * @return \Gishiki\Database\Adapters\Utils\SQLQueryBuilder the updated sql builder
     */
    public function &values(array $values)
    {
        $this->appendToQuery('('.implode(', ', array_keys($values)).') VALUES (');

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

    /**
     * Add LIMIT ? OFFSET ? ORDER BY ..... to the SQL query wheter they are needed.
     *
     * @param ResultModifier $mod the result modifier
     *
     * @return \Gishiki\Database\Adapters\Utils\SQLQueryBuilder the updated sql builder
     */
    public function &limitOffsetOrderBy(ResultModifier $mod)
    {
        //execute the private function 'export'
        $exportMethod = new \ReflectionMethod($mod, 'export');
        $exportMethod->setAccessible(true);
        $resultExported = $exportMethod->invoke($mod);

        //append limit if needed
        if ($resultExported['limit'] > 0) {
            $this->appendToQuery('LIMIT '.$resultExported['limit'].' ');
        }

        //append offset if needed
        if ($resultExported['skip'] > 0) {
            $this->appendToQuery('OFFSET '.$resultExported['skip'].' ');
        }

        //append order if needed
        if (count($resultExported['order']) > 0) {
            $this->appendToQuery('ORDER BY ');
            $first = true;
            foreach ($resultExported['order'] as $column => $order) {
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

    /**
     * Add SELECT * FROM  %tablename% to the SQL query.
     *
     * @param string $table the name of the table to be affected
     *
     * @return \Gishiki\Database\Adapters\Utils\SQLQueryBuilder the updated sql builder
     */
    public function &selectAllFrom($table)
    {
        $this->appendToQuery('SELECT * FROM "'.$table.'" ');

        //chain functions calls
        return $this;
    }

    /**
     * Add SELECT col1, col2, col3 FROM %tablename% to the SQL query.
     *
     * @param string $table  the name of the table to be affected
     * @param array  $fields the list containing names of columns to be selected
     *
     * @return \Gishiki\Database\Adapters\Utils\SQLQueryBuilder the updated sql builder
     */
    public function &selectFrom($table, array $fields)
    {
        $this->appendToQuery('SELECT '.implode(', ', $fields).' FROM "'.$table.'" ');

        //chain functions calls
        return $this;
    }

    /**
     * Add DELETE FROM  %tablename% to the SQL query.
     *
     * @param string $table the name of the table to be affected
     *
     * @return \Gishiki\Database\Adapters\Utils\SQLQueryBuilder the updated sql builder
     */
    public function &deleteFrom($table)
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
        return self::Beautify($this->sql);
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
