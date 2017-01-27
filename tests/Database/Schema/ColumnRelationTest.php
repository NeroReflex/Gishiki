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
 * The tester for the ColumnRelation class.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class ColumnRelationTest extends \PHPUnit_Framework_TestCase {
    
    /**
     * @expectedException Gishiki\Database\DatabaseException
     */
    public function testColumnRelationNotPrimaryKey()
    {
        $externTable = new Table(__FUNCTION__);
        
        $externColumn = new Column('id', ColumnType::INTEGER);
        // $externColumn->setPrimaryKey(true); this is not a primary key, so... an error should be triggered
        $externColumn->setNotNull(true);
        $externColumn->setAutoIncrement(true);
        
        $externTable->addColumn($externColumn);
        
        $localColumn = new Column(__FUNCTION__.'_id', ColumnType::INTEGER);
        
        $relation = new ColumnRelation($localColumn, $externTable, $externColumn);
    }
    
    /**
     * @expectedException Gishiki\Database\DatabaseException
     */
    public function testColumnRelationUnbindedColumn()
    {
        $externTable = new Table(__FUNCTION__);
        
        $externColumn = new Column('id', ColumnType::INTEGER);
        $externColumn->setPrimaryKey(true); 
        $externColumn->setNotNull(true);
        $externColumn->setAutoIncrement(true);
        
        $localColumn = new Column(__FUNCTION__.'_id', ColumnType::INTEGER);
        
        $relation = new ColumnRelation($localColumn, $externTable, $externColumn);
    }
    
    public function testColumnRelation()
    {
        $externTable = new Table(__FUNCTION__);
        
        $externColumn = new Column('id', ColumnType::INTEGER);
        $externColumn->setPrimaryKey(true); 
        $externColumn->setNotNull(true);
        $externColumn->setAutoIncrement(true);
        
        $externTable->addColumn($externColumn);
        
        $localColumn = new Column(__FUNCTION__.'_id', ColumnType::INTEGER);
        
        $relation = new ColumnRelation($localColumn, $externTable, $externColumn);
        
        $this->assertEquals($externTable, $relation->getForeignTable());
        $this->assertEquals($externColumn, $relation->getForeignKey());
        $this->assertEquals($localColumn, $relation->getLocalKey());
    }
}
