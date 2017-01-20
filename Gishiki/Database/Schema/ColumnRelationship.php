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

/**
 * Represent a column inside a table of a relational database.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class ColumnRelationship
{
    /**
     * @var Column the column of the current table
     */
    protected $column;

    /**
     * @var Column the foreign key column (in another table)
     */
    protected $foreignKey;

    public function __construct(Column &$column, Column &$foreignColumn)
    {
        //oh come one.... you cannot create a reference to a column in the sable table
        if ($column->getTable() == $foreignColumn->getTable()) {
            
        }
        
        //and I hope you are not going to reference something that is not a primary key
        if ($foreignColumn->getPrimaryKey()) {
            
        }
    }
}
