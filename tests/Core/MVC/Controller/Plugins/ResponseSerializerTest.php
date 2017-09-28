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
use Gishiki\Core\MVC\Controller\Plugins\ResponseSerializer;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;

use PHPUnit\Framework\TestCase;

/**
 * The tester for the ResponseSerializer plugin.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class ResponseSerializerTest extends TestCase
{
    public function testSerializer()
    {
        $data = \FakeController::generateTestingData();

        $request = new Request();
        $request = $request->withHeader('Accepted', 'application/json;charset=utf-8');
        $response = new Response();

        $collection = new GenericCollection([]);
        $plugins = [
            ResponseSerializer::class
        ];

        $controller = new \FakeController($request, $response, $collection, $plugins);
        $controller->setResponseSerialized(new SerializableCollection($data));

        $response->getBody()->rewind();
        $unserializedData = SerializableCollection::deserialize((string)$response->getBody(), SerializableCollection::JSON);

        $this->assertEquals($data, $unserializedData->all());
        $this->assertEquals('application/json', $controller->getResponse()->getHeader('Content-Type')[0]);
    }

    public function testDefaultFormatSerializer()
    {
        $data = \FakeController::generateTestingData();

        $request = new Request();
        $request = $request->withHeader('Accepted', 'unknown/whatuwant');
        $response = new Response();

        $collection = new GenericCollection([]);
        $plugins = [
            ResponseSerializer::class
        ];

        $controller = new \FakeController($request, $response, $collection, $plugins);
        $controller->setResponseSerialized(new SerializableCollection($data));

        $response->getBody()->rewind();
        $unserializedData = SerializableCollection::deserialize((string)$response->getBody(), SerializableCollection::JSON);

        $this->assertEquals($data, $unserializedData->all());
        $this->assertEquals(ResponseSerializer::DEFAULT_TYPE, $controller->getResponse()->getHeader('Content-Type')[0]);
    }
}