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

namespace Gishiki\Database\Adapters;

use Gishiki\Database\DatabaseInterface;
use Gishiki\Algorithms\Collections\GenericCollection;

/**
 * Represent a mongodb database.
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class Mongodb implements DatabaseInterface
{
    private $connected;
    private $connection;

    public function __construct($details)
    {
        $this->connection = array();
        $this->connected = false;

        //connect to the database
        $this->Connect($details);
    }

    public function Connect($details)
    {
        //check for malformed input
        if (!is_string($details)) {
            throw new \InvalidArgumentException('Connection info must be given as an array');
        }

        //try connecting with the database
        try {
            //connect to the database
            $this->connection['db_manager'] = new \MongoDB\Driver\Manager('mongodb://'.$details);
            $this->connected = true;
        } catch (\MongoDB\Driver\Exception\InvalidArgumentException $ex) {
            $this->connected = false;
            throw new DatabaseException('Error while parsing the connection query', 1);
        } catch (\MongoDB\Driver\Exception\RuntimeException $ex) {
            $this->connected = false;
            throw new DatabaseException('Error while parsing the connection query', 1);
        }
    }

    public function Connected()
    {
        return $this->connected;
    }

    public function Write($collection, $data)
    {
        //check for input and get an associative array
        if ((!is_string($collection)) || (strlen($collection) < 3) || (strpos($collection, '.') < 1)) {
            throw new \InvalidArgumentException('The collection name to be filled on the database must be given as "database.collection"');
        }
        if ((!is_array($data)) && (!($data instanceof GenericCollection))) {
            throw new \InvalidArgumentException('The data to be written on the database must be given as a collection');
        }
        $adaptedData = ($data instanceof GenericCollection) ? $data->all() : $data;

        $bulk = new \MongoDB\Driver\BulkWrite();

        $bulk->insert($adaptedData);
        $result = $this->connection['db_manager']->executeBulkWrite($collection, $bulk);

        return $result->getInsertedCount();
    }
}
