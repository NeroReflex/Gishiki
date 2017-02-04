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

/**
 * An helper class for storing logs of what happens on the server.
 *
 * This is the gelf version of the logger (based on gelf-php project)
 *
 * @link https://github.com/bzikarsky/gelf-php
 *
 * Benato Denis <benato.denis96@gmail.com>
 */
class GelfAdapter
{
    //this is the managed gelf resource
    private $gelf_resource = null;

    /**
     * Setup a logger that works on gelf.
     *
     * This requires a graylog server to be waiting for logs.
     *
     * Default server is on udp:localhost:12201
     *
     * @param string $server the address of the server
     */
    public function __construct($server = 'null')
    {
        $native_server = ($server == 'null') ? null : $server;

        if ($native_server) {
            $conn_info = explode(':', $native_server);

            $transport = null;
            switch (strtolower($conn_info[0])) {
                case 'tcp':
                    $transport = new \Gelf\Transport\TcpTransport($conn_info[1], intval($conn_info[2]));
                    break;

                case 'udp':
                    $transport = new \Gelf\Transport\UdpTransport($conn_info[1], intval($conn_info[2]));
                    break;

                default:
                    $transport = null;
            }

            //create the new logger with the build transport
            $this->gelf_resource = new \Gelf\Logger($transport);
        } else {
            $this->gelf_resource = new \Gelf\Logger();
        }
    }

    public function log($level, $message, array $context = array())
    {
        //return value isn't documentated because it MUST NOT be used/trusted
        return ($this->gelf_resource) ? $this->gelf_resource->log($level, $message, $context) : null;
    }
}
