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

use Gishiki\Database\Adapters\Utils\SQLBuilder;
use Gishiki\Database\SelectionCriteria;
use Gishiki\Database\FieldRelationship;
use Gishiki\Database\ResultModifier;
use Gishiki\Database\FieldOrdering;

/**
 * The tester for the SQLBuilder class.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class SQLBuilderTest  extends \PHPUnit_Framework_TestCase
{
    private static function filterQuery($query) {
        $count = 1;
        while ($count > 0) {
            $query = str_replace("  ", " ", $query, $count);
        }
        
        $count = 1;
        while ($count > 0) {
            $query = str_replace("( ", "(", $query, $count);
        }
        
        $count = 1;
        while ($count > 0) {
            $query = str_replace(" )", ")", $query, $count);
        }
        
        $count = 1;
        while ($count > 0) {
            $query = str_replace("?, ?", "?,?", $query, $count);
        }
            
        return trim($query);
    }
    
    public function testSelectAllFrom() {
        $query = new SQLBuilder();
        $query->selectAllFrom("test1");
        
        $this->assertEquals(self::filterQuery("SELECT * FROM \"test1\""), self::filterQuery($query->exportQuery()));
        $this->assertEquals([], $query->exportParams());
    }
    
    public function testSelectAllFromWhere() {
        $query = new SQLBuilder();
        $query->selectAllFrom("test1")->where(SelectionCriteria::Select([
            "id" => [5, 6, 7]
        ])->or_where('name', FieldRelationship::NOT_LIKE, '%inv%'));
        
        $this->assertEquals(self::filterQuery("SELECT * FROM \"test1\" WHERE id IN (?,?,?) OR name NOT LIKE ?"), self::filterQuery($query->exportQuery()));
        $this->assertEquals([5, 6, 7, '%inv%'], $query->exportParams());
    }
    
    public function testSelectAllFromWhereLimitOffsetOrderBy() {
        $query = new SQLBuilder();
        $query->selectAllFrom("test1")
                ->where(SelectionCriteria::Select([
                        "id" => [5, 6, 7]
                    ])->or_where('price', FieldRelationship::GREATER_THAN, 1.25))
                ->limitOffsetOrderBy(ResultModifier::Initialize([
                    'limit' => 1024,
                    'skip' => 100,
                    'name' => FieldOrdering::ASC
                ]));
        
        $this->assertEquals(self::filterQuery("SELECT * FROM \"test1\" WHERE id IN (?,?,?) OR price > ? LIMIT 1024 OFFSET 100 ORDER BY name ASC"), self::filterQuery($query->exportQuery()));
        $this->assertEquals([5, 6, 7, 1.25], $query->exportParams());
    }
    
    public function testSelectFromWhereLimitOffsetOrderBy() {
        $query = new SQLBuilder();
        $query->selectFrom("test1", ['name', 'surname'])
                ->where(SelectionCriteria::Select([
                        "id" => [5, 6, 7]
                    ])->or_where('price', FieldRelationship::GREATER_THAN, 1.25))
                ->limitOffsetOrderBy(ResultModifier::Initialize([
                    'limit' => 1024,
                    'skip' => 100,
                    'name' => FieldOrdering::ASC,
                    'surname' => FieldOrdering::DESC
                ]));
        
        $this->assertEquals(self::filterQuery("SELECT name, surname FROM \"test1\" WHERE id IN (?,?,?) OR price > ? LIMIT 1024 OFFSET 100 ORDER BY name ASC, surname DESC"), self::filterQuery($query->exportQuery()));
        $this->assertEquals([5, 6, 7, 1.25], $query->exportParams());
    }
    
    public function testInsertIntoValues() {
        $query = new SQLBuilder();
        $query->insertInto("users")->values([
            'name' => 'Mario',
            'surname' => 'Rossi',
            'age' => 25,
            'time' => 56.04
        ]);
        
        $this->assertEquals(self::filterQuery("INSERT INTO \"users\" (name, surname, age, time) VALUES (?,?,?,?)"), self::filterQuery($query->exportQuery()));
        $this->assertEquals(['Mario', 'Rossi', 25, 56.04], $query->exportParams());
    }
    
    public function testDeleteFrom() {
        $query = new SQLBuilder();
        $query->deleteFrom("users");
        
        $this->assertEquals(self::filterQuery("DELETE FROM \"users\""), self::filterQuery($query->exportQuery()));
        $this->assertEquals([], $query->exportParams());
    }
    
    public function testUpdateSetWhere() {
        $query = new SQLBuilder();
        $query->update("users")->set(['name' => 'Gianni', 'surname' => 'Pinotto'])->where(SelectionCriteria::Select(['id' => 200]));
        
        $this->assertEquals(self::filterQuery("UPDATE \"users\" SET name = ?, surname = ? WHERE id = ?"), self::filterQuery($query->exportQuery()));
        $this->assertEquals(['Gianni', 'Pinotto', 200], $query->exportParams());
    }
}
