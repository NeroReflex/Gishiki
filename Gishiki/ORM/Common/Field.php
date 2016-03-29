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

namespace Gishiki\ORM\Common {
    
    /**
     * Abstract representation of a table field to be used 
     * on a data mapper ORM
     *
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    class Field {
        //this is the name of the field
        private $name;
        
        //this is the field that is a primary key
        private $is_primary_key;
        
        //this is the data type of the field
        private $type;
        
        //can the current field be NULL?
        private $required;
        
        /**
         * Create an empty field with no name, type and attributes
         */
        public function __construct() {
            //empty name for the current field
            $this->name = "";
            
            //stub primary key
            $this->primary_key = NULL;
            
            //a field can contain NULL-data by default
            $this->required = FALSE;
            
            //integer by default....
            $this->type = \Gishiki\ORM\Common\DataType::INTEGER;
        }
        
        /**
         * the current field can contain data only if that data is not NULL
         */
        public function setDataRequired() {
            $this->required = TRUE;
        }
        
        /**
         * Can NULL data be stored inside the current field?
         * 
         * @return boolean TRUE if the current field can only contain non-NULL data
         */
        public function isDataRequired() {
            return ($this->required = TRUE);
        }
        
        /**
         * Change the type of the data that can be stored inside the current 
         * field
         * 
         * @param \Gishiki\ORM\Common\DataType $field_type the type of the data
         */
        public function setDataType($field_type) {
            //register the given data field
            $this->type = $field_type;
        }
        
        /** 
         * Get the type of the data that can ben stored inside the current field 
         * 
         * @return \Gishiki\ORM\Common\DataType the type of the data
         */
        public function getDataType() {
            //return the data type
           return $this->type; 
        }
        
        /**
         * Give a name to the current table
         * 
         * @param string $field_name the name of the field
         */
        public function setName($field_name) {
            $this->name = $field_name;
        }
        
        /**
         * Check weather the current field has a valid name that can be used 
         * identify the field inside a table
         * 
         * @return boolean TRUE if and only if the field has a valid name
         */
        public function hasValidName() {
            //has the current field a valid name?
            return (strlen($this->name) > 0);
        }
        
        /**
         * Mark the current field to be used as the primary key for a table
         */
        public function markAsPrimaryKey() {
            //the current field should be used as a primary key
            $this->is_primary_key = true;
        }
        
        /**
         * Check weather the current field is going to be used as the 
         * primary key for a table
         * 
         * @return boolean TRUE if and only if the current field has been marked to be the primary key
         */
        public function markedAsPrimaryKey() {
            //was the current field marked as a primary key?
            return ($this->is_primary_key == TRUE);
        }
        
        /**
         * Get the name of the current field if used as a string
         * 
         * @return string the name of the current field
         */
        public function __toString() {
            //when used as a string return the table name
            return $this->name;
        }
    }
}