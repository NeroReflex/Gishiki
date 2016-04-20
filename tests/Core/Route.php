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

namespace Gishiki\Tests\Core;

use Gishiki\Core\Route;

class ManipulationTest extends \PHPUnit_Framework_TestCase {
    public function testRegex() {
        $test_route = new Route("/user/{username}/post/{postnumber}", function() {
            throw new \Exception("Bad Test!");
        });
        
        //check the generated regex
        $this->assertEquals($test_route->getRegex()['regex'], "/^\/user\/([^\/]+)\/post\/([^\/]+)$/");
        
        $test_partregex_route = new Route("/user/new/{address:email}", function() {
            throw new \Exception("Bad Test!");
        });
        
        //check the generated regex
        $this->assertEquals($test_partregex_route->getRegex()['regex'], "/^\/user\/new\/([a-zA-Z0-9_-.+]+@[a-zA-Z0-9-]+.[a-zA-Z]+)$/");
        $this->assertEquals($test_partregex_route->getRegex()['params'], ['address']);
    }
    
}