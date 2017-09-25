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

namespace Gishiki\tests\Core\Router;

use Gishiki\Core\Router\Router;
use Gishiki\Core\Router\Route;

use PHPUnit\Framework\TestCase;

/**
 * The tester for the Router class.
 *
 * Used to test every feature of the router component
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class RouterTest extends TestCase
{
    public function testStrangeMatch()
    {
        $router = new Router();

        $expr = null;

        $result = $router->matches("/home", "/", $expr);

        $this->assertEquals(false, $result);
    }

    public function testBadUrl()
    {
        $router = new Router();

        $expr = null;

        $this->expectException(\InvalidArgumentException::class);
        $router->matches(null, "/", $expr);
    }

    public function testBadUri()
    {
        $router = new Router();

        $expr = null;

        $this->expectException(\InvalidArgumentException::class);
        $router->matches("/home", null, $expr);
    }

    public function testStatic()
    {
        $router = new Router();

        $expr = null;

        $this->assertEquals(true, $router->matches("/home/hello/test", "/home/hello/test", $expr));
    }

    public function testDynamicEmail()
    {
        $router = new Router();

        $expr = null;

        $this->assertEquals(true, $router->matches("/email/{address:email}", "/email/example@gmail.com", $expr));
        $this->assertEquals([
            "address" => "example@gmail.com"
        ], $expr);
    }

    public function testDynamicUint()
    {
        $router = new Router();

        $expr = null;

        $this->assertEquals(true, $router->matches("/uint/{number:uint}", "/uint/54", $expr));
        $this->assertEquals([
            "number" => 54
        ], $expr);
    }

    public function testDynamicSint()
    {
        $router = new Router();

        $expr = null;

        $this->assertEquals(true, $router->matches("/uint/{number:int}", "/uint/-55", $expr));
        $this->assertEquals([
            "number" => -55
        ], $expr);
    }

    public function testDynamicFloat()
    {
        $router = new Router();

        $expr = null;

        $this->assertEquals(true, $router->matches("/uint/{number:float}", "/uint/-55.25", $expr));
        $this->assertEquals([
            "number" => -55.25
        ], $expr);
    }
}