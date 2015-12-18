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
     * The JSON object class. This class is designed to be used inside cycles
     * and has a great supporto for recursive and cycle based algorithms!
     *
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    class JSONObject extends JSONValue {
        
        /** this is the property following the last returned property.
        This solution is adopted to get huge performance increase!!! */
        private $nextProperty;
        
        /* this is the number of returned properties */
        private $currentPropertyNumber;
        
        
        /**
         * Setup a basic JSON value manager and then initialize the JSON object
         */
        public function __construct() {
            //call the JSONValue constructor
            parent::__construct();
            
            //change the value type
            $this->type = JSONValueType::OBJECT_VALUE;
            
            //initialize an empty JSON object
            $this->value = [];
            
            //initialize the cycle service
            $this->ResetProperties();
        }
        
        /**
         * Reset the current property pointer
         */
        public function ResetProperties() {
            //reset the list of entities
            reset($this->value);
           
            //and set the property to be returned when GetNextProperty will be called
            $this->nextProperty = current($this->value);
            
            //update the number of fetched properties
            $this->currentPropertyNumber = 0;
        }
        
        /** 
         * Get the currently pointed property of the object or NULL if the 
         * property beyont the last one is requested
         * 
         * @return JSONEntity the value of the current enitity
         */
        public function GetNextProperty() {
            //fetch the property currently pointed by the php internal pointer
            $currentEntity = $this->nextProperty;
            
            //jump to the next entity on the list
            $this->nextProperty = next($this->value);
            
            //update the number of fetched properties
            $this->currentPropertyNumber = 1;
            
            //return the previously fetched property
            return $currentEntity;
        }
        
        /** 
         * This is used to stop the cycle, and answer the question: 
         * Was the last property fetched?
         * 
         * @return boolean TRUE if the last property has been fetched, FALSE otherwise 
         */
        public function EndOfProperties() {
            return ($this->currentPropertyNumber == count($this->value)) || (!is_object($this->nextProperty));
        }
        
        
        /**
         * Get the property of the current object that has the given name.
         * Be aware that a reset operation will be performed!
         * 
         * @param string $propertyName the name of the property to be found
         * @return \Gishiki\JSON\JSONProperty return the property of the current object or NULL if it is not found
         */
        public function GetPropertyByName($propertyName) {
            //setup the default return value
            $toReturn = NULL;
           
            /*    in order to found the correct property a ccle is deployed   */
            
            //the cycle must begin from the first property
            $this->ResetProperties();
            
            //cycle each property to find the one with the given name
            while ((!$this->EndOfProperties()) && ($toReturn === NULL)) {
                //get the current property and prepare the next one
                $curProperty = $this->GetNextProperty();
                
                //check if the current property is the one searched, and......
                if (strcmp($curProperty->GetName(), $propertyName) == 0) {
                    //if it is return it
                    $toReturn = new JSONProperty($propertyName, $curProperty->GetValue());
                }
            }
            
            //after the cycle the property pointer must be reset'd to the first element
            $this->ResetProperties();
            
            //return the requested property
            return $toReturn;
        }
        
        /**
         * Add a JSON property to the current JSON object. 
         * Be aware that a reset operation will be performed!
         * 
         * @param \Gishiki\JSON\JSONProperty $newProperty the property to be added
         */
        public function AddProperty(JSONProperty $newProperty) {
            //store the new property
            $this->value[] = $newProperty;
            
            //perform the reset
            $this->ResetProperties();
        }
    }
}
