<?php
/**************************************************************************
Copyright 2015 Benato Denis

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

namespace Gishiki\Security\Hashing;

/**
 * This class is a collection of supported algorithms.
 * 
 * Note: This class uses OpenSSL for strong encryption
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class Algorithms
{
    /**************************************************************************
     *                     Common hashing algorithms                          *
     **************************************************************************/
    const MD5 = 'MD5';
    const SHA1 = 'sha1';
    const SHA256 = 'sha256';
    const SHA328 = 'sha384';
    const SHA512 = 'sha512';

    /**
     * Generate the message digest for the given message.
     * 
     * An example usage is:
     * 
     * <code>
     * $message = "this is the message to be hashed";
     * 
     * $test_gishiki_md5 = Algorithms::hash($message, Algorithms::MD5);
     * $test_php_md5 = md5($message);
     * 
     * if ($test_gishiki_md5 == $test_php_md5) {
     *     echo "Gishiki's MD5 produces the same exact hash of the PHP's MD5";
     * }
     * </code>
     * 
     * @param string $message   the string to be hashed
     * @param string $algorithm the name of the hashing algorithm
     *
     * @return string the result of the hash algorithm
     *
     * @throws \InvalidArgumentException the message or the algorithm is given as a non-string or an empty string
     * @throws HashingException          the error occurred while generating the hash for the given message
     */
    public static function hash($message, $algorithm = self::MD5)
    {
        //check for the message
        if ((!is_string($message)) || (strlen($message) <= 0)) {
            throw new \InvalidArgumentException('The message to be hashed must be given as a valid non-empty string');
        }

        //check for the algorithm name
        if ((!is_string($algorithm)) || (strlen($algorithm) <= 0)) {
            throw new \InvalidArgumentException('The name of the hashing algorithm must be given as a valid non-empty string');
        }

        //check if the hashing algorithm is supported
        if (!in_array($algorithm, openssl_get_md_methods())) {
            throw new HashingException('An error occurred while generating the hash, because an unsupported hashing algorithm has been selected', 0);
        }

        //calculate the hash for the given message
        $hash = openssl_digest($message, $algorithm);

        //check for errors
        if ($hash === false) {
            throw new HashingException('An unknown error occurred while generating the hash', 1);
        }

        //return the calculated message digest
        return $hash;
    }
}
