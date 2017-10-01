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
        $this->assertEquals(false, SimpleLexer::isEmail(null));
        $this->assertEquals(false, SimpleLexer::isEmail(".example@example.com"));
        $this->assertEquals(false, SimpleLexer::isEmail(".example@exam@ple.com"));
        $this->assertEquals(false, SimpleLexer::isEmail("John..Doe@exam@ple.com"));

        $this->assertEquals(true, SimpleLexer::isEmail("\"John..Doe\"@example.com"));
        $this->assertEquals(true, SimpleLexer::isEmail("\".example\"@example.com"));
        $this->assertEquals(true, SimpleLexer::isEmail("++example@exm.com"));
    }

    public function testFloat()
    {
        $this->assertEquals(false, SimpleLexer::isFloat(null));
        $this->assertEquals(false, SimpleLexer::isFloat("0.0.1"));
        $this->assertEquals(false, SimpleLexer::isFloat("+0a5"));
        $this->assertEquals(false, SimpleLexer::isFloat("+0-5"));
        $this->assertEquals(false, SimpleLexer::isFloat("-45."));

        $this->assertEquals(true, SimpleLexer::isFloat("+1"));
        $this->assertEquals(true, SimpleLexer::isFloat("2"));
        $this->assertEquals(true, SimpleLexer::isFloat("0.5"));
        $this->assertEquals(true, SimpleLexer::isFloat("-50.75"));
    }

    public function testSignedInteger()
    {
        $this->assertEquals(false, SimpleLexer::isSignedInteger(null));
        $this->assertEquals(false, SimpleLexer::isSignedInteger("0.0"));
        $this->assertEquals(false, SimpleLexer::isSignedInteger("+0a5"));
        $this->assertEquals(false, SimpleLexer::isSignedInteger("-45."));
        $this->assertEquals(false, SimpleLexer::isSignedInteger("n45"));
        $this->assertEquals(false, SimpleLexer::isSignedInteger(8));

        $this->assertEquals(true, SimpleLexer::isSignedInteger("8"));
        $this->assertEquals(true, SimpleLexer::isSignedInteger("+1"));
        $this->assertEquals(true, SimpleLexer::isSignedInteger("-2"));
        $this->assertEquals(true, SimpleLexer::isSignedInteger("-57"));
        $this->assertEquals(true, SimpleLexer::isSignedInteger("+50"));
    }

    public function testUnsignedInteger()
    {
        $this->assertEquals(false, SimpleLexer::isUnsignedInteger(null));
        $this->assertEquals(false, SimpleLexer::isUnsignedInteger("0.0"));
        $this->assertEquals(false, SimpleLexer::isUnsignedInteger("0a5"));
        $this->assertEquals(false, SimpleLexer::isUnsignedInteger(5));
        $this->assertEquals(false, SimpleLexer::isUnsignedInteger("+1"));
        $this->assertEquals(false, SimpleLexer::isUnsignedInteger("-2"));
        $this->assertEquals(false, SimpleLexer::isUnsignedInteger("-57"));
        $this->assertEquals(false, SimpleLexer::isUnsignedInteger("+50"));

        $this->assertEquals(true, SimpleLexer::isUnsignedInteger("8"));
        $this->assertEquals(true, SimpleLexer::isUnsignedInteger("58"));
        $this->assertEquals(true, SimpleLexer::isUnsignedInteger("00"));
    }
}