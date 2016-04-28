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
    public function testROT13()
    {
        $message = 'this is a small>example<to/test rot-13';
        $message_rot13 = 'guvf vf n fznyy>rknzcyr<gb/grfg ebg-13';

        //test hash compatibility
        $rot_ed = Algorithms::hash($message, Algorithms::ROT13);
        $this->assertEquals($message_rot13, $rot_ed);
        $this->assertEquals($message, Algorithms::hash($rot_ed, Algorithms::ROT13));
    }

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

    /**
     * @expectedException Gishiki\Security\Hashing\HashingException
     */
    public function testBadHashPbkdf2()
    {
        Algorithms::pbkdf2('password', 'salt', 512, 3, 'bad-algo');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBadCountHashPbkdf2()
    {
        Algorithms::pbkdf2('password', 'salt', 512, '3', Algorithms::SHA256);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBadKeylengthHashPbkdf2()
    {
        Algorithms::pbkdf2('password', 'salt', '512', 3, Algorithms::SHA256);
    }

    public function testHashPbkdf2()
    {
        //test vectors from https://www.ietf.org/rfc/rfc6070.txt
        $testVector = [
            ['password', 'salt', 20, 1, 'SHA1'],
            ['password', 'salt', 20, 2, 'SHA1'],
            ['password', 'salt', 20, 4096, 'SHA1'],
            ['password', 'salt', 20, 16777216, 'SHA1'],
            ['passwordPASSWORDpassword', 'saltSALTsaltSALTsaltSALTsaltSALTsalt', 25, 4096, 'SHA1'],
            ["pass\0word", "sa\0lt", 16, 4096, 'SHA1'],
            ];
        $resultsVector = [
            '0c60c80f961f0e71f3a9b524af6012062fe037a6',
            'ea6c014dc72d6f8ccd1ed92ace1d41f0d8de8957',
            '4b007901b765489abead49d926f721d065a429c1',
            'eefe3d61cd4da4e4e9945b3d6ba2158c2634e984',
            '3d2eec4fe41c849b80c8d83662c0e44a8b291a964cf2f07038',
            '56fa6aa75548099dcc37d7f03425e0c3',
            ];

        foreach ($testVector as $testIndex => $testValue) {
            //run the vector test allowing openssl hashing
            $this->assertEquals($resultsVector[$testIndex], Algorithms::pbkdf2($testValue[0], $testValue[1], $testValue[2], $testValue[3], $testValue[4], false, false));
        }
    }

    public function testSlowHashPbkdf2()
    {
        //test vectors from https://www.ietf.org/rfc/rfc6070.txt
        $testVector = [
            ['password', 'salt', 20, 1, 'SHA1'],
            ['password', 'salt', 20, 2, 'SHA1'],
            ['password', 'salt', 20, 4096, 'SHA1'],
            ['password', 'salt', 20, 16777216, 'SHA1'],
            ['passwordPASSWORDpassword', 'saltSALTsaltSALTsaltSALTsaltSALTsalt', 25, 4096, 'SHA1'],
            ["pass\0word", "sa\0lt", 16, 4096, 'SHA1'],
            ];
        $resultsVector = [
            '0c60c80f961f0e71f3a9b524af6012062fe037a6',
            'ea6c014dc72d6f8ccd1ed92ace1d41f0d8de8957',
            '4b007901b765489abead49d926f721d065a429c1',
            'eefe3d61cd4da4e4e9945b3d6ba2158c2634e984',
            '3d2eec4fe41c849b80c8d83662c0e44a8b291a964cf2f07038',
            '56fa6aa75548099dcc37d7f03425e0c3',
            ];

        foreach ($testVector as $testIndex => $testValue) {
            //run the vector test forcing the hash library hashing
            $this->assertEquals($resultsVector[$testIndex], Algorithms::pbkdf2($testValue[0], $testValue[1], $testValue[2], $testValue[3], $testValue[4], false, true));
        }
    }
}
