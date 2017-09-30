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

namespace Gishiki\Algorithms\Strings;

/**
 * The lexical analyzer without lexer support
 * used to validate input.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class SimpleLexer
{
    /**
     * Check if the given string can be validates as a valid email address.
     *
     * @param  string $str the string to be validated
     * @return bool true if the given string is a valid email address, false otherwise
     */
    static function isEmail($str) : bool
    {
        return self::isString($str) && filter_var($str, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Check if the given parameter is a valid PHP string.
     *
     * @param string $str the parameter to be validated
     * @return bool true if and only if the given parameter is a valid string
     */
    static function isString($str) : bool
    {
        return is_string($str);
    }

    /**
     * Check if the given string can be evaluated in a valid floating point number.
     *
     * @param string $str the string to be validated
     * @return bool true if the given string is a valid float, false otherwise
     */
    static function isFloat($str) : bool
    {
        if (!self::isString($str)) {
            return false;
        }

        $fountDot = false;

        for ($i = 0; $i < strlen($str); $i++) {
            if (($i != 0) && (($str[$i] == '-') || ($str[$i] == '+'))) {
                return false;
            } else if (($str[$i] == '.') && ($fountDot == true)) {
                return false;
            } else if (strpos("+-.0123456789", $str[$i]) === false) {
                return false;
            }

            //update $fountDot value
            $fountDot =  ($str[$i] == '.') ? true : $fountDot;
        }

        return $str[strlen($str) - 1] != '.';
    }

    /**
     * Check if the given string can be evaluated in a valid unsigned integer number.
     *
     * @param string $str the string to be validated
     * @return bool true if the given string is a valid unsigned integer, false otherwise
     */
    static public function isUnsignedInteger($str) : bool
    {
        if (!self::isString($str)) {
            return false;
        }

        //check the entire string
        for ($i = 0; $i < strlen($str); $i++) {
            if (strpos("0123456789", $str[$i]) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the given string can be evaluated in a valid signed integer number.
     *
     * @param string $str the string to be validated
     * @return bool true if the given string is a valid signed integer, false otherwise
     */
    static public function isSignedInteger($str) : bool
    {
        if (!self::isString($str)) {
            return false;
        }

        //check the 1st character
        if (strpos("+-0123456789", $str[0]) === false) {
            return false;
        }

        //check from the 2nd character afterward
        for ($i = 1; $i < strlen($str); $i++) {
            if (strpos("0123456789", $str[$i]) === false) {
                return false;
            }
        }

        return true;
    }
}