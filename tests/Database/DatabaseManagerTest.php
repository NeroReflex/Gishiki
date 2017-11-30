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

namespace Gishiki\tests\Database;

use Gishiki\Database\Adapters\Utils\ConnectionParser\ConnectionParserException;
use PHPUnit\Framework\TestCase;

use Gishiki\Database\DatabaseException;
use Gishiki\Database\DatabaseManager;

/**
 * The tester for the DatabaseManager class.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class DatabaseManagerTest extends TestCase
{
    public function testBadConnectionQuery()
    {
        $this->expectException(\InvalidArgumentException::class);
        $dbManager = new DatabaseManager();
        $dbManager->connect(3, 'unknown_db_adapter://user:pass@host:port/db');
    }

    public function testConnectionQuery()
    {
        $this->expectException(ConnectionParserException::class);
        $dbManager = new DatabaseManager();
        $dbManager->connect('default', 'unknown_db_adapter://user:pass@host:port/db');
    }

    public function testVoidConnection()
    {
        $this->expectException(DatabaseException::class);
        $dbManager = new DatabaseManager();
        $dbManager->retrieve('testing_bad_db (unconnected)');
    }

    public function testInvalidNameConnection()
    {
        $this->expectException(\InvalidArgumentException::class);
        $dbManager = new DatabaseManager();
        $dbManager->retrieve(3);
    }

    public function testValidConnection()
    {
        //connect an empty-memory bounded database
        $dbManager = new DatabaseManager();
        $dbManager->connect('temp_db', 'sqlite://:memory:');

        //retrieve the connected database
        $connection = $dbManager->retrieve('temp_db');

        //test for a successful retrieve operation
        $this->assertTrue($connection->connected());
    }
}
