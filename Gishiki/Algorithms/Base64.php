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

namespace Gishiki\Algorithms;

/**
 * An helper class for string manipulation.
 *
 * Benato Denis <benato.denis96@gmail.com>
 */
abstract class Base64
{
    /**
     * Create the Base64 binary-safe representation of the given message.
     *
     * The given message can be a binary unsafe string.
     *
     * Example of usage:
     * <code>
     * //this is the binary unsafe message
     * $message = " ... ";
     *
     * //print the result
     * var_dump(Base64::Encode($message));
     * </code>
     *
     * @param string $message the binary-unsafe message
     * @param bool   $urlSafe the generated result doesn't contains special characters
     *
     * @return string the binary-safe representation of the given message
     *
     * @throws \InvalidArgumentException the given message is not represented as a string
     */
    public static function Encode($message, $urlSafe = true)
    {
        //check for the message type
        if (!is_string($message)) {
            throw new \InvalidArgumentException('the binary usafe content must be given as a string');
        }

        //get the base64 url unsafe
        $encoded = base64_encode($message);

        //return the url safe version if requested
        return ($urlSafe) ? rtrim(strtr($encoded, '+/=', '-_~'), '~') : $encoded;
    }

    /**
     * Get the binary-unsafe representation of the given base64-encoded message.
     *
     * This function is compatible with the php standard base64_encode and the
     * framework Base64::Encode( ... ).
     *
     * Example of usage:
     * <code>
     * //this is the binary unsafe message
     * $message = " ... ";
     *
     * //print the input string (binary unsafe)
     * var_dump(Base64::Decode(Base64::Encode($message)));
     * </code>
     *
     * @param string $message a message base64 encoded
     *
     * @return string the message in a binary-unsafe format
     *
     * @throws \InvalidArgumentException the given message is not represented as a string
     */
    public static function Decode($message)
    {
        //check for the message type
        if (!is_string($message)) {
            throw new \InvalidArgumentException('the base64 of a string is represented as another string');
        }

        //is the base64 encoded in an URL safe format?
        $url_safe = (strlen($message) % 4) || (strpos($message, '_') !== false) || (strpos($message, '~') !== false);

        //get the base64 encoded valid string and return the decode result
        $validBase64 = ($url_safe) ?
                str_pad(strtr($message, '-_~', '+/='), strlen($message) + 4 - (strlen($message) % 4), '=', STR_PAD_RIGHT)
                : $message;

        return base64_decode($validBase64);
    }
}
