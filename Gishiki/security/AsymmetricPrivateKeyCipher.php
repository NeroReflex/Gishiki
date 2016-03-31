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
     * The asymmetric cipher algorithm implementation on a private-key point of view
     *
     * Benato Denis <benato.denis96@gmail.com>
     */
    class AsymmetricPrivateKeyCipher {

        //the private key used for encryption and decyption
        private $privateKey;

        /**
         * Setup an empty private key
         */
        public function __construct() {
            //initialize an empty private key
            $this->privateKey = NULL;
        }

        /**
         * Check if a valid RSA private key has been loaded
         * 
         * @return boolean TRUE if the private key has been loaded, FASE otherwise
         */
        public function IsLoaded() {
            return ((gettype($this->privateKey) != "NULL") && (gettype($this->privateKey) == "resource"));
        }

        /**
         * Import the private key from a string
         * 
         * @param string $keyAsString the RSA unencrypted or encrypted key
         * @param string $password the password used to decrypt the RSA private key
         * @throws CipherException the exception occurred while importing the key
         */
        public function ImportPrivateKey($keyAsString, $password = "") {
            $isEncrypted = (gettype($password) == "string") && (strlen($password) > 0);
            
            if (!$isEncrypted) {
                //check if the given string is a loadable key
                if ((strpos($keyAsString, "-----BEGIN PRIVATE KEY-----") !== FALSE) && (strpos($keyAsString, "-----END PRIVATE KEY-----") !== FALSE)) {
                    //load the private key
                    $this->privateKey = openssl_pkey_get_private($keyAsString);
                } else {
                    throw new CipherException("The RSA exported private key cannot be loaded, because the given data is not a private key", 6);
                }
            } else {
                //check if the given string is a loadable key
                if ((strpos($keyAsString, "-----BEGIN ENCRYPTED PRIVATE KEY-----") !== FALSE) && (strpos($keyAsString, "-----END ENCRYPTED PRIVATE KEY-----") !== FALSE)) {
                    if (strlen($password) <= 0) {
                        throw new CipherException("The given password is not valid, and cannot be used to decrypt the encrypted key", 8);
                    }

                    //load the encrypted private key
                    $this->privateKey = openssl_pkey_get_private($keyAsString, $password);
                } else {
                    throw new CipherException("The RSA exported private key cannot be loaded, because the given data is not a private encrypted key", 7);
                }
            }
        }

        /**
         * Export the private key in a way it can be imported by AsymmetricPrivateKeyCipher::ImportPrivateKey()
         * or used by OpenSSL
         * 
         * @param type $password the passphrase used to encrypt the key (increase security)
         * @return string the private key in a serialized format
         * @throws CipherException the exception occurred while exporting the key
         */
        public function ExportPrivateKey($password = "") {
            if (gettype($password) != "string") {
                throw new CipherException("The private key cannot be exported, because the password was given as a ".gettype($password)." value, and not as a string", 11);
            }

            //start building the configuration array
            $config = [
                "digest_alg" => "sha512",
                /*"private_key_bits" => $keyLength,*/
                "private_key_type" => OPENSSL_KEYTYPE_RSA,
            ];

            //if a custom openssl configuration is found
            if (file_exists(ROOT."Gishiki".DS."openssl.cnf")) {
                //use it
                $config["config"] = ROOT."Gishiki".DS."openssl.cnf";
            }

            if (gettype($this->privateKey) != "NULL") {
                $serializedKey = "";

                //serialize the key and encrypt it if requested
                if (strlen($password) > 0) {
                    openssl_pkey_export($this->privateKey, $serializedKey, $password, $config);
                } else {
                    openssl_pkey_export($this->privateKey, $serializedKey, NULL, $config);
                }

                //return the serialized key
                return $serializedKey;
            } else {
                throw new CipherException("The private key cannot be exported, because the private key wasn't loaded", 10);
            }
        }

        /**
         * Export the public key as a string that can be imported by 
         * AsymmetricPublicKeyCipher::ImportPublicKey()
         * 
         * @return string the public key exported as a string
         * @throws CipherException the exception occurred while serializing the key
         */
        public function ExportPublicKey() {
            if ($this->IsLoaded()) {
                //get details of the current private key
                $privateKeyDetails = openssl_pkey_get_details($this->privateKey);

                //return the public key
                return $privateKeyDetails["key"];
            } else {
                throw new CipherException("The public key cannot be exported, because the private key wasn't loaded", 9);
            }
        }

        /**
         * Generate the digital signature of the given *plain* message
         * 
         * @param string $message the message to sign
         * @return string the signature in a binary-safe format
         * @throws CipherException the error occurred
         */
        public function GenerateDigitalSignature($message) {
            if ($this->IsLoaded()) {
                //the digital signature
                $digitalSignature = NULL;

                if (!openssl_sign($message, $digitalSignature, $this->privateKey, "sha256WithRSAEncryption")) {
                    throw new CipherException("OpenSSL was unable to create the digital signature", 1);
                }

                //return the signature in a binary-safe format
                return base64_encode($digitalSignature);
            } else {
                throw new CipherException("The digital signature cannot be created, because the private key wasn't loaded", 0);
            }
        }

        /**
         * Encrypt a message using the loaded private key
         * 
         * @param string $message the message to encrypt
         * @return string the encrypted message in a binary safe format 
         * @throws CipherException the error occurred
         */
        public function Encrypt($message) {
            if ($this->IsLoaded()) {
                //the encrypted message
                $encrypted = NULL;

                //encrypt the message and check for failure
                if (!openssl_private_encrypt($message, $encrypted, $this->privateKey, OPENSSL_PKCS1_PADDING))
                {
                    throw new CipherException("OpenSSL couldn't encrypt the given message", 3);
                }

                //return the ecnrypted message base64-encoded
                return base64_encode($encrypted);
            } else {
                throw new CipherException("The plain message cannot be encrypted, because the private key wasn't loaded", 2);
            }
        }

        /**
         * Decrypt a message encrypted with the public key using the loaded private key
         * 
         * @param string $encMessage the encrypted message (using the public key)
         * @return string the plain message before the encrypted
         * @throws CipherException the error occurred
         */
        public function Decrypt($encMessage) {
            if ($this->IsLoaded()) {
                //the encrypted message
                $plainMessage = NULL;

                //encrypt the message and check for failure
                if (!openssl_private_decrypt(base64_decode($encMessage), $plainMessage, $this->privateKey, OPENSSL_PKCS1_PADDING))
                {
                    throw new CipherException("OpenSSL couldn't decrypt the given message", 5);
                }

                //return the ecnrypted message
                return $plainMessage;
            } else {
                throw new CipherException("The encrypted message cannot be decrypted, because the private key wasn't loaded", 4);
            }
        }

        /**
         * Free the memory used by OpenSSL to hold the private key
         */
        public function __destruct() {
            //free the private key (if any)
            if ($this->IsLoaded()) {
                openssl_free_key($this->privateKey);
            }
        }
    }
}