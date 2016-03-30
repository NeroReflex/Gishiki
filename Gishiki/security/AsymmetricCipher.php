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
 * An helper class for asymmetric cipher algorithms
 *
 * Benato Denis <benato.denis96@gmail.com>
 */
class AsymmetricCipher {
    //rsa keys length
    const RSA512        = 512;
    const RSA1024       = 1024;
    const RSA2048       = 2048;
    const RSA4096       = 4096;
    const RSAEXTREME    = 16384;
    
    //the private key
    private $privateKey;
        
    //the public key
    private $publicKey;
    
    /**
     * Create an asymmetric cipher
     */
    public function __construct() {
        //initialize an empty private key
        $this->privateKey = NULL;
        
        //initialize an empty public key
        $this->publicKey = NULL;
    }
    
    /**
     * Remove a key pair previously stored by the GenerateNewKey function.
     * 
     * @param string $name the name used to store the key
     * @throws CipherException the error occurred
     * @throws CipherException the error occurred
     */
    static function RemoveKey($name) {
        //avoid deleting the application unique key
        if (strtoupper($name) == strtoupper(Environment::GetCurrentEnvironment()->GetConfigurationProperty('MASTER_ASYMMETRIC_KEY_NAME'))) {
            throw new Exception('The application unique key cannot be removed', 6);
        } else {
            //delete the key if possible
            $privateKeyPath = KEYS_DIR.$name.".private.pem";
            $publicKeyPath = KEYS_DIR.$name.".public.pem";
            
            if (file_exists($publicKeyPath)) {
                unlink($publicKeyPath);
            }
            
            if (file_exists($privateKeyPath)) {
                unlink($privateKeyPath);
            }
        }
    }
    
    /**
     * Load a key pair previously stored by the GenerateNewKey function.
     * 
     * @param string $name the name used to store the key
     * @throws CipherException the error occurred
     */
    public function LoadKey($name) {
        if ((file_exists(KEYS_DIR.$name.".private.pem")) && (file_exists(KEYS_DIR.$name.".public.pem"))) {
            //load the private key
            $this->privateKey = openssl_pkey_get_private(file_get_contents(KEYS_DIR.$name.".private.pem"), Environment::GetCurrentEnvironment()->GetConfigurationProperty('SECURITY_MASTER_SYMMETRIC_KEY'));

            //load the public key
            $this->publicKey = openssl_pkey_get_public(file_get_contents(KEYS_DIR.$name.".public.pem"));
        } else {
            throw new CipherException("The given key doesn't exists or is not completed", 7);
        }
    }
    
    /**
     * Generate a new key pair. This key is stored in-memory (object) and in two files. 
     * The new key is than loaded and ready to be used
     * 
     * @param string $name Store The name of the key
     * @throws CipherException the error occurred
     */
    public function GenerateNewKey($name, $keyLength = self::RSA2048) {
        //check for the key length validity
        if (($keyLength != self::RSA512) && ($keyLength != self::RSA1024) && ($keyLength != self::RSA2048) && ($keyLength != self::RSA4096) && ($keyLength != self::RSAEXTREME)) {
            throw new CipherException("The given key length is not valid", 0);
        
        }
            
        //if the key length is extremely long, a very long time will be needed to generate the key, and it is necessary ti set no time limits to generate it
        if ($keyLength == self::RSAEXTREME) {
            set_time_limit(0);
        }
        
        //start building the configuration array
        $config = array(
            "digest_alg" => "sha512",
            "private_key_bits" => $keyLength,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );
        
        //if a custom openssl configuration is found
        if (file_exists(ROOT."Gishiki".DS."openssl.cnf"))
            //use it
            $config["config"] = ROOT."Gishiki".DS."openssl.cnf";
        
        //create a new pair of keys: public and private
        $this->privateKey = openssl_pkey_new($config);
        
        //if the creation process ended successfully
        if ($this->privateKey) {
            //store the public key in the given file name with .public.pem extension
            $this->publicKey = openssl_pkey_get_details($this->privateKey);
            $this->publicKey = $this->publicKey["key"];
            @file_put_contents(KEYS_DIR.$name.".public.pem", $this->publicKey);

            //extract the private key string-encoded from the generated key pair
            $pKeyEncoded = "";
            openssl_pkey_export($this->privateKey, $pKeyEncoded, Environment::GetCurrentEnvironment()->GetConfigurationProperty('SECURITY_MASTER_SYMMETRIC_KEY'), $config);

            //store the private key in the given file name with .private.pem extension
            @file_put_contents(KEYS_DIR.$name.".private.pem", $pKeyEncoded);
        } else {
            //print out the error
            throw new CipherException("OpenSSL couldn't generate the new key", 1);
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
        //the digital signature
        $digitalSignature = NULL;
        
        if (!openssl_sign($message, $digitalSignature, $this->privateKey, "sha256WithRSAEncryption")) {
            throw new CipherException("OpenSSL was unable to create the digital signature", 2);
        }
        
        //return the signature in a binary-safe format
        return base64_encode($digitalSignature);
    }
    
    /**
     * Check if a message was altered using its digital signature (which is provate-key dependant)
     * 
     * @param string $message the messae to be checked
     * @param string $signature the digital signature of the message
     * @return boolean verify if the message is correct using its digital signature
     * @throws CipherException the error occurred
     */
    public function VerifyDigitalSignature($message, $signature) {
        $bynary_non_safe_signature = base64_decode($signature);
        
        $verificationResult = openssl_verify($message, $bynary_non_safe_signature, $this->publicKey, OPENSSL_ALGO_SHA256);
        
        //select what must be returned
        switch ($verificationResult) {
            case 0:
                return FALSE;
            case 1:
                return TRUE;
            default:
                throw new CipherException("An error occurred while verifying the digital signature", 3);
        }
    }
    
    /**
     * Encrypt a message using the loaded private key
     * 
     * @param string $message the message to encrypt
     * @return string the binary-encoded NON base64 encrypted message
     * @throws CipherException the error occurred
     */
    public function Encrypt($message) {
        //the encrypted message
        $encrypted = NULL;
        
        //encrypt the message and check for failure
        if (!openssl_private_encrypt($message, $encrypted, $this->privateKey, OPENSSL_PKCS1_PADDING))
        {
            throw new CipherException("OpenSSL couldn't encrypt the given message", 4);
        }
        
        //return the ecnrypted message base64-encoded
        return base64_encode($encrypted);
    }
    
    /**
     * Decrypt a message using the loaded public key
     * 
     * @param string $encMessage the encrypted message (private key)
     * @return string the plain message before the encrypted
     * @throws CipherException the error occurred
     */
    public function Decrypt($encMessage) {
        //the encrypted message
        $plainMessage = NULL;
        
        //encrypt the message and check for failure
        if (!openssl_public_decrypt(base64_decode($encMessage), $plainMessage, $this->publicKey, OPENSSL_PKCS1_PADDING))
        {
            throw new CipherException("OpenSSL couldn't decrypt the given message", 5);
        }
        
        //return the ecnrypted message
        return $plainMessage;
    }
    
    /**
     * Free the memory used to hold the key pair
     */
    public function __destruct() {
        //free the private key (if any)
        if (($this->privateKey != NULL) && (gettype($this->privateKey) == "resource"))
            openssl_free_key($this->privateKey);
        
        //free the public key (if any)
        if (($this->publicKey != NULL) && (gettype($this->publicKey) == "resource"))
            openssl_free_key($this->publicKey);
    }
}