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

namespace Gishiki\tests\Database\Adapters;

use Gishiki\Database\DatabaseException;
use PHPUnit\Framework\TestCase;

use Gishiki\Database\Adapters\Utils\SQLQueryBuilder;
use Gishiki\Database\Runtime\SelectionCriteria;
use Gishiki\Database\Runtime\FieldRelation;
use Gishiki\Database\Runtime\ResultModifier;
use Gishiki\Database\Runtime\FieldOrdering;
use Gishiki\Database\Schema\Table;
use Gishiki\Database\Schema\Column;
use Gishiki\Database\Schema\ColumnType;
use Gishiki\Database\Schema\ColumnRelation;
use Gishiki\Database\DatabaseManager;

/**
 * The tester for the SQLQueryBuilder class.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */

class SqliteTest extends TestCase
{
    private static function getDatabase()
    {
        $connection = null;

        try {
            $connection = DatabaseManager::retrieve("sqliteTest");
        } catch (DatabaseException $ex) {
            $connection = DatabaseManager::connect("sqliteTest", "sqlite3://sqliteTest.sqlite");
        }

        return $connection;
    }

    function testNoRelationsAndNoID()
    {
        $table = new Table("User".__FUNCTION__);

        $idColumn = new Column('id', ColumnType::INTEGER);
        $idColumn->setNotNull(true);
        $idColumn->setAutoIncrement(true);
        $idColumn->setPrimaryKey(true);
        $table->addColumn($idColumn);
        $newColumn = new Column('new', ColumnType::INTEGER);
        $newColumn->setNotNull(true);
        $table->addColumn($newColumn);
        $nameColumn = new Column('name', ColumnType::TEXT);
        $nameColumn->setNotNull(true);
        $table->addColumn($nameColumn);
        $surnameColumn = new Column('surname', ColumnType::TEXT);
        $surnameColumn->setNotNull(true);
        $table->addColumn($surnameColumn);
        $passwordColumn = new Column('password', ColumnType::TEXT);
        $passwordColumn->setNotNull(true);
        $table->addColumn($passwordColumn);
        $creditColumn = new Column('credit', ColumnType::REAL);
        $creditColumn->setNotNull(true);
        $table->addColumn($creditColumn);
        $registeredColumn = new Column('registered', ColumnType::DATETIME);
        $registeredColumn->setNotNull(false);
        $table->addColumn($registeredColumn);

        $connection = self::getDatabase();
        $connection->createTable($table);

        $connection->create("User".__FUNCTION__, [
            "new" => 1,
            "name" => "Mario",
            "surname" => "Rossi",
            "password" => sha1("asdfgh"),
            "credit" => 15.68,
            "registered" => time()
        ]);

        $readResult = $connection->readSelective("User".__FUNCTION__, ["name", "surname"],
            SelectionCriteria::select(["name" => "Mario"])->and_where("new", FieldRelation::GREATER_OR_EQUAL_THAN, 1),
            ResultModifier::initialize());

        $this->assertEquals([[
            "name" => "Mario",
            "surname" => "Rossi"]], $readResult);

        /*$ancientRecords = count(
            $connection->read(
                "User".__FUNCTION__,
                SelectionCriteria::select([])->and_where("new", FieldRelation::GREATER_OR_EQUAL_THAN, 1),
                ResultModifier::initialize())
        );*/

        $newlyAncientRecords = $connection->update(
            "User".__FUNCTION__,
            ["new" => 0],
            SelectionCriteria::select([])->and_where("new", FieldRelation::GREATER_OR_EQUAL_THAN, 1)
        );

        $this->assertEquals(1, $newlyAncientRecords);
    }
}