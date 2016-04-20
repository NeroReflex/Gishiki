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

class RouteTest extends \PHPUnit_Framework_TestCase {
    public function testRegexRouter() {
        $test_route = new Route("/user/{username}/post/{post:number}", function() {
            throw new \Exception("Bad Test!");
        });
        
        //check the generated regex
        $this->assertEquals("/^\/user\/([^\/]+)\/post\/((\+|-)?(\d)+)$/", $test_route->getRegex()['regex']);
        
        //and additional info
        $this->assertEquals(['username', 'post'], $test_route->getRegex()['params']);
        
        $test_partregex_route = new Route("/user/new/{address:email}", function() {
            throw new \Exception("Bad Test!");
        });
        
        //check the generated regex
        $this->assertEquals($test_partregex_route->getRegex()['regex'], "/^\/user\/new\/(([a-zA-Z0-9_\\-.+]+)\\@([a-zA-Z0-9-]+)\\.([a-zA-Z]+)(\\.([a-zA-Z]+)?))$/");
        
        //and additional info
        $this->assertEquals($test_partregex_route->getRegex()['params'], ['address']);
    }
    
    public function testFailbackRouter() {
        $not_found = new Route(Route::NOT_FOUND, function() {
            throw new \Exception("Bad Test!");
        });
        
        //check the generated regex
        $this->assertEquals('', $not_found->getRegex()['regex']);
        $this->assertEquals(2, count($not_found->getRegex()));
        $this->assertEquals(Route::NOT_FOUND, $not_found->isSpecialCallback());
    }
    
    public function testMatchingRouter() {
        //test an email
        $email_route = new Route("/send/{address:email}", function () {
            throw new \Exception("Bad Test!");
        });
        
        $email_match = $email_route->matchURI("/send/test3m4il@sp4c3.co.uk", 'GET');
        
        $this->assertEquals(
                new \Gishiki\Algorithms\Collections\GenericCollection(["address" => "test3m4il@sp4c3.co.uk"]),
                $email_match);
        
        //test using a number
        $number_route = new Route("MyNumber/{random:number}", function () {
            throw new \Exception("Bad Test!");
        });
        
        $random_number = '-'.strval(rand());
        
        $number_match = $number_route->matchURI("MyNumber/".$random_number, 'GET');
        
        $this->assertEquals(
                new \Gishiki\Algorithms\Collections\GenericCollection(["random" => $random_number]),
                $number_match);
    }
    
}