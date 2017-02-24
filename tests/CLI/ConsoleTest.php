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
    public function testUncoloredWriteBooleanFalse()
    {
        Console::enableColors(false);

        ob_start();

        Console::write(false);

        $this->assertEquals('false', ob_get_clean());
    }

    public function testUncoloredWriteBooleanTrue()
    {
        Console::enableColors(false);

        ob_start();

        Console::write(true);

        $this->assertEquals('true', ob_get_clean());
    }

    public function testUncoloredWriteNull()
    {
        Console::enableColors(false);

        ob_start();

        Console::write(null);

        $this->assertEquals('null', ob_get_clean());
    }

    public function testUncoloredWriteArray()
    {
        Console::enableColors(false);

        $arr = ['Hello, ', 'World!', "It's ", time(), ' Already'];

        ob_start();

        Console::write($arr);

        $this->assertEquals(implode('', $arr), ob_get_clean());
    }

    public function testUncoloredWriteLine()
    {
        Console::enableColors(false);

        ob_start();

        Console::writeLine('The sum is'.': '.(50 + 3));

        $this->assertEquals("The sum is: 53\n", ob_get_clean());
    }

    public function testColorsSupportChange()
    {
        Console::colorsEnable(false);
        $this->assertEquals(false, Console::colorsEnabled());

        Console::colorsEnable(true);
        $this->assertEquals(true, Console::colorsEnabled());

        Console::colorsEnable(false);
        $this->assertEquals(false, Console::colorsEnabled());
    }
}
