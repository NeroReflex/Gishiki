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

namespace Gishiki\Database;

use Gishiki\Database\Runtime\ResultModifier;
use Gishiki\Database\Runtime\SelectionCriteria;

/**
 * Represent how a relational database connection must be implemented.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
interface RelationalDatabaseInterface extends DatabaseInterface
{
    /**
     * Fetch documents/records matching the given criteria, but retrieve only the specified columns.
     *
     * @param string            $collection the name of the collection that will be searched
     * @param array             $fields     the list containing names of columns to be fetched
     * @param SelectionCriteria $where      the criteria used to select documents/records to fetch
     * @param ResultModifier    $mod        the modifier to be applied to the result set
     * @throw \InvalidArgumentException the given collection name is not a valid collection name
     *
     * @throws DatabaseException the error occurred while fetching data from the database
     *
     * @return array the search result expressed as an array of associative arrays
     */
    public function readSelective($collection, $fields, SelectionCriteria $where, ResultModifier $mod);

    public function createTable($tbName);
}
