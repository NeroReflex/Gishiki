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

use Gishiki\Algorithms\Manipulation;

/**
 * An helper class for storing logs of what happens on the server.
 *
 * This is the Stream version of the logger
 *
 * Benato Denis <benato.denis96@gmail.com>
 */
class StreamAdapter extends \Psr\Log\AbstractLogger
{
    //this is the program that is generating the log:
    private $stream;

    /**
     * Setup a logger that works on streams.
     *
     * Allowed streams are: 'stderr', 'stdout' and 'stdmem'.
     *
     * Default on stderr
     *
     * @param string $stream the name of the application
     */
    public function __construct($stream = '')
    {
        if (($stream == 'stderr') || ($stream == 'err') || ($stream == 'error') || ($stream == '')) {
            $this->stream = fopen('php://stderr', 'w');
        } elseif (($stream == 'stdout') || ($stream == 'out') || ($stream == 'output')) {
            $this->stream = fopen('php://stdout', 'w');
        } elseif (($stream == 'memory') || ($stream == 'mem') || ($stream == 'stdmem')) {
            $this->stream = fopen('php://memory', 'rw');
        }
    }

    public function log($level, $message, array $context = array())
    {
        $interpolated_message = Manipulation::interpolate($message, $context);
        $interpolated_message = trim($interpolated_message, "\n");

        //return value isn't documentated because it MUST NOT be used/trusted
        return ($this->stream) ? fwrite($this->stream, '['.$level.'] '.$interpolated_message."\n") : null;
    }
}
