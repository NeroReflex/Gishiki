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
 * Represent a table inside a table of a relational database.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class Table
{
    /**
     * @var string the name of the table
     */
    protected $name;

    /**
     * Initialize a table with the given name.
     * This function internally calls setName(), and you should catch
     * exceptions thrown by that function.
     *
     * @param string $name the name of the table
     */
    public function __construct($name)
    {
        $this->name = '';

        $this->setName($name);
    }

    /**
     * Change the name of the current table.
     *
     * @param string $name the name of the table
     *
     * @throws \InvalidArgumentException the table name is invalid
     */
    public function &setName($name)
    {
        //avoid bad names
        if ((!is_string($name)) || (strlen($name) < 0)) {
            throw new \InvalidArgumentException('The name of a table must be expressed as a non-empty string');
        }

        $this->name = $name;

        return $this;
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
