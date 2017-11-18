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

namespace Gishiki\Database\Adapters\Utils\SQLGenerator;

use Gishiki\Database\Runtime\SelectionCriteria;
use Gishiki\Database\Runtime\ResultModifier;
use Gishiki\Database\Runtime\FieldOrdering;

/**
 * Represents how a SQL query wrapper must be implemented when designing a new database connector.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
interface SQLWrapperInterface
{
    /**
     * Initialize an empty SQL query.
     */
    public function __construct();

    /**
     * Add UPDATE %tablename% to the SQL query.
     *
     * @param string $table the name of the table to be updated
     *
     * @return SQLWrapperInterface the updated sql builder
     */
    public function &update($table) : SQLWrapperInterface;

    /**
     * Add SET col1 = ?, col2 = ?, col3 = ? to the SQL query.
     *
     * @param array $values an associative array of columns => value to be changed
     *
     * @return SQLWrapperInterface the updated sql builder
     */
    public function &set(array $values) : SQLWrapperInterface;

    /**
     * Add CREATE TABLE IF NOT EXISTS %tablename% to the SQL query.
     *
     * @param string $tableName the name of the table
     *
     * @return SQLWrapperInterface the updated sql builder
     */
    public function &createTable($tableName) : SQLWrapperInterface;

    /**
     * Add DROP TABLE IF EXISTS %tablename% to the SQL query.
     *
     * @param string $tableName the name of the table
     *
     * @return SQLWrapperInterface the updated sql builder
     */
    public function &dropTable($tableName) : SQLWrapperInterface;

    /**
     * Add WHERE col1 = ? OR col2 <= ? ....... to the SQL query.
     *
     * @param SelectionCriteria $where the selection criteria
     *
     * @return SQLWrapperInterface the updated sql builder
     */
    public function &where(SelectionCriteria $where) : SQLWrapperInterface;

    /**
     * Add INSERT INTO %tablename% to the SQL query.
     *
     * @param string $table the name of the table to be affected
     *
     * @return SQLWrapperInterface the updated sql builder
     */
    public function &insertInto($table) : SQLWrapperInterface;

    /**
     * Add (col1, col2, col3) VALUES (?, ?, ?, ?) to the SQL query.
     *
     * @param array $values an associative array of columnName => rowValue
     *
     * @return SQLWrapperInterface the updated sql builder
     */
    public function &values(array $values) : SQLWrapperInterface;

    /**
     * Add LIMIT ? OFFSET ? ORDER BY ..... to the SQL query whether they are needed.
     *
     * @param ResultModifier $mod the result modifier
     *
     * @return SQLWrapperInterface the updated sql builder
     */
    public function &limitOffsetOrderBy(ResultModifier $mod) : SQLWrapperInterface;

    /**
     * Add SELECT * FROM  %tablename% to the SQL query.
     *
     * @param string $table the name of the table to be affected
     *
     * @return SQLWrapperInterface the updated sql builder
     */
    public function &selectAllFrom($table) : SQLWrapperInterface;

    /**
     * Add DELETE FROM  %tablename% to the SQL query.
     *
     * @param string $table the name of the table to be affected
     *
     * @return SQLWrapperInterface the updated sql builder
     */
    public function &deleteFrom($table) : SQLWrapperInterface;

    /**
     * Add (id INTEGER PRIMARY KEY NUT NULL, name TEXT NOT NULL, ... ) to the SQL query.
     *
     * @param array $columns a collection of Gishiki\Database\Schema\Column
     *
     * @return SQLWrapperInterface the updated sql builder
     */
    public function &definedAs(array $columns) : SQLWrapperInterface;

    /**
     * Export the SQL query string with ? in place of actual parameters.
     *
     * @return string the SQL query without values
     */
    public function exportQuery() : string;

    /**
     * Export the list of parameters that will replace ? in the SQL query.
     *
     * @return array the list of params
     */
    public function exportParams() : array;
}