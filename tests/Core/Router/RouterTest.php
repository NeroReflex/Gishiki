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
use Gishiki\Core\Environment;
use Gishiki\HttpKernel\Headers;
use Gishiki\HttpKernel\Request;
use Gishiki\HttpKernel\RequestBody;
use Gishiki\HttpKernel\UploadedFile;
use Gishiki\HttpKernel\Uri;

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
    protected static function requestFactory($url)
    {
        $env = Environment::mock();

        $uri = Uri::createFromString($url);
        $headers = Headers::createFromEnvironment($env);
        $cookies = [
            'user' => 'john',
            'id' => '123',
        ];
        $serverParams = $env->all();
        $body = new RequestBody();
        $uploadedFiles = UploadedFile::createFromEnvironment($env);
        $request = new Request('GET', $uri, $headers, $cookies, $serverParams, $body, $uploadedFiles);

        return $request;
    }

    public function testCompleteRouting()
    {
        $route = new Route([
            "verbs" => [
                Route::GET, Route::POST
            ],
            "uri" => "/email/{mail:email}",
            "status" => Route::OK,
            "controller" => "FakeController",
            "action" => "quickAction"
        ]);

        $router = new Router();
        $router->register($route);

        $request = self::requestFactory('https://example.com:443/email/nicemail@live.com');

        $response = $router->run($request);
        $body = $response->getBody();
        $body->rewind();

        $this->assertEquals('should I send an email to nicemail@live.com?', $body->getContents());
    }

    public function testStrangeMatch()
    {
        $expr = null;

        $result = Router::matches("/home", "/", $expr);

        $this->assertEquals(false, $result);
    }

    public function testBadUrl()
    {
        $expr = null;

        $this->expectException(\InvalidArgumentException::class);
        Router::matches(null, "/", $expr);
    }

    public function testBadUri()
    {
        $expr = null;

        $this->expectException(\InvalidArgumentException::class);
        Router::matches("/home", null, $expr);
    }

    public function testStatic()
    {
        $expr = null;

        $this->assertEquals(true, Router::matches("/home/hello/test", "/home/hello/test", $expr));
    }

    public function testDynamicEmail()
    {
        $expr = null;

        $this->assertEquals(true, Router::matches("/email/{address:email}", "/email/example@gmail.com", $expr));
        $this->assertEquals([
            "address" => "example@gmail.com"
        ], $expr);
    }

    public function testDynamicUint()
    {
        $expr = null;

        $this->assertEquals(true, Router::matches("/uint/{number:uint}", "/uint/54", $expr));
        $this->assertEquals([
            "number" => 54
        ], $expr);
    }

    public function testDynamicSint()
    {
        $expr = null;

        $this->assertEquals(true, Router::matches("/sint/{number:int}", "/sint/-55", $expr));
        $this->assertEquals([
            "number" => -55
        ], $expr);
    }

    public function testDynamicFloat()
    {
        $expr = null;

        $this->assertEquals(true, Router::matches("/float/{number:float}", "/float/-55.25", $expr));
        $this->assertEquals([
            "number" => -55.25
        ], $expr);
    }

    public function testDynamicComplex()
    {
        $expr = null;

        $this->assertEquals(true, Router::matches("/cplx/{id:uint}/{mail:email}/set", "/cplx/9/example@xmpl.com/set", $expr));
        $this->assertEquals([
            "id" => 9,
            "mail" => "example@xmpl.com"
        ], $expr);
    }

    public function testDynamicBadSplitNumber()
    {
        $expr = null;

        $this->assertEquals(false, Router::matches("/cplx/{id:uint}", "/cplx", $expr));
    }
}