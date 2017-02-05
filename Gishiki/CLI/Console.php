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

namespace Gishiki\CLI;

/**
 * A class to emulate the C# System.Console class.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class Console
{
    /**
     * Write to the standard output without printing a newline.
     *
     * @param mixed $what what will be printed out
     */
    public static function write($what)
    {
        $str = '';

        switch (strtolower(gettype($what))) {
            case 'boolean':
                $str = ($what) ? 'true' : 'false';
                break;

            case 'null':
                $str = 'null';
                break;

            case 'array':
                foreach ($what as $element) {
                    self::write($element);
                }
                break;

            default:
                $str = ''.$what;
        }

        printf($str);
    }

    /**
     * Write to the standard output printing a newline afterward.
     *
     * @param mixed $what what will be printed out
     */
    public static function writeLine($what)
    {
        self::write($what);

        //print the newline
        self::write("\n");
    }
}
