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

use Gishiki\Algorithms\Collections\SerializableCollection;
use Gishiki\Database\DatabaseInterface;

/**
 * Provides basic implementation of an object that
 * are eligible for CRUD operations inside a database.
 *
 * @see ActiveRecordInterface Documentation.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class ActiveRecord extends SerializableCollection implements ActiveRecordInterface
{
    use ActiveRecordTableTrait;

    /**
     * @var DatabaseInterface|null the database handler
     */
    protected $database = null;

    public function __construct(DatabaseInterface &$connection)
    {
        //store a reference to the database connection
        $this->database = &$connection;
    }

    public function save()
    {
        if (!is_null($this->getObjectID())) {
            $this->database->create("", $this->all());
        }
    }

    public function delete()
    {
        // TODO: Implement delete() method.
    }

    public function getObjectID()
    {

    }

    public static function load(DatabaseInterface &$connection) : array
    {
        // TODO: Implement load() method.
    }
}