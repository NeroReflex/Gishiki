<?php
/**************************************************************************
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
*****************************************************************************/

namespace Gishiki\JSON {

    /**
     * The JSON string class. This class is designed to work with utf8-only 
     * strings, however multiple formats are supported.
     *
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    class JSONString extends JSONValue {
        
        /**
         * Setup a basic JSON value manager and then initialize the JSON string
         * 
         * @param string $message the string to be used
         */
        public function __construct($message) {
            //call the JSONValue constructor
            parent::__construct();
            
            //change the value type
            $this->type = JSONValueType::STRING_VALUE;
            
            //store the given message
            $this->value = $message;
        }
        
        /**
         * Get the message of the JSON value
         * 
         * @return string the message encoded as a standard utf-8 string
         */
        public function GetMessageAsUTF8() {
            //return the message as an utf-8 string
            return $this->value;
        }
        
        /**
         * Get the message of the JSON value
         * 
         * @return string the message encoded as a standard utf-16 string
         */
        public function GetMessageAsUTF16() {
            //return the message as an utf-16 string
            return mb_convert_encoding($this->value, "utf-8", "UTF-16BE");
        }
    }
}
