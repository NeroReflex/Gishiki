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

namespace Gishiki\tests\Core;

use Gishiki\Core\Route;
use Gishiki\HttpKernel\Request;
use Gishiki\HttpKernel\Response;
use Gishiki\Core\Environment;
use Gishiki\Algorithms\Collections\SerializableCollection;
use Gishiki\Algorithms\Collections\GenericCollection;

/**
 * The tester for the Route class.
 *
 * Used to test every feature of the router
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class RouteTest extends \PHPUnit_Framework_TestCase
{
    public function testRegexRouter()
    {
        $test_route = new Route('/user/{username}/post/{post:number}', function () {
            throw new \Exception('Bad Test!');
        });

        //check the generated regex
        $this->assertEquals("/^\/user\/([^\/]+)\/post\/((\\+|\\-)?(\\d)+)$/", $test_route->getRegex()['regex']);

        //and additional info
        $this->assertEquals(['username', 'post'], $test_route->getRegex()['params']);

        $test_partregex_route = new Route('/user/new/{address:email}', function () {
            throw new \Exception('Bad Test!');
        });

        //check the generated regex
        $this->assertEquals($test_partregex_route->getRegex()['regex'], "/^\/user\/new\/(([a-zA-Z0-9_\\-.+]+)\\@([a-zA-Z0-9-]+)\\.([a-zA-Z]+)((\\.([a-zA-Z]+))?))$/");

        //and additional info
        $this->assertEquals($test_partregex_route->getRegex()['params'], ['address']);
    }

    public function testFailbackRouter()
    {
        $not_found = new Route(Route::NOT_FOUND, function () {
            throw new \Exception('Bad Test!');
        });

        //check the generated regex
        $this->assertEquals('', $not_found->getRegex()['regex']);
        $this->assertEquals(4, count($not_found->getRegex()));
        $this->assertEquals(Route::NOT_FOUND, $not_found->isSpecialCallback());
    }

    public function testMatchingRouter()
    {
        //test an email
        $email_route = new Route('/send/{address:email}', function () {
            throw new \Exception('Bad Test!');
        });

        //test some email address
        $this->assertEquals(
                new SerializableCollection(['address' => 'test_ing+s3m4il@sp4c3.com']),
                $email_route->matchURI('/send/test_ing+s3m4il@sp4c3.com', 'GET'));
        $this->assertEquals(
                new SerializableCollection(['address' => 'test3m4il@sp4c3.co.uk']),
                $email_route->matchURI('/send/test3m4il@sp4c3.co.uk', 'GET'));
        $this->assertEquals(
                new SerializableCollection(['address' => 'benato.denis96@gmail.com']),
                $email_route->matchURI('/send/benato.denis96@gmail.com', 'GET'));

        //test using a number
        $number_route = new Route('/MyNumber/{random:number}', function () {
            throw new \Exception('Bad Test!');
        });

        $random_number = '-'.strval(rand());

        $this->assertEquals(
                new SerializableCollection(['random' => $random_number]),
                $number_route->matchURI('/MyNumber/'.$random_number, 'GET'));
    }

    public function testBrokenRoute()
    {
        $number_route = new Route('/MyNumber/{random:number}', function () {
            throw new \Exception('Bad Test!');
        });

        $random_number = strval(rand());

        $this->assertEquals(
                null,
                $number_route->matchURI('/MyNum/problem/ber/'.$random_number, 'GET'));
    }

    public function testMultipleMatching()
    {
        $email_route = new Route('/send/{address:email}/{test}/{test_num:inteGer}', function () {
            throw new \Exception('Bad Test!');
        });

        //test the multiple rules matcher
        $this->assertEquals(
            $email_route->getRegex()['regex'],
            '/^\/send\/(([a-zA-Z0-9_\-.+]+)\@([a-zA-Z0-9-]+)\.([a-zA-Z]+)((\.([a-zA-Z]+))?))\/([^\/]+)\/((\+|\-)?(\d)+)$/');

        $this->assertEquals(
                new SerializableCollection([
                    'address' => 'test_ing+s3m4il@sp4c3.com',
                    'test' => 'uuuuh... likeit! :)',
                    'test_num' => 32, ]),
                $email_route->matchURI('/send/test_ing+s3m4il@sp4c3.com/uuuuh... likeit! :)/+32', 'GET'));
    }

    public function testTypeHandler()
    {
        $email_route = new Route('/send/{address:email}/{test}/{test_num:inteGer}/{another_mail:mail}', function () {
            throw new \Exception('Bad Test!');
        });

        //test the multiple rules matcher
        $this->assertEquals(
            4,
            count($email_route->getRegex()['param_types']));
        $this->assertEquals(
            ['email', 'default', 'signed_integer', 'email'],
            $email_route->getRegex()['param_types']);
    }

    public function testRouteExecution()
    {
        $this->setUp();

        $test_route = new Route('/add/{num_1:integer}/{num_2:integer}', function (Request $request, Response &$response, SerializableCollection &$params) {
            $result = $params->num_1 + $params->num_2;

            $response->write(strval($result));
            $response = $response->withStatus(500);
        });

        $match_result = $test_route->matchURI('/add/+59/-9', Route::GET);

        $this->assertEquals(
            new SerializableCollection([
                    'num_1' => +59,
                    'num_2' => -9, ]),
            $match_result
        );

        $env = Environment::mock([
            'SCRIPT_NAME' => '/foo/bar/index.php',
            'REQUEST_URI' => '/foo/bar?abc=123',
        ]);
        $request = Request::createFromEnvironment($env);
        $response = new Response();
        $test_route($request, $response, $match_result);

        $body = $response->getBody();
        $body->rewind();
        $data = '';
        while (!$body->eof()) {
            $data .= $body->read(1);
        }

        $this->assertEquals(500, $response->getStatusCode());

        $this->assertEquals(50, intval($data));
    }

    public function testFullRouterExecution()
    {
        $this->setUp();

        $env = Environment::mock([
            'SCRIPT_NAME' => '/index.php',
            'REQUEST_URI' => '/23/post/testing',
        ]);
        $reqestToFulfill = Request::createFromEnvironment($env);

        Route::post('/should_not_match', function (Request &$request, Response &$response, SerializableCollection &$collection) {
            $response->write('Bad error!');
        });

        Route::get('/{user_mail:email}/post/{postname}', function (Request &$request, Response &$response, SerializableCollection &$collection) {
            $response->write('Bad error!');
        });

        Route::match([Route::GET], '/{user_id:number}/post/{postname}', function (Request &$request, Response &$response, SerializableCollection &$collection) {
            $response->write('Searched post '.$collection->postname.' by user '.$collection->user_id);
        });

        Route::head('/{user_id:number}/post/{postname}', function (Request &$request, Response &$response, SerializableCollection &$collection) {
            $response->write('Created at: '.time());
        });

        Route::delete('/{user_id:number}/post/{postname}', function (Request &$request, Response &$response, SerializableCollection &$collection) {
            $response->write('It is not possible to remove posts by user '.$collection->user_id);
        });

        $responseFilled = Route::run($reqestToFulfill);

        $body = $responseFilled->getBody();
        $body->rewind();
        $data = '';
        while (!$body->eof()) {
            $data .= $body->read(1);
        }

        $this->assertEquals('Searched post testing by user 23', $data);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBadMatchParams()
    {
        Route::match(Route::GET, '/{user_id:number}/post/{postname}', function (Request &$request, Response &$response, SerializableCollection &$collection) {
            $response->write('Searched post '.$collection->postname.' by user '.$collection->user_id);
        });
    }

    public function testReturningAddress()
    {
        $test_route = new Route('/add/{num_1:integer}/{num_2:integer}', function (Request $request, Response &$response, SerializableCollection &$params) {
            $result = $params->num_1 + $params->num_2;

            $response->write(strval($result));
            $response = $response->withStatus(500);
        });

        $this->assertSame($test_route, Route::addRoute($test_route));
    }

    public function testSpecialRouting()
    {
        $this->setUp();

        $env = Environment::mock([
            'SCRIPT_NAME' => '/index.php',
            'REQUEST_URI' => '/this_cannot_be_found',
        ]);
        $reqestToFulfill = Request::createFromEnvironment($env);

        Route::put('/should_not_match', function (Request &$request, Response &$response, GenericCollection &$collection) {
            $response->write('Bad error!');
        });

        Route::get('/{user_mail:email}/post/{postname}', function (Request &$request, Response &$response, GenericCollection &$collection) {
            $response->write('Bad error!');
        });

        Route::match([Route::GET], '/{user_id:number}/post/{postname}', function (Request &$request, Response &$response, GenericCollection &$collection) {
            $response->write('Searched post '.$collection->postname.' by user '.$collection->user_id);
        });

        Route::head('/{user_id:number}/post/{postname}', function (Request &$request, Response &$response, GenericCollection &$collection) {
            $response->write('Created at: '.time());
        });

        Route::delete('/{user_id:number}/post/{postname}', function (Request &$request, Response &$response, SerializableCollection &$collection) {
            $response->write('It is not possible to remove posts by user '.$collection->user_id);
        });

        Route::any(Route::NOT_FOUND, function (&$request, &$response) {
            $response->write('404 Not Found');
        });

        $responseFilled = Route::run($reqestToFulfill);

        $body = $responseFilled->getBody();
        $body->rewind();
        $data = '';
        while (!$body->eof()) {
            $data .= $body->read(1);
        }

        $this->assertEquals('404 Not Found', $data);
    }

    public function testControllerRouting()
    {
        $this->setUp();

        $env = Environment::mock([
            'SCRIPT_NAME' => '/index.php',
            'REQUEST_URI' => '/benato.denis96@gmail.com',
        ]);
        $reqestToFulfill = Request::createFromEnvironment($env);

        Route::get('/{mail:email}', 'Gishiki\tests\Application\FakeController->myAction');

        $responseFilled = Route::run($reqestToFulfill);

        $body = $responseFilled->getBody();
        $body->rewind();
        $data = '';
        while (!$body->eof()) {
            $data .= $body->read(1);
        }

        $this->assertEquals('My email is: benato.denis96@gmail.com', $data);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNonexistentControllerRouting()
    {
        $this->setUp();

        $env = Environment::mock([
            'SCRIPT_NAME' => '/index.php',
            'REQUEST_URI' => '/benato.denis96@gmail.com/bad',
        ]);
        $reqestToFulfill = Request::createFromEnvironment($env);

        Route::get('/{mail:email}/bad', 'badController->badAction');

        //this will trigger the expected exception: no class badController!
        Route::run($reqestToFulfill);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBadNameControllerRouting()
    {
        $this->setUp();

        $env = Environment::mock([
            'SCRIPT_NAME' => '/index.php',
            'REQUEST_URI' => '/benato.denis96@gmail.com/badname',
        ]);
        $reqestToFulfill = Request::createFromEnvironment($env);

        Route::get('/{mail:email}/badname', 'badController->');

        //this will trigger the expected exception: no class badController!
        Route::run($reqestToFulfill);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBadControllerIdentifierRouting()
    {
        $this->setUp();

        $env = Environment::mock([
            'SCRIPT_NAME' => '/index.php',
            'REQUEST_URI' => '/benato.denis96@gmail.com/badid',
        ]);
        $reqestToFulfill = Request::createFromEnvironment($env);

        Route::get('/{mail:email}/badid', 'badController');

        //this will trigger the expected exception: no class badController!
        Route::run($reqestToFulfill);
    }

    public function testControllerStaticInvokationRouting()
    {
        $this->setUp();

        $env = Environment::mock([
            'SCRIPT_NAME' => '/index.php',
            'REQUEST_URI' => '/t3st1n9@fake.co.uk/quick',
        ]);
        $reqestToFulfill = Request::createFromEnvironment($env);

        Route::get('/{mail:email}/quick', 'Gishiki\tests\Application\FakeController::quickAction');

        $responseFilled = Route::run($reqestToFulfill);

        $body = $responseFilled->getBody();
        $body->rewind();
        $data = '';
        while (!$body->eof()) {
            $data .= $body->read(1);
        }

        $this->assertEquals('should I send an email to t3st1n9@fake.co.uk?', $data);
    }
}
