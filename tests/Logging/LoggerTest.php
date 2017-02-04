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

use Gishiki\Logging\Logger;

/**
 * The tester for the Logger PSR-3 class.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class LoggerTest extends \PHPUnit_Framework_TestCase
{
    public function testConnection()
    {
        $logger = new Logger('null');

        $this->assertEquals('null', (string) $logger);
    }

    public function testFileLogging()
    {
        file_put_contents('test.log', '');
        $logger = new Logger('file://test.log');
        $logger->info('my name is {{name}} and this is a {{what}}', [
            'name' => 'Denis',
            'what' => 'test',
        ]);
        $logger = null;

        $this->assertEquals(trim('[info] my name is Denis and this is a test'), trim(file_get_contents('test.log')));

        unlink('test.log');
    }

    public function testStreamLogging()
    {
        $logger = new Logger('stream://stdmem');

        $output = "[debug] error\n";
        $unprocessedOutput = '{{simple}}';
        $options = [
            'simple' => 'error',
        ];

        $logger->log('debug', $unprocessedOutput, $options);

        $loggerReflected = new \ReflectionProperty($logger, 'adapter');
        $loggerReflected->setAccessible(true);
        $streamLogger = $loggerReflected->getValue($logger);

        $streamReflected = new \ReflectionProperty($streamLogger, 'stream');
        $streamReflected->setAccessible(true);
        $stream = $streamReflected->getValue($streamLogger);

        fseek($stream, 0);
        $this->assertEquals($output, stream_get_contents($stream));
    }
}
