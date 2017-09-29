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

namespace Gishiki\Security\Hashing;

use Gishiki\Algorithms\Base64;

/**
 * This class is a collection of supported algorithms.
 *
 * Note: This class uses OpenSSL for strong encryption
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class Algorithm
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
    const BCRYPT = 'bcrypt';
    const PBKDF2 = 'pbkdf2';

    /**
     * Generate the message digest for the given message using the OpenSSL library
     *
     * An example usage is:
     *
     * <code>
     * $message = "this is the message to be hashed";
     *
     * $test_gishiki_md5 = Algorithm::opensslHash($message, Algorithm::MD5);
     *
     * echo "The hash of the message is: $test_gishiki_md5";
     * </code>
     *
     * This function should be called from an Hasher object.
     *
     * @param string $message      the string to be hashed
     * @param string $algorithm    the name of the hashing algorithm
     *
     * @return string the result of the hash algorithm
     *
     * @throws \InvalidArgumentException the message is given as a non-string or an empty string
     * @throws HashingException          the error occurred while generating the hash for the given message
     */
    public static function opensslHash($message, $algorithm)
    {
        //check for the message
        if ((!is_string($message)) || (strlen($message) <= 0)) {
            throw new \InvalidArgumentException('The message to be hashed must be given as a valid non-empty string');
        }

        //calculate the hash for the given message
        $result = ((in_array($algorithm, openssl_get_md_methods()))) ? openssl_digest($message, $algorithm, false) : hash($algorithm, $algorithm, false);

        //check for errors
        if ($result === false) {
            throw new HashingException('An unknown error occurred while generating the hash', 1);
        }

        //return the calculated message digest
        return $result;
    }

    /**
     * Check if the digest is the hash of the given message (using OpenSSL algorithms).
     *
     * This function should be called from an Hasher object.
     *
     * @param string $message      the string to be checked against the message digest
     * @param string $digest       the message digest to be checked
     * @return string the result of the hash algorithm
     *
     * @throws \InvalidArgumentException the message or the message digest is given as a non-string or an empty string
     */
    public static function opensslVerify($message, $digest, $algorithm)
    {
        //check for the message
        if ((!is_string($message)) || (strlen($message) <= 0)) {
            throw new \InvalidArgumentException('The message to be hashed must be given as a valid non-empty string');
        }

        //check for the digest
        if ((!is_string($digest)) || (strlen($digest) <= 0)) {
            throw new \InvalidArgumentException('The message digest to be checked must be given as a valid non-empty string');
        }

        return (strcmp(self::opensslHash($message, $algorithm), $digest) == 0);
    }

    /**
     * Generate the rot13 for the given message.
     *
     * An example usage is:
     *
     * <code>
     * echo "You should watch Star Wars VII to find out that " . Algorithm::rot13Hash("Han Solo dies.", 'rot13');
     * </code>
     *
     * This function should be called from an Hasher object.
     *
     * @param string $message      the string to be hashed
     *
     * @return string the result of the hash algorithm
     *
     * @throws \InvalidArgumentException the message is given as a non-string or an empty string
     */
    public static function rot13Hash($message)
    {
        //check for the message
        if ((!is_string($message)) || (strlen($message) <= 0)) {
            throw new \InvalidArgumentException('The message to be hashed must be given as a valid non-empty string');
        }

        return str_rot13($message);
    }

    /**
     * Check if the digest is rot13 hash of the given message.
     *
     * This function should be called from an Hasher object.
     *
     * @param string $message      the string to be checked against the message digest
     * @param string $digest       the message digest to be checked
     * @return string the result of the hash algorithm
     *
     * @throws \InvalidArgumentException the message or the message digest is given as a non-string or an empty string
     */
    public static function rot13Verify($message, $digest)
    {
        //check for the message
        if ((!is_string($message)) || (strlen($message) <= 0)) {
            throw new \InvalidArgumentException('The message to be hashed must be given as a valid non-empty string');
        }

        //check for the digest
        if ((!is_string($digest)) || (strlen($digest) <= 0)) {
            throw new \InvalidArgumentException('The message digest to be checked must be given as a valid non-empty string');
        }

        return (strcmp(str_rot13($digest), $message) == 0);
    }

    /**
     * Generate the message digest for the given message using the default PHP bcrypt implementation.
     *
     * The BCrypt algorithm is thought to provide a secure way of storing passwords.
     * This function should be *NEVER* called directly: use an instance of the Hasher class!
     *
     * @param string $message      the string to be hashed
     *
     * @return string the result of the hash algorithm
     *
     * @throws \InvalidArgumentException the message is given as a non-string or an empty string
     * @throws HashingException          the error occurred while generating the hash for the given message
     */
    public static function bcryptHash($message)
    {
        //check for the message
        if ((!is_string($message)) || (strlen($message) <= 0)) {
            throw new \InvalidArgumentException('The message to be hashed must be given as a valid non-empty string');
        }

        $result = password_hash($message, PASSWORD_BCRYPT);

        if ($result === false) {
            throw new HashingException('An unknown error occurred while generating the hash', 1);
        }

        return $result;
    }

    /**
     * Check if the digest is bcrypt hash of the given message.
     *
     * This function should be called from an Hasher object.
     *
     * @param string $message      the string to be checked against the message digest
     * @param string $digest       the message digest to be checked
     * @return string the result of the hash algorithm
     *
     * @throws \InvalidArgumentException the message or the message digest is given as a non-string or an empty string
     */
    public static function bcryptVerify($message, $digest)
    {
        //check for the message
        if ((!is_string($message)) || (strlen($message) <= 0)) {
            throw new \InvalidArgumentException('The message to be hashed must be given as a valid non-empty string');
        }

        //check for the digest
        if ((!is_string($digest)) || (strlen($digest) <= 0)) {
            throw new \InvalidArgumentException('The message digest to be checked must be given as a valid non-empty string');
        }

        return password_verify($message, $digest);
    }

    /**
     * Generate the message digest for the given message using the pbkdf2 algorithm.
     *
     * The pbkdf2 algorithm is thought to be slow and produce an hash.
     * This function should be *NEVER* called directly: use an instance of the Hasher class!
     *
     * @param string $message      the string to be hashed
     *
     * @return string the result of the hash algorithm
     *
     * @throws \InvalidArgumentException the message is given as a non-string or an empty string
     * @throws HashingException          the error occurred while generating the hash for the given message
     */
    public static function pbkdf2Hash($message)
    {
        $iteration = 16777216;
        $hashingAlgorithm = 'sha512';

        $salt = Base64::encode(openssl_random_pseudo_bytes(64));

        $hash = Base64::encode(self::pbkdf2($message, $salt, 64, $iteration, $hashingAlgorithm));

        return '|pbkdf2%'.$hashingAlgorithm.'%'.$iteration.'%'.$salt.'%'.$hash;
    }

    /**
     * Check if the digest is the pbkdf2 hash of the given message.
     *
     * This function should be called from an Hasher object.
     *
     * @param string $message      the string to be checked against the message digest
     * @param string $digest       the message digest to be checked
     * @return string the result of the hash algorithm
     *
     * @throws \InvalidArgumentException the message or the message digest is given as a non-string or an empty string
     */
    public static function pbkdf2Verify($message, $digest)
    {
        //check for the message
        if ((!is_string($message)) || (strlen($message) <= 0)) {
            throw new \InvalidArgumentException('The message to be hashed must be given as a valid non-empty string');
        }

        //check for the digest
        if ((!is_string($digest)) || (strlen($digest) <= 0)) {
            throw new \InvalidArgumentException('The message digest to be checked must be given as a valid non-empty string');
        }

        $params = explode('%', $digest);

        if (count($params) < 5) {
            return false;
        }

        if (strcmp($params[0], "|pbkdf2") != 0) {
            return false;
        }

        $hashingAlgorithm = $params[1];
        $iteration = intval($params[2]);
        $salt = $params[3];

        $hashRecalc = Base64::encode(self::pbkdf2($message, $salt, 64, $iteration, $hashingAlgorithm));

        return (strcmp($digest, $hashRecalc) == 0);
    }

    /**
     * PBKDF2 key derivation function as defined by RSA's PKCS #5: https://www.ietf.org/rfc/rfc2898.txt.
     *
     * Test vectors can be found here: https://www.ietf.org/rfc/rfc6070.txt
     *
     * This implementation of PBKDF2 was originally created by https://defuse.ca
     * With improvements by http://www.variations-of-shadow.com
     *
     * @param string $password     the password
     * @param string $salt         a salt that is unique to the password
     * @param string $keyLength    the length of the derived key in bytes
     * @param string $count        iteration count. Higher is better, but slower. Recommended: At least 1000
     * @param string $algorithm    the hash algorithm to use. Recommended: SHA256
     *
     * @return string the key derived from the password and salt
     *
     * @throws \InvalidArgumentException invalid arguments have been passed
     * @throws HashingException          the error occurred while generating the requested hashing algorithm
     */
    public static function pbkdf2($password, $salt, $keyLength, $count, $algorithm = self::SHA256)
    {
        if ((!is_integer($count)) || ($count <= 0)) {
            throw new \InvalidArgumentException('The iteration number for the PBKDF2 function must be a positive non-zero integer', 2);
        }

        if ((!is_integer($keyLength)) || ($keyLength <= 0)) {
            throw new \InvalidArgumentException('The resulting key length for the PBKDF2 function must be a positive non-zero integer', 2);
        }

        if ((!is_string($algorithm)) || (strlen($algorithm) <= 0)) {
            throw new \InvalidArgumentException('The hashing algorithm for the PBKDF2 function must be a non-empty string', 2);
        }

        //an algorithm is represented as a string of only lowercase chars
        $algorithm = strtolower($algorithm);

        //the raw output of the max length (beyond the $keyLength algorithm)
        $output = '';

        /*          execute the native openssl_pbkdf2                   */

        //check if the algorithm is valid
        if (!in_array($algorithm, openssl_get_md_methods(true), true)) {
            throw new HashingException('Invalid algorithm: the choosen algorithm is not valid for the PBKDF2 function', 2);
        }

        $output = openssl_pbkdf2($password, $salt, $keyLength, $count, $algorithm);

        return bin2hex(substr($output, 0, $keyLength));
    }
}
