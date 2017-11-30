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

namespace Gishiki\tests\Algorithms\Strings;

use PHPUnit\Framework\TestCase;

use Gishiki\Algorithms\Strings\SimpleLexer;

class SimpleLexerTest extends TestCase
{
    public function testEmail()
    {
        $this->assertFalse(SimpleLexer::isEmail(null));
        $this->assertFalse(SimpleLexer::isEmail(".example@example.com"));
        $this->assertFalse(SimpleLexer::isEmail(".example@exam@ple.com"));
        $this->assertFalse(SimpleLexer::isEmail("John..Doe@exam@ple.com"));

        $this->assertTrue(SimpleLexer::isEmail("\"John..Doe\"@example.com"));
        $this->assertTrue(SimpleLexer::isEmail("\".example\"@example.com"));
        $this->assertTrue(SimpleLexer::isEmail("++example@exm.com"));
    }

    public function testFloat()
    {
        $this->assertFalse(SimpleLexer::isFloat(null));
        $this->assertFalse(SimpleLexer::isFloat("0.0.1"));
        $this->assertFalse(SimpleLexer::isFloat("+0a5"));
        $this->assertFalse(SimpleLexer::isFloat("+0-5"));
        $this->assertFalse(SimpleLexer::isFloat("-45."));

        $this->assertTrue(SimpleLexer::isFloat("+1"));
        $this->assertTrue(SimpleLexer::isFloat("2"));
        $this->assertTrue(SimpleLexer::isFloat("0.5"));
        $this->assertTrue(SimpleLexer::isFloat("-50.75"));
    }

    public function testSignedInteger()
    {
        $this->assertFalse(SimpleLexer::isSignedInteger(null));
        $this->assertFalse(SimpleLexer::isSignedInteger("0.0"));
        $this->assertFalse(SimpleLexer::isSignedInteger("+0a5"));
        $this->assertFalse(SimpleLexer::isSignedInteger("-45."));
        $this->assertFalse(SimpleLexer::isSignedInteger("n45"));
        $this->assertFalse(SimpleLexer::isSignedInteger(8));

        $this->assertTrue(SimpleLexer::isSignedInteger("8"));
        $this->assertTrue(SimpleLexer::isSignedInteger("+1"));
        $this->assertTrue(SimpleLexer::isSignedInteger("-2"));
        $this->assertTrue(SimpleLexer::isSignedInteger("-57"));
        $this->assertTrue(SimpleLexer::isSignedInteger("+50"));
    }

    public function testUnsignedInteger()
    {
        $this->assertFalse(SimpleLexer::isUnsignedInteger(null));
        $this->assertFalse(SimpleLexer::isUnsignedInteger("0.0"));
        $this->assertFalse(SimpleLexer::isUnsignedInteger("0a5"));
        $this->assertFalse(SimpleLexer::isUnsignedInteger(5));
        $this->assertFalse(SimpleLexer::isUnsignedInteger("+1"));
        $this->assertFalse(SimpleLexer::isUnsignedInteger("-2"));
        $this->assertFalse(SimpleLexer::isUnsignedInteger("-57"));
        $this->assertFalse(SimpleLexer::isUnsignedInteger("+50"));

        $this->assertTrue(SimpleLexer::isUnsignedInteger("8"));
        $this->assertTrue(SimpleLexer::isUnsignedInteger("58"));
        $this->assertTrue(SimpleLexer::isUnsignedInteger("00"));
    }
}