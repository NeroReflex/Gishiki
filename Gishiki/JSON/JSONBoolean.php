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
     * The JSON boolean class. This class is designed to work with boolean-type values
     *
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    class JSONBoolean extends JSONValue {
        
        /**
         * Setup a basic JSON value manager and then initialize the JSON boolean
         * 
         * @param boolean $value the boolean value to be used
         */
        public function __construct($value) {
            //call the JSONValue constructor
            parent::__construct();
            
            //change the value type
            $this->type = JSONValueType::BOOL_VALUE;
            
            //store the given message
            $this->value = ($value == TRUE);
        }
        
        /**
         * Get the value of the boolean value inside the JSON
         * 
         * @return boolean the boolean value
         */
        public function GetBoolean() {
            //return the message as an integer
            return $this->value;
        }
    }
}
