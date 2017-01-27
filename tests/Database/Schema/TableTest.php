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

use Gishiki\Database\Schema\Column;
use Gishiki\Database\Schema\ColumnRelation;
use Gishiki\Database\Schema\ColumnType;
use Gishiki\Database\Schema\Table;

/**
 * The tester for the Table class.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class TableTest extends \PHPUnit_Framework_TestCase {
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testTableBadName() {
        $table = new Table('');
    }
    
    public function testTableName() {
        $table = new Table(__FUNCTION__);
        
        $this->assertEquals(__FUNCTION__, $table->getName());
    }
    
    /**
     * @expectedException Gishiki\Database\DatabaseException
     */
    public function testTableDuplicateColumns() {
        $table = new Table(__FUNCTION__);
        
        $columnOne = new Column('id', ColumnType::INTEGER);
        $columnTwo = new Column('id', ColumnType::TEXT);
        
        $table->addColumn($columnOne)->addColumn($columnTwo);
    }
    
    public function testTableColumns() {
        $table = new Table(__FUNCTION__);
        
        $columnOne = new Column('id', ColumnType::INTEGER);
        $columnTwo = new Column('test', ColumnType::TEXT);
        $columnThree = new Column('created_at', ColumnType::DATETIME);
        
        $table->addColumn($columnOne)->addColumn($columnTwo)->addColumn($columnThree);
        
        $this->assertEquals([
            $columnOne,
            $columnTwo,
            $columnThree
        ], $table->getColumns());
    }
    
    public function testTableColumnRelation()
    {
        $externTable = new Table(__FUNCTION__.'_extern');
        
        $externColumn = new Column('id', ColumnType::INTEGER);
        $externColumn->setPrimaryKey(true); 
        $externColumn->setNotNull(true);
        $externColumn->setAutoIncrement(true);
        
        $externTable->addColumn($externColumn);
        
        $localColumn = new Column(($externTable->getName()).'_id', ColumnType::INTEGER);
        
        $relation = new ColumnRelation($localColumn, $externTable, $externColumn);
        
        $this->assertEquals($externTable, $relation->getForeignTable());
        $this->assertEquals($externColumn, $relation->getForeignKey());
        $this->assertEquals($localColumn, $relation->getLocalKey());
        
        $localTable = new Table(__FUNCTION__.'local');
        
        //before adding a column relation there are none of them
        $this->assertEquals([ ], $localTable->getRelations());
        
        $localTable->addRelation($relation);
        
        // after adding one there should be one :)
        $this->assertEquals([$relation], $localTable->getRelations());
    }
    
    /**
     * @expectedException Gishiki\Database\DatabaseException
     */
    public function testTableDuplicateColumnRelation()
    {
        $externTable = new Table(__FUNCTION__.'_extern');
        
        $externColumn = new Column('id', ColumnType::INTEGER);
        $externColumn->setPrimaryKey(true); 
        $externColumn->setNotNull(true);
        $externColumn->setAutoIncrement(true);
        
        $externTable->addColumn($externColumn);
        
        $localColumn = new Column(($externTable->getName()).'_id', ColumnType::INTEGER);
        $localColumnDuplicate = new Column(($externTable->getName()).'_id_dup', ColumnType::INTEGER);
        
        $relation = new ColumnRelation($localColumn, $externTable, $externColumn);
        $relationDuplicate = new ColumnRelation($localColumnDuplicate, $externTable, $externColumn);
        
        $this->assertEquals($externTable, $relation->getForeignTable());
        $this->assertEquals($externColumn, $relation->getForeignKey());
        $this->assertEquals($localColumn, $relation->getLocalKey());
        
        $localTable = new Table(__FUNCTION__.'local');
        
        $localTable->addRelation($relation);
        $localTable->addRelation($relationDuplicate);
    }
}
