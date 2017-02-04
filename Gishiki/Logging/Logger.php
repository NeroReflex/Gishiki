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
 *******************************************************************************/

namespace Gishiki\Logging;

use Gishiki\Core\Environment;
use Psr\Log\AbstractLogger;

/**
 * An helper class for storing logs of what happens on the server.
 *
 * Benato Denis <benato.denis96@gmail.com>
 */
final class Logger extends AbstractLogger
{
    //the logger adapter is the real logger implementation
    private $adapter;

    //this is the connection string used to setup the logger adapter
    private $connection_string;

    /**
     * Setup the logger instance using the proper
     * adapter for the given connector OR the default
     * one if 'default' is given.
     *
     * Giving a connector named 'null' will result in
     * a null logger: a logger that doesn't log!
     *
     * @param string $connector
     */
    public function __construct($connector = 'default')
    {
        $this->connection_string = $connector;

        //create te logger from the correct adapter
        if (($connector === null) || (strlen($connector) == 0) || (strtolower($connector) == 'null') || (strtolower($connector) == 'void')) {
            $this->adapter = new \Psr\Log\NullLogger();
        } elseif (strpos($connector, '://') !== false) {
            if ($connector == 'default') {
                $connector = Environment::GetCurrentEnvironment()->GetConfigurationProperty('LOG_CONNECTION_STRING');
            }

            //separe adapter name from connection info
            $conection_exploded = explode('://', $connector, 2);

            //open a log if it is really possible:
            if (count($conection_exploded) == 2) {
                $adapter = $conection_exploded[0];
                $query = $conection_exploded[1];

                //get the classname from the adapter name
                $adapter_class = 'Gishiki\\Logging\\Adapter\\'.ucwords(strtolower($adapter)).'Adapter';
                if (class_exists($adapter_class)) {
                    $reflected_logger = new \ReflectionClass($adapter_class);
                    $this->adapter = $reflected_logger->newInstance($query);
                }
            }
        }
    }

    /**
     * Logs with an arbitrary level.
     *
     * <code>
     *  //initialize a new logger
     *  $logger = new Logger("stream://stdout");
     *
     *  if ($temp > 60.5) {
     *      //the new log entry has alert as severity level
     *      $logger->alert('Room temperature too high!', [
     *          'temperature' => $temp
     *      ]);
     *  } else {
     *      //the new log entry has info as severity level
     *      $logger->alert('Room temperature normal.', [
     *          'temperature' => $temp
     *      ]);
     *  }
     * </code>
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     */
    public function log($level, $message, array $context = array())
    {
        //proxy the log call to the given adapter
        ($this->adapter) ? $this->adapter->log($level, $message, $context) : null;
    }

    /**
     * Get the connection string passed to the constructor.
     *
     * @return string the connection string passed to the constructor
     */
    public function __toString()
    {
        return $this->connection_string;
    }
}
