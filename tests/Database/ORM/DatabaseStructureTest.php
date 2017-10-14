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

namespace Gishiki\tests\Database\ORM;

use Gishiki\Algorithms\Collections\GenericCollection;
use Gishiki\Database\ORM\DatabaseStructure;
use Gishiki\Database\ORM\StructureException;
use Gishiki\Algorithms\Collections\SerializableCollection;
use PHPUnit\Framework\TestCase;


class DatabaseStructureTest extends TestCase
{
    public function testNoRelation()
    {
        $description = new SerializableCollection([
            "connection" => "example",
            "tables" => [
                [
                "name" => "users",
                "fields" => [
                        [
                            "name" => "id",
                            "type" => "int",
                            "primary_key" => true,
                            "not_null" => true,
                            "auto_increment" => true,
                        ],
                        [
                            "name" => "username",
                            "type" => "string",
                            "not_null" => true,
                        ]
                    ]
                ]
            ]
        ]);

        $structure = new DatabaseStructure($description);

        $this->assertEquals("example", $structure->getConnectionName());

        $tables = $structure->getTables();

        $firstTable = $tables->pop();

        $this->assertEquals("users", $firstTable->getName());

        $userColumns = $firstTable->getColumns();

        $this->assertEquals("id", $userColumns[0]->getName());
        $this->assertEquals(true, $userColumns[0]->isPrimaryKey());
        $this->assertEquals(true, $userColumns[0]->isNotNull());
        $this->assertEquals(true, $userColumns[0]->isAutoIncrement());
    }

    public function testNoConnection()
    {
        $descr = new GenericCollection([
            "columns" => [ ]
        ]);

        $this->expectException(StructureException::class);

        new DatabaseStructure($descr);
    }


}