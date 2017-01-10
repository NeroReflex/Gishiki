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

namespace Gishiki\Security\Encryption\Asymmetric;

/**
 * This class represents a public key for the asymmetric encryption engine.
 *
 * Note: This class uses OpenSSL for strong encryption
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class PublicKey
{
    /**
     * @var resource the public key ready to be used by OpenSSL
     */
    private $key = null;

    /**
     * Used to create a public key from the given string.
     *
     * If a string containing a serialized public key is
     * not give, the framework default one will be used
     *
     * @param string|null $custom_key the public key serialized as a string
     *
     * @throws \InvalidArgumentException the given key isn't a valid serialized key
     * @throws AsymmetricException       the given key is invalid
     */
    public function __construct($custom_key = null)
    {
        $serialized_key = '';

        if (is_null($custom_key)) {
            //create the default private key
            $default_private_key = new PrivateKey();

            //and retrive the public key from the default private key
            $serialized_key = $default_private_key->retrivePublicKey();
        } elseif (is_string($custom_key)) {
            $serialized_key = $custom_key;
        } else {
            throw new \InvalidArgumentException('The serialized public key must be a string');
        }

        //load the public key
        $this->key = openssl_pkey_get_public($serialized_key);

        //check for errors
        if (!$this->isLoaded()) {
            throw new AsymmetricException('The public key could not be loaded', 2);
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
     * Check if the key has been loaded.
     *
     * @return bool true if the key has been loaded
     */
    public function isLoaded()
    {
        return is_resource($this->key);
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
            throw new AsymmetricException('It is impossible to obtain an unloaded public key', 1);
        }

        //get private key details
        $details = openssl_pkey_get_details($this->key);

        return [
            'key' => &$this->key,
            'byteLength' => $details['bits'] / 8,
        ];
    }
}
