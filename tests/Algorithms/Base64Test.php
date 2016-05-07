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

namespace Gishiki\tests\Algorithms;

use Gishiki\Algorithms\Base64;

class Base64Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEncodeBadMessage()
    {
        Base64::encode(1);
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDecodeBadMessage()
    {
        Base64::decode(1);
    }
    
    public function testEncodes() {
        for ($i = 1; $i < 100; $i++) {
            $message = bin2hex(openssl_random_pseudo_bytes($i));
            
            $binsafe_message = Base64::encode($message, false);
            $urlsafe_message = Base64::encode($message, true);
            
            $this->assertEquals($message, Base64::decode($binsafe_message));
            $this->assertEquals($message, Base64::decode($urlsafe_message));
        }
    }
}