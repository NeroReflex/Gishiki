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

use Gishiki\Core\Router\RouterException;
use Gishiki\Algorithms\Collections\GenericCollection;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use Gishiki\Core\Router\Route;

use PHPUnit\Framework\TestCase;

/**
 * The tester for the Route class.
 *
 * Used to test every feature of the route component
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class RouteTest extends TestCase
{

    public function testRoute()
    {
        $verb = Route::GET;
        $uri = "/index";
        $status = Route::OK;

        $route = new Route([
            "verbs" => [
                $verb
            ],
            "uri" => $uri,
            "status" => $status,
            "controller" => "FakeController",
            "action" => 'none',
        ]);

        $this->assertEquals([$verb], $route->getMethods());
        $this->assertEquals($uri, $route->getURI());
        $this->assertEquals($status, $route->getStatus());
    }

    public function testRouteBadVerb()
    {
        $this->expectException(RouterException::class);

        new Route([
            "verbs" => 'hello',
            "uri" => "/",
            "status" => 200,
            "controller" => \FakeController::class,
            "action" => 'none',
        ]);
    }

    public function testRouteBadUri()
    {
        $this->expectException(RouterException::class);

         new Route([
            "verbs" => [ Route::GET ],
            "uri" => null,
            "status" => 200,
            "controller" => "FakeController",
            "action" => 'none',
        ]);
    }

    public function testRouteBadStatus()
    {
        $this->expectException(RouterException::class);

        new Route([
            "verbs" => [ Route::GET ],
            "uri" => "/",
            "status" => ":( I shouldn't be here",
            "controller" => \FakeController::class,
            "action" => 'none',
        ]);
    }

    public function testRouteBadController()
    {
        $this->expectException(RouterException::class);

        new Route([
            "verbs" => [ Route::GET ],
            "uri" => "/",
            "status" => 200,
            "controller" => "I don't exists :)",
            "action" => 'none',
        ]);
    }

    public function testRouteBadAction()
    {
        $this->expectException(RouterException::class);

        new Route([
            "verbs" => [ Route::GET ],
            "uri" => "/",
            "status" => 200,
            "controller" => "I don't exists :)",
            "action" => 'none',
        ]);
    }

    public function testRouteInvoke()
    {
        $verb = Route::GET;
        $uri = "/do";
        $status = Route::NOT_ALLOWED;

        $route = new Route([
            "verbs" => [
                $verb
            ],
            "uri" => $uri,
            "status" => $status,
            "controller" => \FakeController::class,
            "action" => 'do',
        ]);

        $this->assertEquals([$verb], $route->getMethods());
        $this->assertEquals($uri, $route->getURI());
        $this->assertEquals($status, $route->getStatus());

        //generate a request to be passed
        $request = new Request(
            $uri,
            'GET',
            'php://memory',
            []
        );

        //generate a response that will be changed
        $response = new Response();

        //generate a meaningless collection to be passed
        $coll = new GenericCollection();

        $route($request, $response, $coll);

        $body = $response->getBody();
        $body->rewind();

        $output = $body->getContents();

        $this->assertEquals('Th1s 1s 4 t3st', $output);
    }

    public function testRouteInvokeWithParam()
    {
        $value = "example.mail@gmail.com";

        $route = new Route([
            "verbs" => [
                Route::GET, Route::POST
            ],
            "uri" => "/mail",
            "status" => Route::OK,
            "controller" => \FakeController::class,
            "action" => 'myAction',
        ]);

        $this->assertEquals([  Route::GET, Route::POST ], $route->getMethods());
        $this->assertEquals("/mail", $route->getURI());
        $this->assertEquals(Route::OK, $route->getStatus());

        //generate a request to be passed
        $request = new Request(
            'https://example.com:443/main/',
            'GET',
            'php://memory',
            []
        );

        //generate a response that will be changed
        $response = new Response();

        //generate a meaningless collection to be passed
        $coll = new GenericCollection([
            "mail" => $value
        ]);

        $route($request, $response, $coll);

        $body = $response->getBody();
        $body->rewind();
        $this->assertEquals("My email is: ".$value, $body->getContents());

        $this->assertEquals(200, $response->getStatusCode());
    }
}