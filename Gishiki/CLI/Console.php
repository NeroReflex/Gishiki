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
     * @var integer the color of the text, look at ConsoleTextColor
     */
    protected static $foregroundColor = ConsoleTextColor::off;
    
    /**
     * @var integer the color of the background, look at ConsoleBackgroundColor
     */
    protected static $backgroundColor = ConsoleBackgroundColor::off;
    
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

        printf("\033[" . self::$backgroundColor . "m\033[" . self::$foregroundColor . "m" . $str . "\033[0m");
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
    
    /**
     * Change the text color/style of the console.
     * Look at the class ConsoleTextColor for a list ov available colors.
     *
     * @param integer $color the console color code to be used
     */
    public static function setForegroundColor($color)
    {
        self::$foregroundColor = $color;
    }
    
    /**
     * Change the background color of the console.
     * Look at the class ConsoleBackgroundColor for a list ov available colors.
     * 
     * @param integer $color the console color code to be used
     */
    public static function setBackgroundColor($color)
    {
        self::$backgroundColor = $color;    
    }
    
    /**
     * Reset the foreground and background colors
     * of the console to default values.
     */
    public static function resetColors()
    {
        self::$foregroundColor = self::setForegroundColor(ConsoleTextColor::off);
        self::$backgroundColor = self::setBackgroundColor(ConsoleBackgroundColor::off);
    }
}
