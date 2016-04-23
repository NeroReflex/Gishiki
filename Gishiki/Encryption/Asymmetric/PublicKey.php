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

namespace Gishiki\Encryption\Asymmetric;

/**
 * This class represents a private key for the asymmetric encryption engine.
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
     * @throws \InvalidArgumentException|AsymmetricException the given key isn't valid
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
     * Check if the key has been loaded.
     * 
     * @return bool true if the key has been loaded
     */
    public function isLoaded()
    {
        return is_resource($this->key);
    }
}
