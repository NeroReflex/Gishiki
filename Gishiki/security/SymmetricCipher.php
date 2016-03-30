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

/**
 * An helper class for symmetric cipher algorithms
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class SymmetricCipher {
    //crypting algorythms
    const AES128 = 16;
    const AES192 = 24;
    const AES256 = 32;

    /** the algorithm to be used */
    private $algorithm;

    /** the key that will be used */
    private $key; /*note that this can be used even as key length*/

    /** the openssl internal algorithm name */
    private $algorithmName; /*used to speedup the source*/

    /**
     * Create a ciper that uses the given algorithm/key length
     * 
     * @param int $encAlg the key length for AES (default: ::AES128)
     * @throws CipherException the error occurred
     */
    public function __construct($encAlg = SymmetricCipher::AES128) {
        //store the encryption algorith
        $this->algorithm = $encAlg;

        //set the default password
        $this->SetKey(Environment::GetCurrentEnvironment()->GetConfigurationProperty('SECURITY_MASTER_SYMMETRIC_KEY'));

        //get the encryption name
        switch ($this->algorithm) {
            case self::AES128:
                $this->algorithmName = 'aes-128-cbc';
                break;
            case self::AES192:
                $this->algorithmName = 'aes-192-cbc';
                break;
            case self::AES256:
                $this->algorithmName = 'aes-256-cbc';
                break;
            default:
                throw new CipherException("The given algoryth/key length is not valid", 8);
        }
    }

    /**
     * Set a new key for encryptions and decryptions
     * @param string $key the new key
     */
    public function SetKey($key) {
        //generate and store the key
        $this->key = substr($key.sha1($key).md5($key), 0, $this->algorithm);

        /*openssl_pbkdf2($key, mb_substr(strtohex($Salt), 0, 8, 'UTF-8'), $this->algorithm, 1000, $keySize); is not supported on all PHP framework target versions*/
    }

    /**
     * Encrypt a message
     * 
     * @param string $plainData the original string/data to be encrypted
     * @return string the encrypted data
     */
    public function Encrypt($plainData) {
        //generate and store a random IV
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->algorithmName));

        //get the encrypted data
        $encrypted = openssl_encrypt($plainData, $this->algorithmName, $this->key, 0, $iv);

        //encode encrypted data and the random IV with Base64 and add it to the encryption result
        $encrypted = base64_encode($iv).":>=|=<:".base64_encode($encrypted);

        //return the encoded string
        return $encrypted;
    }

    /**
     * Decrypt a message encrypted by the Encrypt function
     * @param string $cipedData the result of the encryption
     * @return string the original data
     * @throws CipherException the error occurred
     */
    public function Decrypt($cipedData) {
        //get IV and encrypted text in an array
        $dataToEncrypt = explode(":>=|=<:", $cipedData, 2);

        //check for the data to be valid
        if (count($dataToEncrypt) != 2) {
            throw new CipherException("The content is not a valid encrypted content", 9);
        }
            
        //rebuild the random IV used to encrypt the string
        $iv = base64_decode($dataToEncrypt[0]);

        //get the data to decrypt
        $encrypted = base64_decode($dataToEncrypt[1]);

        //decrypt and return the result
        return openssl_decrypt($encrypted, $this->algorithmName, $this->key, 0, $iv);
    }
}