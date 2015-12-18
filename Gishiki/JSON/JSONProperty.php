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
     * The abstract representation of a JSON property. 
     *
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    class JSONProperty {
        /** The name of the current property, stored as a string */
        private $name;
        
        /** The value of the class property, this must be 
         * an object of a class that extends the JSONValue class */
        private $value;
        
        /**
         * Creates a new property with the given name and value
         * 
         * @param string $propertyName the name of the new property
         * @param \Gishiki\JSON\JSONValue $propertyValue the value of the new property
         */
        public function __construct($propertyName, \Gishiki\JSON\JSONValue $propertyValue) {
            //save the name of the property
            $this->name = (string)$propertyName;
            
            //save the value of the property
            $this->value = $propertyValue;
        }
        
        /**
         * Get the name of the current property, encoded as a PHP string (utf8)
         * 
         * @return string the name of the current property
         */
        public function GetName() {
            //return the name of the current property
            return $this->name;
        }
        
        /**
         * Get the value of the current property, encoded as an object 
         * of a class that inherit from JSONValue. Use the GetType function
         * of the result
         * 
         * @return \Gishiki\JSON\JSONValue the value of the current property
         */
        public function GetValue() {
            //return the value of the current property
            return $this->value;
        }
    }
}
