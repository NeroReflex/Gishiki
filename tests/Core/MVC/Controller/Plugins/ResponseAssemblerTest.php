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

namespace Gishiki\tests\Core\MVC\Controller\Plugins;

use Gishiki\Algorithms\Collections\GenericCollection;
use Gishiki\Algorithms\Collections\SerializableCollection;
use Gishiki\Core\MVC\Controller\ControllerException;
use Gishiki\Core\MVC\Controller\Plugins\ResponseAssembler;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;

use PHPUnit\Framework\TestCase;

/**
 * The tester for the ResponseAssembler plugin.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class ResponseAssemblerTest extends TestCase
{
    public function testAssemble()
    {
        $request = new Request();
        $response = new Response();
        $collection = new GenericCollection([]);
        $plugins = [
            ResponseAssembler::class
        ];

        $controller = new \FakeController($request, $response, $collection, $plugins);
        $controller->assemblyWith(function (RequestInterface &$request, ResponseInterface &$response, SerializableCollection &$collection) {
            $collection->set('test1', 2);
        });
        $controller->assemblyWith(function (RequestInterface &$request, ResponseInterface &$response, SerializableCollection &$collection) {
            $collection->set('test2', 10.5);
        });
        $this->assertEquals([
            "test1" => 2,
            "test2" => 10.5,
            ], $controller->assembly()->all());
    }

    public function testBadParamAssemble()
    {
        $request = new Request();
        $response = new Response();
        $collection = new GenericCollection([]);
        $plugins = [
            ResponseAssembler::class
        ];

        $this->expectException(ControllerException::class);

        $controller = new \FakeController($request, $response, $collection, $plugins);
        $controller->assemblyWith(function (RequestInterface &$request, RequestInterface &$response) {

        });
    }
}