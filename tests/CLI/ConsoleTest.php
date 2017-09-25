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

use \PHPUnit\Framework\TestCase;

use \Gishiki\CLI\Console;


/**
 * The tester for the Console class.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class ConsoleTest extends TestCase
{
    public function testWriteBooleanFalse()
    {
        $this->expectOutputString('false');
        
        Console::colorsEnable(false);

        Console::write(false);
    }

    public function testWriteBooleanTrue()
    {
        $this->expectOutputString('true');
        
        Console::colorsEnable(false);

        Console::write(true);
    }

    public function testWriteNull()
    {
        $this->expectOutputString('null');
        
        Console::colorsEnable(false);

        Console::write(null);
    }

    public function testWriteArray()
    {
        $arr = ['Hello, ', 'World!', "It's ", time(), ' Already'];
        
        $this->expectOutputString(implode('', $arr));
        
        Console::colorsEnable(false);

        Console::write($arr);
    }

    public function testWriteLine()
    {
        $this->expectOutputString("The sum is: 53\n");
        
        Console::colorsEnable(false);

        Console::writeLine('The sum is'.': '.(50 + 3));
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
