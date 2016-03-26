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
     * Abstract representation of a database table to be used 
     * on a data mapper ORM
     *
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    class Table {
        //this is the name of the table
        private $name;
        
        //this is the field that is a primary key
        private $primary_key;
        
        //this is the list of fields
        private $data_fields;
        
        /**
         * Create an empty table with no attributes nor fields
         * 
         * @param string $table_name the name of the current table
         */
        public function __construct($table_name) {
            //store the name of the table
            $this->name = $table_name;
            
            //stub primary key
            $this->primary_key = NULL;
            
            //empty field list
            $this->data_fields = array();
        }
        
        /**
         * Check weather a the primary key of the current table has already 
         * been declared and registered
         * 
         * @return boolean TRUE if a field has beed registered as a primary key
         */
        public function hasPrimaryKey() {
            //check if the primary key is a valid primary key
            return ($this->primary_key != NULL);
        }
        
        /**
         * Register the given field inside the current table.
         * 
         * The given field is automatically registered as primary key if it was
         * marked as such
         * 
         * @param \Gishiki\ORM\Common\Field $table_field the field to be registeres+d
         */
        public function RegisterField(Field &$table_field) {
            //check the field
            if ($table_field->markedAsPrimaryKey()) //save the field as the primary key
            {   $this->primary_key = $table_field;     }
            else //save the field as a data field
            {   $this->data_fields[] = $table_field;   }
        }
        
        /**
         * Get the name of the current table if used as a string
         * 
         * @return string the name of the current table
         */
        public function __toString() {
            //when used as a string return the table name
            return $this->name;
        }
    }
}