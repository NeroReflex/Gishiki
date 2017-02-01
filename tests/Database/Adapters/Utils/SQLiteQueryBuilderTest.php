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

use Gishiki\Database\Adapters\Utils\SQLiteQueryBuilder;
use Gishiki\Database\Schema\Table;
use Gishiki\Database\Schema\Column;
use Gishiki\Database\Schema\ColumnType;
use Gishiki\Database\Schema\ColumnRelation;

/**
 * The tester for the SQLiteQueryBuilder class.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class SQLiteQueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testDropTable()
    {
        $query = new SQLiteQueryBuilder();
        $query->dropTable(__FUNCTION__);
        
        $this->assertEquals(SQLiteQueryBuilder::Beautify('DROP TABLE IF EXISTS '.__FUNCTION__), SQLiteQueryBuilder::Beautify($query->exportQuery()));
    }
    
    public function testSelectAllFrom()
    {
        $table = new Table(__FUNCTION__);
        
        $idColumn = new Column('id', ColumnType::INTEGER);
        $idColumn->setNotNull(true);
        $idColumn->setPrimaryKey(true);
        $table->addColumn($idColumn);
        $nameColumn = new Column('name', ColumnType::TEXT);
        $nameColumn->setNotNull(true);
        $table->addColumn($nameColumn);
        $creditColumn = new Column('credit', ColumnType::REAL);
        $creditColumn->setNotNull(true);
        $table->addColumn($creditColumn);
        
        $query = new SQLiteQueryBuilder();
        $query->createTable($table->getName())->definedAs($table->getColumns());

        $this->assertEquals(SQLiteQueryBuilder::Beautify('CREATE TABLE IF NOT EXISTS '.__FUNCTION__.' ('
                . 'id INT PRIMARY KEY NOT NULL, '
                . 'name TEXT NOT NULL, '
                . 'credit REAL NOT NULL'
                . ')'), SQLiteQueryBuilder::Beautify($query->exportQuery()));
    }
}
