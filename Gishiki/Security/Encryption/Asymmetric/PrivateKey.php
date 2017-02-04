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

use Gishiki\Core\Environment;

/**
 * This class represents a private key for the asymmetric encryption engine.
 *
 * Note: This class uses OpenSSL for strong encryption
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class PrivateKey
{
    //RSA keys length
    const RSA512 = 512;
    const RSA1024 = 1024;
    const RSA2048 = 2048;
    const RSA4096 = 4096;
    const RSAEXTREME = 16384;

    /**
     * Create a random private key of the given length (in bits).
     *
     * You can use predefined constants to have valid keylength values.
     *
     * The higher the key length, the higher the security the higher the required time to generate the key.
     *
     * Usage example:
     *
     * <code>
     * //give a name to the file containing the generation result
     * $filename = APPLICATION_DIR."newkey.private.pem";
     *
     * //generate the new key
     * $serailized_key = PrivateKey::Generate(PrivateKey::RSA4096);
     *
     * //export to file the serialized key
     * file_put_contents($filename, $serailized_key);
     * //NOTE: this example is really BAD because the file is NOT encrypted
     *
     * //yes, you can load private keys directly from file
     * $private_key = new PrivateKey("file://".$filename);
     * </code>
     *
     * @param int $keyLength the length (in bits) of the private key to e generated
     *
     * @return string the serialized private key
     *
     * @throws \InvalidArgumentException the given key length is not an integer power of two
     * @throws AsymmetricException       the error occurred while generating and exporting the new private key
     */
    public static function Generate($keyLength = self::RSA4096)
    {
        if (!is_integer($keyLength)) {
            throw new \InvalidArgumentException('The key length must be given as an integer number which is a power of two');
        } elseif ((($keyLength & ($keyLength - 1)) != 0) || ($keyLength == 0)) {
            throw new \InvalidArgumentException('The key length must be a power of two');
        }

        //if the key length is extremely long, a very long time will be needed to generate the key, and it is necessary ti set no time limits to generate it
        //if ($keyLength == self::RSAEXTREME) {
            set_time_limit(0);
        //}

        //build the configuration array
        $config = [
            'digest_alg' => 'sha512',
            'private_key_bits' => $keyLength,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];
        //use the application openssl configuration
        if (!is_null(Environment::GetCurrentEnvironment())) {
            $config = array_merge(
                    $config,
                    ['config' => (file_exists(Environment::GetCurrentEnvironment()->GetConfigurationProperty('APPLICATION_DIR').'openssl.cnf')) ?
                        Environment::GetCurrentEnvironment()->GetConfigurationProperty('APPLICATION_DIR').'openssl.cnf' : null, ]);
        }

        //create a new private key
        $privateKey = openssl_pkey_new($config);

        //check the result
        if (!is_resource($privateKey)) {
            throw new AsymmetricException('The key generation is not possible due to an unknown '.openssl_error_string(), 8);
        }

        //extract the private key string-encoded from the generated private key
        $pKeyEncoded = '';
        openssl_pkey_export($privateKey, $pKeyEncoded, null, $config);

        //free the memory space used to hold the newly generated key
        openssl_free_key($privateKey);

        //check for the result
        if (!is_string($pKeyEncoded)) {
            throw new AsymmetricException("The key generation was completed, but the result couldn't be exported", 9);
        }

        //return the operation result
        return $pKeyEncoded;
    }

    /**************************************************************************
     *                                                                        *
     *                          NON-static properties                         *
     *                                                                        *
     **************************************************************************/

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
     * @param string|null $customKey         the private key serialized as a string
     * @param string      $customKeyPassword the password to decrypt the serialized private key (if necessary)
     *
     * @throws \InvalidArgumentException the given key and/or password isn't a valid string
     * @throws AsymmetricException       the given key is invalid
     */
    public function __construct($customKey = null, $customKeyPassword = '')
    {
        if (!is_string($customKeyPassword)) {
            throw new \InvalidArgumentException('The private key password cannot be something else than a string');
        }

        if ((!is_string($customKey)) && (!is_null($customKey))) {
            throw new \InvalidArgumentException('The serialized private key must be a string');
        }

        //get a string containing a serialized asymmetric key
        $serialized_key = (is_string($customKey)) ?
            $serialized_key = $customKey :
            Environment::GetCurrentEnvironment()->GetConfigurationProperty('MASTER_ASYMMETRIC_KEY');

        //get the beginning and ending of a private key (to stip out additional shit and check for key validity)
        $is_encrypted = strpos($serialized_key, 'ENCRYPTED') !== false;

        //get the password of the serialized key
        $serializedKeyPassword = ($is_encrypted) ? $customKeyPassword : '';

        //load the private key
        $this->key = openssl_pkey_get_private($serialized_key, $serializedKeyPassword);

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
     * @param string $keyPassword the private key password
     *
     * @return string the serialized private key
     *
     * @throws \InvalidArgumentException the given password is not a string
     * @throws AsymmetricException       the given key is invalid
     */
    public function export($keyPassword = '')
    {
        if (!is_string($keyPassword)) {
            throw new \InvalidArgumentException('The private key password cannot be something else than a string');
        } elseif (!$this->isLoaded()) {
            throw new AsymmetricException('It is impossible to serialize an unloaded private key: '.openssl_error_string(), 1);
        }

        $serialized_key = '';

        //build the configuration array
        $config = [
            'digest_alg' => 'sha512',
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];
        //use the application openssl configuration
        if (!is_null(Environment::GetCurrentEnvironment())) {
            $config = array_merge(
                    $config,
                    ['config' => (file_exists(Environment::GetCurrentEnvironment()->GetConfigurationProperty('APPLICATION_DIR').'openssl.cnf')) ?
                        Environment::GetCurrentEnvironment()->GetConfigurationProperty('APPLICATION_DIR').'openssl.cnf' : null, ]);
        }

        //serialize the key and encrypt it if requested
        if (strlen($keyPassword) > 0) {
            openssl_pkey_export($this->key, $serialized_key, $keyPassword, $config);
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
