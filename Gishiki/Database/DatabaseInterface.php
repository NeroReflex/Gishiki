<?php
/**************************************************************************
Copyright 2016 Benato Denis

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

use Gishiki\Algorithms\Collections\GenericCollection;

/**
 * Represent how a database connection must be implemented.
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
interface DatabaseInterface
{
    /**
     * Create a new database handler and logically connect the given database.
     * 
     * @param string $details the connection string
     *
     * @throws DatabaseException         the error occurred while connecting to the database
     * @throws \InvalidArgumentException given details are invalid
     */
    public function __construct($details);

    /**
     * Logically connect the given database.
     * 
     * @param string $details the connection string
     *
     * @throws DatabaseException         the error occurred while connecting to the database
     * @throws \InvalidArgumentException given details are invalid
     */
    public function Connect($details);

    /**
     * Check if the database handler is connected with a real database.
     * 
     * @return bool TRUE only if the database connection is alive
     */
    public function Connected();

    /**
     * Write data to the database on the given collection/table.
     * The name of the collection must be given witht the database.collection syntax.
     * 
     * @param string                    $collection the name of the collection that will hold the data
     * @param array|CollectionInterface $data       the collection of data to be written
     * @throw \InvalidArgumentException the given collection name or data is not a collection of valid values
     *
     * @throws DatabaseException the error occurred while inserting data to the database
     *
     * @return ObjectIDInterface the unique ID of the inserted data
     */
    public function Insert($collection, $data);

    /**
     * Update values of documents/records matching the given criteria.
     * 
     * @param string                    $collection the name of the collection that will hold the changed data
     * @param array|CollectionInterface $data       the new data of selected documents/records
     * @param SelectionCriteria         $where      the criteria used to select documents/records to update
     * @throw \InvalidArgumentException the given collection name or data is not a collection of valid values
     *
     * @throws DatabaseException the error occurred while updating data on the database
     *
     * @return int the number of affected documents/records
     */
    public function Update($collection, $data, SelectionCriteria $where);

    /**
     * Remove documents/records matching the given criteria.
     * 
     * @param string            $collection the name of the collection that will be affected
     * @param SelectionCriteria $where      the criteria used to select documents/records to update
     * @throw \InvalidArgumentException the given collection name is not a valid collection name
     *
     * @throws DatabaseException the error occurred while removing data from the database
     *
     * @return int the number of removed documents/records
     */
    public function Delete($collection, SelectionCriteria $where);

    /**
     * Fetch documents/records matching the given criteria.
     * 
     * @param string            $collection the name of the collection that will be searched
     * @param SelectionCriteria $where      the criteria used to select documents/records to fetch
     * @param integer           $limit      the maximum number of documents/records to fetch
     * @throw \InvalidArgumentException the given collection name is not a valid collection name
     *
     * @throws DatabaseException the error occurred while fetching data from the database
     *
     * @return GenericCollection the search result expressed as a collection of \Gishiki\Database\Record
     */
    public function Fetch($collection, SelectionCriteria $where, $limit = -1);
}
