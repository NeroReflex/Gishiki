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
use Gishiki\Database\Schema\ColumnType;
use Gishiki\Database\Schema\Table;
use PHPUnit\Framework\TestCase;


class DatabaseStructureTest extends TestCase
{
    public function testTypeMoney()
    {
        $description = new SerializableCollection([
            "connection" => "example",
            "tables" => [
                [
                    "name" => __FUNCTION__,
                    "fields" => [
                        [
                            "name" => __FUNCTION__,
                            "type" => "money"
                        ]
                    ]
                ]
            ]
        ]);

        $dbStructure = new DatabaseStructure($description);
        $tables = $dbStructure->getTables();

        $testField = $tables->pop()->getColumns()[0];

        $this->assertEquals(ColumnType::MONEY, $testField->getType());
    }

    public function testTypeDatetime()
    {
        $description = new SerializableCollection([
            "connection" => "example",
            "tables" => [
                [
                    "name" => __FUNCTION__,
                    "fields" => [
                        [
                            "name" => __FUNCTION__,
                            "type" => "datetime"
                        ]
                    ]
                ]
            ]
        ]);

        $dbStructure = new DatabaseStructure($description);
        $tables = $dbStructure->getTables();

        $testField = $tables->pop()->getColumns()[0];

        $this->assertEquals(ColumnType::DATETIME, $testField->getType());
    }

    public function testTypeDouble()
    {
        $description = new SerializableCollection([
            "connection" => "example",
            "tables" => [
                [
                    "name" => __FUNCTION__,
                    "fields" => [
                        [
                            "name" => __FUNCTION__,
                            "type" => "double"
                        ]
                    ]
                ]
            ]
        ]);

        $dbStructure = new DatabaseStructure($description);
        $tables = $dbStructure->getTables();

        $testField = $tables->pop()->getColumns()[0];

        $this->assertEquals(ColumnType::DOUBLE, $testField->getType());
    }

    public function testTypeFloat()
    {
        $description = new SerializableCollection([
            "connection" => "example",
            "tables" => [
                [
                    "name" => __FUNCTION__,
                    "fields" => [
                        [
                            "name" => __FUNCTION__,
                            "type" => "float"
                        ]
                    ]
                ]
            ]
        ]);

        $dbStructure = new DatabaseStructure($description);
        $tables = $dbStructure->getTables();

        $testField = $tables->pop()->getColumns()[0];

        $this->assertEquals(ColumnType::FLOAT, $testField->getType());
    }

    public function testTypeNumeric()
    {
        $description = new SerializableCollection([
            "connection" => "example",
            "tables" => [
                [
                    "name" => __FUNCTION__,
                    "fields" => [
                        [
                            "name" => __FUNCTION__,
                            "type" => "numeric"
                        ]
                    ]
                ]
            ]
        ]);

        $dbStructure = new DatabaseStructure($description);
        $tables = $dbStructure->getTables();

        $testField = $tables->pop()->getColumns()[0];

        $this->assertEquals(ColumnType::NUMERIC, $testField->getType());
    }

    public function testTypeBigint()
    {
        $description = new SerializableCollection([
            "connection" => "example",
            "tables" => [
                [
                    "name" => __FUNCTION__,
                    "fields" => [
                        [
                            "name" => __FUNCTION__,
                            "type" => "bigint"
                        ]
                    ]
                ]
            ]
        ]);

        $dbStructure = new DatabaseStructure($description);
        $tables = $dbStructure->getTables();

        $testField = $tables->pop()->getColumns()[0];

        $this->assertEquals(ColumnType::BIGINT, $testField->getType());
    }

    public function testTypeSmallint()
    {
        $description = new SerializableCollection([
            "connection" => "example",
            "tables" => [
                [
                    "name" => __FUNCTION__,
                    "fields" => [
                        [
                            "name" => __FUNCTION__,
                            "type" => "smallint"
                        ]
                    ]
                ]
            ]
        ]);

        $dbStructure = new DatabaseStructure($description);
        $tables = $dbStructure->getTables();

        $testField = $tables->pop()->getColumns()[0];

        $this->assertEquals(ColumnType::SMALLINT, $testField->getType());
    }

    public function testTypeInt()
    {
        $description = new SerializableCollection([
            "connection" => "example",
            "tables" => [
                [
                    "name" => __FUNCTION__,
                    "fields" => [
                        [
                            "name" => __FUNCTION__,
                            "type" => "integer"
                        ]
                    ]
                ]
            ]
        ]);

        $dbStructure = new DatabaseStructure($description);
        $tables = $dbStructure->getTables();

        $testField = $tables->pop()->getColumns()[0];

        $this->assertEquals(ColumnType::INTEGER, $testField->getType());
    }

    public function testBadTypeName()
    {
        $description = new SerializableCollection([
            "connection" => "example",
            "tables" => [
                [
                    "name" => __FUNCTION__,
                    "fields" => [
                        [
                            "name" => "randomName",
                            "type" => "lol, away from me",
                            "primary_key" => true,
                            "not_null" => true,
                            "auto_increment" => true,
                        ]
                    ]
                ]
            ]
        ]);

        $this->expectException(StructureException::class);

        new DatabaseStructure($description);
    }

    public function testNoTypeName()
    {
        $description = new SerializableCollection([
            "connection" => "example",
            "tables" => [
                [
                    "name" => __FUNCTION__,
                    "fields" => [
                        [
                            "name" => "lol, no type :)",
                            "primary_key" => true,
                            "not_null" => true,
                            "auto_increment" => true,
                        ]
                    ]
                ]
            ]
        ]);

        $this->expectException(StructureException::class);

        new DatabaseStructure($description);
    }

    public function testNoFieldName()
    {
        $description = new SerializableCollection([
            "connection" => "example",
            "tables" => [
                [
                    "name" => __FUNCTION__,
                    "fields" => [
                        [
                            "type" => "int",
                            "primary_key" => true,
                            "not_null" => true,
                            "auto_increment" => true,
                        ]
                    ]
                ]
            ]
        ]);

        $this->expectException(StructureException::class);

        new DatabaseStructure($description);
    }

    public function testBadTableName()
    {
        $description = new SerializableCollection([
            "connection" => "example",
            "tables" => [
                [
                    "name" => "",
                    "fields" => [
                        [
                            "name" => "id",
                            "type" => "int",
                            "primary_key" => true,
                            "not_null" => true,
                            "auto_increment" => true,
                        ]
                    ]
                ]
            ]
        ]);

        $this->expectException(StructureException::class);

        new DatabaseStructure($description);
    }

    public function testBadField()
    {
        $description = new SerializableCollection([
            "connection" => "example",
            "tables" => [ null ]
        ]);

        $this->expectException(StructureException::class);

        new DatabaseStructure($description);
    }

    public function testNoTables()
    {
        $description = new SerializableCollection([
            "connection" => "example",
        ]);

        $this->expectException(StructureException::class);

        new DatabaseStructure($description);
    }

    public function testNoConnectionName()
    {
        $description = new SerializableCollection([
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

        $this->expectException(StructureException::class);

        new DatabaseStructure($description);
    }

    public function testBadConnectionName()
    {
        $description = new SerializableCollection([
            "connection" => "",
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

        $this->expectException(StructureException::class);

        new DatabaseStructure($description);
    }

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