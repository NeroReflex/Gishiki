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

namespace Gishiki\tests\Security\Encryption\Symmetric;

use Gishiki\Security\Encryption\Symmetric\SecretKey;
use Gishiki\Security\Encryption\Symmetric\Cryptography;

/**
 * Various tests for encryption algorithms.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class CriptographyTest extends \PHPUnit_Framework_TestCase
{
    public function testAES128Encryption()
    {
        //generate the key
        $key = new SecretKey(SecretKey::Generate('testing/key'));

        $message = 'you should hide this, lol!';

        //encrypt the message
        $enc_message = Cryptography::encrypt($key, $message);

        //decrypt the message
        $result = Cryptography::decrypt($key, $enc_message['Encryption'], $enc_message['IV_base64']);

        //test the result
        $this->assertEquals($message, $result);
    }

    public function testAES128LongEncryption()
    {
        //generate the key
        $key = new SecretKey(SecretKey::Generate('testing/key'));

        $message = base64_encode(openssl_random_pseudo_bytes(515));

        //encrypt the message
        $enc_message = Cryptography::encrypt($key, $message);

        //decrypt the message
        $result = Cryptography::decrypt($key, $enc_message['Encryption'], $enc_message['IV_base64']);

        //test the result
        $this->assertEquals($message, $result);
    }

    public function testAES192Encryption()
    {
        //generate the key
        $key = new SecretKey(SecretKey::Generate('T3st1n9/k3y <3', 24));

        $message = base64_encode(openssl_random_pseudo_bytes(512));

        //encrypt the message
        $enc_message = Cryptography::encrypt($key, $message, null, Cryptography::AES_CBC_192);

        //decrypt the message
        $result = Cryptography::decrypt($key, $enc_message['Encryption'], $enc_message['IV_base64'], Cryptography::AES_CBC_192);

        //test the result
        $this->assertEquals($message, $result);
    }

    public function testAES256Encryption()
    {
        //generate the key
        $key = new SecretKey(SecretKey::Generate('T3st1n9/k3y <3', 32));

        $message = base64_encode(openssl_random_pseudo_bytes(512));

        //encrypt the message
        $enc_message = Cryptography::encrypt($key, $message, null, Cryptography::AES_CBC_256);

        //decrypt the message
        $result = Cryptography::decrypt($key, $enc_message['Encryption'], $enc_message['IV_base64'], Cryptography::AES_CBC_256);

        //test the result
        $this->assertEquals($message, $result);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidKey()
    {
        //generate the key
        $key = new SecretKey(SecretKey::Generate('T3st1n9/k3y <3', 1));

        $message = base64_encode(openssl_random_pseudo_bytes(512));

        //encrypt the message (trigger the exception)
        Cryptography::encrypt($key, $message, null, Cryptography::AES_CBC_128);
    }

    /**
     * @expectedException Gishiki\Security\Encryption\Symmetric\SymmetricException
     */
    public function testAES128BadKey()
    {
        //generate the key
        $key = new SecretKey(SecretKey::Generate('T3st1n9/k3y <3', 2));

        $message = base64_encode(openssl_random_pseudo_bytes(512));

        //encrypt the message (trigger the exception)
        Cryptography::encrypt($key, $message, null, Cryptography::AES_CBC_128);
    }

    /**
     * @expectedException Gishiki\Security\Encryption\Symmetric\SymmetricException
     */
    public function testAES192BadKey()
    {
        //generate the key
        $key = new SecretKey(SecretKey::Generate('T3st1n9/k3y <3', 40));

        $message = base64_encode(openssl_random_pseudo_bytes(512));

        //encrypt the message (trigger the exception)
        Cryptography::encrypt($key, $message, null, Cryptography::AES_CBC_192);
    }

    /**
     * @expectedException Gishiki\Security\Encryption\Symmetric\SymmetricException
     */
    public function testAES256BadKey()
    {
        //generate the key
        $key = new SecretKey(SecretKey::Generate('T3st1n9/k3y <3', 12));

        $message = base64_encode(openssl_random_pseudo_bytes(512));

        //encrypt the message (trigger the exception)
        Cryptography::encrypt($key, $message, null, Cryptography::AES_CBC_256);
    }
}
