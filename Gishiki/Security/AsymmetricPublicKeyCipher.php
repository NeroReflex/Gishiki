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

namespace Gishiki\Security {
    
    /**
     * The asymmetric cipher algorithm implementation on a public-key point of view
     *
     * Benato Denis <benato.denis96@gmail.com>
     */
    class AsymmetricPublicKeyCipher
    {

        //the public key
        private $publicKey;

        /**
         * Create an asymmetric cipher
         */
        public function __construct()
        {
            //initialize an empty public key
            $this->publicKey = null;
        }

        /**
         * Check if a valid RSA public key has been loaded
         * 
         * @return bool TRUE if the public key has been loaded, FASE otherwise
         */
        public function IsLoaded()
        {
            return ((gettype($this->publicKey) != "NULL") && (gettype($this->publicKey) == "resource"));
        }

        /**
         * Load the public key from a string
         * 
         * @param  string          $keyAsString the key encoded as a string
         * @throws CipherException the exception occurred while importing the key
         */
        public function ImportPublicKey($keyAsString)
        {
            //check if the given string is a loadable key
            if ((strpos($keyAsString, "-----BEGIN PUBLIC KEY-----") !== false) && (strpos($keyAsString, "-----END PUBLIC KEY-----") !== false)) {
                //if the key seems to be valid load it
                $this->publicKey = openssl_pkey_get_public($keyAsString);
            } else {
                throw new CipherException("The RSA exported public key cannot be loaded, because the given data is not a public key", 6);
            }
        }

        /**
         * Check if a message was altered using its digital signature (which is provate-key dependant)
         * 
         * @param  string          $message   the messae to be checked
         * @param  string          $signature the digital signature of the message
         * @return bool            verify if the message is correct using its digital signature
         * @throws CipherException the error occurred
         */
        public function VerifyDigitalSignature($message, $signature)
        {
            if (gettype($this->publicKey) != "NULL") {
                $bynary_non_safe_signature = base64_decode($signature);

                $verificationResult = openssl_verify($message, $bynary_non_safe_signature, $this->publicKey, OPENSSL_ALGO_SHA256);

                //select what must be returned
                switch ($verificationResult) {
                    case 0:
                        return false;
                    case 1:
                        return true;
                    default:
                        throw new CipherException("An unknown error has occurred while verifying the digital signature", 1);
                }
            } else {
                throw new CipherException("The digital signature cannot be verified, because the public key wasn't loaded", 0);
            }
        }

        /**
         * Encrypt a message using the loaded public key
         * 
         * @param  string          $message the message to encrypt
         * @return string          the encrypted message in a binary safe format 
         * @throws CipherException the error occurred
         */
        public function Encrypt($message)
        {
            if (gettype($this->publicKey) != "NULL") {
                //the encrypted message
                $encrypted = null;

                //encrypt the message and check for failure
                if (!openssl_public_encrypt($message, $encrypted, $this->publicKey, OPENSSL_PKCS1_PADDING)) {
                    throw new CipherException("OpenSSL couldn't encrypt the given message", 3);
                }

                //return the ecnrypted message base64-encoded
                return base64_encode($encrypted);
            } else {
                throw new CipherException("The plain message cannot be encrypted, because the public key wasn't loaded", 2);
            }
        }

        /**
         * Decrypt a message encrypted with the private key using the loaded public key
         * 
         * @param  string          $encMessage the encrypted message (private key)
         * @return string          the plain message before the encrypted
         * @throws CipherException the error occurred
         */
        public function Decrypt($encMessage)
        {
            if (gettype($this->publicKey) != "NULL") {
                //the encrypted message
                $plainMessage = null;

                //encrypt the message and check for failure
                if (!openssl_public_decrypt(base64_decode($encMessage), $plainMessage, $this->publicKey, OPENSSL_PKCS1_PADDING)) {
                    throw new CipherException("OpenSSL couldn't decrypt the given message", 5);
                }

                //return the ecnrypted message
                return $plainMessage;
            } else {
                throw new CipherException("The encrypted message cannot be decrypted, because the public key wasn't loaded", 4);
            }
        }

        /**
         * Free the memory used by OpenSSL to hold the public key
         */
        public function __destruct()
        {
            //free the public key (if any)
            if (gettype($this->publicKey) == "resource") {
                openssl_free_key($this->publicKey);
            }
        }
    }
}
