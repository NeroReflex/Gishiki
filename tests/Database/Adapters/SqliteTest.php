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

use Gishiki\Database\Adapters\Sqlite;
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
            $connection = DatabaseManager::connect("sqliteTest", "sqlite3://:memory:");
        }

        return $connection;
    }

    public function testBadConnectionParam()
    {
        $this->expectException(\InvalidArgumentException::class);

        new Sqlite(null);
    }

    public function testCreateTableOnClosedDatabase()
    {
        $this->expectException(DatabaseException::class);

        $closedConnection = null;
        try {
            $closedConnection = new Sqlite(":memory:");
            $closedConnection->close();
        } catch (\InvalidArgumentException $ex) { }

        $table = new Table(__FUNCTION__);

        $idColumn = new Column('id', ColumnType::INTEGER);
        $idColumn->setNotNull(true);
        $idColumn->setAutoIncrement(true);
        $idColumn->setPrimaryKey(true);
        $table->addColumn($idColumn);

        $closedConnection->createTable($table);
    }

    public function testBadCreateTable()
    {
        $this->expectException(DatabaseException::class);

        $table = new Table("from");

        $idColumn = new Column('where', ColumnType::INTEGER);
        $idColumn->setNotNull(true);
        $idColumn->setAutoIncrement(true);
        $idColumn->setPrimaryKey(true);
        $table->addColumn($idColumn);

        $connection = self::getDatabase();
        $connection->createTable($table);
    }

    public function testNoRelationsAndNoID()
    {
        $table = new Table("User".__FUNCTION__);

        $idColumn = new Column('id', ColumnType::INTEGER);
        $idColumn->setNotNull(true);
        $idColumn->setAutoIncrement(true);
        $idColumn->setPrimaryKey(true);
        $table->addColumn($idColumn);
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

        $connection->create(
            "User".__FUNCTION__,
            [
                "name" => "Mario",
                "surname" => "Rossi",
                "password" => sha1("asdfgh"),
                "credit" => 15.68,
                "registered" => time()
            ]);

        $readResult = $connection->readSelective("User".__FUNCTION__, ["name", "surname"],
            SelectionCriteria::select(["name" => "Mario"]),
            ResultModifier::initialize());

        $this->assertEquals([[
            "name" => "Mario",
            "surname" => "Rossi"]], $readResult);
    }

    public function testUpdateOnClosedConnection()
    {
        $this->expectException(DatabaseException::class);

        $closedConnection = null;
        try {
            $closedConnection = new Sqlite(":memory:");
            $closedConnection->close();
        } catch (\InvalidArgumentException $ex) { }

        $closedConnection->update(__FUNCTION__, ["status" => "lol"], SelectionCriteria::select());
    }

    public function testUpdateBadCollectionName()
    {
        $this->expectException(\InvalidArgumentException::class);

        $connection = self::getDatabase();

        $connection->update(null, ["status" => "lol"], SelectionCriteria::select());
    }

    public function testUpdateBadCollectionData()
    {
        $this->expectException(\InvalidArgumentException::class);

        $connection = self::getDatabase();

        $connection->update(__FUNCTION__, "nonsense :)", SelectionCriteria::select());
    }

    public function testDeleteOnClosedConnection()
    {
        $this->expectException(DatabaseException::class);

        $closedConnection = null;
        try {
            $closedConnection = new Sqlite(":memory:");
            $closedConnection->close();
        } catch (\InvalidArgumentException $ex) { }

        $closedConnection->delete(__FUNCTION__, SelectionCriteria::select(["status" => "unwanted"]));
    }

    public function testDeleteBadCollectionName()
    {
        $this->expectException(\InvalidArgumentException::class);

        $connection = self::getDatabase();

        $connection->delete(null, SelectionCriteria::select(["status" => "unwanted"]));
    }

    public function testDeleteAllOnClosedConnection()
    {
        $this->expectException(DatabaseException::class);

        $closedConnection = null;
        try {
            $closedConnection = new Sqlite(":memory:");
            $closedConnection->close();
        } catch (\InvalidArgumentException $ex) { }

        $closedConnection->deleteAll(__FUNCTION__);
    }

    public function testDeleteAllBadCollectionName()
    {
        $this->expectException(\InvalidArgumentException::class);

        $connection = self::getDatabase();

        $connection->deleteAll(null);
    }

    public function testCreateOnClosedConnection()
    {
        $this->expectException(DatabaseException::class);

        $closedConnection = null;
        try {
            $closedConnection = new Sqlite(":memory:");
            $closedConnection->close();
        } catch (\InvalidArgumentException $ex) { }

        $closedConnection->create(__FUNCTION__, ["status" => "unwanted"]);
    }

    public function testCreateBadCollectionName()
    {
        $this->expectException(\InvalidArgumentException::class);

        $connection = self::getDatabase();

        $connection->create(null, ["status" => "lol"]);
    }

    public function testCreateBadCollectionValue()
    {
        $this->expectException(\InvalidArgumentException::class);

        $connection = self::getDatabase();

        $connection->create(__FUNCTION__, 69);
    }

    public function testBadCreate()
    {
        $this->expectException(DatabaseException::class);

        $connection = self::getDatabase();

        $connection->create(__FUNCTION__, ["status" => "unwanted"]);
    }

    public function testDeleteNoRelationNoID()
    {
        $table = new Table("Books_".__FUNCTION__);

        $idColumn = new Column('id', ColumnType::INTEGER);
        $idColumn->setNotNull(true);
        $idColumn->setAutoIncrement(true);
        $idColumn->setPrimaryKey(true);
        $table->addColumn($idColumn);
        $nameColumn = new Column('title', ColumnType::TEXT);
        $nameColumn->setNotNull(true);
        $table->addColumn($nameColumn);
        $authorColumn = new Column('author', ColumnType::TEXT);
        $table->addColumn($authorColumn);
        $priceColumn = new Column('price', ColumnType::REAL);
        $priceColumn->setNotNull(true);
        $table->addColumn($priceColumn);

        $connection = self::getDatabase();
        $connection->createTable($table);

        $connection->create(
            "Books_".__FUNCTION__,
            [
                'title' => 'Compilers: Principles, Techniques, and Tools',
                'author' => 'Alfred V. Aho, Monica S. Lam, Ravi Sethi, and Jeffrey D. Ullman',
                'price' => 50.99
            ]);
        $connection->create(
            "Books_".__FUNCTION__,
            [
                'title' => 'Bible',
                'price' => 12.99
            ]);
        $connection->create(
            "Books_".__FUNCTION__,
            [
                'title' => '1984',
                'author' => 'George Orwell',
                'price' => 13.40
            ]);
        $connection->create(
            "Books_".__FUNCTION__,
            [
                'title' => 'Animal Farm',
                'author' => 'George Orwell',
                'price' => 25.99
            ]);
        $connection->create(
            "Books_".__FUNCTION__,
            [
                'title' => 'Programming in ANSI C Deluxe Revised',
                'price' => 8.71
            ]);
        $connection->create(
            "Books_".__FUNCTION__,
            [
                'title' => 'C Programming Language, 2nd Edition',
                'price' => 14.46
            ]);
        $connection->create(
            "Books_".__FUNCTION__,
            [
                'title' => 'Modern Operating Systems',
                'author' => 'Andrew S. Tanenbaum',
                'price' => 70.89
            ]);
        $connection->create(
            "Books_".__FUNCTION__,
            [
                'title' => 'Embedded C Coding Standard',
                'price' => 5.38
            ]);
        $connection->create(
            "Books_".__FUNCTION__,
            [
                'title' => 'C Programming for Embedded Microcontrollers',
                'price' => 20.00
            ]);
        $connection->create(
            "Books_".__FUNCTION__,
            [
                'title' => 'ARM Assembly Language',
                'price' => 17.89
            ]);


        $this->assertEquals(7, $connection->delete("Books_".__FUNCTION__,
            SelectionCriteria::select()->AndWhere('price', FieldRelation::LESS_THAN, 20.99)
        ));

        $this->assertEquals(3, $connection->deleteAll("Books_".__FUNCTION__));
    }

    public function testUpdateNoRelationNoID()
    {
        $table = new Table("Books_".__FUNCTION__);

        $idColumn = new Column('id', ColumnType::INTEGER);
        $idColumn->setNotNull(true);
        $idColumn->setAutoIncrement(true);
        $idColumn->setPrimaryKey(true);
        $table->addColumn($idColumn);
        $nameColumn = new Column('title', ColumnType::TEXT);
        $nameColumn->setNotNull(true);
        $table->addColumn($nameColumn);
        $authorColumn = new Column('author', ColumnType::TEXT);
        $table->addColumn($authorColumn);
        $priceColumn = new Column('price', ColumnType::REAL);
        $priceColumn->setNotNull(true);
        $table->addColumn($priceColumn);

        $connection = self::getDatabase();
        $connection->createTable($table);

        $connection->create(
            "Books_".__FUNCTION__,
            [
                'title' => 'Compilers: Principles, Techniques, and Tools',
                'author' => 'Alfred V. Aho, Monica S. Lam, Ravi Sethi, and Jeffrey D. Ullman',
                'price' => 50.99
            ]);
        $connection->create(
            "Books_".__FUNCTION__,
            [
                'title' => 'Bible',
                'price' => 12.99
            ]);
        $connection->create(
            "Books_".__FUNCTION__,
            [
                'title' => '1984',
                'author' => 'George Orwell',
                'price' => 13.40
            ]);
        $connection->create(
            "Books_".__FUNCTION__,
            [
                'title' => 'Animal Farm',
                'author' => 'George Orwell',
                'price' => 25.99
            ]);
        $connection->create(
            "Books_".__FUNCTION__,
            [
                'title' => 'Programming in ANSI C Deluxe Revised',
                'price' => 8.71
            ]);
        $connection->create(
            "Books_".__FUNCTION__,
            [
                'title' => 'C Programming Language, 2nd Edition',
                'price' => 14.46
            ]);
        $connection->create(
            "Books_".__FUNCTION__,
            [
                'title' => 'Modern Operating Systems',
                'author' => 'Andrew S. Tanenbaum',
                'price' => 70.89
            ]);
        $connection->create(
            "Books_".__FUNCTION__,
            [
                'title' => 'Embedded C Coding Standard',
                'price' => 5.38
            ]);
        $connection->create(
            "Books_".__FUNCTION__,
            [
                'title' => 'C Programming for Embedded Microcontrollers',
                'price' => 20.00
            ]);
        $connection->create(
            "Books_".__FUNCTION__,
            [
                'title' => 'ARM Assembly Language',
                'price' => 17.89
            ]);


        $this->assertEquals(5, $connection->update("Books_".__FUNCTION__, ['price' => 10.00],
            SelectionCriteria::select()
                ->AndWhere('price', FieldRelation::LESS_THAN, 20.99)
                ->AndWhere('price', FieldRelation::GREATER_OR_EQUAL_THAN, 10.50)
            ));
    }
}