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

namespace Gishiki\tests\Database\Schema;

use PHPUnit\Framework\TestCase;

use Gishiki\Database\Schema\Column;
use Gishiki\Database\Schema\ColumnType;
use Gishiki\Database\Schema\Table;

/**
 * The tester for the Column class.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class ColumnTest extends TestCase
{
    public function testColumnBadNaming()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        //create a testing column
        new Column('', ColumnType::INTEGER);
    }

    public function testColumnNaming()
    {
        //create a testing column
        $col = new Column('test', ColumnType::INTEGER);

        $this->assertEquals('test', $col->getName());
    }

    public function testColumnPrimaryKey()
    {
        //create a testing column (by default a new column is NOT a primary key)
        $col = new Column('id', ColumnType::INTEGER);

        $this->assertEquals(false, $col->getPrimaryKey());

        $col->setPrimaryKey(true);

        $this->assertEquals(true, $col->getPrimaryKey());
    }

    public function testColumnBadPrimaryKey()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        //create a testing column
        $col = new Column('id', ColumnType::INTEGER);
        $col->setPrimaryKey(null);
    }

    public function testColumnAutoIncrement()
    {
        //create a testing column (by default a new column is NOT a primary key)
        $col = new Column('id', ColumnType::INTEGER);

        $this->assertEquals(false, $col->getAutoIncrement());

        $col->setAutoIncrement(true);

        $this->assertEquals(true, $col->getAutoIncrement());
    }

    public function testColumnBadAutoIncrement()
    {
        $this->expectException(\InvalidArgumentException::class);

        //create a testing column
        $col = new Column('id', ColumnType::INTEGER);
        $col->setAutoIncrement(null);
    }

    public function testColumnBadNotNull()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        //create a testing column
        $col = new Column('id', ColumnType::INTEGER);
        $col->setNotNull(null);
    }

    public function testColumnNotNull()
    {
        //create a testing column (by default a new column is NOT a primary key)
        $col = new Column('id', ColumnType::INTEGER);

        $this->assertEquals(false, $col->getNotNull());

        $col->setNotNull(true);

        $this->assertEquals(true, $col->getNotNull());
    }

    public function testColumnBadType()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        //create a testing column
        $col = new Column('id', null);
        $col->setNotNull(null);
    }

    public function testColumnType()
    {
        //create a testing column (by default a new column is NOT a primary key)
        $col = new Column('id', ColumnType::INTEGER);

        $this->assertEquals(ColumnType::INTEGER, $col->getType());

        $col->setType(ColumnType::TEXT);
        $this->assertEquals(ColumnType::TEXT, $col->getType());

        $col->setType(ColumnType::DOUBLE);
        $this->assertEquals(ColumnType::DOUBLE, $col->getType());

        $col->setType(ColumnType::DATETIME);
        $this->assertEquals(ColumnType::DATETIME, $col->getType());

        $col->setType(ColumnType::INTEGER);
        $this->assertEquals(ColumnType::INTEGER, $col->getType());
    }
}
