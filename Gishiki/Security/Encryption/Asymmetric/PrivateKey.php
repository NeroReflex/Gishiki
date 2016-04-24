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

namespace Gishiki\Security\Encryption\Asymmetric;

use Gishiki\Core\Environment;

/**
 * This class represents a private key for the asymmetric encryption engine.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class PrivateKey
{
    /**
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
     *
     * @throws \InvalidArgumentException the given key and/or password isn't a valid string
     * @throws AsymmetricException       the given key is invalid
     */
    public function __construct($custom_key = null, $custom_key_password = '')
    {
        if (!is_string($custom_key_password)) {
            throw new \InvalidArgumentException('The private key password cannot be something else than a string');
        }

        //get a string containing a serialized asymmetric key
        if (is_string($custom_key)) {
            $serialized_key = $custom_key;
        } elseif (is_null($custom_key)) {
            $serialized_key = Environment::GetCurrentEnvironment()->GetConfigurationProperty('MASTER_ASYMMETRIC_KEY');
        } else {
            throw new \InvalidArgumentException('The serialized private key must be a string');
        }

        //get the beginning and ending of a private key (to stip out additional shit and check for key validity)
        $is_encrypted = strpos($serialized_key, 'ENCRYPTED') !== false;

        //get the password of the serialized key
        $serialized_key_password = ($is_encrypted) ? $custom_key_password : '';

        //load the private key
        $this->key = openssl_pkey_get_private($serialized_key, $serialized_key_password);

        //check for errors
        if (!$this->isLoaded()) {
            throw new AsymmetricException('The private key could not be loaded', 0);
        }
    }

    /**
     * Free resources used to hold this private key.
     */
    public function __destruct()
    {
        if ($this->isLoaded()) {
            openssl_free_key($this->key);
        }
    }

    /**
     * Export the public key corresponding to this private key.
     * 
     * @return string the public key exported from this private key
     */
    public function exportPublicKey()
    {
        //get details of the current private key
        $privateKeyDetails = openssl_pkey_get_details($this->key);

        //return the public key
        return $privateKeyDetails['key'];
    }

    /**
     * Export this private key in a string format.
     * 
     * The resulting string can be used to construct another PrivateKey instance:
     * 
     * <code>
     * use Gishiki\Security\Encryption\Asymmetric\PrivateKey;
     * 
     * //this is the exported private key
     * $exported_key = "...";
     * 
     * //rebuild the private key
     * $privateKey = new PrivateKey($exported_key);
     * </code>
     * 
     * @param string $key_password the private key password
     *
     * @return string the serialized private key
     *
     * @throws \InvalidArgumentException the given password is not a string
     * @throws AsymmetricException       the given key is invalid
     */
    public function export($key_password = '')
    {
        if (!is_string($key_password)) {
            throw new \InvalidArgumentException('The private key password cannot be something else than a string');
        } elseif (!$this->isLoaded()) {
            throw new AsymmetricException('It is impossible to serialize an unloaded private key', 1);
        }

        $serialized_key = '';

        //start building the configuration array
        $config = [
            'digest_alg' => 'sha512',
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        if (!is_null(Environment::GetCurrentEnvironment())) {
            $config = array_merge(
                    $config,
                    ['config' => (file_exists(Environment::GetCurrentEnvironment()->GetConfigurationProperty('APPLICATION_DIR').'openssl.cnf')) ?
                        Environment::GetCurrentEnvironment()->GetConfigurationProperty('APPLICATION_DIR').'openssl.cnf' : null, ]);
        }

        //serialize the key and encrypt it if requested
        if (strlen($key_password) > 0) {
            openssl_pkey_export($this->key, $serialized_key, $key_password, $config);
        } else {
            openssl_pkey_export($this->key, $serialized_key, null, $config);
        }

        //return the serialized key
        return $serialized_key;
    }

    /**
     * Check if the key has been loaded.
     * 
     * @return bool true if the key has been loaded
     */
    public function isLoaded()
    {
        return is_resource($this->key);
    }

    /**
     * Proxy call to the export() function.
     * 
     * @return string the serialized key
     */
    public function __toString()
    {
        return $this->export();
    }

    /**
     * Export a reference to the native private key and its length in bits.
     * 
     * @return array the array that contains the key and its legth (in bytes)
     *
     * @throws AsymmetricException the key cannot be exported
     */
    public function __invoke()
    {
        if (!$this->isLoaded()) {
            throw new AsymmetricException('It is impossible to obtain an unloaded private key', 1);
        }

        //get private key details
        $details = openssl_pkey_get_details($this->key);

        return [
            'key' => &$this->key,
            'byteLength' => $details['bits'] / 8,
        ];
    }
}
