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
    const CRC32 = 'crc32';
    const MD4 = 'md4';
    const MD5 = 'md5';
    const SHA1 = 'sha1';
    const SHA256 = 'sha256';
    const SHA328 = 'sha384';
    const SHA512 = 'sha512';
    const ROT13 = 'rot13';

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
     * @param bool   $rawOutput if false the result is binhex
     *
     * @return string the result of the hash algorithm
     *
     * @throws \InvalidArgumentException the message or the algorithm is given as a non-string or an empty string
     * @throws HashingException          the error occurred while generating the hash for the given message
     */
    public static function hash($message, $algorithm = self::MD5, $rawOutput = false)
    {
        //check for the parameter
        if (!is_bool($rawOutput)) {
            throw new \InvalidArgumentException('The binary safeness must be given as a boolean value');
        }

        //check for the message
        if ((!is_string($message)) || (strlen($message) <= 0)) {
            throw new \InvalidArgumentException('The message to be hashed must be given as a valid non-empty string');
        }

        //check for the algorithm name
        if ((!is_string($algorithm)) || (strlen($algorithm) <= 0)) {
            throw new \InvalidArgumentException('The name of the hashing algorithm must be given as a valid non-empty string');
        }

        if ($algorithm == self::ROT13) {
            return str_rot13($message);
        }

        //check if the hashing algorithm is supported
        if ((!in_array($algorithm, openssl_get_md_methods())) && (!in_array($algorithm, hash_algos()))) {
            throw new HashingException('An error occurred while generating the hash, because an unsupported hashing algorithm has been selected', 0);
        }

        //calculate the hash for the given message
        $hash = ((in_array($algorithm, openssl_get_md_methods()))) ? openssl_digest($message, $algorithm, $rawOutput) : hash($algorithm, $algorithm, $rawOutput);

        //check for errors
        if ($hash === false) {
            throw new HashingException('An unknown error occurred while generating the hash', 1);
        }

        //return the calculated message digest
        return $hash;
    }

    /**
     * PBKDF2 key derivation function as defined by RSA's PKCS #5: https://www.ietf.org/rfc/rfc2898.txt.
     * 
     * Test vectors can be found here: https://www.ietf.org/rfc/rfc6070.txt
     *
     * This implementation of PBKDF2 was originally created by https://defuse.ca
     * With improvements by http://www.variations-of-shadow.com
     * 
     * @param string $algorithm the hash algorithm to use. Recommended: SHA256
     * @param string $password  the password
     * @param string $salt      a salt that is unique to the password
     * @param string $count     iteration count. Higher is better, but slower. Recommended: At least 1000
     * @param string $keyLength the length of the derived key in bytes
     * @param string $rawOutput if true, the key is returned in raw binary format. Hex encoded otherwise
     *
     * @return string the key derived from the password and salt
     *
     * @throws \InvalidArgumentException invalid arguments have been passed
     * @throws HashingException          the error occurred while generating the requested hashing algorithm
     */
    public static function pbkdf2($password, $salt, $keyLength, $count, $algorithm = self::SHA1, $rawOutput = false, $forceSlowAlgo = false)
    {
        if ((!is_integer($count)) || ($count <= 0)) {
            throw new \InvalidArgumentException('The iteration number for the PBKDF2 function must be a positive non-zero integer', 2);
        }

        if ((!is_integer($keyLength)) || ($keyLength <= 0)) {
            throw new \InvalidArgumentException('The resulting key length for the PBKDF2 function must be a positive non-zero integer', 2);
        }

        if ((!is_string($algorithm)) || (strlen($algorithm) <= 0)) {
            throw new \InvalidArgumentException('The hashing algorithm for the PBKDF2 function must be a non-empty string', 2);
        } else {
            $algorithm = strtolower($algorithm);
        }

        //the raw output of the max legth (beyond the $keyLength algorithm)
        $output = '';

        if ((function_exists('openssl_pbkdf2')) && (!$forceSlowAlgo)) {
            /*          execute the native openssl_pbkdf2                   */

            //check if the algorithm is valid
            if (!in_array($algorithm, openssl_get_md_methods(true), true)) {
                throw new HashingException('Invalid algorithm: the choosen algorithm is not valid for the PBKDF2 function', 2);
            }

            $output = openssl_pbkdf2($password, $salt, $keyLength, $count, $algorithm);
        } elseif (function_exists('hash_pbkdf2')) {
            /*          execute the native hash_pbkdf2                     */

            //check if the algorithm is valid
            if (!in_array($algorithm, hash_algos(), true)) {
                throw new HashingException('Invalid algorithm: the choosen algorithm is not valid for the PBKDF2 function', 2);
            }

            // The output length is in NIBBLES (4-bits) if $rawOutput is false!
            if (!$rawOutput) {
                $keyLength = $keyLength * 2;
            }

            return hash_pbkdf2($algorithm, $password, $salt, $count, $keyLength, $rawOutput);
        } else {
            /*          use an hack to emulate openssl_pbkdf2               */

            //check if the algorithm is valid
            if (!in_array($algorithm, hash_algos(), true)) {
                throw new HashingException('Invalid algorithm: the choosen algorithm is not valid for the PBKDF2 function', 2);
            }

            $hashLength = strlen(hash($algorithm, '', true));
            $blockCount = ceil($keyLength / $hashLength);

            for ($i = 1; $i <= $blockCount; ++$i) {
                // $i encoded as 4 bytes, big endian.
                $last = $salt.pack('N', $i);

                // first iteration
                $last = $xorsum = hash_hmac($algorithm, $last, $password, true);

                // perform the other $count - 1 iterations
                for ($j = 1; $j < $count; ++$j) {
                    $xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));
                }
                $output .= $xorsum;
            }
        }

        return ($rawOutput) ? substr($output, 0, $keyLength) : bin2hex(substr($output, 0, $keyLength));
    }
}
