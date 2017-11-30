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

use Gishiki\Database\DatabaseInterface;
use Gishiki\Database\Schema\Table;

/**
 * Represent the interface following active record pattern
 * used on objects that are mapped into a database.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
interface ActiveRecordInterface
{
    /**
     * Get the table that will be used when doing CRUD operations.
     *
     * @return Table the table currently used for operations
     * @throws ActiveRecordException the exception preventing definition to be inspected
     */
    public static function &getTableDefinition() : Table;

    /**
     * Create a new object.
     *
     * @param DatabaseInterface $connection the database connection to be used
     */
    public function __construct(DatabaseInterface &$connection);

    /**
     * Save the object instance to the database.
     *
     * This function automatically performs create or update,
     * based on data availability within the database.
     */
    public function save();

    /**
     * Read one or more object from the database.
     *
     * @param DatabaseInterface $connection the database connection to be used
     * @return ActiveRecordInterface[] the result set
     */
    public static function load(DatabaseInterface &$connection) : array;

    /**
     * Delete the object from the database.
     *
     * Does nothing if the object is not currently stored.
     */
    public function delete();

    /**
     * Return the current object ID, as it is used within the database
     */
    public function getObjectID();

}