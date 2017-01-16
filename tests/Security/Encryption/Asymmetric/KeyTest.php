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

/**
 * Tests for the private and public key loaders.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class KeyTest extends \PHPUnit_Framework_TestCase
{
    public static function getTestRSAPublicKey()
    {
        return '-----BEGIN PUBLIC KEY-----
MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAwUHsaL7kLg0CWpUlnQkT
YW3HFSNRV8fAKKWWeZswc4JXbPTqeiorGZT3mPlKl/e+FrwKPK7ZUYHlLxdEDOB5
0Sqe/qOalA/zr6OGWkxC+rq5BibPz29dcTFr9GSsF01PorR57sKqqeZPfTboAKA1
jTNL8dwFqFuT/ZI+NC6rki2lTt3JZb/LUOqvqY0vW8wlyXBxTScJpPEhlXcRuN5Y
QLJCYhz9f8AlMhzUYj3AN6hhYFCwa5+PgAGzjBpWVzDKGNFWCrxVejGkONrypbSr
nF6yHj6jVpVSfZvQFK9cmujGeU6fackVLcARUiUDY3rS0lFNC41VADHP5hpgPLTp
YwONOkuWoPFvyVIJDMCB0IIkbRSojrtuNlkGx7ZVksXA7pRP48LhFRjyHEHGnZ87
7pMoDd8e33/1Lut2NUMpCOR/8owgek985rv049tsa9Uw6xL92anOt6CcUS4SxOdY
lh3mnDuAB4egzLS2UUVmmaOgGVkfnzoPLxg+m1tF7nRqSNtCQOBAQVcDP3189//w
DPj/pt5btnKsH9bC720+AmuM7XKuX3uiIjDo8UTENdQpoEUG0A/gKXmdCagOeLZ+
tdaf03LPdaT7yoYCcmdLezqtMRywLkyxqDNhLfqJvQD+tct02fASZKjxLP2+F48L
LDdopnAc51efTUT3s+DN+J0CAwEAAQ==
-----END PUBLIC KEY-----';
    }

    public static function getTestRSAPrivateKey()
    {
        return '-----BEGIN RSA PRIVATE KEY-----
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
-----END RSA PRIVATE KEY-----';
    }

    public function testPrivateKeyload()
    {
        $loadedKey = new PrivateKey(self::getTestRSAPrivateKey());

        //check if the private key has been loaded correctly
        $this->assertEquals(true, $loadedKey->isLoaded());
    }

    public function testPublicKeyload()
    {
        $loadedKey = new PublicKey(self::getTestRSAPublicKey());

        //check if the private key has been loaded correctly
        $this->assertEquals(true, $loadedKey->isLoaded());
    }

    /**
     * @expectedException Gishiki\Security\Encryption\Asymmetric\AsymmetricException
     */
    public function testFakePrivateKeyload()
    {
        //the load of a bad key results in an exception
        //$this->expectException('Gishiki\Security\Encryption\Asymmetric\AsymmetricException');

        //try load an invalid key
        $loadedKey = new PrivateKey('th1s is s0m3 Sh1t th4t, obviously, is NOT an RSA pr1v4t3 k3y!');

        //the exception was thrown, the loaded key is null!
        $this->assertEquals(null, $loadedKey);
    }

    public function testKeyGeneration()
    {
        //generate a new serialized key
        $serialized_private_key = PrivateKey::Generate();

        $this->assertEquals(true, strlen($serialized_private_key) > 1);

        //load the newly generated key
        $private_key = new PrivateKey($serialized_private_key);

        $this->assertEquals(true, $private_key->isLoaded());
    }
}
