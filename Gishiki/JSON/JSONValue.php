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
     * The JSON value base class. This class provided a standard way to 
     * analyze a JSON value, may it be an object, an array, a string, whatever....
     * This class is also used to represent a NULL JSON value.
     * 
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    class JSONValue {
        
        /** The type of the current value, check JSONValueType for possible types */
        protected $type;
        
        /** The raw value that can be managed by PHP */
        protected $value = NULL;

        /**
         * Setup a basic JSON value manager
         */
        public function __construct() {
            //setup a null value
            $this->type = JSONValueType::NULL_VALUE;
            
            //setup the raw value
            $this->value = NULL;
        }
        
        /**
         * Get the type of the current JSON value.
         * 
         * @return integer one of JSONValueType constants values
         */
        public function GetType() {
            //return the value type (this way the developer knows how to properly manage the value)
            return $this->type;
        }
        
        /**
         * Get the value of the JSON value. The type depends on the type of value stored
         * 
         * @return mixed the raw value of the current JSON value
         */
        public function GetValue() {
            //return the raw value
            return $this->value;
        }
        
    }
}