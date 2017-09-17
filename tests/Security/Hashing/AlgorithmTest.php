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

use PHPUnit\Framework\TestCase;

use Gishiki\Security\Hashing\HashingException;
use Gishiki\Security\Hashing\Algorithm;

/**
 * Tests for the Hasher class.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class AlgorithmTest extends TestCase
{
    public function testInvalidMessageForOpensslHash()
    {
        $this->expectException(\InvalidArgumentException::class);

        //test hash compatibility
        Algorithm::opensslHash('', Algorithm::SHA512);
    }

    public function testInvalidMessageForRot13Hash()
    {
        $this->expectException(\InvalidArgumentException::class);

        //test hash compatibility
        Algorithm::rot13Hash('');
    }

    public function testInvalidMessageForBcryptHash()
    {
        $this->expectException(\InvalidArgumentException::class);

        Algorithm::bcryptHash('');
    }

    public function testInvalidMessageForOpensslVerify()
    {
        $this->expectException(\InvalidArgumentException::class);

        //test hash compatibility
        Algorithm::opensslVerify('', ':)', Algorithm::SHA512);
    }

    public function testInvalidMessageForRot13Verify()
    {
        $this->expectException(\InvalidArgumentException::class);

        //test hash compatibility
        Algorithm::rot13Verify('', ':)');
    }

    public function testInvalidMessageForBcryptVerify()
    {
        $this->expectException(\InvalidArgumentException::class);

        //test hash compatibility
        Algorithm::bcryptVerify('', ':)');
    }

    public function testInvalidMessageDigestForOpensslVerify()
    {
        $this->expectException(\InvalidArgumentException::class);

        //test hash compatibility
        Algorithm::opensslVerify('My message', '', Algorithm::SHA512);
    }

    public function testInvalidMessageDigestForRot13Verify()
    {
        $this->expectException(\InvalidArgumentException::class);

        //test hash compatibility
        Algorithm::rot13Verify('My message', '');
    }

    public function testInvalidMessageDigestForBcryptVerify()
    {
        $this->expectException(\InvalidArgumentException::class);

        //test hash compatibility
        Algorithm::bcryptVerify('My message', '');
    }

    public function testOpensslVerify()
    {
        $random = bin2hex(openssl_random_pseudo_bytes(25));

        //test hash compatibility
        $hash = Algorithm::opensslHash($random, Algorithm::SHA512);
        $this->assertEquals(true, Algorithm::opensslVerify($random, $hash, Algorithm::SHA512));

        $this->assertEquals(false, Algorithm::opensslVerify($random, 'any other thing', Algorithm::SHA512));
    }

    public function testRot13Verify()
    {
        $random = bin2hex(openssl_random_pseudo_bytes(25));

        //test hash compatibility
        $hash = Algorithm::rot13Hash($random);
        $this->assertEquals(true, Algorithm::rot13Hash($random, $hash));

        $this->assertEquals(false, Algorithm::rot13Verify($random, 'any other thing'));
    }

    public function testBCryptVerify()
    {
        $random = bin2hex(openssl_random_pseudo_bytes(25));

        //test hash compatibility
        $hash = Algorithm::bcryptHash($random);
        $this->assertEquals(true, Algorithm::bcryptHash($random, $hash));

        $this->assertEquals(false, Algorithm::bcryptVerify($random, 'any other thing'));
    }

    public function testROT13()
    {
        $message = 'this is a small>example<to/test rot-13';
        $message_rot13 = 'guvf vf n fznyy>rknzcyr<gb/grfg ebg-13';

        //test hash compatibility
        $rot_ed = Algorithm::rot13Hash($message, Algorithm::ROT13);
        $this->assertEquals($message_rot13, $rot_ed);
        $this->assertEquals($message, Algorithm::rot13Hash($rot_ed, Algorithm::ROT13));

        $this->assertEquals(true, Algorithm::rot13Verify($message, $rot_ed));
    }

    public function testHashCompatibility()
    {
        $message = openssl_random_pseudo_bytes(128);

        //test hash compatibility
        $this->assertEquals(md5($message), Algorithm::opensslHash($message, Algorithm::MD5));
        $this->assertEquals(sha1($message), Algorithm::opensslHash($message, Algorithm::SHA1));
    }

    public function testBadHashPbkdf2()
    {
        $this->expectException(HashingException::class);
        
        Algorithm::pbkdf2('password', 'salt', 512, 3, 'bad-algo');
    }

    public function testBadAlgorithmPbkdf2()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        Algorithm::pbkdf2('message', 'salt', 512, 3, '');
    }

    public function testBadCountHashPbkdf2()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        Algorithm::pbkdf2('password', 'salt', 512, '3', Algorithm::SHA256);
    }

    public function testBadKeylengthHashPbkdf2()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        Algorithm::pbkdf2('password', 'salt', '512', 3, Algorithm::SHA256);
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
            $this->assertEquals($resultsVector[$testIndex], Algorithm::pbkdf2($testValue[0], $testValue[1], $testValue[2], $testValue[3], $testValue[4], false, false));
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
            $this->assertEquals($resultsVector[$testIndex], Algorithm::pbkdf2($testValue[0], $testValue[1], $testValue[2], $testValue[3], $testValue[4], false, true));
        }
    }
}
