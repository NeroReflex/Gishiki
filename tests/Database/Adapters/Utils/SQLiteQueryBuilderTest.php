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

namespace Gishiki\tests\Database\Adapters\Utils;

use PHPUnit\Framework\TestCase;

use Gishiki\Database\Adapters\Utils\SQLGenerator\SQLiteWrapper;
use Gishiki\Database\Runtime\SelectionCriteria;
use Gishiki\Database\Runtime\ResultModifier;
use Gishiki\Database\Runtime\FieldRelation;
use Gishiki\Database\Runtime\FieldOrdering;
use Gishiki\Database\Schema\Table;
use Gishiki\Database\Schema\Column;
use Gishiki\Database\Schema\ColumnType;
use Gishiki\Database\Schema\ColumnRelation;

/**
 * The tester for the SQLiteQueryBuilder class.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class SQLiteQueryBuilderTest extends TestCase
{
    public function testBeautify()
    {
        $this->assertEquals(
            'SELECT * FROM test0 WHERE id = ? OR name = ? ORDER BY id DESC',
            SQLiteWrapper::beautify('SELECT  *  FROM  test0 WHERE id   = ? OR name = ? ORDER BY id DESC'));
    }

    public function testCreateTableWithNoForeignKey()
    {
        $table = new Table(__FUNCTION__);

        $idColumn = new Column('id', ColumnType::INTEGER);
        $idColumn->setNotNull(true);
        $idColumn->setPrimaryKey(true);
        $table->addColumn($idColumn);
        $nameColumn = new Column('name', ColumnType::TEXT);
        $nameColumn->setNotNull(true);
        $table->addColumn($nameColumn);
        $creditColumn = new Column('credit', ColumnType::NUMERIC);
        $creditColumn->setNotNull(true);
        $table->addColumn($creditColumn);
        $registeredColumn = new Column('registered', ColumnType::DATETIME);
        $registeredColumn->setNotNull(false);
        $table->addColumn($registeredColumn);

        $query = new SQLiteWrapper();
        $query->createTable($table->getName())->definedAs($table->getColumns());

        $this->assertEquals(SQLiteWrapper::beautify('CREATE TABLE IF NOT EXISTS '.__FUNCTION__.' ('
                .'id INTEGER PRIMARY KEY NOT NULL, '
                .'name TEXT NOT NULL, '
                .'credit REAL NOT NULL, '
                .'registered INTEGER'
                .')'), SQLiteWrapper::beautify($query->exportQuery()));
    }

    public function testCreateTableWithForeignKey()
    {
        $tableExtern = new Table('users');
        $userIdColumn = new Column('id', ColumnType::INTEGER);
        $userIdColumn->setNotNull(true);
        $userIdColumn->setPrimaryKey(true);
        $tableExtern->addColumn($userIdColumn);

        $table = new Table('orders');

        $relation = new ColumnRelation($tableExtern, $userIdColumn);

        $idColumn = new Column('id', ColumnType::INTEGER);
        $idColumn->setNotNull(true);
        $idColumn->setPrimaryKey(true);
        $table->addColumn($idColumn);
        $nameColumn = new Column('customer_id', ColumnType::INTEGER);
        $nameColumn->setNotNull(true);
        $nameColumn->setRelation($relation);
        $table->addColumn($nameColumn);
        $creditColumn = new Column('spent', ColumnType::FLOAT);
        $creditColumn->setNotNull(true);
        $table->addColumn($creditColumn);
        $registeredColumn = new Column('ship_date', ColumnType::DATETIME);
        $registeredColumn->setNotNull(false);
        $table->addColumn($registeredColumn);

        $query = new SQLiteWrapper();
        $query->createTable($table->getName())->definedAs($table->getColumns());

        $this->assertEquals(SQLiteWrapper::beautify('CREATE TABLE IF NOT EXISTS orders ('
                .'id INTEGER PRIMARY KEY NOT NULL, '
                .'customer_id INTEGER NOT NULL, '
                .'FOREIGN KEY (customer_id) REFERENCES users(id), '
                .'spent REAL NOT NULL, '
                .'ship_date INTEGER'
                .')'), SQLiteWrapper::beautify($query->exportQuery()));
    }

    public function testCreateTableWithAutoIncrementAndNoForeignKey()
    {
        $table = new Table(__FUNCTION__);

        $idColumn = new Column('id', ColumnType::INTEGER);
        $idColumn->setAutoIncrement(true);
        $idColumn->setNotNull(true);
        $idColumn->setPrimaryKey(true);
        $table->addColumn($idColumn);
        $nameColumn = new Column('name', ColumnType::TEXT);
        $nameColumn->setNotNull(true);
        $table->addColumn($nameColumn);
        $creditColumn = new Column('credit', ColumnType::NUMERIC);
        $creditColumn->setNotNull(true);
        $table->addColumn($creditColumn);
        $registeredColumn = new Column('registered', ColumnType::DATETIME);
        $registeredColumn->setNotNull(false);
        $table->addColumn($registeredColumn);

        $query = new SQLiteWrapper();
        $query->createTable($table->getName())->definedAs($table->getColumns());

        $this->assertEquals(SQLiteWrapper::beautify('CREATE TABLE IF NOT EXISTS '.__FUNCTION__.' ('
            .'id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, '
            .'name TEXT NOT NULL, '
            .'credit REAL NOT NULL, '
            .'registered INTEGER'
            .')'), SQLiteWrapper::beautify($query->exportQuery()));
    }

    public function testDropTable()
    {
        $query = new SQLiteWrapper();
        $query->dropTable(__FUNCTION__);

        $this->assertEquals(SQLiteWrapper::beautify('DROP TABLE IF EXISTS '.__FUNCTION__), SQLiteWrapper::beautify($query->exportQuery()));
    }

    public function testSelectAllFrom()
    {
        $query = new SQLiteWrapper();
        $query->selectAllFrom('test1');

        $this->assertEquals(SQLiteWrapper::beautify('SELECT * FROM test1'), SQLiteWrapper::beautify($query->exportQuery()));
        $this->assertEquals([], $query->exportParams());
    }

    public function testSelectAllFromWhere()
    {
        $query = new SQLiteWrapper();
        $query->selectAllFrom('test1')->where(SelectionCriteria::select([
            'id' => [5, 6, 7],
        ])->orWhere('name', FieldRelation::NOT_LIKE, '%inv%'));

        $this->assertEquals(SQLiteWrapper::beautify('SELECT * FROM test1 WHERE id IN (?,?,?) OR name NOT LIKE ?'), SQLiteWrapper::beautify($query->exportQuery()));
        $this->assertEquals([5, 6, 7, '%inv%'], $query->exportParams());
    }

    public function testSelectAllFromWhereLimitOffsetOrderBy()
    {
        $query = new SQLiteWrapper();
        $query->selectAllFrom('test1')
            ->where(SelectionCriteria::select([
                'id' => [5, 6, 7],
            ])->orWhere('price', FieldRelation::GREATER_THAN, 1.25))
            ->limitOffsetOrderBy(ResultModifier::initialize([
                'limit' => 1024,
                'skip' => 100,
                'name' => FieldOrdering::ASC,
            ]));

        $this->assertEquals(SQLiteWrapper::beautify('SELECT * FROM test1 WHERE id IN (?,?,?) OR price > ? LIMIT 1024 OFFSET 100 ORDER BY name ASC'), SQLiteWrapper::beautify($query->exportQuery()));
        $this->assertEquals([5, 6, 7, 1.25], $query->exportParams());
    }

    public function testSelectFromWhereLimitOffsetOrderBy()
    {
        $query = new SQLiteWrapper();
        $query->selectFrom('test1', ['name', 'surname'])
            ->where(SelectionCriteria::select([
                'id' => [5, 6, 7],
            ])->orWhere('price', FieldRelation::GREATER_THAN, 1.25))
            ->limitOffsetOrderBy(ResultModifier::initialize([
                'limit' => 1024,
                'skip' => 100,
                'name' => FieldOrdering::ASC,
                'surname' => FieldOrdering::DESC,
            ]));

        $this->assertEquals(SQLiteWrapper::beautify('SELECT name, surname FROM test1 WHERE id IN (?,?,?) OR price > ? LIMIT 1024 OFFSET 100 ORDER BY name ASC, surname DESC'), SQLiteWrapper::beautify($query->exportQuery()));
        $this->assertEquals([5, 6, 7, 1.25], $query->exportParams());
    }

    public function testInsertIntoValues()
    {
        $query = new SQLiteWrapper();
        $query->insertInto('users')->values([
            'name' => 'Mario',
            'surname' => 'Rossi',
            'age' => 25,
            'time' => 56.04,
        ]);

        $this->assertEquals(SQLiteWrapper::beautify('INSERT INTO users (name, surname, age, time) VALUES (?,?,?,?)'), SQLiteWrapper::beautify($query->exportQuery()));
        $this->assertEquals(['Mario', 'Rossi', 25, 56.04], $query->exportParams());
    }

    public function testDeleteFrom()
    {
        $query = new SQLiteWrapper();
        $query->deleteFrom('users');

        $this->assertEquals(SQLiteWrapper::beautify('DELETE FROM users'), SQLiteWrapper::beautify($query->exportQuery()));
        $this->assertEquals([], $query->exportParams());
    }

    public function testUpdateSetWhere()
    {
        $query = new SQLiteWrapper();
        $query->update('users')->set(['name' => 'Gianni', 'surname' => 'Pinotto'])->where(SelectionCriteria::select(['id' => 200]));

        $this->assertEquals(SQLiteWrapper::beautify('UPDATE users SET name = ?, surname = ? WHERE id = ?'), SQLiteWrapper::beautify($query->exportQuery()));
        $this->assertEquals(['Gianni', 'Pinotto', 200], $query->exportParams());
    }
}
