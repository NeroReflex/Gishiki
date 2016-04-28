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
 * Note: This class uses OpenSSL for strong encryption
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
        $completeMsg = '';
        foreach (str_split($message, $managedKey['byteLength'] / 2) as $msgChunk) {
            //the encrypted message
            $encryptedChunk = null;

            //encrypt the current message and check for failure
            if (!openssl_private_encrypt($msgChunk, $encryptedChunk, $managedKey['key'], OPENSSL_PKCS1_PADDING)) {
                throw new AsymmetricException("The message encryption can't be accomplished due to an unknown error", 4);
            }

            //join the current encrypted chunk to the encrypted message
            $completeMsg .=  (string) $encryptedChunk;
        }

        //return the encrypted message base64-encoded
        return base64_encode($completeMsg);
    }

    /**
     * Encrypt the given message using the given public key.
     * 
     * You will need the private key to decrypt the encrypted content.
     * 
     * You can decrypt an encrypted content with the decryptReverse() function.
     * 
     * An example of usage can be:
     * 
     * <code>
     * $default_pubkey = new PublicKey();
     * $encrypted_message = Cryptography::encryptReverse($default_pubkey, "this is my important message from my beloved half");
     * 
     * echo "Take good care of this and give it to my GF: " . $encrypted_message;
     * </code>
     * 
     * @param PublicKey $key     the public key to be used to encrypt the plain message
     * @param string    $message the message to be encrypted
     *
     * @return string the encrypted message
     *
     * @throws \InvalidArgumentException the plain message is not a string
     * @throws AsymmetricException       an error occurred while encrypting the given message
     */
    public static function encryptReverse(PublicKey &$key, $message)
    {
        //check the plain message type
        if ((!is_string($message)) || (strlen($message) <= 0)) {
            throw new \InvalidArgumentException('The plain message to be encrypted must be given as a non-empty string');
        }

        //get the key in native format and its length
        $managedKey = $key();

        //encrypt the complete message
        $completeMsg = '';
        foreach (str_split($message, $managedKey['byteLength'] / 2) as $msgChunk) {
            //the encrypted message
            $encryptedChunk = null;

            //encrypt the current message and check for failure
            if (!openssl_public_encrypt($msgChunk, $encryptedChunk, $managedKey['key'], OPENSSL_PKCS1_PADDING)) {
                throw new AsymmetricException("The message encryption can't be accomplished due to an unknown error", 15);
            }

            //join the current encrypted chunk to the encrypted message
            $completeMsg .=  (string) $encryptedChunk;
        }

        //return the encrypted message base64-encoded
        return base64_encode($completeMsg);
    }

    /**
     * Decrypt an encrypted message created using the encrypt() function.
     * 
     * The used public key must be decoupled from the private key used to generate the message.
     * 
     * En example usage can be:
     * 
     * <code>
     * //load the default public key
     * $default_pubkey = new PublicKey();
     * 
     * //this is a message encrypted with the application's default key
     * $encrypted_message = "...";
     * 
     * //decrypt the message
     * $plain_message = Cryptography::decrypt($default_pubkey, $encrypted_message);
     * 
     * echo $encrypted_message;
     * </code>
     * 
     * 
     * @param PublicKey $key          the public key to be used to decrypt the encrypted message
     * @param string    $encryptedMsg the message to be decrypted
     *
     * @return string the encrypted message
     *
     * @throws \InvalidArgumentException the encrypted message is not a string
     * @throws AsymmetricException       an error occurred while decrypting the given message
     */
    public static function decrypt(PublicKey &$key, $encryptedMsg)
    {
        //check the encrypted message type
        if ((!is_string($encryptedMsg)) || (strlen($encryptedMsg) <= 0)) {
            throw new \InvalidArgumentException('The encrypted message to be decrypted must be given as a non-empty string');
        }

        //base64-decode of the encrypted message
        $completeMsg = base64_decode($encryptedMsg);

        //get the key in native format and its length
        $managedKey = $key();

        //check if the message can be decrypted
        /*if (($completeMsg % $managedKey['byteLength']) != 0) {
            throw new AsymmetricException('The message decryption cannot take place because the given message is malformed', 6);
        }*/

        //encrypt the complete message
        $message = '';
        foreach (str_split($completeMsg, $managedKey['byteLength']) as $encryptedChunk) {
            $msgChunk = null;

            //decrypt the current chunk of encrypted message
            if (!openssl_public_decrypt($encryptedChunk, $msgChunk, $managedKey['key'], OPENSSL_PKCS1_PADDING)) {
                throw new AsymmetricException("The message decryption can't be accomplished due to an unknown error", 5);
            }

            //join the current unencrypted chunk to the complete message
            $message .= (string) $msgChunk;
        }

        //return the decrypted message
        return $message;
    }

    /**
     * Decrypt an encrypted message created using the encryptReverse() function.
     * 
     * The used private key must be must be the corresponding public key used to generate the message.
     * 
     * En example usage can be:
     * 
     * <code>
     * //load the default private key
     * $default_pubkey = new PrivateKey();
     * 
     * //this is a message encrypted with the application's default key
     * $encrypted_message = "...";
     * 
     * //decrypt the message
     * $plain_message = Cryptography::decryptReverse($default_privkey, $encrypted_message);
     * 
     * echo $encrypted_message;
     * </code>
     * 
     * 
     * @param PrivateKey $key          the public key to be used to decrypt the encrypted message
     * @param string     $encryptedMsg the message to be decrypted
     *
     * @return string the encrypted message
     *
     * @throws \InvalidArgumentException the encrypted message is not a string
     * @throws AsymmetricException       an error occurred while decrypting the given message
     */
    public static function decryptReverse(PrivateKey &$key, $encryptedMsg)
    {
        //check the encrypted message type
        if ((!is_string($encryptedMsg)) || (strlen($encryptedMsg) <= 0)) {
            throw new \InvalidArgumentException('The encrypted message to be decrypted must be given as a non-empty string');
        }

        //base64-decode of the encrypted message
        $completeMsg = base64_decode($encryptedMsg);

        //get the key in native format and its length
        $managedKey = $key();

        //check if the message can be decrypted
        /*if (($completeMsg % $managedKey['byteLength']) != 0) {
            throw new AsymmetricException('The message decryption cannot take place because the given message is malformed', 6);
        }*/

        //encrypt the complete message
        $complete_unencrypted_message = '';
        foreach (str_split($completeMsg, $managedKey['byteLength']) as $encryptedChunk) {
            $msgChunk = null;

            //decrypt the current chunk of encrypted message
            if (!openssl_private_decrypt($encryptedChunk, $msgChunk, $managedKey['key'], OPENSSL_PKCS1_PADDING)) {
                throw new AsymmetricException("The message decryption can't be accomplished due to an unknown error", 20);
            }

            //join the current unencrypted chunk to the complete message
            $complete_unencrypted_message .= (string) $msgChunk;
        }

        //return the decrypted message
        return $complete_unencrypted_message;
    }

    /**
     * Generate a digital signature for the given message.
     * 
     * The digital signature can be used to authenticate the message because
     * a different message will produce a different digital signature.
     * 
     * You will be using the public key corresponding to the given private key
     * to check the digital signature.
     * 
     * Example usage:
     * <code>
     * $message = "who knows if this message will be modified.....";
     * 
     * //get the default private key
     * $privKey = new PrivateKey();
     * 
     * //generate the digital signature
     * $signature = Cryptography::generateDigitalSignature($privKey, $message);
     * 
     * //transmit the digital signature
     * </code>
     * 
     * @param PrivateKey $key     the priate key to be used to generate the message
     * @param string     $message the message to be signed
     *
     * @return string the generate digital signature
     *
     * @throws \InvalidArgumentException the given message is not a valid string
     * @throws AsymmetricException       the error occurred while generating the message
     */
    public static function generateDigitalSignature(PrivateKey &$key, $message)
    {
        //check the message type
        if ((!is_string($message)) && (strlen($message) <= 0)) {
            throw new \InvalidArgumentException('The message to be signed must be a non-empty string');
        }

        //check for the private key
        if (!$key->isLoaded()) {
            throw new AsymmetricException('It is impossible to generate a digital signature with an unloaded key', 11);
        }

        //get the managed version of the native key
        $managed_key = $key();

        //generate the digital signature
        $digitalSignature = null;
        if (!openssl_sign($message, $digitalSignature, $managed_key['key'], 'sha256WithRSAEncryption')) {
            throw new AsymmetricException('It is impossible to generate the digital signature due to an unknown error', 12);
        }

        //return the signature in a binary-safe format
        return base64_encode($digitalSignature);
    }

    /**
     * Check if the given digital signature belongs to the given message.
     * 
     * You should be calling this function with a digital signature generated with
     * the generateDigitalSignature() function.
     * 
     * Usage example (continuation of the generateDigitalSignature() example):
     * 
     * <code>
     * //get the default public key
     * $pubKey = new PublicKey();
     * 
     * if (Cryptography::verifyDigitalSignature($pubKey, $message, $signature)) {
     *     echo "the message was not modified";
     * } else {
     *     echo "the message have been modified";
     * }
     * </code>
     * 
     * @param PublicKey $key       the public key associated with the private key used to generate the signature
     * @param string    $message   the message to be checked
     * @param string    $signature the digital signature of the given message
     *
     * @return bool true if the message digitaly signed it equal to the digital signature
     *
     * @throws \InvalidArgumentException the given message or the given signature are not a valid string
     * @throws AsymmetricException       the error occurred while checking the message
     */
    public static function verifyDigitalSignature(PublicKey &$key, $message, $signature)
    {
        //check the message type
        if ((!is_string($message)) && (strlen($message) <= 0)) {
            throw new \InvalidArgumentException('The message to be checked must be a non-empty string');
        }

        //check the message type
        if ((!is_string($signature)) && (strlen($signature) <= 0)) {
            throw new \InvalidArgumentException('The digital signature of the message must be a non-empty string');
        }

        //check for the private key
        if (!$key->isLoaded()) {
            throw new AsymmetricException('It is impossible to generate a digital signature with an unloaded key', 13);
        }

        //get the signature result
        $binSignature = base64_decode($signature);

        //attempt to verify the digital signature
        $verificationResult = openssl_verify($message, $binSignature, $key()['key'], OPENSSL_ALGO_SHA256);

        //check for errors in the process
        if (($verificationResult !== 0) && ($verificationResult !== 1)) {
            throw new AsymmetricException('An unknown error has occurred while verifying the digital signature', 14);
        }

        //return the result
        return $verificationResult != 0;
    }
}
