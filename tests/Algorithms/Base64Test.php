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

namespace Gishiki\tests\Algorithms;

use PHPUnit\Framework\TestCase;

use Gishiki\Algorithms\Base64;

class Base64Test extends TestCase
{
    public function testEncodeBadMessage()
    {
        $this->expectException(\InvalidArgumentException::class);
        Base64::encode(1);
    }

    public function testDecodeBadMessage()
    {
        $this->expectException(\InvalidArgumentException::class);
        Base64::decode(1);
    }

    public function testURLUnsafeEncodes()
    {
        for ($i = 1; $i < 100; ++$i) {
            $message = bin2hex(openssl_random_pseudo_bytes($i));

            $binsafe_message = Base64::encode($message, false);

            $this->assertEquals($message, Base64::decode($binsafe_message));
        }
    }

    public function testURLSafeEncodes()
    {
        for ($i = 1; $i < 100; ++$i) {
            $message = bin2hex(openssl_random_pseudo_bytes($i));

            $urlsafe_message = Base64::encode($message, true);

            $this->assertEquals($urlsafe_message, urlencode($urlsafe_message));

            $this->assertEquals($message, Base64::decode($urlsafe_message));
        }
    }

    public function testCompatibility()
    {
        for ($i = 1; $i < 100; ++$i) {
            $message = bin2hex(openssl_random_pseudo_bytes($i));

            $safe_message = base64_encode($message);

            $this->assertEquals($message, Base64::decode($safe_message));
        }
    }
}
