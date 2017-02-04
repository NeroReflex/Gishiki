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

namespace Gishiki\Security\Encryption\Symmetric;

/**
 * This class represents an algorithm collection for the asymmetric
 * encryption engine.
 *
 * Note: This class uses OpenSSL for strong encryption
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class Cryptography
{
    /******************************************************
     *              List of known algorithms              *
     ******************************************************/
    const AES_CBC_128 = 'aes-128-cbc';
    const AES_CBC_192 = 'aes-192-cbc';
    const AES_CBC_256 = 'aes-256-cbc';

    /**
     * Encrypt the given content using the given secure key (that should be
     * prepared using pbkdf2).
     *
     * The resulting IV (automatically generated if null is passed) is
     * base64 encoded.
     *
     * The resulting encrypted content is base64 encoded.
     *
     * Example usage:
     * <code>
     * //prepare the secret key for the symmetric cipher
     * $key = new SecretKey( ... );
     * //Note: the key is NOT the password
     *
     * //encrypt the content
     * $enc_result = Cryptography::encrypt($key, "this is the message to be encrypted");
     *
     * //transmit the IV_base64 and Encryption to decrypt the content
     * //if you used a cistom IV you don't need to pass the IV
     * </code>
     *
     * @param SecretKey   $key        the key to be used to encrypt the given message
     * @param string      $message    the message to be encrypted
     * @param string|null $initVector the base64 representation of the IV to be used (pick a random one if null)
     * @param string      $algorithm  the name of the algorithm to be used
     *
     * @return array the base64 of the raw encryption result and the used IV
     *
     * @throws \InvalidArgumentException one or more arguments are invalid
     * @throws SymmetricException        the error occurred while encrypting the content
     */
    public static function encrypt(SecretKey &$key, $message, $initVector = null, $algorithm = self::AES_CBC_128)
    {
        //check the plain message type
        if ((!is_string($message)) || (strlen($message) <= 0)) {
            throw new \InvalidArgumentException('The plain message to be encrypted must be given as a non-empty string');
        }

        //get the managed kersion of the key
        $managedKey = $key();

        //check for the key length
        if (($algorithm == self::AES_CBC_128) && ($managedKey['byteLength'] != 16)) {
            throw new SymmetricException('You must be using a key with the correct length for the choosen algorithm ('.$managedKey['byteLength'].'/16)', 0);
        } elseif (($algorithm == self::AES_CBC_192) && ($managedKey['byteLength'] != 24)) {
            throw new SymmetricException('You must be using a key with the correct length for the choosen algorithm ('.$managedKey['byteLength'].'/24)', 1);
        } elseif (($algorithm == self::AES_CBC_256) && ($managedKey['byteLength'] != 32)) {
            throw new SymmetricException('You must be using a key with the correct length for the choosen algorithm ('.$managedKey['byteLength'].'/32)', 2);
        }

        //generate and store a random IV
        $decodedIv = (is_null($initVector)) ? openssl_random_pseudo_bytes(openssl_cipher_iv_length($algorithm)) : base64_decode($initVector);

        //get the encrypted data
        $encrypted = openssl_encrypt($message, $algorithm, $managedKey['key'], OPENSSL_RAW_DATA, $decodedIv);

        //return the encryption result and the randomly generated IV
        return [
            'Encryption' => base64_encode($encrypted),
            'IV_base64' => base64_encode($decodedIv),
            'IV_hex' => bin2hex($decodedIv),
        ];
    }

    /**
     * Decrypt the given encrypted content using the given secure key
     * (that should be prepared using pbkdf2 Ahead Of Time).
     *
     * Example Usage:
     * <code>
     * //this is the key encoded in hex format required to create the SecureKey
     * $key_hex_encoded = " ... "; //make sure this is the key used when encrypting
     * //Note: the key is NOT the password
     *
     * //build the key
     * $key = new SecretKey($key_hex_encoded);
     *
     * //this is the IV encoded in base64: it is returned by the encrypt() function
     * $initVector_base_encoded = " ... ";
     *
     * //$message will hold the original plaintext message
     * $message = Cryptography::decrypt($key, $encryptedMessage, $initVector_base_encoded);
     * </code>
     *
     * @param SecretKey $key              the key that has been used to encrypt the message
     * @param string    $encryptedMessage the encryption result (must be base64-encoded)
     * @param string    $initVector       the iv represented in base64
     * @param string    $algorithm        the name of the algorithm to be used
     *
     * @return string the decrypted content
     *
     * @throws \InvalidArgumentException one or more arguments are invalid
     * @throws SymmetricException        the error occurred while decrypting the content
     */
    public static function decrypt(SecretKey &$key, $encryptedMessage, $initVector, $algorithm = self::AES_CBC_128)
    {
        //check the plain message type
        if ((!is_string($encryptedMessage)) || (strlen($encryptedMessage) <= 0)) {
            throw new \InvalidArgumentException('The encrypted message to be decrypted must be given as a non-empty string');
        }

        //get the managed version of the key
        $managedKey = $key();

        //check for the key length
        if (($algorithm == self::AES_CBC_128) && ($managedKey['byteLength'] != 16)) {
            throw new SymmetricException('You must be using a key with the correct length for the choosen algorithm ('.$managedKey['byteLength'].'/16)', 3);
        } elseif (($algorithm == self::AES_CBC_192) && ($managedKey['byteLength'] != 24)) {
            throw new SymmetricException('You must be using a key with the correct length for the choosen algorithm ('.$managedKey['byteLength'].'/24)', 4);
        } elseif (($algorithm == self::AES_CBC_256) && ($managedKey['byteLength'] != 32)) {
            throw new SymmetricException('You must be using a key with the correct length for the choosen algorithm ('.$managedKey['byteLength'].'/32)', 5);
        }

        //get the IV
        $decodedIv = base64_decode($initVector);

        //get the data to decrypt
        $encrypted = base64_decode($encryptedMessage);

        //decrypt and return the result
        return openssl_decrypt($encrypted, $algorithm, $managedKey['key'], OPENSSL_RAW_DATA, $decodedIv);
    }
}
