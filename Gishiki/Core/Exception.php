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
********************************************************************************/

namespace Gishiki\Core {

    /**
    * The base class of an exception related with the framework
    *
    * Benato Denis <benato.denis96@gmail.com>
    */
    class Exception extends \Exception {
        
        /**
         * Create a base exception and save the log of what's happening
         *
         * @param string $message the error message
         * @param integer $errorCode the error code
         */
        public function __construct($message, $errorCode) {
            //perform a basic Exception constructor call
            parent::__construct($message, $errorCode, NULL);
            
            //build the new log entry
            new \Gishiki\Logging\Log(get_class($this)." exception thrown", $message, \Gishiki\Logging\Priority::CRITICAL);
            //the log entry is automatically saved
        }
    }
}