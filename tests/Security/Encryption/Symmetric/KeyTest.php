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

namespace Gishiki\tests\Security\Encryption\Symmetric;

use Gishiki\Security\Encryption\Symmetric\SecretKey;
use Gishiki\Security\Encryption\Symmetric\Cryptography;

/**
 * Tests for the SecretKey class.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class KeyTest extends \PHPUnit_Framework_TestCase {
    
    public function testKeyExport() {
        
        $key = new Secretkey("6578616d706c65206865782064617461");
        
        $this->assertEquals("6578616d706c65206865782064617461", ''.$key);
    }
    
}
