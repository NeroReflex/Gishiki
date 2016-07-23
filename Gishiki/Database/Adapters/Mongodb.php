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
use Gishiki\Database\DatabaseException;
use Gishiki\Database\SelectionCriteria;
use Gishiki\Algorithms\Collections\CollectionInterface;

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

    public function Insert($collection, $data)
    {
        //check for input and get an associative array
        if ((!is_string($collection)) || (strlen($collection) < 3) || (strpos($collection, '.') < 1)) {
            throw new \InvalidArgumentException('The collection name to be filled on the database must be given as "database.collection"');
        }
        if ((!is_array($data)) && (!($data instanceof CollectionInterface))) {
            throw new \InvalidArgumentException('The data to be written on the database must be given as a collection');
        }
        $adaptedData = ($data instanceof GenericCollection) ? $data->all() : $data;

        //create a bulkwriter and fill it
        $bulk = new \MongoDB\Driver\BulkWrite();

        //execute the write operation
        try {
            $nativeID = $bulk->insert($adaptedData);
            //$writeConcern = new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 1000);
            $result = $this->connection['db_manager']->executeBulkWrite($collection, $bulk/*, $writeConcern*/);
        } catch (\MongoDB\Driver\Exception\BulkWriteException $ex) {
            throw new DatabaseException('Insertion failed due to a write error', 2);
        } catch (\MongoDB\Driver\Exception\InvalidArgumentException $ex) {
            throw new DatabaseException('Insertion failed due to an error occurred while parsing data', 2);
        } catch (\MongoDB\Driver\Exception\ConnectionException $ex) {
            throw new DatabaseException('Insertion failed due to an unknown error on authentication', 2);
        } catch (\MongoDB\Driver\Exception\AuthenticationException $ex) {
            throw new DatabaseException('Insertion failed due to an unknown error on connection', 2);
        } catch (\MongoDB\Driver\Exception\RuntimeException $ex) {
            throw new DatabaseException('Insertion failed due to an unknown error', 2);
        }

        //check for write errors
        if ($result->getInsertedCount() <= 0) {
            throw new DatabaseException('Insertion failed due to an unknown error', 2);
        }

        //return the MongoDB Object ID
        return new MongodbObjectID($nativeID);
    }

    public function Update($collection, $data, SelectionCriteria $where)
    {
        //check for input and get an associative array
        if ((!is_string($collection)) || (strlen($collection) < 3) || (strpos($collection, '.') < 1)) {
            throw new \InvalidArgumentException('The collection name to be filled on the database must be given as "database.collection"');
        }
        if ((!is_array($data)) && (!($data instanceof CollectionInterface))) {
            throw new \InvalidArgumentException('The data to be written on the database must be given as a collection');
        }
        $adaptedData = ($data instanceof GenericCollection) ? $data->all() : $data;

        //create a bulkwriter and fill it
        $bulk = new \MongoDB\Driver\BulkWrite(['ordered' => true]);

        //execute the write operation
        try {
            $bulk->update(self::resolveSelectionCriteria($where), [
                '$set' => $adaptedData, ], ['multi' => true, 'upsert' => false, 'w' => 'majority',
            ]);
            //$writeConcern = new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 1000);
            $result = $this->connection['db_manager']->executeBulkWrite($collection, $bulk/*, $writeConcern*/);
        } catch (\MongoDB\Driver\Exception\BulkWriteException $ex) {
            throw new DatabaseException('Update failed due to a write error', 2);
        } catch (\MongoDB\Driver\Exception\InvalidArgumentException $ex) {
            throw new DatabaseException('Update failed due to an error occurred while parsing data', 2);
        } catch (\MongoDB\Driver\Exception\ConnectionException $ex) {
            throw new DatabaseException('Update failed due to an unknown error on authentication', 2);
        } catch (\MongoDB\Driver\Exception\AuthenticationException $ex) {
            throw new DatabaseException('Update failed due to an unknown error on connection', 2);
        } catch (\MongoDB\Driver\Exception\RuntimeException $ex) {
            throw new DatabaseException('Update failed due to an unknown error', 2);
        }

        //check for write errors
        if ($result->getModifiedCount() <= 0) {
            throw new DatabaseException('Update failed due to an unknown error', 2);
        }

        //return the MongoDB Object ID
        return $result->getModifiedCount();
    }

    private static function resolveSelectionCriteria(SelectionCriteria $where)
    {
        //reflect the criteria object
        $reflector = new \ReflectionObject($where);

        //get the id selection
        $idProperty = $reflector->getProperty('id');
        $idProperty->setAccessible(true);
        $idValue = $idProperty->getValue($where);

        //get the criteria selection
        $criteriaProperty = $reflector->getProperty('criteria');
        $criteriaProperty->setAccessible(true);
        $criteriaValue = $criteriaProperty->getValue($where);

        if ((is_null($idValue)) && (count($criteriaValue) <= 0)) {
            return array();
        }

        if (is_null($idValue)) {
            return $criteriaValue;
        }

        if (count($criteriaValue) <= 0) {
            return ['_id' => new \MongoDB\BSON\ObjectID(strtolower(''.$idValue))];
        }

        return [
            '$and' => [
                ['_id' => new \MongoDB\BSON\ObjectID(strtolower(''.$idValue))],
                $criteriaProperty,
            ],
        ];
    }
}
