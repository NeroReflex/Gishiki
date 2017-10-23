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

use PHPUnit\Framework\TestCase;

/**
 * Tester to ensure a database structure is imported as expected.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class DatabaseStructureTest extends TestCase
{
    public function testNonTrivialRelation()
    {
        $description = new SerializableCollection([
            "connection" => "example",
            "tables" => [
                [
                    "name" => 'reseller',
                    "fields" => [
                        [
                            "name" => 'name',
                            "type" => "text",
                            "not_null" => true,
                        ],
                        [
                            "name" => 'id',
                            "type" => "int",
                            "primary_key" => true,
                            "not_null" => true,
                            "auto_increment" => true,
                        ],
                    ]
                ],
                [
                    "name" => 'author',
                    "fields" => [
                        [
                            "name" => 'first_name',
                            "type" => "text",
                            "not_null" => true,
                        ],
                        [
                            "name" => 'last_name',
                            "type" => "text",
                            "not_null" => true,
                        ],
                        [
                            "name" => 'id',
                            "type" => "int",
                            "primary_key" => true,
                            "not_null" => true,
                            "auto_increment" => true,
                        ],
                    ]
                ],
                [
                    "name" => 'book',
                    "fields" => [
                        [
                            "name" => "id",
                            "type" => "int",
                            "not_null" => true,
                            "auto_increment" => true,
                            "primary_key" => true
                        ],
                        [
                            "name" => "title",
                            "type" => "text",
                        ],
                        [
                            "name" => "cost",
                            "type" => "money",
                        ],
                        [
                            "name" => "author_id",
                            "type" => "int",
                            "not_null" => true,
                            "relation" => [
                                "table" => 'author',
                                "field" => 'id'
                            ]
                        ],
                        [
                            "name" => "reseller_id",
                            "type" => "int",
                            "not_null" => true,
                            "relation" => [
                                "table" => 'reseller',
                                "field" => 'id'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $dbStructure = new DatabaseStructure();
        $dbStructure->parse($description);

        $this->assertEquals(3, count($dbStructure->getTables()));

        $bookTable = $dbStructure->getTables()[2];
        $this->assertEquals('book', $bookTable->getName());

        $bookcolumns = 0;
        foreach ($bookTable->getColumns() as $currentColumn) {

            if (strcmp($currentColumn->getName(), "author_id") == 0) {
                $this->assertEquals('author', $currentColumn->getRelation()->getForeignTable()->getName());
                $this->assertEquals('id', $currentColumn->getRelation()->getForeignKey()->getName());
            } elseif (strcmp($currentColumn->getName(), "reseller_id") == 0) {
                $this->assertEquals('reseller', $currentColumn->getRelation()->getForeignTable()->getName());
                $this->assertEquals('id', $currentColumn->getRelation()->getForeignKey()->getName());
            }

            $bookcolumns++;
        }

        $this->assertEquals(5, $bookcolumns);
    }

    public function testRelation()
    {
        $description = new SerializableCollection([
            "connection" => "example",
            "tables" => [
                [
                    "name" => 'bar',
                    "fields" => [
                        [
                            "name" => 'foo',
                            "type" => "int",
                            "primary_key" => true,
                            "not_null" => true,
                            "auto_increment" => true,
                        ],
                        [
                            "name" => 'cash',
                            "type" => "money",
                            "not_null" => true,
                        ]
                    ]
                ],

                [
                    "name" => 'etc',
                    "fields" => [
                        [
                            "name" => __FUNCTION__,
                            "type" => "money",
                            "relation" => [
                                "table" => 'bar',
                                "field" => 'foo'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $dbStructure = new DatabaseStructure();
        $dbStructure->parse($description);

        $this->assertEquals(2, count($dbStructure->getTables()));

        $barTable = $dbStructure->getTables()[0];
        $this->assertEquals('bar', $barTable->getName());

        $etcTable = $dbStructure->getTables()[1];
        $this->assertEquals('etc', $etcTable->getName());
    }

    public function testBadRelation()
    {
        $description = new SerializableCollection([
            "connection" => "example",
            "tables" => [
                [
                    "name" => 'etc',
                    "fields" => [
                        [
                            "name" => __FUNCTION__,
                            "type" => "money",
                            "relation" => [
                                "table" => 'bar',
                                "field" => 'foo'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $this->expectException(StructureException::class);

        $dbStructure = new DatabaseStructure();
        $dbStructure->parse($description);
    }

    public function testBadFieldRelation()
    {
        $description = new SerializableCollection([
            "connection" => "example",
            "tables" => [
                [
                    "name" => __FUNCTION__,
                    "fields" => [
                        [
                            "name" => __FUNCTION__,
                            "type" => "money",
                            "relation" => [
                                "table" => 'bar',
                                "field" => 4
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $this->expectException(StructureException::class);

        $dbStructure = new DatabaseStructure();
        $dbStructure->parse($description);
    }

    public function testBadTableRelation()
    {
        $description = new SerializableCollection([
            "connection" => "example",
            "tables" => [
                [
                    "name" => __FUNCTION__,
                    "fields" => [
                        [
                            "name" => __FUNCTION__,
                            "type" => "money",
                            "relation" => [
                                "table" => null,
                                "field" => 'foo'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $this->expectException(StructureException::class);

        $dbStructure = new DatabaseStructure();
        $dbStructure->parse($description);
    }

    public function testBadRelationType()
    {
        $description = new SerializableCollection([
            "connection" => "example",
            "tables" => [
                [
                    "name" => __FUNCTION__,
                    "fields" => [
                        [
                            "name" => __FUNCTION__,
                            "type" => "money",
                            "relation" => null
                        ]
                    ]
                ]
            ]
        ]);

        $this->expectException(StructureException::class);

        $dbStructure = new DatabaseStructure();
        $dbStructure->parse($description);
    }

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
                            "type" => "money",
                        ]
                    ]
                ]
            ]
        ]);

        $dbStructure = new DatabaseStructure();
        $dbStructure->parse($description);

        $testField = (current($dbStructure->getTables()))->getColumns()[0];

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

        $dbStructure = new DatabaseStructure();
        $dbStructure->parse($description);

        $testField = (current($dbStructure->getTables()))->getColumns()[0];

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

        $dbStructure = new DatabaseStructure();
        $dbStructure->parse($description);

        $testField = (current($dbStructure->getTables()))->getColumns()[0];

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

        $dbStructure = new DatabaseStructure();
        $dbStructure->parse($description);

        $testField = (current($dbStructure->getTables()))->getColumns()[0];

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

        $dbStructure = new DatabaseStructure();
        $dbStructure->parse($description);

        $testField = (current($dbStructure->getTables()))->getColumns()[0];

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

        $dbStructure = new DatabaseStructure();
        $dbStructure->parse($description);

        $testField = (current($dbStructure->getTables()))->getColumns()[0];

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

        $dbStructure = new DatabaseStructure();
        $dbStructure->parse($description);

        $testField = (current($dbStructure->getTables()))->getColumns()[0];

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

        $dbStructure = new DatabaseStructure();
        $dbStructure->parse($description);

        $testField = (current($dbStructure->getTables()))->getColumns()[0];

        $this->assertEquals(ColumnType::INTEGER, $testField->getType());
    }

    public function testBadFieldDefinition()
    {
        $description = new SerializableCollection([
            "connection" => "example",
            "tables" => [
                [
                    "name" => __FUNCTION__,
                    "fields" => [
                        [
                            "name" => "randomName",
                            "type" => "int",
                            "primary_key" => true,
                            "not_null" => true,
                            "auto_increment" => true,
                        ],
                        null
                    ]
                ]
            ]
        ]);

        $this->expectException(StructureException::class);

        $dbStructure = new DatabaseStructure();
        $dbStructure->parse($description);
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

        $dbStructure = new DatabaseStructure();
        $dbStructure->parse($description);
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

        $dbStructure = new DatabaseStructure();
        $dbStructure->parse($description);
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

        $dbStructure = new DatabaseStructure();
        $dbStructure->parse($description);
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

        $dbStructure = new DatabaseStructure();
        $dbStructure->parse($description);
    }

    public function testBadField()
    {
        $description = new SerializableCollection([
            "connection" => "example",
            "tables" => [ null ]
        ]);

        $this->expectException(StructureException::class);

        $dbStructure = new DatabaseStructure();
        $dbStructure->parse($description);
    }

    public function testNoTables()
    {
        $description = new SerializableCollection([
            "connection" => "example",
        ]);

        $this->expectException(StructureException::class);

        $dbStructure = new DatabaseStructure();
        $dbStructure->parse($description);
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

        $dbStructure = new DatabaseStructure();
        $dbStructure->parse($description);
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

        $dbStructure = new DatabaseStructure();
        $dbStructure->parse($description);
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

        $dbStructure = new DatabaseStructure();
        $dbStructure->parse($description);

        $this->assertEquals("example", $dbStructure->getConnectionName());

        $tables = $dbStructure->getTables();

        $firstTable = $tables[0];

        $this->assertEquals("users", $firstTable->getName());

        $userColumns = $firstTable->getColumns();

        $this->assertEquals("id", $userColumns[0]->getName());
        $this->assertEquals(true, $userColumns[0]->isPrimaryKey());
        $this->assertEquals(true, $userColumns[0]->isNotNull());
        $this->assertEquals(true, $userColumns[0]->isAutoIncrement());
    }

    public function testNoConnection()
    {
        $description = new GenericCollection([
            "columns" => [ ]
        ]);

        $this->expectException(StructureException::class);

        $dbStructure = new DatabaseStructure();
        $dbStructure->parse($description);
    }
}