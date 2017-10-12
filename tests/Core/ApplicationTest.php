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

use Gishiki\Core\Application;

use Gishiki\Core\Router\Route;
use Gishiki\Core\Router\Router;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Request;
use Zend\Diactoros\Uri;

/**
 * The tester for the Application class.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class ApplicationTest extends TestCase
{
    public function testDirectory()
    {
        $app = new Application();

        $directory = new \ReflectionProperty($app, 'currentDirectory');
        $directory->setAccessible(true);

        //appending ../../ because the test MUST be launched at the project root
        $this->assertEquals(realpath(__DIR__.'/../../'), realpath($directory->getValue($app)));
    }

    public function testCompleteApplication()
    {
        copy(__DIR__."/../testSettings.json", __DIR__."/../../settings.json");
        $app = new Application();
        unlink(__DIR__."/../../settings.json");

        $router = new Router();
        $router->add(new Route([
            "verbs" => [
                Route::DELETE
            ],
            "uri" => "/bye/{name:str}",
            "status" => Route::OK,
            "controller" => "FakeController",
            "action" => "completeTest"
        ]));

        $request = new \ReflectionProperty($app, 'request');
        $request->setAccessible(true);

        $testRequest = new Request();
        $testRequest = $testRequest->withMethod('DELETE');
        $uri = new Uri();
        $uri = $uri->withHost('www.testingsite.com');
        $uri = $uri->withPort(80);
        $uri = $uri->withPath('/bye/Mario');
        $testRequest = $testRequest->withUri($uri);

        $request->setValue($app, $testRequest);

        $response = new \ReflectionProperty($app, 'response');
        $response->setAccessible(true);

        $app->run($router);

        $emitter = $app->emit('TestingEmitter');

        $this->assertEquals('bye bye Mario', $emitter->getBodyContent());
    }

    public function testRouteDefaultNotAllowed()
    {
        copy(__DIR__."/../testSettings.json", __DIR__."/../../settings.json");
        $app = new Application();
        unlink(__DIR__."/../../settings.json");

        $router = new Router();

        $router->add(new Route([
            "verbs" => [
                Route::GET
            ],
            "uri" => "/notAllowed",
            "status" => Route::OK,
            "controller" => "FakeController",
            "action" => "none"
        ]));

        $request = new \ReflectionProperty($app, 'request');
        $request->setAccessible(true);

        $testRequest = new Request();
        $testRequest = $testRequest->withMethod('PATCH');
        $uri = new Uri();
        $uri = $uri->withHost('www.testingsite.com');
        $uri = $uri->withPort(80);
        $uri = $uri->withPath('/notAllowed');
        $testRequest = $testRequest->withUri($uri);

        $request->setValue($app, $testRequest);

        $response = new \ReflectionProperty($app, 'response');
        $response->setAccessible(true);

        $app->run($router);

        $emitter = $app->emit(\TestingEmitter::class);

        $this->assertEquals('405 - Not Allowed', $emitter->getBodyContent());
        $this->assertEquals(Route::NOT_ALLOWED, $emitter->getStatusCode());
    }

    public function testRouteDefaultNotFound()
    {
        copy(__DIR__."/../testSettings.json", __DIR__."/../../settings.json");
        $app = new Application();
        unlink(__DIR__."/../../settings.json");

        $router = new Router();

        $request = new \ReflectionProperty($app, 'request');
        $request->setAccessible(true);

        $testRequest = new Request();
        $testRequest = $testRequest->withMethod('OPTIONS');
        $uri = new Uri();
        $uri = $uri->withHost('www.testingsite.com');
        $uri = $uri->withPort(80);
        $uri = $uri->withPath('/notFound');
        $testRequest = $testRequest->withUri($uri);

        $request->setValue($app, $testRequest);

        $response = new \ReflectionProperty($app, 'response');
        $response->setAccessible(true);

        $app->run($router);

        $emitter = $app->emit(\TestingEmitter::class);

        $this->assertEquals('404 - Not Found', $emitter->getBodyContent());
        $this->assertEquals(Route::NOT_FOUND, $emitter->getStatusCode());
    }

    public function testCompleteApplicationBadEmitter()
    {
        copy(__DIR__."/../testSettings.json", __DIR__."/../../settings.json");
        $app = new Application();
        unlink(__DIR__."/../../settings.json");

        $router = new Router();
        $router->add(new Route([
            "verbs" => [
                Route::DELETE
            ],
            "uri" => "/bye/{name:str}",
            "status" => Route::OK,
            "controller" => "FakeController",
            "action" => "completeTest"
        ]));

        $request = new \ReflectionProperty($app, 'request');
        $request->setAccessible(true);

        $testRequest = new Request();
        $testRequest = $testRequest->withMethod('DELETE');
        $uri = new Uri();
        $uri = $uri->withHost('www.testingsite.com');
        $uri = $uri->withPort(80);
        $uri = $uri->withPath('/bye/Mario');
        $testRequest = $testRequest->withUri($uri);

        $request->setValue($app, $testRequest);

        $response = new \ReflectionProperty($app, 'response');
        $response->setAccessible(true);

        $app->run($router);

        $this->expectException(\Exception::class);

        $emitter = $app->emit('BadEmitter');
    }

    public function testException()
    {
        copy(__DIR__."/../testSettings.json", __DIR__."/../../settings.json");
        $app = new Application();
        unlink(__DIR__."/../../settings.json");

        $router = new Router();
        $router->add(new Route([
            "verbs" => [
                Route::DELETE
            ],
            "uri" => "/test",
            "status" => Route::OK,
            "controller" => "FakeController",
            "action" => "exceptionTest"
        ]));

        $request = new \ReflectionProperty($app, 'request');
        $request->setAccessible(true);

        $testRequest = new Request();
        $testRequest = $testRequest->withMethod('DELETE');
        $uri = new Uri();
        $uri = $uri->withHost('www.testingsite.com');
        $uri = $uri->withPort(80);
        $uri = $uri->withPath('/test');
        $testRequest = $testRequest->withUri($uri);

        $request->setValue($app, $testRequest);

        $response = new \ReflectionProperty($app, 'response');
        $response->setAccessible(true);

        file_put_contents(__DIR__."/../customLog.log", "");

        $app->run($router);

        $this->assertGreaterThan(20, strlen(file_get_contents(__DIR__."/../customLog.log")));
    }
}