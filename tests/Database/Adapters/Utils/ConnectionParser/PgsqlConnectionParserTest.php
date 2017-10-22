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
use Gishiki\Database\Adapters\Utils\ConnectionParser\PgsqlConnectionParser;

use PHPUnit\Framework\TestCase;

/**
 * The tester for the PgsqlConnectionParser class.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class PgsqlConnectionParserTest extends TestCase
{
    public static function getParser()
    {
        return new PgsqlConnectionParser();
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
            'pgsql:host=192.168.1.120;port=8034;dbname=mywebsite;',
            'root',
            'mypass__',
            null
        ], $parser->getPDOConnection());
    }
}