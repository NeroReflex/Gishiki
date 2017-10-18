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

namespace Gishiki\Database\ORM;

use Gishiki\Algorithms\Collections\CollectionInterface;
use Gishiki\Algorithms\Collections\GenericCollection;
use Gishiki\Algorithms\Collections\StackCollection;
use Gishiki\Database\Schema\Column;
use Gishiki\Database\Schema\ColumnType;
use Gishiki\Database\Schema\Table;

/**
 * Build the database logic structure from a json descriptor.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class DatabaseStructure
{
    /**
     * @var string The name of the corresponding connection
     */
    protected $connectionName;

    /**
     * @var StackCollection the collection of tables in creation reversed order
     */
    protected $stackTables;

    /**
     * Build the Database structure from a json text.
     *
     * @param  CollectionInterface $description the json description of the database
     * @throws StructureException the error in the description
     */
    public function __construct(CollectionInterface &$description)
    {
        $this->stackTables = new StackCollection();

        if (!$description->has('connection')) {
            throw new StructureException('A database description must contains the connection field', 0);
        }

        $this->connectionName = $description->get('connection');

        if ((!is_string($this->connectionName)) || (strlen($this->connectionName) <= 0)) {
            throw new StructureException('The connection name must be given as a non-empty string', 3);
        }

        if (!$description->has('tables')) {
            throw new StructureException("A database description must contains a tables field", 1);
        }

        foreach ($description->get('tables') as $tb) {
            if (!is_array($tb)) {
                throw new StructureException("Wrong structure: the 'tables' filed must contains arrays", 2);
            }

            $table = new GenericCollection($tb);

            if ((!$table->has('name')) || (!is_string($table->get('name'))) || (strlen($table->get('name')) <= 0)) {
                throw new StructureException('Each table must have a name given as a non-empty string', 4);
            }

            $currentTable = new Table($table->get('name'));

            foreach ($table->get('fields') as $fd) {
                $field = new GenericCollection($fd);

                if (!$field->has('name')) {
                    throw new StructureException('Each column must have a name', 5);
                }

                if (!$field->has('type')) {
                    throw new StructureException('Each column must have a type', 6);
                }

                $typeIdentifier = ColumnType::UNKNOWN;
                switch ($field->get('type')) {
                    case 'string':
                    case 'text':
                        $typeIdentifier = ColumnType::TEXT;
                        break;

                    case 'smallint':
                        $typeIdentifier = ColumnType::SMALLINT;
                        break;

                    case 'int':
                    case 'integer':
                        $typeIdentifier = ColumnType::INTEGER;
                        break;

                    case 'bigint':
                        $typeIdentifier = ColumnType::BIGINT;
                        break;

                    case 'money':
                        $typeIdentifier = ColumnType::MONEY;
                        break;

                    case 'numeric':
                        $typeIdentifier = ColumnType::NUMERIC;
                        break;

                    case 'float':
                        $typeIdentifier = ColumnType::FLOAT;
                        break;

                    case 'double':
                        $typeIdentifier = ColumnType::DOUBLE;
                        break;

                    case 'datetime':
                        $typeIdentifier = ColumnType::DATETIME;
                        break;

                    default:
                        throw new StructureException('Invalid data type for column '.$field->get('name'), 7);
                }

                $currentField = new Column($field->get('name'), $typeIdentifier);
                $currentField->setPrimaryKey(($field->get('primary_key') === true));
                $currentField->setNotNull(($field->get('not_null') === true));
                $currentField->setAutoIncrement(($field->get('auto_increment') === true));

                $currentTable->addColumn($currentField);
            }

            //add the table to the collection
            $this->stackTables->push($currentTable);
        }
    }

    /**
     * Get the name of the database connection that will be used
     * to create tables and overall structure.
     *
     * @return string the name of the database connection
     */
    public function getConnectionName() : string
    {
        return $this->connectionName;
    }

    /**
     * Get the collection of tables in a stack where the first table to be popped
     * is the first that must be created.
     *
     * @return StackCollection the collection of tables
     */
    public function getTables() : StackCollection
    {
        //clone the database structure
        $tables = clone $this->stackTables;

        //reverse the table order to ease the life of the caller
        $tables->reverse();

        return $tables;
    }
}
