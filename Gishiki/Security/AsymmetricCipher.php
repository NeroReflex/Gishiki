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
     * An helper class for asymmetric cipher algorithms
     *
     * Benato Denis <benato.denis96@gmail.com>
     */
    abstract class AsymmetricCipher
    {
        /**
         * Remove a key pair previously stored by the GenerateNewKey function.
         * 
         * @param  string          $name the name used to store the key
         * @throws CipherException the error occurred
         */
        public static function RemoveKey($name)
        {
            //avoid deleting the application unique key
            if (strtoupper($name) == strtoupper(Environment::GetCurrentEnvironment()->GetConfigurationProperty('MASTER_ASYMMETRIC_KEY_NAME'))) {
                throw new Exception('The application master key cannot be removed', 6);
            } else {
                //delete the key if possible
                $privateKeyPath = \Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('KEYS_DIR').$name.".private.pem";
                $publicKeyPath = \Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('KEYS_DIR').$name.".public.pem";

                if (file_exists($publicKeyPath)) {
                    unlink($publicKeyPath);
                }

                if (file_exists($privateKeyPath)) {
                    unlink($privateKeyPath);
                }
            }
        }

        /**
         * Serialize and store an already loaded private key from a AsymmetricPrivateKeyCipher instance
         * 
         * @param  AsymmetricPrivateKeyCipher $key      the key to be serialized and stored
         * @param  string                     $name     the name that will be used to restore the key
         * @param  string                     $password the password used to encrypt the key file
         * @throws CipherException            the error occurred while storing the private key
         */
        public static function StorePrivateKey(AsymmetricPrivateKeyCipher &$key, $name, $password = "")
        {
            if (gettype($name) != "string") {
                throw new CipherException("The private key cannot be exported, because the key name was given as a ".gettype($name)." value, and not as a string", 11);
            }

            //get the private key serialized and encrypte, if requested
            $keyAsExported = $key->ExportPrivateKey($password);

            //store the key in a file
            file_put_contents(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('KEYS_DIR').$name.".private.pem", $keyAsExported, FILE_APPEND | LOCK_EX);
        }

        /**
         * Serialize and store an already loaded public key from a AsymmetricPrivateKeyCipher instance
         * 
         * @param  AsymmetricPrivateKeyCipher $key  the key to be serialized and stored
         * @param  string                     $name the name that will be used to restore the key
         * @throws CipherException            the error occurred while storing the public key
         */
        public static function StorePublicKey(AsymmetricPrivateKeyCipher &$key, $name)
        {
            if (gettype($name) != "string") {
                throw new CipherException("The public key cannot be exported, because the key name was given as a ".gettype($name)." value, and not as a string", 11);
            }

            //get the private key serialized and encrypte, if requested
            $keyAsExported = $key->ExportPublicKey();

            //store the key in a file
            file_put_contents(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('KEYS_DIR').$name.".public.pem", $keyAsExported, FILE_APPEND | LOCK_EX);
        }

        /**
         * Load a private key previously stored by AsymmetricCipher::StorePrivateKey().
         * 
         * @param  string          $name     the name used to store the key
         * @param  string          $password the password used to decrypt the RSA private key
         * @throws CipherException the error occurred while loading the private key
         */
        public static function LoadPrivateKey($name, $password = "")
        {
            if (file_exists(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('KEYS_DIR').$name.".private.pem")) {
                //retrive the serialized private key
                $privateKeyAsExported = file_get_contents(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('KEYS_DIR').$name.".private.pem");

                //build a new empty public key
                $privateKey = new AsymmetricPrivateKeyCipher();

                //load the retrived public key into the newly created public key
                $privateKey->ImportPrivateKey($privateKeyAsExported, $password);

                //return the loaded key
                return $privateKey;
            } else {
                throw new CipherException("The given key doesn't exists or is invalid", 7);
            }
        }

        /**
         * Load a public key previously stored by AsymmetricCipher::StorePublicKey().
         * 
         * @param  string          $name the name used to store the key
         * @throws CipherException the error occurred while loading the public key
         */
        public static function LoadPublicKey($name)
        {
            if (file_exists(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('KEYS_DIR').$name.".public.pem")) {
                //retrive the serialized public key
                $publicKeyAsExported = file_get_contents(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('KEYS_DIR').$name.".public.pem");

                //build a new empty public key
                $publicKey = new AsymmetricPublicKeyCipher();

                //load the retrived public key into the newly created public key
                $publicKey->ImportPublicKey($publicKeyAsExported);

                //return the loaded key
                return $publicKey;
            } else {
                throw new CipherException("The given key doesn't exists or is invalid", 7);
            }
        }

        /**
         * Generate a new private key and return it encoded as a string, ready to be
         * imported by AsymmetricPrivateKeyCipher::ImportPrivateKey() 
         * 
         * @param  int             $keyLength a valid key length (look at AsymmetricCipherAlgorithms)
         * @throws CipherException the error occurred
         */
        public static function GenerateNewKey($keyLength = AsymmetricCipherAlgorithms::RSA2048)
        {
            //check for the key length validity
            if (($keyLength != AsymmetricCipherAlgorithms::RSA512) && ($keyLength != AsymmetricCipherAlgorithms::RSA1024) && ($keyLength != AsymmetricCipherAlgorithms::RSA2048) && ($keyLength != AsymmetricCipherAlgorithms::RSA4096) && ($keyLength != AsymmetricCipherAlgorithms::RSAEXTREME)) {
                throw new CipherException("The given key length is not valid", 0);
            }

            //if the key length is extremely long, a very long time will be needed to generate the key, and it is necessary ti set no time limits to generate it
            if ($keyLength == AsymmetricCipherAlgorithms::RSAEXTREME) {
                set_time_limit(0);
            }

            //start building the configuration array
            $config = [
                "digest_alg" => "sha512",
                "private_key_bits" => $keyLength,
                "private_key_type" => OPENSSL_KEYTYPE_RSA,
            ];

            //if a custom openssl configuration is found
            if (file_exists(ROOT."Gishiki".DS."openssl.cnf")) {
                //use it
                $config["config"] = ROOT."Gishiki".DS."openssl.cnf";
            }

            //setup an invalid private key
            $privateKey = null;

            //create a new private key
            $privateKey = openssl_pkey_new($config);

            //if the creation process ended successfully
            if (!$privateKey) {
                //print out the error
                throw new CipherException("OpenSSL couldn't generate the new key", 1);
            }

            //extract the private key string-encoded from the generated private key
            $pKeyEncoded = "";
            openssl_pkey_export($privateKey, $pKeyEncoded, null, $config);

            //free the memory space used to hold the newly generated key
            openssl_free_key($privateKey);

            //return the operation result
            return $pKeyEncoded;
        }
    }
}
