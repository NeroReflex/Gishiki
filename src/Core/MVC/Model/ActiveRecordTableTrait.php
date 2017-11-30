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

namespace Gishiki\Core\MVC\Model;

use Gishiki\Algorithms\Collections\GenericCollection;
use Gishiki\Database\Schema\Column;
use Gishiki\Database\Schema\ColumnRelation;
use Gishiki\Database\Schema\ColumnType;
use Gishiki\Database\Schema\Table;

/**
 * Provides a working implementation of table schema extractor.
 *
 * @see ActiveRecordInterface Documentation.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
trait ActiveRecordTableTrait
{
    private static $typeMap = [
        'text' => ColumnType::TEXT,
        'string' => ColumnType::TEXT,
        'smallint' => ColumnType::SMALLINT,
        'int' => ColumnType::INTEGER,
        'integer' => ColumnType::INTEGER,
        'bigint' => ColumnType::BIGINT,
        'money' => ColumnType::MONEY,
        'numeric' => ColumnType::NUMERIC,
        'float' => ColumnType::FLOAT,
        'double' => ColumnType::DOUBLE,
        'datetime' => ColumnType::DATETIME,
    ];

    /**
     * @var Table|null the table definition
     */
    protected static $table = null;

    protected static $structure = [];

    /**
     * Check if the table definition has been loaded
     *
     * @return bool true if the table is defined
     */
    private static function isTableLoaded() :bool
    {
        return !is_null(static::$table);
    }


    public static function &getTableDefinition() : Table
    {
        if (!self::isTableLoaded()) {
            self::loadTable();
        }

        return static::$table;
    }

    /**
     * Load the table definition from the static::$structure array.
     *
     * @throws ActiveRecordException the exception preventing data to be parsed correctly
     */
    private static function loadTable()
    {
        if ((!array_key_exists('name', static::$structure)) || (!is_string(static::$structure['name'])) || (strlen(static::$structure['name']) <= 0)) {
            throw new ActiveRecordException('Table definition does not contains a valid name', 100);
        }

        static::$table = new Table(static::$structure['name']);

        self::loadFields();
    }

    /**
     * Load all fields inside the table from the static::$structure array.
     *
     * @throws ActiveRecordException the exception preventing data to be parsed correctly
     */
    private static function loadFields()
    {
        if ((!array_key_exists('fields', static::$structure)) || (!is_array(static::$structure['fields'])) || (count(static::$structure['fields']) <= 0)) {
            throw new ActiveRecordException('Table definition does not contains a valid fields set', 104);
        }

        foreach (static::$structure['fields'] as &$fieldDefinition) {
            self::loadField($fieldDefinition);
        }
    }

    /**
     * Load a field inside the table from the static::$structure array.
     *
     * @param array $fieldDefinition the field definition
     * @throws ActiveRecordException the exception preventing data to be parsed correctly
     */
    private static function loadField(array $fieldDefinition)
    {
        $field = new GenericCollection($fieldDefinition);

        if ((!$field->has('name')) || (!is_string($field->get('name'))) || (strlen($field->get('name')) <= 0)) {
            throw new ActiveRecordException('Table definition contains a field with no name', 101);
        }

        if ((!$field->has('type')) || (!is_string($field->get('type'))) || (strlen($field->get('type')) <= 0)) {
            throw new ActiveRecordException('Table definition contains a field with no type ('.$field->get('name').')', 102);
        }

        if (!array_key_exists($field->get('type'), self::$typeMap)) {
            throw new ActiveRecordException('Invalid data type ('.$field->get('type').') for column '.$field->get('name'), 103);
        }

        //build the field as it was defined
        $currentField = new Column($field->get('name'), self::$typeMap[$field->get('type')]);
        $currentField->setAutoIncrement($field->has('primary_key') && ($field->get('primary_key') === true));
        $currentField->setAutoIncrement($field->has('not_null') && ($field->get('not_null') === true));
        $currentField->setAutoIncrement($field->has('auto_increment') && ($field->get('auto_increment') === true));

        static::$table->addColumn($currentField);
    }
}
