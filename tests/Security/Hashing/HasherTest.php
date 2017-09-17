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

namespace Gishiki\tests\Security\Hashing;

use Gishiki\Security\Hashing\Algorithm;
use PHPUnit\Framework\TestCase;

use Gishiki\Security\Hashing\HashingException;
use Gishiki\Security\Hashing\Hasher;

/**
* Tests for the Hasher class.
*
* @author Benato Denis <benato.denis96@gmail.com>
*/
class HasherTest extends TestCase
{
    public function testBadAlgorithm()
    {
        $this->expectException(HashingException::class);

        $hasher = new Hasher('bad algo');
    }

    public function testBCrypt()
    {
        $random = bin2hex(openssl_random_pseudo_bytes(128));

        $hasher = new Hasher(Algorithm::BCRYPT);

        $digest = $hasher->hash($random);

        $this->assertEquals(true, $hasher->verify($random, $digest));
        $this->assertEquals(false, $hasher->verify($random, 'anything else'));
    }

    public function testOpenssl()
    {
        $random = bin2hex(openssl_random_pseudo_bytes(128));

        $hasher = new Hasher(Algorithm::SHA256);

        $digest = $hasher->hash($random);

        $this->assertEquals(true, $hasher->verify($random, $digest));
        $this->assertEquals(false, $hasher->verify($random, 'anything else'));
    }
}