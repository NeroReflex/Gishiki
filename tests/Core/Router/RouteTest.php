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
    public function testBadUriParamType()
    {
        $expr = [];
        $get = [];

        $route = new Route([
            "verbs" => [
                Route::GET, Route::POST
            ],
            "uri" => "/{my_string:bad}",
            "status" => Route::OK,
            "controller" => "FakeController",
            "action" => "quickAction"
        ]);

        $this->expectException(RouterException::class);
        $route->matches(Route::GET, "/whatever", $expr, $get);
    }

    public function testBadUrlInMatchURI()
    {
        $expr = [];
        $get = [];

        $this->expectException(\InvalidArgumentException::class);
        Route::matchURI("/", null, $expr, $get);
    }

    public function testBadUriInMatchURI()
    {
        $expr = [];
        $get = [];

        $this->expectException(\InvalidArgumentException::class);
        Route::matchURI(null, "/", $expr, $get);
    }

    public function testBadUrl()
    {
        $expr = [];
        $get = [];

        $route = new Route([
            "verbs" => [
                Route::GET, Route::POST
            ],
            "uri" => "/email/{mail:email}",
            "status" => Route::OK,
            "controller" => "FakeController",
            "action" => "quickAction"
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $route->matches(Route::GET, null, $expr, $get);
    }

    public function testBadController()
    {
        $this->expectException(RouterException::class);

        new Route([
            "verbs" => [
                Route::GET, Route::POST
            ],
            "uri" => "/email/{mail:email}",
            "status" => Route::OK,
            "controller" => 6,
            "action" => "quickAction"
        ]);
    }

    public function testBadAction()
    {
        $this->expectException(RouterException::class);

        new Route([
            "verbs" => [
                Route::GET, Route::POST
            ],
            "uri" => "/email/{mail:email}",
            "status" => Route::OK,
            "controller" => "FakeController",
            "action" => null
        ]);
    }

    public function testUnknownAction()
    {
        $this->expectException(RouterException::class);

        new Route([
            "verbs" => [
                Route::GET, Route::POST
            ],
            "uri" => "/email/{mail:email}",
            "status" => Route::OK,
            "controller" => "FakeController",
            "action" => "doSomething... PLS!"
        ]);
    }

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
            "plugins" => []
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

    public function testStrangeMatch()
    {
        $expr = [];
        $get = [];

        $route = new Route([
            "verbs" => [
                Route::GET
            ],
            "uri" => "/home",
            "status" => Route::OK,
            "controller" => \FakeController::class,
            "action" => 'do',
        ]);

        $this->assertEquals(false, $route->matches(Route::GET, "/", $expr, $get));
    }

    public function testStatic()
    {
        $expr = [];
        $get = [];

        $route = new Route([
            "verbs" => [
                Route::POST
            ],
            "uri" => "/home/hello/test",
            "status" => Route::OK,
            "controller" => \FakeController::class,
            "action" => 'do',
        ]);

        $this->assertEquals(true, $route->matches(Route::POST, "/home/hello/test", $expr, $get));
    }

    public function testDynamicEmail()
    {
        $expr = [];
        $get = [];

        $route = new Route([
            "verbs" => [
                Route::POST
            ],
            "uri" => "/email/{address:email}",
            "status" => Route::OK,
            "controller" => \FakeController::class,
            "action" => 'do',
        ]);

        $this->assertEquals(true, $route->matches(Route::POST, "/email/example@gmail.com", $expr, $get));
        $this->assertEquals([
            "address" => "example@gmail.com"
        ], $expr);
    }

    public function testDynamicUint()
    {
        $expr = [];
        $get = [];

        $route = new Route([
            "verbs" => [
                Route::POST
            ],
            "uri" => "/uint/{number:uint}",
            "status" => Route::OK,
            "controller" => \FakeController::class,
            "action" => 'do',
        ]);

        $this->assertEquals(true, $route->matches(Route::POST, "/uint/54", $expr, $get));
        $this->assertEquals([
            "number" => 54
        ], $expr);
    }

    public function testDynamicSint()
    {
        $expr = [];
        $get = [];

        $route = new Route([
            "verbs" => [
                Route::POST
            ],
            "uri" => "/sint/{number:int}",
            "status" => Route::OK,
            "controller" => \FakeController::class,
            "action" => 'do',
        ]);

        $this->assertEquals(true, $route->matches(Route::POST, "/sint/-55", $expr, $get));
        $this->assertEquals([
            "number" => -55
        ], $expr);
    }

    public function testDynamicString()
    {
        $expr = [];
        $get = [];

        $route = new Route([
            "verbs" => [
                Route::POST
            ],
            "uri" => "/hello/{name:str}",
            "status" => Route::OK,
            "controller" => \FakeController::class,
            "action" => 'do',
        ]);

        $this->assertEquals(true, $route->matches(Route::POST, "/hello/John", $expr, $get));
        $this->assertEquals([
            "name" => "John",
        ], $expr);
    }

    public function testDynamicFloat()
    {
        $expr = [];
        $get = [];

        $route = new Route([
            "verbs" => [
                Route::HEAD
            ],
            "uri" => "/float/{number:float}",
            "status" => Route::OK,
            "controller" => \FakeController::class,
            "action" => 'do',
        ]);

        $this->assertEquals(true, $route->matches(Route::HEAD, "/float/-55.25", $expr, $get));
        $this->assertEquals([
            "number" => -55.25
        ], $expr);
    }

    public function testDynamicComplex()
    {
        $expr = [];
        $get = [];

        $route = new Route([
            "verbs" => [
                Route::DELETE
            ],
            "uri" => "/cplx/{id:uint}/{mail:email}/set",
            "status" => Route::OK,
            "controller" => \FakeController::class,
            "action" => 'do',
        ]);

        $this->assertEquals(true, $route->matches(Route::DELETE, "/cplx/9/example@xmpl.com/set", $expr, $get));
        $this->assertEquals([
            "id" => 9,
            "mail" => "example@xmpl.com"
        ], $expr);
    }

    public function testDynamicBadSplitNumber()
    {
        $expr = [];
        $get = [];

        $route = new Route([
            "verbs" => [
                Route::GET
            ],
            "uri" => "/cplx/{id:uint}",
            "status" => Route::OK,
            "controller" => \FakeController::class,
            "action" => 'do',
        ]);

        $this->assertEquals(false, $route->matches(Route::GET, "/cplx", $expr, $get));
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

    public function testRouteInvokeWithGet()
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

        $freq = "onceaday";

        //generate a request to be passed
        $request = new Request(
            'https://example.com:443/mail/?frequency='.$freq,
            'GET',
            'php://memory',
            []
        );

        //generate a response that will be changed
        $response = new Response();

        $coll = new GenericCollection([
            "uri" => [
                "mail" => $value
            ],
            "get" => [
                "frequency" => $freq
            ]
        ]);

        $route($request, $response, $coll);

        $body = $response->getBody();
        $body->rewind();
        $this->assertEquals("My email is: ".$value." and I am accepting newsletter ".$freq, $body->getContents());

        $this->assertEquals(200, $response->getStatusCode());
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
            'https://example.com:443/mail/',
            'GET',
            'php://memory',
            []
        );

        //generate a response that will be changed
        $response = new Response();

        $coll = new GenericCollection([
            "uri" => [
                "mail" => $value
            ]
        ]);

        $route($request, $response, $coll);

        $body = $response->getBody();
        $body->rewind();
        $this->assertEquals("My email is: ".$value, $body->getContents());

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDynamicWithGet()
    {
        $expr = [];
        $get = [];

        $route = new Route([
            "verbs" => [
                Route::GET
            ],
            "uri" => "/search/{type:int}",
            "status" => Route::OK,
            "controller" => \FakeController::class,
            "action" => 'do',
        ]);

        $this->assertEquals(true, $route->matches(Route::GET, "/search/4/?distanceMax=50km&distanceMin=1km", $expr, $get));
        $this->assertEquals([
            "type" => 4
        ], $expr);
        $this->assertEquals([
            "distanceMax" => "50km",
            "distanceMin" => "1km",
        ], $get);
    }
}
