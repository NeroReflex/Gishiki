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
use Symfony\Component\Yaml\Yaml;
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
class RequestDeserializerTest extends TestCase
{
    public static function generateTestingData()
    {
        srand(null);

        $data = [
            "int_test" => rand(0, 150),
            "str_test" => base64_encode(openssl_random_pseudo_bytes(32)),
            "float_test" => rand(0, 3200) + (rand(0, 9) / 10),
            "array_test" => [
                base64_encode(openssl_random_pseudo_bytes(32)),
                base64_encode(openssl_random_pseudo_bytes(32)),
                base64_encode(openssl_random_pseudo_bytes(32)),
                base64_encode(openssl_random_pseudo_bytes(32))
                ],
        ];

        return $data;
    }

    public function testJsonDeserialization()
    {
        $data = self::generateTestingData();

        $request = new Request();
        $request = $request->withHeader('Content-Type', 'application/json;charset=utf-8');
        $request->getBody()->write(
            json_encode($data)
        );
        $request->getBody()->rewind();

        $response = new Response();

        $collection = new GenericCollection([]);
        $plugins = [
            0 => \Gishiki\Core\MVC\Controller\Plugins\RequestDeserializer::class
        ];

        $controller = new \FakeController($request, $response, $collection, $plugins);

        $this->assertEquals($data, $controller->getRequestDeserialized()->all());
    }

    public function testYamlDeserialization()
    {
        $data = self::generateTestingData();

        $request = new Request();
        $request = $request->withHeader('Content-Type', 'text/x-yaml');
        $request->getBody()->write(
            Yaml::dump($data)
        );
        $request->getBody()->rewind();

        $response = new Response();

        $collection = new GenericCollection([]);
        $plugins = [
            0 => \Gishiki\Core\MVC\Controller\Plugins\RequestDeserializer::class
        ];

        $controller = new \FakeController($request, $response, $collection, $plugins);

        $this->assertEquals($data, $controller->getRequestDeserialized()->all());
    }

    public function testXmlDeserialization()
    {
        $data = self::generateTestingData();

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
            0 => \Gishiki\Core\MVC\Controller\Plugins\RequestDeserializer::class
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
            0 => \Gishiki\Core\MVC\Controller\Plugins\RequestDeserializer::class
        ];

        $controller = new \FakeController($request, $response, $collection, $plugins);

        $this->assertEquals([
            "id" => 3,
            "test_str" => "fdaffe ccco"
        ], $controller->getRequestDeserialized()->all());
    }

    public function testBadDeserialization()
    {
        $data = self::generateTestingData();

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
            0 => \Gishiki\Core\MVC\Controller\Plugins\RequestDeserializer::class
        ];

        $controller = new \FakeController($request, $response, $collection, $plugins);

        $this->expectException(ControllerException::class);

        $controller->getRequestDeserialized();
    }
}