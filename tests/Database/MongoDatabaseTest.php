<?php
/**************************************************************************
Copyright 2016 Benato Denis

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

use Gishiki\Database\DatabaseManager;

/**
 * The tester for the MongoDatabase class.
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class MongoDatabaseTest extends \PHPUnit_Framework_TestCase
{
    public static function GetConnectionQuery()
    {
        $host = 'localhost';
        $port = '27017';
        $user = 'MongoDB_testing';
        $pass = '45Jfh4oe8E';

        return 'mongodb://'.$user.((strlen($pass) > 0) ? ':' : '').$pass.'@'.$host.':'.$port.'/gishiki';
    }
    
    private static function GetConnection()
    {
        try {
            return DatabaseManager::Retrieve('mongodb_testing_db');
        } catch (\Gishiki\Database\DatabaseException $ex) {
            return DatabaseManager::Connect('mongodb_testing_db', self::GetConnectionQuery());
        }
    }

    public function testInsertion()
    {
        $connection = self::GetConnection();
        $this->assertEquals(true, $connection->Insert('testing.'.__FUNCTION__, ['x' => 3])->Valid());
    }

    public function testChange()
    {
        //connect and setup the test
        $connection = self::GetConnection();
        $connection->Insert('testing.'.__FUNCTION__, ['u' => 3, 'name' => 'Benato Denis']);
        $connection->Insert('testing.'.__FUNCTION__, ['u' => 3, 'name' => 'Mario Rossi']);
        $connection->Insert('testing.'.__FUNCTION__, ['u' => 3, 'email' => 'test@tt.comm']);

        //try to update elements
        $numberOfAffected = $connection->Update('testing.'.__FUNCTION__, ['u' => 2, 'n' => 'prova'], (new \Gishiki\Database\SelectionCriteria())->EqualThan('u', 3));
        $this->assertEquals(3, $numberOfAffected);

        //try to delete elements
        $connection->Insert('testing.'.__FUNCTION__, ['u' => 8, 'email' => 'retest@tt.com']);
        $connection->Insert('testing.'.__FUNCTION__, ['u' => 1, 'email' => 'retest1@tt.com']);
        $numberOfRemoved = $connection->Delete('testing.'.__FUNCTION__, (new \Gishiki\Database\SelectionCriteria())->GreaterThan('u', 1));
        $this->assertEquals(4, $numberOfRemoved);
    }

    public function testUpdateWithFixedID()
    {
        $connection = self::GetConnection();
        $objectID = $connection->Insert('testing.'.__FUNCTION__, ['n' => 'non e una prova....']);
        $numberOfAffected = $connection->Update('testing.'.__FUNCTION__, ['u' => 2, 'n' => 'scherzavo.. e una prova'], (new \Gishiki\Database\SelectionCriteria())->WhereID($objectID));
        $this->assertEquals(1, $numberOfAffected);
    }

    public function testChangeWithFixedIDWithConcurrencySecurity()
    {
        $badStr = 'non e una prova....';

        $connection = self::GetConnection();
        $objectID = $connection->Insert('testing.'.__FUNCTION__, ['n' => $badStr]);
        $numberOfAffected = $connection->Update('testing.'.__FUNCTION__, ['u' => 2, 'n' => 'scherzavo.. e una prova'], (new \Gishiki\Database\SelectionCriteria())->WhereID($objectID)->EqualThan('n', $badStr));
        $this->assertEquals(1, $numberOfAffected);
    }

    public function testRead()
    {
        $connection = self::GetConnection();
        $connection->Insert('testing.'.__FUNCTION__, ['k' => 7, 'name' => 'Denis']);
        $connection->Insert('testing.'.__FUNCTION__, ['k' => 2, 'mail' => 'benato.denis96@gmail.com']);
        $connection->Insert('testing.'.__FUNCTION__, ['k' => 11, 'mail' => 'fake@mail.kk']);

        $result = $connection->Fetch('testing.'.__FUNCTION__, (new \Gishiki\Database\SelectionCriteria())->LessThan('k', 10));
        $this->assertEquals(2, $result->count());

        foreach ($result as $record) {
            $this->assertEquals(true, strlen($record->GetObjectID().'') > 0);
            $this->assertEquals('testing.'.__FUNCTION__, $record->GetObjectID()->GetTableName());
        }

        $numberOfRemoved = $connection->Delete('testing.'.__FUNCTION__, new \Gishiki\Database\SelectionCriteria());
        $this->assertEquals(3, $numberOfRemoved);
    }
}
