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

namespace Gishiki\tests\Logging;

use PHPUnit\Framework\TestCase;
use Gishiki\Logging\LoggerManager;
use Monolog\Handler\StreamHandler;

/**
 * The tester for the Logger PSR-3 class.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class LoggerManagerTest extends TestCase
{
    public function testConnectBadName()
    {
        $this->expectException(\InvalidArgumentException::class);

        LoggerManager::connect(6, []);
    }

    public function testConnectBadValue()
    {
        $this->expectException(\InvalidArgumentException::class);

        LoggerManager::connect(__FUNCTION__, [
            [
                'connection' => ['testLog.log', 0]
            ]
        ]);
    }

    public function testConnectBadClassValue()
    {
        $this->expectException(\InvalidArgumentException::class);

        LoggerManager::connect(__FUNCTION__, [
            [
                'class' => 'lol',
                'connection' => ['testLog.log', 0]
            ]
        ]);
    }

    public function testSetDefaultBadConnectionName()
    {
        $this->expectException(\InvalidArgumentException::class);

        LoggerManager::setDefault(null);
    }

    public function testSetDefaultInexistentConnectionName()
    {
        $this->expectException(\InvalidArgumentException::class);

        LoggerManager::setDefault("what a lol");
    }

    public function testRetrieveBadConnectionName()
    {
        $this->expectException(\InvalidArgumentException::class);

        LoggerManager::retrieve(100.05);
    }

    public function testRetrieveInexistentConnectionName()
    {
        $this->expectException(\InvalidArgumentException::class);

        LoggerManager::retrieve("what a lol");
    }

    public function testRetrieveUnsetDefaultConnection()
    {
        $this->expectException(\InvalidArgumentException::class);

        //set to null the default connection
        $reflectionClass = new \ReflectionClass(LoggerManager::class);
        $reflectionProperty = $reflectionClass->getProperty('hashOfDefault');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(null);

        LoggerManager::retrieve(null);
    }

    public function testSetDefaultLogger()
    {
        //empty the error testing file
        file_put_contents('testLog.log', '');

        LoggerManager::connect(__FUNCTION__, [
            [
                'class' => 'StreamHandler',
                'connection' => ['testLog.log', \Monolog\Logger::ERROR ]
            ]
        ]);

        LoggerManager::setDefault(__FUNCTION__);

        $logger = LoggerManager::retrieve(null);
        $logger->error("testing error");

        $this->assertGreaterThanOrEqual(strlen('testing error'), strlen(file_get_contents('testLog.log')));
    }

    public function testRetrieveLogger()
    {
        //empty the error testing file
        file_put_contents('testLog.log', '');

        LoggerManager::connect(__FUNCTION__, [
            [
                'class' => StreamHandler::class,
                'connection' => ['testLog.log', \Monolog\Logger::NOTICE ]
            ]
        ]);

        $logger = LoggerManager::retrieve(__FUNCTION__);
        $logger->notice("testing notice");

        $this->assertGreaterThanOrEqual(strlen('testing notice'), strlen(file_get_contents('testLog.log')));
    }
}
