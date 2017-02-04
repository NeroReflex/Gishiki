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

namespace Gishiki\tests\CLI;

use Gishiki\CLI\Console;

/**
 * The tester for the Console class.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class ConsoleTest extends \PHPUnit_Framework_TestCase
{
    public function testWriteBooleanFalse()
    {
        ob_start();

        Console::Write(false);

        $this->assertEquals('false', ob_get_clean());
    }

    public function testWriteBooleanTrue()
    {
        ob_start();

        Console::Write(true);

        $this->assertEquals('true', ob_get_clean());
    }

    public function testWriteNull()
    {
        ob_start();

        Console::Write(null);

        $this->assertEquals('null', ob_get_clean());
    }

    public function testWriteArray()
    {
        $arr = ['Hello, ', 'World!', "It's ", time(), ' Already'];

        ob_start();

        Console::Write($arr);

        $this->assertEquals(implode('', $arr), ob_get_clean());
    }

    public function testWriteLine()
    {
        ob_start();

        Console::WriteLine('The sum is'.': '.(50 + 3));

        $this->assertEquals("The sum is: 53\n", ob_get_clean());
    }
}
