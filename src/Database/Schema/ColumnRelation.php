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

namespace Gishiki\Database\Schema;

use Gishiki\Database\DatabaseException;

/**
 * Represent a column inside a table of a relational database.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class ColumnRelation
{
    /**
     * @var Table the table containing the foreign key
     */
    protected $foreignTable;

    /**
     * @var Column the column of the current table
     */
    protected $foreignKey;

    /**
     * Create a new relation to the given pprimary key.
     *
     * @param \Gishiki\Database\Schema\Column $externColumn the foreign column
     *
     * @throws DatabaseException the error occurred while enstabilishing the Relation
     */
    public function __construct(Table &$externTable, Column &$externColumn)
    {
        //I hope you are not going to reference something that is not a primary key
        if (!$externColumn->isPrimaryKey()) {
            throw new DatabaseException('A Relation can only be created with a foreign primary key', 128);
        }

        //... oh and I am pretty sure you are not doing something bad, right?
        if (!in_array($externColumn, $externTable->getColumns())) {
            throw new DatabaseException("The given foreign table doesn't contain a column with the same name", 129);
        }

        $this->foreignKey = $externColumn;
        $this->foreignTable = $externTable;
    }

    /**
     * Get the column on the current table.
     *
     * @return Column the reference to the column
     */
    public function &getForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * Get the table containing the foreign key.
     *
     * @return Table the table containing the foreign key
     */
    public function &getForeignTable()
    {
        return $this->foreignTable;
    }
}
