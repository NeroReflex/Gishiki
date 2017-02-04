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

use Gishiki\Algorithms\Base64;

class Base64Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEncodeBadMessage()
    {
        Base64::Encode(1);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDecodeBadMessage()
    {
        Base64::Decode(1);
    }

    public function testURLUnsafeEncodes()
    {
        for ($i = 1; $i < 100; ++$i) {
            $message = bin2hex(openssl_random_pseudo_bytes($i));

            $binsafe_message = Base64::Encode($message, false);

            $this->assertEquals($message, Base64::Decode($binsafe_message));
        }
    }

    public function testURLSafeEncodes()
    {
        for ($i = 1; $i < 100; ++$i) {
            $message = bin2hex(openssl_random_pseudo_bytes($i));

            $urlsafe_message = Base64::Encode($message, true);

            $this->assertEquals($urlsafe_message, urlencode($urlsafe_message));

            $this->assertEquals($message, Base64::Decode($urlsafe_message));
        }
    }

    public function testCompatibility()
    {
        for ($i = 1; $i < 100; ++$i) {
            $message = bin2hex(openssl_random_pseudo_bytes($i));

            $safe_message = base64_encode($message);

            $this->assertEquals($message, Base64::Decode($safe_message));
        }
    }
}
