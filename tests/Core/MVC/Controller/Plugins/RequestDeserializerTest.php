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
use Gishiki\Core\MVC\Controller\Plugins\RequestDeserializer;
use Symfony\Component\Yaml\Yaml;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;

use PHPUnit\Framework\TestCase;

/**
 * The tester for the RequestDeserializer plugin.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class RequestDeserializerTest extends TestCase
{
    public function testDeserializationWithNoContentType()
    {
        $data = \FakeController::generateTestingData();

        $request = new Request();
        $request->getBody()->write(
            json_encode($data)
        );
        $request->getBody()->rewind();

        $response = new Response();

        $collection = new GenericCollection([]);
        $plugins = [
            RequestDeserializer::class
        ];

        $controller = new \FakeController($request, $response, $collection, $plugins);

        $this->expectException(ControllerException::class);

        $controller->getRequestDeserialized();
    }

    public function testJsonDeserialization()
    {
        $data = \FakeController::generateTestingData();

        $request = new Request();
        $request = $request->withHeader('Content-Type', 'application/json;charset=utf-8');
        $request->getBody()->write(
            json_encode($data)
        );
        $request->getBody()->rewind();

        $response = new Response();

        $collection = new GenericCollection([]);
        $plugins = [
            RequestDeserializer::class
        ];

        $controller = new \FakeController($request, $response, $collection, $plugins);

        $this->assertEquals($data, $controller->getRequestDeserialized()->all());
    }

    public function testYamlDeserialization()
    {
        $data = \FakeController::generateTestingData();

        $request = new Request();
        $request = $request->withHeader('Content-Type', 'text/x-yaml');
        $request->getBody()->write(
            Yaml::dump($data)
        );
        $request->getBody()->rewind();

        $response = new Response();

        $collection = new GenericCollection([]);
        $plugins = [
            RequestDeserializer::class
        ];

        $controller = new \FakeController($request, $response, $collection, $plugins);

        $this->assertEquals($data, $controller->getRequestDeserialized()->all());
    }

    public function testXmlDeserialization()
    {
        $data = \FakeController::generateTestingData();

        $xml = new SerializableCollection($data);

        $request = new Request();
        $request = $request->withHeader('Content-Type', 'text/xml');
        $request->getBody()->write(
            $xml->serialize(SerializableCollection::XML)
        );
        $request->getBody()->rewind();

        $response = new Response();

        $collection = new GenericCollection([]);
        $plugins = [
            RequestDeserializer::class
        ];

        $controller = new \FakeController($request, $response, $collection, $plugins);

        $this->assertEquals($data, $controller->getRequestDeserialized()->all());
    }

    public function testMultipartDeserialization()
    {
        $request = new Request();
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request->getBody()->write(
            "id=3&test_str=fdaffe+ccco"
        );
        $request->getBody()->rewind();

        $response = new Response();

        $collection = new GenericCollection([]);
        $plugins = [
            RequestDeserializer::class
        ];

        $controller = new \FakeController($request, $response, $collection, $plugins);

        $this->assertEquals([
            "id" => 3,
            "test_str" => "fdaffe ccco"
        ], $controller->getRequestDeserialized()->all());
    }

    public function testBadDeserialization()
    {
        $data = \FakeController::generateTestingData();

        $xml = new SerializableCollection($data);

        $request = new Request();
        $request = $request->withHeader('Content-Type', 'text/json');
        $request->getBody()->write(
            $xml->serialize(SerializableCollection::XML)
        );
        $request->getBody()->rewind();

        $response = new Response();

        $collection = new GenericCollection([]);
        $plugins = [
            RequestDeserializer::class
        ];

        $controller = new \FakeController($request, $response, $collection, $plugins);

        $this->expectException(ControllerException::class);

        $controller->getRequestDeserialized();
    }

    public function testUnknownFormatDeserialization()
    {
        $request = new Request();
        $request = $request->withHeader('Content-Type', 'unknownw/nonw');
        $request->getBody()->write(
            "hello=>7"
        );
        $request->getBody()->rewind();

        $response = new Response();

        $collection = new GenericCollection([]);
        $plugins = [
            RequestDeserializer::class
        ];

        $controller = new \FakeController($request, $response, $collection, $plugins);

        $this->expectException(ControllerException::class);

        $controller->getRequestDeserialized();
    }
}