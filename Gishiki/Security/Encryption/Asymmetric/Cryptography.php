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

/**
 * This class represents an algorithm collection for the asymmetric
 * encryption engine.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class Cryptography
{
    /**
     * Encrypt the given message using the given private key.
     * 
     * You will need the public key to decrypt the encrypted content.
     * 
     * You can decrypt an encrypted content with the decrypt() function.
     * 
     * An example of usage can be:
     * 
     * <code>
     * $default_privkey = new PrivateKey();
     * $encrypted_message = Cryptography::encrypt($default_privkey, "this is my important message from my beloved half");
     * 
     * echo "Take good care of this and give it to my GF: " . $encrypted_message;
     * </code>
     * 
     * @param PrivateKey $key     the private key to be used to encrypt the plain message
     * @param string     $message the message to be encrypted
     *
     * @return string the encrypted message
     *
     * @throws \InvalidArgumentException the plain message is not a string
     * @throws AsymmetricException       an error occurred while encrypting the given message
     */
    public static function encrypt(PrivateKey &$key, $message)
    {
        //check the plain message type
        if ((!is_string($message)) || (strlen($message) <= 0)) {
            throw new \InvalidArgumentException('The plain message to be encrypted must be given as a non-empty string');
        }

        //get the key in native format and its length
        $managedKey = $key();

        //encrypt the complete message
        $complete_message = '';
        foreach (str_split($message, $managedKey['byteLength'] / 2) as $message_chunk) {
            //the encrypted message
            $encrypted_chunk = null;

            //encrypt the current message and check for failure
            if (!openssl_private_encrypt($message_chunk, $encrypted_chunk, $managedKey['key'], OPENSSL_PKCS1_PADDING)) {
                throw new AsymmetricException("The message encryption can't be accomplished due to an unknown error:".openssl_error_string(), 4);
            }

            //join the current encrypted chunk to the encrypted message
            $complete_message .=  (string) $encrypted_chunk;
        }

        //return the encrypted message base64-encoded
        return base64_encode($complete_message);
    }

    /**
     * @param PublicKey $key         the public key to be used to decrypt the encrypted message
     * @param string    $enc_message the message to be decrypted
     *
     * @return string the encrypted message
     *
     * @throws \InvalidArgumentException the encrypted message is not a string
     * @throws AsymmetricException       an error occurred while decrypting the given message
     */
    public static function decrypt(PublicKey &$key, $enc_message)
    {
        //check the encrypted message type
        if ((!is_string($enc_message)) || (strlen($enc_message) <= 0)) {
            throw new \InvalidArgumentException('The encrypted message to be decrypted must be given as a non-empty string');
        }

        //base64-decode of the encrypted message
        $complete_message = base64_decode($enc_message);

        //get the key in native format and its length
        $managedKey = $key();

        //check if the message can be decrypted
        if (($complete_message % $managedKey['byteLength']) != 0) {
            throw new AsymmetricException('The message decryption cannot take place because the given message is malformed', 6);
        }

        //encrypt the complete message
        $complete_unencrypted_message = '';
        foreach (str_split($complete_message, $managedKey['byteLength']) as $encrypted_chunk) {
            $message_chunk = null;

            //decrypt the current chunk of encrypted message
            if (!openssl_public_decrypt($encrypted_chunk, $message_chunk, $managedKey['key'], OPENSSL_PKCS1_PADDING)) {
                throw new AsymmetricException("The message decryption can't be accomplished due to an unknown error", 5);
            }

            //join the current unencrypted chunk to the complete message
            $complete_unencrypted_message .= (string) $message_chunk;
        }

        //return the decrypted message
        return $complete_unencrypted_message;
    }
}
