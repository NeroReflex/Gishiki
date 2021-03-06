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
use Gishiki\Core\MVC\Controller\Plugins\TwigWrapper;
use Gishiki\tests\Core\Router\ApplicationTest;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;

use PHPUnit\Framework\TestCase;

/**
 * The tester for the TwigWrapper plugin.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class TwigWrapperTest extends TestCase
{
    public function testDefaultDirectoryThatDoesntExist()
    {
        $app = ApplicationTest::setupTestingApplication();

        $request = new Request();
        $request->getBody()->rewind();

        $response = new Response();

        $collection = new GenericCollection([]);
        $plugins = [
            TwigWrapper::class
        ];

        $controller = new \FakeController($request, $response, $collection, $plugins, $app);

        $this->expectException(ControllerException::class);

        $controller->setTwigLoader();
    }

    public function testTemplateCompilation()
    {
        $request = new Request();
        $request->getBody()->rewind();

        $response = new Response();

        $collection = new GenericCollection([]);
        $plugins = [
            TwigWrapper::class
        ];

        $controller = new \FakeController($request, $response, $collection, $plugins);

        $controller->setTwigLoader(new \Twig_Loader_Array([
            'test.html.twig' => '<html><head></head><body>Hello, {{ obj }}!</body></html>'
        ]));

        $data = new SerializableCollection([
            'obj' => 'world'
        ]);
        $controller->renderTwigTemplate('test.html.twig', $data);

        $this->assertEquals("<html><head></head><body>Hello, world!</body></html>", (string)$controller->getResponse()->getBody());
    }

    public function testRenderBeforePreparingLoader()
    {
        $request = new Request();
        $request->getBody()->rewind();

        $response = new Response();

        $collection = new GenericCollection([]);
        $plugins = [
            TwigWrapper::class
        ];

        $controller = new \FakeController($request, $response, $collection, $plugins);

        $this->expectException(ControllerException::class);

        $data = new SerializableCollection([
            'this' => 'test will fail'
        ]);

        $controller->renderTwigTemplate('test.html.twig', $data);
    }
}
