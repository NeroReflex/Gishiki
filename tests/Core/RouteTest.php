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
use Gishiki\Algorithms\Collections\GenericCollection;

class RouteTest extends \PHPUnit_Framework_TestCase {
    public function testRegexRouter() {
        $test_route = new Route("/user/{username}/post/{post:number}", function() {
            throw new \Exception("Bad Test!");
        });
        
        //check the generated regex
        $this->assertEquals("/^\/user\/([^\/]+)\/post\/((\\+|\\-)?(\\d)+)$/", $test_route->getRegex()['regex']);
        
        //and additional info
        $this->assertEquals(['username', 'post'], $test_route->getRegex()['params']);
        
        $test_partregex_route = new Route("/user/new/{address:email}", function() {
            throw new \Exception("Bad Test!");
        });
        
        //check the generated regex
        $this->assertEquals($test_partregex_route->getRegex()['regex'], "/^\/user\/new\/(([a-zA-Z0-9_\\-.+]+)\\@([a-zA-Z0-9-]+)\\.([a-zA-Z]+)((\\.([a-zA-Z]+))?))$/");
        
        //and additional info
        $this->assertEquals($test_partregex_route->getRegex()['params'], ['address']);
    }
    
    public function testFailbackRouter() {
        $not_found = new Route(Route::NOT_FOUND, function() {
            throw new \Exception("Bad Test!");
        });
        
        //check the generated regex
        $this->assertEquals('', $not_found->getRegex()['regex']);
        $this->assertEquals(3, count($not_found->getRegex()));
        $this->assertEquals(Route::NOT_FOUND, $not_found->isSpecialCallback());
    }
    
    public function testMatchingRouter() {
        //test an email
        $email_route = new Route("/send/{address:email}", function () {
            throw new \Exception("Bad Test!");
        });
        
        //test some email address
        $this->assertEquals(
                new GenericCollection(["address" => "test_ing+s3m4il@sp4c3.com"]),
                $email_route->matchURI("/send/test_ing+s3m4il@sp4c3.com", 'GET'));
        $this->assertEquals(
                new GenericCollection(["address" => "test3m4il@sp4c3.co.uk"]),
                $email_route->matchURI("/send/test3m4il@sp4c3.co.uk", 'GET'));
        $this->assertEquals(
                new GenericCollection(["address" => "benato.denis96@gmail.com"]),
                $email_route->matchURI("/send/benato.denis96@gmail.com", 'GET'));
        
        //test using a number
        $number_route = new Route("/MyNumber/{random:number}", function () {
            throw new \Exception("Bad Test!");
        });
        
        $random_number = '-'.strval(rand());
        
        $this->assertEquals(
                new GenericCollection(["random" => $random_number]),
                $number_route->matchURI("/MyNumber/".$random_number, 'GET'));
    }
    
    public function testBrokenRoute() {
        //test using a number
        $number_route = new Route("/MyNumber/{random:number}", function () {
            throw new \Exception("Bad Test!");
        });
        
        $random_number = strval(rand());
        
        $this->assertEquals(
                null,
                $number_route->matchURI("/MyNum/problem/ber/".$random_number, 'GET'));
    }
    
    public function testMultipleMatching() {
        //test an email
        $email_route = new Route("/send/{address:email}/{test}/{test_num:inteGer}", function () {
            throw new \Exception("Bad Test!");
        });
        
        //test the multiple rules matcher
        $this->assertEquals(
            $email_route->getRegex()['regex'],
            '/^\/send\/(([a-zA-Z0-9_\-.+]+)\@([a-zA-Z0-9-]+)\.([a-zA-Z]+)((\.([a-zA-Z]+))?))\/([^\/]+)\/((\+|\-)?(\d)+)$/');
        
        $this->assertEquals(
                new GenericCollection([
                    "address" => "test_ing+s3m4il@sp4c3.com",
                    "test" => "uuuuh... likeit!",
                    "test_num" => "+32"]),
                $email_route->matchURI("/send/test_ing+s3m4il@sp4c3.com/uuuuh... likeit!/+32", 'GET'));
    }
}