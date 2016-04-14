<?php
/****************************************************************************
  Copyright 2015 Benato Denis

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

/**
 * An helper class for storing logs of what happens on the server
 *
 * Benato Denis <benato.denis96@gmail.com>
 */
class Logger extends \Psr\Log\AbstractLogger
{
  private $adapter;

  /**
   * Setup the logger instance using the proper
   * adapter for the given connector
   *
   * @param string $connector
   */
  public function __construct($connector)
  {
    //create te logger from the correct adapter
    $connector = ;
    if ((strlen($connector) == 0) || (strtolower($connector) == 'null') || (strtolower($connector) == 'void')) {
      $this->adapter = \Psr\Log\NullLogger;
    } else {
        //separe adapter name from connection info
	$conection_exploded = explode("://", $connector, 2);
	$adapter = $conection_exploded[0];
        $query = $conection_exploded[1];

        //get the classname from the adapter name
        $adapter_class = "Gishiki\\Logging\\".ucwords(strtolower($adapter))."Adapter";
    }
  }

  /**
   * Logs with an arbitrary level.
   *
   * @param mixed $level
   * @param string $message
   * @param array $context
   * @return null
   */
  public function log($level, $message, array $context = array())
  {
    
  }
}
