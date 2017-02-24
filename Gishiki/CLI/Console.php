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
     * @var int the color of the text, look at ConsoleColor
     */
    protected static $foregroundColor = ConsoleColor::OFF;

    /**
     * @var int the color of the background, look at ConsoleColor
     */
    protected static $backgroundColor = ConsoleColor::OFF;

    /**
     * @var bool TRUE only if colors have to be enabled
     */
    protected static $enableColors = false;

    /**
     * Enable or disable colors support.
     *
     * @param bool $enable TRUE enable colors, FALSE disable them
     */
    public static function colorsEnable($enable)
    {
        $this->enableColors = boolval($enable);
    }

    /**
     * Check whether colors support is enabled.
     *
     * @return bool TRUE with colors enabled, FALSE otherwise
     */
    public static function colorsEnabled()
    {
        return $this->enableColors;
    }

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

        //do not paint newlines
        $lines = explode("\n", $str);

        for ($lineIndex = 0; $lineIndex < count($lines); ++$lineIndex) {
            //color the text if necessary
            if ($this->colorsEnabled()) {
                printf("\033[".self::$backgroundColor."m\033[".self::$foregroundColor.'m');
            }

            //write the plain-text string
            printf($lines[$lineIndex]);

            //color the text if necessary
            if ($this->colorsEnabled()) {
                printf("\033[0m");
            }

            //print the newline without colors
            if ($lineIndex != count($lines) - 1) {
                printf("\n");
            }
        }

        //if the given string ended with a newline just print it
        if (substr($str, -1)) {
            printf("\n");
        }
    }

    /**
     * Write to the standard output printing a newline afterward.
     *
     * @param mixed $what what will be printed out
     */
    public static function writeLine($what)
    {
        self::write($what);

        self::write("\n");
    }

    /**
     * Change the text color/style of the console.
     * Look at the class ConsoleColor for a list ov available colors.
     *
     * @param int $color the console color code to be used
     *
     * @throws \InvalidArgumentException the given color is not valid
     */
    public static function setForegroundColor($color)
    {
        if ((!is_int($color)) || (($color != ConsoleColor::OFF) && (($color < 1) || ($color > 8)) && (($color < 30) || ($color > 37)))) {
            throw new \InvalidArgumentException('Invalid text color');
        }

        self::$foregroundColor = $color;
    }

    /**
     * Change the background color of the console.
     * Look at the class ConsoleColor for a list ov available colors.
     *
     * @param int $color the console color code to be used
     *
     * @throws \InvalidArgumentException the given color is not valid
     */
    public static function setBackgroundColor($color)
    {
        if ((!is_int($color)) || (($color != ConsoleColor::OFF) && (($color < 40) || ($color > 47)))) {
            throw new \InvalidArgumentException('Invalid text color');
        }

        self::$backgroundColor = $color;
    }

    /**
     * Reset the foreground and background colors
     * of the console to default values.
     */
    public static function resetColors()
    {
        self::$foregroundColor = self::setForegroundColor(ConsoleColor::OFF);
        self::$backgroundColor = self::setBackgroundColor(ConsoleColor::OFF);
    }
}
