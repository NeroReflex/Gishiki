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

namespace Gishiki\tests\Security\Encryption\Asymmetric;

use Gishiki\Security\Encryption\Asymmetric\PrivateKey;
use Gishiki\Security\Encryption\Asymmetric\PublicKey;
use Gishiki\Security\Encryption\Asymmetric\Cryptography;

/**
 * Various tests for encryption algorithms.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class CryptographyTest extends \PHPUnit_Framework_TestCase
{
    public function testEncryption()
    {
        $privateKey = new PrivateKey(PrivateKey::Generate());

        //check if the private key has been loaded correctly
        $this->assertEquals(true, $privateKey->isLoaded());

        //perform the encryption
        $encrytpion_result = Cryptography::encrypt($privateKey, 'ciao bello!');

        //check if the encryption result has the correct type
        $this->assertEquals(true, is_string($encrytpion_result));
    }

    public function testMessage()
    {
        //this is the test example message
        $message = 'mL84hPpR+nmb2UuWDnhiXnpMDxzQT0NMPXT.dY.*?ImTrO86Dt';

        //generate two keys
        $privateKey = new PrivateKey(PrivateKey::Generate());
        $publicKey = new PublicKey($privateKey->exportPublicKey());

        //check if the private key has been loaded correctly
        $this->assertEquals(true, $privateKey->isLoaded());

        //perform the encryption and decryption
        $encrytpion_result = Cryptography::encrypt($privateKey, $message);
        $decryption_result = Cryptography::decrypt($publicKey, $encrytpion_result);

        //test the return value
        $this->assertEquals($message, $decryption_result);
    }

    public function testLongMessage()
    {
        //generate two keys
        $privateKey = new PrivateKey(KeyTest::getTestRSAPrivateKey());
        $publicKey = new PublicKey($privateKey->exportPublicKey());

        //generate a very long example message
        $message = openssl_random_pseudo_bytes(25 * $privateKey()['byteLength']);

        //check if the private key has been loaded correctly
        $this->assertEquals(true, $privateKey->isLoaded());

        //perform the encryption and decryption
        $encrytpion_result = Cryptography::encrypt($privateKey, $message);
        $decryption_result = Cryptography::decrypt($publicKey, $encrytpion_result);

        //test the return value
        $this->assertEquals($message, $decryption_result);
    }

    /**
     * @expectedException Gishiki\Security\Encryption\Asymmetric\AsymmetricException
     */
    public function testBadDecryption()
    {
        //generate two keys
        $privateKey = new PrivateKey(KeyTest::getTestRSAPrivateKey());
        $publicKey = new PublicKey($privateKey->exportPublicKey());

        //generate a very long example message
        $message = openssl_random_pseudo_bytes(5 * $privateKey()['byteLength']);

        //check if the private key has been loaded correctly
        $this->assertEquals(true, $privateKey->isLoaded());

        //perform the encryption and decryption
        $encrytpion_result = Cryptography::encrypt($privateKey, $message);
        $malformed_encrytpion_result = str_shuffle(substr($encrytpion_result, 1));

        //an exception should be thrown....
        //$this->expectException('Gishiki\Security\Encryption\Asymmetric\AsymmetricException');

        //come on, decrypt a malformed message, if you can!
        $decryption_result = Cryptography::decrypt($publicKey, $malformed_encrytpion_result);

        //test the return value (should be null)
        $this->assertEquals(null, $decryption_result);
    }

    public function testDigitalSignature()
    {
        //generate a new private key and the associated public key
        $privKey = new PrivateKey(PrivateKey::Generate());
        $pubKey = new PublicKey($privKey->exportPublicKey());

        $message = 'who knows if this message will be modified.....';

        //generate the signature
        $signature = Cryptography::generateDigitalSignature($privKey, $message);

        //check the result
        $this->assertEquals(true, Cryptography::verifyDigitalSignature($pubKey, $message, $signature));
    }

    public function testReverse()
    {
        //generate a new private key and the associated public key
        $privKey = new PrivateKey(PrivateKey::Generate());
        $pubKey = new PublicKey($privKey->exportPublicKey());

        //generate a very long example message
        $message = openssl_random_pseudo_bytes(5 * $privKey()['byteLength']);

        //encrypt and decrypt
        $enc_message = Cryptography::encryptReverse($pubKey, $message);
        $this->assertEquals($message, Cryptography::decryptReverse($privKey, $enc_message));
    }
}
