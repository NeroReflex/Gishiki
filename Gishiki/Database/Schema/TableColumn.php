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
final class TableColumn {
    
    /**
     * @var string the name of the column
     */
    protected $name;
    
    /**
     * @var integer the type of the column, expressed as one of the ColumnType constants
     */
    protected $dataType;
    
    /**
     * @var bool TRUE if the column uses auto increment
     */
    protected $ai;
    
    /**
     * @var bool TRUE if the column cannot hold null
     */
    protected $notNull;
    
    /**
     * Initialize an anonymous table
     */
    public function __construct()
    {
        $this->name = '';
        $this->dataType = 0;
        $this->ai = false;
    }
    
    /**
     * Change the primary key flag on the table
     * 
     * @param  bool $enable              TRUE is used to flag a primary key column as such
     * @throws \InvalidArgumentException the new status is invalid
     */
    public function setPrimaryKey($enable)
    {
        if (!is_bool($enable)) {
            throw new \InvalidArgumentException('The auto-increment flag of a column must be given as a boolean value');
        }
        
        $this->ai = $enable;
    }
    
    
    /**
     * Change the auto increment flag on the table
     * 
     * @param  bool $enable  TRUE if the column is a primary key
     */
    public function getPrimaryKey()
    {
        return $this->ai;
    }
    
    /**
     * Change the auto increment flag on the table
     * 
     * @param  bool $enable              TRUE is used to enables auto increment
     * @throws \InvalidArgumentException the new status is invalid
     */
    public function setAutoIncrement($enable)
    {
        if (!is_bool($enable)) {
            throw new \InvalidArgumentException('The auto-increment flag of a column must be given as a boolean value');
        }
        
        $this->ai = $enable;
    }
    
    
    /**
     * Change the auto increment flag on the table
     * 
     * @param  bool $enable              TRUE is used to enables auto increment
     */
    public function getAutoIncrement()
    {
        return $this->ai;
    }
    
    /**
     * Change the name of the current table.
     * 
     * @param  string $name the name of the table
     * @throws \InvalidArgumentException the table name is invalid
     */
    public function setName($name)
    {
        //avoid bad names
        if ((!is_string($name)) || (strlen($name) < 0)) {
            throw new \InvalidArgumentException('The name of a column must be expressed as a non-empty string');
        }
        
        $this->name = $name;
    }
    
    /**
     * Retrieve the name of the table.
     * 
     * @return string the table name
     */
    public function getName()
    {
        return $this->name;
    }
}
