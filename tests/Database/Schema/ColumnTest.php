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
use Gishiki\Database\Schema\ColumnType;
use Gishiki\Database\Schema\Table;

/**
 * The tester for the Column class.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class ColumnTest extends \PHPUnit_Framework_TestCase {
    
    public function testColumnTable()
    {
        //create the table for this test
        $table = new Table(__FUNCTION__);
        
        //create a testing column
        $col = new Column($table, 'test', ColumnType::INTEGER);
        
        $this->assertEquals($table, $col->getTable());
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testColumnBadNaming()
    {
        //create the table for this test
        $table = new Table(__FUNCTION__);
        
        //create a testing column
        $col = new Column($table, '', ColumnType::INTEGER);
    }
    
    public function testColumnNaming()
    {
        //create the table for this test
        $table = new Table(__FUNCTION__);
        
        //create a testing column
        $col = new Column($table, 'test', ColumnType::INTEGER);
        
        $this->assertEquals('test', $col->getName());
    }
    
    public function testColumnPrimaryKey()
    {
        //create the table for this test
        $table = new Table(__FUNCTION__);
        
        //create a testing column (by default a new column is NOT a primary key)
        $col = new Column($table, 'id', ColumnType::INTEGER);
        
        $this->assertEquals(false, $col->getPrimaryKey());
        
        $col->setPrimaryKey(true);
        
        $this->assertEquals(true, $col->getPrimaryKey());
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testColumnBadPrimaryKey()
    {
        //create the table for this test
        $table = new Table(__FUNCTION__);
        
        //create a testing column
        $col = new Column($table, 'id', ColumnType::INTEGER);
        $col->setPrimaryKey(null);
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testColumnBadAutoIncrement()
    {
        //create the table for this test
        $table = new Table(__FUNCTION__);
        
        //create a testing column
        $col = new Column($table, 'id', ColumnType::INTEGER);
        $col->setAutoIncrement(null);
    }
    
    public function testColumnAutoIncrement()
    {
        //create the table for this test
        $table = new Table(__FUNCTION__);
        
        //create a testing column (by default a new column is NOT a primary key)
        $col = new Column($table, 'id', ColumnType::INTEGER);
        
        $this->assertEquals(false, $col->getAutoIncrement());
        
        $col->setAutoIncrement(true);
        
        $this->assertEquals(true, $col->getAutoIncrement());
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testColumnBadNotNull()
    {
        //create the table for this test
        $table = new Table(__FUNCTION__);
        
        //create a testing column
        $col = new Column($table, 'id', ColumnType::INTEGER);
        $col->setNotNull(null);
    }
    
    public function testColumnNotNull()
    {
        //create the table for this test
        $table = new Table(__FUNCTION__);
        
        //create a testing column (by default a new column is NOT a primary key)
        $col = new Column($table, 'id', ColumnType::INTEGER);
        
        $this->assertEquals(false, $col->getNotNull());
        
        $col->setNotNull(true);
        
        $this->assertEquals(true, $col->getNotNull());
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testColumnBadType()
    {
        //create the table for this test
        $table = new Table(__FUNCTION__);
        
        //create a testing column
        $col = new Column($table, 'id', null);
        $col->setNotNull(null);
    }
    
    public function testColumnType()
    {
        //create the table for this test
        $table = new Table(__FUNCTION__);
        
        //create a testing column (by default a new column is NOT a primary key)
        $col = new Column($table, 'id', ColumnType::INTEGER);
        
        $this->assertEquals(ColumnType::INTEGER, $col->getType());
        
        
        $col->setType(ColumnType::TEXT);
        $this->assertEquals(ColumnType::TEXT, $col->getType());
        
        $col->setType(ColumnType::REAL);
        $this->assertEquals(ColumnType::REAL, $col->getType());
        
        $col->setType(ColumnType::DATETIME);
        $this->assertEquals(ColumnType::DATETIME, $col->getType());
        
        $col->setType(ColumnType::INTEGER);
        $this->assertEquals(ColumnType::INTEGER, $col->getType());
    }
}
