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

use Gishiki\Database\Adapters\Utils\SQLGenerator\GenericSQL;
use Gishiki\Database\Runtime\SelectionCriteria;
use Gishiki\Database\Runtime\FieldRelation;
use Gishiki\Database\Runtime\ResultModifier;
use Gishiki\Database\Runtime\FieldOrdering;

/**
 * The tester for the SQLQueryBuilder class.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class SQLQueryBuilderTest extends TestCase
{
    public function testBeautify()
    {
        $this->assertEquals(
            'SELECT * FROM test0 WHERE id = ? OR name = ? ORDER BY id DESC',
            GenericSQL::beautify('SELECT  *  FROM  test0 WHERE id   = ? OR name = ? ORDER BY id DESC'));
    }

    public function testDropTable()
    {
        $query = new GenericSQL();
        $query->dropTable(__FUNCTION__);

        $this->assertEquals(GenericSQL::beautify('DROP TABLE IF EXISTS '.__FUNCTION__), GenericSQL::beautify($query->exportQuery()));
    }

    public function testSelectAllFrom()
    {
        $query = new GenericSQL();
        $query->selectAllFrom('test1');

        $this->assertEquals(GenericSQL::beautify('SELECT * FROM test1'), GenericSQL::beautify($query->exportQuery()));
        $this->assertEquals([], $query->exportParams());
    }

    public function testSelectAllFromWhere()
    {
        $query = new GenericSQL();
        $query->selectAllFrom('test1')->where(SelectionCriteria::select([
            'id' => [5, 6, 7],
        ])->OrWhere('name', FieldRelation::NOT_LIKE, '%inv%'));

        $this->assertEquals(GenericSQL::beautify('SELECT * FROM test1 WHERE id IN (?,?,?) OR name NOT LIKE ?'), GenericSQL::beautify($query->exportQuery()));
        $this->assertEquals([5, 6, 7, '%inv%'], $query->exportParams());
    }

    public function testSelectAllFromWhereLimitOffsetOrderBy()
    {
        $query = new GenericSQL();
        $query->selectAllFrom('test1')
                ->where(SelectionCriteria::select([
                        'id' => [5, 6, 7],
                    ])->OrWhere('price', FieldRelation::GREATER_THAN, 1.25))
                ->limitOffsetOrderBy(ResultModifier::initialize([
                    'limit' => 1024,
                    'skip' => 100,
                    'name' => FieldOrdering::ASC,
                ]));

        $this->assertEquals(GenericSQL::beautify('SELECT * FROM test1 WHERE id IN (?,?,?) OR price > ? LIMIT 1024 OFFSET 100 ORDER BY name ASC'), GenericSQL::beautify($query->exportQuery()));
        $this->assertEquals([5, 6, 7, 1.25], $query->exportParams());
    }

    public function testSelectFromWhereLimitOffsetOrderBy()
    {
        $query = new GenericSQL();
        $query->selectFrom('test1', ['name', 'surname'])
                ->where(SelectionCriteria::select([
                        'id' => [5, 6, 7],
                    ])->OrWhere('price', FieldRelation::GREATER_THAN, 1.25))
                ->limitOffsetOrderBy(ResultModifier::initialize([
                    'limit' => 1024,
                    'skip' => 100,
                    'name' => FieldOrdering::ASC,
                    'surname' => FieldOrdering::DESC,
                ]));

        $this->assertEquals(GenericSQL::beautify('SELECT name, surname FROM test1 WHERE id IN (?,?,?) OR price > ? LIMIT 1024 OFFSET 100 ORDER BY name ASC, surname DESC'), GenericSQL::beautify($query->exportQuery()));
        $this->assertEquals([5, 6, 7, 1.25], $query->exportParams());
    }

    public function testInsertIntoValues()
    {
        $query = new GenericSQL();
        $query->insertInto('users')->values([
            'name' => 'Mario',
            'surname' => 'Rossi',
            'age' => 25,
            'time' => 56.04,
        ]);

        $this->assertEquals(GenericSQL::beautify('INSERT INTO users (name, surname, age, time) VALUES (?,?,?,?)'), GenericSQL::beautify($query->exportQuery()));
        $this->assertEquals(['Mario', 'Rossi', 25, 56.04], $query->exportParams());
    }

    public function testDeleteFrom()
    {
        $query = new GenericSQL();
        $query->deleteFrom('users');

        $this->assertEquals(GenericSQL::beautify('DELETE FROM users'), GenericSQL::beautify($query->exportQuery()));
        $this->assertEquals([], $query->exportParams());
    }

    public function testUpdateSetWhere()
    {
        $query = new GenericSQL();
        $query->update('users')->set(['name' => 'Gianni', 'surname' => 'Pinotto'])->where(SelectionCriteria::select(['id' => 200]));

        $this->assertEquals(GenericSQL::beautify('UPDATE users SET name = ?, surname = ? WHERE id = ?'), GenericSQL::beautify($query->exportQuery()));
        $this->assertEquals(['Gianni', 'Pinotto', 200], $query->exportParams());
    }
}
