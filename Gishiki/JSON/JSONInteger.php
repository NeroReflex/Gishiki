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
     * The JSON integer class. This class is designed to work with integers,
     * however maximum and minimum integer values follows PHP rules
     *
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    class JSONInteger extends JSONValue {
        
        /**
         * Setup a basic JSON value manager and then initialize the JSON integer
         * 
         * @param string $number the integer number to be used
         */
        public function __construct($number) {
            //call the JSONValue constructor
            parent::__construct();
            
            //change the value type
            $this->type = JSONValueType::INTEGER_VALUE;
            
            //store the given message
            $this->value = $number;
        }
        
        /**
         * Get the value of the integer number inside the JSON
         * 
         * @return integer the integer value of the integer number
         */
        public function GetInteger() {
            //return the message as an integer
            return $this->value;
        }
        
        /**
         * Get the value of the integer number inside the JSON
         * 
         * @return float the floating point value of the integer number
         */
        public function GetFloat() {
            //return the message as an integer
            return floatval($this->value);
        }
    }
}
