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

use Gishiki\Algorithms\Collections\SerializableCollection;
use Gishiki\Database\Adapters\Sqlite;
use Gishiki\Database\DatabaseException;
use PHPUnit\Framework\TestCase;
use Gishiki\Database\Runtime\SelectionCriteria;
use Gishiki\Database\Runtime\FieldRelation;
use Gishiki\Database\Runtime\ResultModifier;
use Gishiki\Database\Schema\Table;
use Gishiki\Database\Schema\Column;
use Gishiki\Database\Schema\ColumnType;
use Gishiki\Database\Schema\ColumnRelation;


/**
 * The base tester for every relational database wrapper.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class DatabaseRelationalTest extends TestCase
{
    protected function getDatabase()
    {
        return new Sqlite(":memory:");
    }

    public function testMultipleOperationsWithRelations()
    {
        // build the author table
        $authorTable = new Table('authors');
        $idColumn = new Column('id', ColumnType::INTEGER);
        $idColumn->setAutoIncrement(true);
        $idColumn->setPrimaryKey(true);
        $idColumn->setNotNull(true);
        $authorTable->addColumn($idColumn);
        $nameColumn = new Column('names', ColumnType::TEXT);
        $nameColumn->setNotNull(true);
        $authorTable->addColumn($nameColumn);

        //build the relation...
        $authorRelation = new ColumnRelation($authorTable, $idColumn);

        // build the book table
        $bookTable = new Table('books');
        $bookIdColumn = new Column('id', ColumnType::INTEGER);
        $bookIdColumn->setPrimaryKey(true);
        $bookIdColumn->setNotNull(true);
        $bookTable->addColumn($bookIdColumn);
        $authorIdColumn = new Column('author_id', ColumnType::INTEGER);
        $authorIdColumn->setRelation($authorRelation);
        $authorIdColumn->setNotNull(true);
        $bookTable->addColumn($authorIdColumn);
        $bookTitleColumn = new Column('title', ColumnType::TEXT);
        $bookTitleColumn->setNotNull(true);
        $bookTable->addColumn($bookTitleColumn);
        $bookDateColumn = new Column('publication_date', ColumnType::TEXT);
        $bookDateColumn->setNotNull(false);
        $bookTable->addColumn($bookDateColumn);
        $bookPriceColumn = new Column('price', ColumnType::NUMERIC);
        $bookPriceColumn->setNotNull(true);
        $bookTable->addColumn($bookPriceColumn);

        // create tables
        $connection = $this->getDatabase();
        $connection->createTable($authorTable);
        $connection->createTable($bookTable);

        $connection->create(
            'books',
            [
                'id' => 1,
                'author_id' => $connection->create('authors', [ 'names' => 'Stephen B. Furber' ]),
                'title' => 'ARM System-On-Chip Architecture',
                'publication_date' => '2000',
                'price' => 68.52
            ]
        );

        $ARMBookAuthorId = $connection->readSelective('books', ['author_id'], SelectionCriteria::select(['title' => 'ARM System-On-Chip Architecture']), ResultModifier::initialize()->limit(1))[0]['author_id'];

        $this->assertEquals(1, $connection->delete('books', SelectionCriteria::select(['author_id' => $ARMBookAuthorId])));
        $this->assertEquals(1, $connection->delete('authors', SelectionCriteria::select(['id' => $ARMBookAuthorId])));
    }

    public function testCreateTableOnClosedDatabase()
    {
        $this->expectException(DatabaseException::class);

        $closedConnection = null;
        try {
            $closedConnection = $this->getDatabase();
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

        $connection = $this->getDatabase();
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
        $creditColumn = new Column('credit', ColumnType::NUMERIC);
        $creditColumn->setNotNull(true);
        $table->addColumn($creditColumn);
        $registeredColumn = new Column('registered', ColumnType::DATETIME);
        $registeredColumn->setNotNull(false);
        $table->addColumn($registeredColumn);

        $connection = $this->getDatabase();
        $connection->createTable($table);

        $userExample = [
            "name" => "Mario",
            "surname" => "Rossi",
            "password" => sha1("asdfgh"),
            "credit" => 15.68,
            "registered" => time()
        ];

        $currentID = $connection->create(
            "User".__FUNCTION__,
            new SerializableCollection($userExample));

        $readSelectiveResult = $connection->readSelective("User".__FUNCTION__, ["name", "surname"],
            SelectionCriteria::select(["name" => "Mario"]),
            ResultModifier::initialize());

        $this->assertEquals([[
            "name" => "Mario",
            "surname" => "Rossi"]], $readSelectiveResult);

        $readResult = $connection->read(
            "User".__FUNCTION__,
            SelectionCriteria::select(["name" => "Mario"]),
            ResultModifier::initialize());

        $this->assertEquals([array_merge($userExample, ['id' => $currentID])], $readResult);

        $this->assertEquals(1, $connection->deleteAll("User".__FUNCTION__));
    }

    public function testUpdateOnClosedConnection()
    {
        $this->expectException(DatabaseException::class);

        $closedConnection = $this->getDatabase();
        $closedConnection->close();

        $closedConnection->update(__FUNCTION__, ["status" => "lol"], SelectionCriteria::select());
    }

    public function testUpdateBadCollectionName()
    {
        $this->expectException(\InvalidArgumentException::class);

        $connection = $this->getDatabase();

        $connection->update(null, ["status" => "lol"], SelectionCriteria::select());
    }

    public function testUpdateBadCollectionData()
    {
        $this->expectException(\InvalidArgumentException::class);

        $connection = $this->getDatabase();

        $connection->update(__FUNCTION__, "nonsense :)", SelectionCriteria::select());
    }

    public function testDeleteOnClosedConnection()
    {
        $this->expectException(DatabaseException::class);

        $closedConnection = $this->getDatabase();
        $closedConnection->close();

        $closedConnection->delete(__FUNCTION__, SelectionCriteria::select(["status" => "unwanted"]));
    }

    public function testDeleteBadCollectionName()
    {
        $this->expectException(\InvalidArgumentException::class);

        $connection = $this->getDatabase();

        $connection->delete(null, SelectionCriteria::select(["status" => "unwanted"]));
    }

    public function testDeleteAllOnClosedConnection()
    {
        $this->expectException(DatabaseException::class);

        $closedConnection = $this->getDatabase();
        $closedConnection->close();

        $closedConnection->deleteAll(__FUNCTION__);
    }

    public function testDeleteAllBadCollectionName()
    {
        $this->expectException(\InvalidArgumentException::class);

        $connection = $this->getDatabase();

        $connection->deleteAll(null);
    }

    public function testCreateOnClosedConnection()
    {
        $this->expectException(DatabaseException::class);

        $closedConnection = $this->getDatabase();
        $closedConnection->close();

        $closedConnection->create(__FUNCTION__, ["status" => "unwanted"]);
    }

    public function testCreateBadCollectionName()
    {
        $this->expectException(\InvalidArgumentException::class);

        $connection = $this->getDatabase();

        $connection->create(null, ["status" => "lol"]);
    }

    public function testCreateBadCollectionValue()
    {
        $this->expectException(\InvalidArgumentException::class);

        $connection = $this->getDatabase();

        $connection->create(__FUNCTION__, 69);
    }

    public function testBadCreate()
    {
        $this->expectException(DatabaseException::class);

        $connection = $this->getDatabase();

        $connection->create(__FUNCTION__, ["status" => "unwanted"]);
    }

    public function testBadReadSelective()
    {
        $this->expectException(DatabaseException::class);

        $connection = $this->getDatabase();

        $connection->readSelective(__FUNCTION__, ['id' => 7], SelectionCriteria::select(), ResultModifier::initialize());
    }

    public function testBadRead()
    {
        $this->expectException(DatabaseException::class);

        $connection = $this->getDatabase();

        $connection->read(__FUNCTION__, SelectionCriteria::select(), ResultModifier::initialize());
    }

    public function testBadDelete()
    {
        $this->expectException(DatabaseException::class);

        $connection = $this->getDatabase();

        $connection->delete(__FUNCTION__, SelectionCriteria::select(["id" => 10]));
    }

    public function testBadDeleteAll()
    {
        $this->expectException(DatabaseException::class);

        $connection = $this->getDatabase();

        $connection->deleteAll(__FUNCTION__);
    }

    public function testReadBadCollectionName()
    {
        $this->expectException(\InvalidArgumentException::class);

        $connection = $this->getDatabase();

        $connection->read(null, SelectionCriteria::select(['id' => 7]), ResultModifier::initialize());
    }

    public function testReadSelectiveBadCollectionName()
    {
        $this->expectException(\InvalidArgumentException::class);

        $connection = $this->getDatabase();

        $connection->readSelective(null, ['id'],SelectionCriteria::select(['id' => 7]), ResultModifier::initialize());
    }

    public function testReadSelectiveOnClosedConnection()
    {
        $this->expectException(DatabaseException::class);

        $closedConnection = $this->getDatabase();
        $closedConnection->close();

        $closedConnection->readSelective(__FUNCTION__, ['id'],SelectionCriteria::select(['id' => 7]), ResultModifier::initialize());
    }

    public function testReadOnClosedConnection()
    {
        $this->expectException(DatabaseException::class);

        $closedConnection = $this->getDatabase();
        $closedConnection->close();

        $closedConnection->read(__FUNCTION__, SelectionCriteria::select(['id' => 7]), ResultModifier::initialize());
    }

    public function testBadUpdate()
    {
        $this->expectException(DatabaseException::class);

        $connection = $this->getDatabase();

        $connection->update(__FUNCTION__, ["price" => 9.00], SelectionCriteria::select());
    }

    public function testDeleteNoRelationNoID()
    {
        $table = new Table("Books".__FUNCTION__);

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
        $priceColumn = new Column('price', ColumnType::MONEY);
        $priceColumn->setNotNull(true);
        $table->addColumn($priceColumn);

        $connection = $this->getDatabase();
        $connection->createTable($table);

        $connection->create(
            "Books".__FUNCTION__,
            [
                'title' => 'Compilers: Principles, Techniques, and Tools',
                'author' => 'Alfred V. Aho, Monica S. Lam, Ravi Sethi, and Jeffrey D. Ullman',
                'price' => 50.99
            ]);
        $connection->create(
            "Books".__FUNCTION__,
            [
                'title' => 'Bible',
                'price' => 12.99
            ]);
        $connection->create(
            "Books".__FUNCTION__,
            [
                'title' => '1984',
                'author' => 'George Orwell',
                'price' => 13.40
            ]);
        $connection->create(
            "Books".__FUNCTION__,
            [
                'title' => 'Animal Farm',
                'author' => 'George Orwell',
                'price' => 25.99
            ]);
        $connection->create(
            "Books".__FUNCTION__,
            [
                'title' => 'Programming in ANSI C Deluxe Revised',
                'price' => 8.71
            ]);
        $connection->create(
            "Books".__FUNCTION__,
            [
                'title' => 'C Programming Language, 2nd Edition',
                'price' => 14.46
            ]);
        $connection->create(
            "Books".__FUNCTION__,
            [
                'title' => 'Modern Operating Systems',
                'author' => 'Andrew S. Tanenbaum',
                'price' => 70.89
            ]);
        $connection->create(
            "Books".__FUNCTION__,
            [
                'title' => 'Embedded C Coding Standard',
                'price' => 5.38
            ]);
        $connection->create(
            "Books".__FUNCTION__,
            [
                'title' => 'C Programming for Embedded Microcontrollers',
                'price' => 20.00
            ]);
        $connection->create(
            "Books".__FUNCTION__,
            [
                'title' => 'ARM Assembly Language',
                'price' => 17.89
            ]);


        $this->assertEquals(7, $connection->delete("Books".__FUNCTION__,
            SelectionCriteria::select()->andWhere('price', FieldRelation::LESS_THAN, 20.99)
        ));

        $this->assertEquals(3, $connection->deleteAll("Books".__FUNCTION__));
    }

    public function testUpdateNoRelationNoID()
    {
        $table = new Table("Books".__FUNCTION__);

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
        $priceColumn = new Column('price', ColumnType::MONEY);
        $priceColumn->setNotNull(true);
        $table->addColumn($priceColumn);

        $connection = $this->getDatabase();
        $connection->createTable($table);

        $connection->create(
            "Books".__FUNCTION__,
            [
                'title' => 'Compilers: Principles, Techniques, and Tools',
                'author' => 'Alfred V. Aho, Monica S. Lam, Ravi Sethi, and Jeffrey D. Ullman',
                'price' => 50.99
            ]);
        $connection->create(
            "Books".__FUNCTION__,
            [
                'title' => 'Bible',
                'price' => 12.99
            ]);
        $connection->create(
            "Books".__FUNCTION__,
            [
                'title' => '1984',
                'author' => 'George Orwell',
                'price' => 13.40
            ]);
        $connection->create(
            "Books".__FUNCTION__,
            [
                'title' => 'Animal Farm',
                'author' => 'George Orwell',
                'price' => 25.99
            ]);
        $connection->create(
            "Books".__FUNCTION__,
            [
                'title' => 'Programming in ANSI C Deluxe Revised',
                'price' => 8.71
            ]);
        $connection->create(
            "Books".__FUNCTION__,
            [
                'title' => 'C Programming Language, 2nd Edition',
                'price' => 14.46
            ]);
        $connection->create(
            "Books".__FUNCTION__,
            [
                'title' => 'Modern Operating Systems',
                'author' => 'Andrew S. Tanenbaum',
                'price' => 70.89
            ]);
        $connection->create(
            "Books".__FUNCTION__,
            [
                'title' => 'Embedded C Coding Standard',
                'price' => 5.38
            ]);
        $connection->create(
            "Books".__FUNCTION__,
            [
                'title' => 'C Programming for Embedded Microcontrollers',
                'price' => 20.00
            ]);
        $connection->create(
            "Books".__FUNCTION__,
            [
                'title' => 'ARM Assembly Language',
                'price' => 17.89
            ]);


        $this->assertEquals(5, $connection->update("Books".__FUNCTION__, ['price' => 10.00],
            SelectionCriteria::select()
                ->andWhere('price', FieldRelation::LESS_THAN, 20.99)
                ->andWhere('price', FieldRelation::GREATER_OR_EQUAL_THAN, 10.50)
        ));
    }
}