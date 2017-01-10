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

namespace Gishiki\Logging\Adapter;

use Gishiki\Core\Environment;
use Gishiki\Algorithms\Manipulation;

/**
 * An helper class for storing logs of what happens on the server.
 *
 * This is the File version of the logger
 *
 * Benato Denis <benato.denis96@gmail.com>
 */
class FileAdapter extends \Psr\Log\AbstractLogger
{
    //this is the path of the log file
    private $path = null;

    //this is the file stream
    private $handler = null;

    /**
     * Setup a logger that works on files.
     *
     * Default is error.log on the application root
     *
     * @param string $file_path the path of the file
     */
    public function __construct($file_path = '')
    {
        //get the file path
        $this->path = ($file_path != '') ? $file_path : Environment::GetCurrentEnvironment()->GetConfigurationProperty('APPLICATION_DIR').'error.log';

        //open the file
        $this->handler = fopen($file_path, 'a');
    }

    public function log($level, $message, array $context = array())
    {
        $interpolated_message = Manipulation::interpolate($message, $context);
        $interpolated_message = trim($interpolated_message);

        return ($this->handler) ? fwrite($this->handler, '['.$level.'] '.$interpolated_message."\n") : null;
    }
}
