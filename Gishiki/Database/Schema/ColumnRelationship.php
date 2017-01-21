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
     * @var Column the column of the current table
     */
    protected $column;

    /**
     * @var Column the foreign key column (in another table)
     */
    protected $externColumn;

    /**
     * Create a new Relation from the first column to the second one, which
     * is a primary key.
     * 
     * @param  \Gishiki\Database\Schema\Column $column       the column to be related with a foreign key
     * @param  \Gishiki\Database\Schema\Column $externColumn the foreign column
     * @throws DatabaseException the error occurred while enstabilishing the Relation
     */
    public function __construct(Column &$column, Column &$externColumn)
    {
        //oh come one.... you cannot create a reference to a column in the sable table
        if ($column->getTable() == $foreignColumn->getTable()) {
            throw new DatabaseException('A Relation between two column cannot be created on the same column', 128);
        }
        
        //and I hope you are not going to reference something that is not a primary key
        if ($foreignColumn->getPrimaryKey()) {
            throw new DatabaseException('A Relation can only be created with a foreign primary key', 129);
        }
        
        $this->column = $column;
        $this->externColumn = $externColumn;
    }
    
    /**
     * Get the column on the current table.
     * 
     * @return Column the reference to the column
     */
    public function &getForeignKey() {
        return $this->column;
    }
    
    /**
     * Get the column on the related table.
     * 
     * @return Column the reference to the column
     */
    public function &getReference()
    {
        return $this->externColumn;
    }
}
