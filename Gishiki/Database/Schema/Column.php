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
final class Column
{
    /**
     * @var string the name of the column
     */
    protected $name;

    /**
     * @var int the type of the column, expressed as one of the ColumnType constants
     */
    protected $type;

    /**
     * @var bool TRUE if the column cannot hold null
     */
    protected $notNull;

    /**
     * @var bool TRUE if the column is autoincrement
     */
    protected $autoIncrement;

    /**
     * @var bool TRUE if the column is a primary key
     */
    protected $pkey;

    /**
     * @var ColumnRelation|null the relation to an external table or null
     */
    protected $relation;

    /**
     * Initialize a column with the given name.
     * This function internally calls setName(), and you should catch
     * exceptions thrown by that function.
     *
     * @param string $name the name of the column
     * @param int    $type the data type of the column
     */
    public function __construct($name, $type)
    {
        $this->name = '';
        $this->dataType = 0;
        $this->pkey = false;
        $this->notNull = false;
        $this->relation = null;
        $this->autoIncrement = null;
        $this->setName($name);
        $this->setType($type);
    }

    /**
     * Change the auto increment flag on the column.
     *
     * @param bool $enable TRUE is used to flag an auto increment column as such
     * @return Column a reference to the modified Column
     * @throws \InvalidArgumentException the new status is invalid
     */
    public function &setAutoIncrement($enable)
    {
        if (!is_bool($enable)) {
            throw new \InvalidArgumentException('The auto-increment flag of a column must be given as a boolean value');
        }

        $this->autoIncrement = $enable;

        return $this;
    }

    public function getAutoIncrement()
    {
        return $this->autoIncrement;
    }

    /**
     * Change the primary key flag on the column.
     *
     * @param bool $enable TRUE is used to flag a not null column as such
     * @return Column a reference to the modified Column
     * @throws \InvalidArgumentException the new status is invalid
     */
    public function &setNotNull($enable)
    {
        if (!is_bool($enable)) {
            throw new \InvalidArgumentException('The not null flag of a column must be given as a boolean value');
        }

        $this->notNull = $enable;

        return $this;
    }

    /**
     * Retrieve the not null flag on the column.
     *
     * @return bool $enable TRUE if the column cannot contains null
     */
    public function getNotNull()
    {
        return $this->notNull;
    }

    /**
     * Change the primary key flag on the column.
     *
     * @param bool $enable TRUE is used to flag a primary key column as such
     * @return Column a reference to the modified Column
     * @throws \InvalidArgumentException the new status is invalid
     */
    public function &setPrimaryKey($enable)
    {
        if (!is_bool($enable)) {
            throw new \InvalidArgumentException('The primary key flag of a column must be given as a boolean value');
        }

        $this->pkey = $enable;

        return $this;
    }

    /**
     * Retrieve the auto increment flag on the column.
     *
     * @return bool $enable TRUE if the column is a primary key
     */
    public function getPrimaryKey()
    {
        return $this->pkey;
    }

    /**
     * Change the relation of the current column.
     *
     * @param ColumnRelation $rel the column relation
     * @return Column a reference to the modified Column
     * @throws \InvalidArgumentException the column name is invalid
     */
    public function &setRelation(ColumnRelation &$rel)
    {
        $this->relation = $rel;

        return $this;
    }

    /**
     * Retrieve the relation of the column.
     *
     * @return ColumnRelation|null the column relation or null
     */
    public function getRelation()
    {
        return $this->relation;
    }

    /**
     * Change the name of the current column.
     *
     * @param string $name the name of the column
     * @return Column a reference to the modified Column
     * @throws \InvalidArgumentException the column name is invalid
     */
    public function &setName($name)
    {
        //avoid bad names
        if ((!is_string($name)) || (strlen($name) <= 0)) {
            throw new \InvalidArgumentException('The name of a column must be expressed as a non-empty string');
        }

        $this->name = $name;

        return $this;
    }

    /**
     * Retrieve the name of the column.
     *
     * @return string the column name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Change the type of the current table passing as argument one
     * of the ColumnType contants.
     *
     * @param string $type the type of the column
     * @return Column a reference to the modified Column
     * @throws \InvalidArgumentException the column name is invalid
     */
    public function &settype($type)
    {
        //avoid bad names
        if ((!is_integer($type)) || ($type >= ColumnType::UNKNOWN) || ($type < 0)) {
            throw new \InvalidArgumentException('The type of the column is invalid.');
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Retrieve the type of the column.
     *
     * @return int the column name
     */
    public function getType()
    {
        return $this->type;
    }
}
