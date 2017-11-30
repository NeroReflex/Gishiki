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

namespace Gishiki\tests\Core\MVC\Controller;

use Gishiki\Algorithms\Collections\GenericCollection;
use Gishiki\Core\MVC\Controller\ControllerException;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;

use PHPUnit\Framework\TestCase;

/**
 * The tester for the Controller class.
 *
 * Used to test every feature of the controller component
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class ControllerTest extends TestCase
{
    public function testInvalidPlugins()
    {
        $this->expectException(ControllerException::class);

        $request = new Request();
        $response = new Response();
        $collection = new GenericCollection([]);
        $plugins = [
            0 => \Gishiki\Core\MVC\Controller\Plugins\ResponseAssembler::class,
            1 => 'bye bye :)'
        ];

        new \FakeController($request, $response, $collection, $plugins);
    }

    public function testGetRequest()
    {
        $request = new Request();
        $request = $request->withHeader('Testing-Header', 'Active');
        $response = new Response();
        $collection = new GenericCollection([]);
        $plugins = [];

        $controller = new \FakeController($request, $response, $collection, $plugins);
        $this->assertEquals('Active', $controller->getRequest()->getHeader('Testing-Header')[0]);
    }

    public function testGetResponse()
    {
        $request = new Request();
        $response = new Response();
        $response = $response->withHeader('Testing-Header', 'Active');
        $collection = new GenericCollection([]);
        $plugins = [];

        $controller = new \FakeController($request, $response, $collection, $plugins);
        $this->assertEquals('Active', $controller->getResponse()->getHeader('Testing-Header')[0]);
    }

    public function testUnknownCall()
    {
        $request = new Request();
        $response = new Response();
        $collection = new GenericCollection([]);
        $plugins = [];

        $controller = new \FakeController($request, $response, $collection, $plugins);

        $this->expectException(ControllerException::class);

        $controller->thisDoesNotExists();
    }
}