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

namespace Gishiki\Logging\Adapter;

use Gishiki\Algorithms\Manipulation;
use Psr\Log\LogLevel;

/**
 * An helper class for storing logs of what happens on the server
 *
 * Benato Denis <benato.denis96@gmail.com>
 */
class Stream extends \Psr\Log\AbstractLogger
{
    //this is the program that is generating the log:
    private $stream = "stderr";
    
    /**
     * Setup a logger that works on streams
     * 
     * @param string $stream the name of the application
     */
    public function __construct($stream = '')
    {
        if (($stream == 'stderr') || ($stream == 'err') || ($stream == 'error') || ($stream == '')) {
            $this->stream = 'error';
        } elseif (($stream == 'stdout') || ($stream == 'out') || ($stream == 'output')) {
            $this->stream = 'output';
        }
    }
    
    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = array())
    {
        $interpolated_message = Manipulation::str_interpolate($message, $context);
        $interpolated_message = trim($interpolated_message);
        
        if ($this->stream == 'output') {
            fwrite(STDOUT, $level." ".$interpolated_message."\n");
        } elseif ($this->stream == 'error') {
            fwrite(STDERR, $level." ".$interpolated_message."\n");
        }
    }
}
