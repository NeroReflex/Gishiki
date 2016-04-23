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

namespace Encryption\Asymmetric;

use Gishiki\Core\Environment;

/**
 * This class represents a private key for the asymmetric encryption engine
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class PrivateKey
{
    /*
     * This is a list of OpenSSL key delimiters
     */
    const BEGIN_PRIVATE_KEY     = "-----BEGIN PRIVATE KEY-----";
    const END_PRIVATE_KEY       = "-----END PRIVATE KEY-----";
    const BEGIN_ENCRYPTED_KEY   = "-----BEGIN ENCRYPTED PRIVATE KEY-----";
    const END_ENCRYPTED_KEY     = "-----END ENCRYPTED PRIVATE KEY-----";
    
    /**
     *
     * @var resource the private key ready to be used by OpenSSL
     */
    private $key = null;
    
    /**
     * Used to create a private key from the given string.
     *
     * If a string containing a serialized private key is
     * not give, the framework default one will be used
     *
     * @param string|null $custom_key          the private key serialized as a string
     * @param string      $custom_key_password the password to decrypt the serialized private key (if necessary)
     * @throws \InvalidArgumentException       the given key and/or password isn't valid
     */
    public function __construct($custom_key = null, $custom_key_password = "")
    {
        if (!is_string($custom_key_password)) {
            throw new \InvalidArgumentException("The private key password cannot be something else than a string");
        }
        
        //get a string containing a serialized asymmetric key
        $serialized_key = (is_string($custom_key))?
                $custom_key
                : Environment::GetCurrentEnvironment()->GetConfigurationProperty('MASTER_ASYMMETRIC_KEY');

        //get the beginning and ending of a private key (to stip out additional shit and check for key validity)
        $begin_private_unencrypted = strpos($serialized_key, self::BEGIN_PRIVATE_KEY);
        $end_private_unencrypted = strpos($serialized_key, self::END_PRIVATE_KEY);
        $begin_private_encrypted = strpos($serialized_key, self::BEGIN_ENCRYPTED_KEY);
        $end_private_encrypted = strpos($serialized_key, self::END_ENCRYPTED_KEY);

        //get the password of the serialized key
        $serialized_key_password = "";
        $valid_key = "";
        if (($begin_private_unencrypted !== false) && ($end_private_unencrypted !== false)) {
            //extract the serialized key
            $valid_key = substr($serialized_key, $begin_private_unencrypted, $end_private_unencrypted + strlen(self::END_PRIVATE_KEY));
            
            //a password is not needed
            $serialized_key_password = "";
        } elseif (($begin_private_encrypted !== false) && ($end_private_encrypted !== false)) {
            //extract the serialized key
            $valid_key = substr($serialized_key, $begin_private_encrypted, $end_private_encrypted + strlen(self::END_ENCRYPTED_KEY));
            
            //a password is not needed
            $serialized_key_password = $custom_key_password;
            if (strlen($valid_key) <= 0) {
                throw new \InvalidArgumentException("The given password cannot be used to decrypt the given key");
            }
        } else {
            //bad key, sorry
            throw new \InvalidArgumentException("The given string doesn't represents a valid key");
        }
        
        //load the private key
        $this->key = openssl_pkey_get_private($valid_key, $serialized_key_password);
    }
    
    /**
     * Export this private key in a string format.
     * 
     * The resulting string can be used to construct another PrivateKey instance:
     * 
     * <code>
     * use Gishiki\Encryption\Asymmetric\PrivateKey;
     * 
     * //this is the exported private key
     * $exported_key = "...";
     * 
     * //rebuild the private key
     * $privateKey = new PrivateKey($exported_key);
     * </code>
     * 
     * @param string $key_password       the private key password
     * @return string                    the serialized private key
     * @throws \InvalidArgumentException the given key is not valid
     */
    public function Export($key_password = "")
    {
        if (!is_string($key_password)) {
            throw new \InvalidArgumentException("The private key password cannot be something else than a string");
        }
        
        $serialized_key = "";
        
        //start building the configuration array
        $config = [
            "digest_alg" => "sha512",
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
            "config" => (file_exists(APPLICATION_DIR."openssl.cnf"))?
                APPLICATION_DIR."openssl.cnf" : null
        ];
        
        //serialize the key and encrypt it if requested
        if (strlen($key_password) > 0) {
            openssl_pkey_export($this->key, $serialized_key, $key_password, $config);
        } else {
            openssl_pkey_export($this->key, $serialized_key, null, $config);
        }
        
        //return the serialized key
        return $serialized_key;
    }
}
