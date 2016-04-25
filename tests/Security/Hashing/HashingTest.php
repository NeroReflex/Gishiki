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

namespace Gishiki\tests\Security\Hashing;

use Gishiki\Security\Hashing\Algorithms;

/**
 * Tests for the hashing function.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class HashingTest  extends \PHPUnit_Framework_TestCase
{
    public function testHashCompatibility()
    {
        $message = openssl_random_pseudo_bytes(128);

        //test hash compatibility
        $this->assertEquals(md5($message), Algorithms::hash($message, Algorithms::MD5));
        $this->assertEquals(sha1($message), Algorithms::hash($message, Algorithms::SHA1));
    }

    /**
     * @expectedException Gishiki\Security\Hashing\HashingException
     */
    public function testBadAlgorithm()
    {
        $message = 'fake message';

        Algorithms::hash($message, 'fake algorithm');
    }
}
