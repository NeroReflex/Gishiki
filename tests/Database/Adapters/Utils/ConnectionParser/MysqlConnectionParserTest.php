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

namespace Gishiki\tests\Database\Adapters\Utils\ConnectionParser;

use Gishiki\Database\Adapters\Utils\ConnectionParser\ConnectionParserException;
use Gishiki\Database\Adapters\Utils\ConnectionParser\MysqlConnectionParser;

use PHPUnit\Framework\TestCase;

/**
 * The tester for the MysqlConnectionParser class.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class MysqlConnectionParserTest extends TestCase
{
    public static function getParser()
    {
        return new MysqlConnectionParser();
    }

    public function testValidStandardFormat()
    {
        $parser = static::getParser();

        $parser->parse("root:test_pw@localhost:4580/database");

        $this->assertEquals([
            'mysql:host=localhost;port=4580;dbname=database;',
            'root',
            'test_pw',
            null
        ], $parser->getPDOConnection());
    }

    public function testValidAndCompleteStandardFormat()
    {
        $parser = static::getParser();

        $parser->parse("root:test_pw@localhost:4580/database?charset=utf8");

        $this->assertEquals([
            'mysql:host=localhost;port=4580;dbname=database;',
            'root',
            'test_pw',
            [
                'charset' => 'utf8'
            ]
        ], $parser->getPDOConnection());
    }

    public function testInvalidUsernameStandardFormat()
    {
        $parser = static::getParser();

        $this->expectException(ConnectionParserException::class);
        $parser->parse(":test_pw@localhost:4580/database?charset=utf8");
    }

    public function testInvalidHostnameStandardFormat()
    {
        $parser = static::getParser();

        $this->expectException(ConnectionParserException::class);
        $parser->parse("user:test_pw@:4580/database");
    }

    public function testInvalidDatabaseStandardFormat()
    {
        $parser = static::getParser();

        $this->expectException(ConnectionParserException::class);
        $parser->parse("user:test_pw@localhost:4580");
    }

    public function testBadParam()
    {
        $parser = static::getParser();

        $this->expectException(\InvalidArgumentException::class);
        $parser->parse(null);
    }

    public function testInvalidFormat()
    {
        $parser = static::getParser();

        $this->expectException(ConnectionParserException::class);
        $parser->parse("lol---");
    }

    public function testInvalidParameterPDOFormat()
    {
        $parser = static::getParser();

        $this->expectException(ConnectionParserException::class);
        $parser->parse("host=localhost;port345;user=root;");
    }

    public function testValidPDOFormat()
    {
        $parser = static::getParser();

        $parser->parse("host=192.168.1.120;port=8034;dbname=mywebsite;user=root;password=mypass__;");

        $this->assertEquals([
            'mysql:host=192.168.1.120;port=8034;dbname=mywebsite;',
            'root',
            'mypass__',
            null
        ], $parser->getPDOConnection());
    }
}
