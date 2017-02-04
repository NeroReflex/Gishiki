<?php
/****************************************************************************
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
********************************************************************************/

namespace Gishiki\Core;

use Gishiki\Logging\Logger;
use Psr\Log\LoggerAwareTrait;

/**
 * The base class of an exception related with the framework.
 *
 * Benato Denis <benato.denis96@gmail.com>
 */
class Exception extends \Exception
{
    use LoggerAwareTrait;

    /**
     * Create a base exception and save the log of what's happening.
     *
     * @param string $message   the error message
     * @param int    $errorCode the error code
     */
    public function __construct($message, $errorCode)
    {
        //perform a basic Exception constructor call
        parent::__construct($message, $errorCode, null);

        //setup an empty logger
        $this->logger = null;

        $logger = (!is_null(Environment::GetCurrentEnvironment())) ? new Logger() : new Logger('null');

        //build the new log entry
        $this->setLogger($logger);

        //and use it to transmit the log entry
        $this->writeLog();
    }

    /**
     * Write the log message using the attached logger.
     */
    public function writeLog()
    {
        if (!is_null($this->logger)) {
            //log the exception
            $this->logger->error('{{exception_type}} thrown at: {{file}}: {{line}} with message({{code}}): {{message}}', [
                'exception_type' => get_called_class(),
                'file' => $this->getFile(),
                'line' => $this->getLine(),
                'code' => $this->getCode(),
                'message' => $this->getMessage(),
            ]);
        }
    }
}
