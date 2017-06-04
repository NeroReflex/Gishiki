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

namespace Gishiki\Database\ORM;

use Gishiki\Algorithms\Collections\StackCollection;
use Gishiki\Algorithms\Collections\SerializableCollection;

/**
 * Build the database logic structure from a json descriptor.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class DatabaseStructure
{
    /**
     * @var string The name of the corresponding connection
     */
    protected $connectionName;

    /**
     * @var StackCollection the collection of tables in creation reversed order
     */
    protected $stackTables;

    public function __construct($description)
    {
        $this->connectionName = new StackCollection();

        //deserialize the json content
        $deserializedDescription = SerializableCollection::deserialize($description);

        if (!$deserializedDescription->has('connection')) {
            throw new StructureException('A database description must contains the connection field', 0);
        }

        $this->connectionName = $deserializedDescription->get('connection');
    }

}