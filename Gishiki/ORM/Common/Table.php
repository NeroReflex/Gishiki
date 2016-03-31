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
    class Table extends \Gishiki\Algorithms\CyclableCollection {
        //this is the name of the table
        private $name;
        
        //this is the field that is a primary key
        private $primary_key;
        
        /**
         * Check weather the table name can be used within any supported RDBMS
         * 
         * @return boolean TRUE if the database name is a valid one
         */
        public function hasValidName() {
            if (strlen($this->name) > 0) {
                //get the list of all valid characters
                $valid_chars = str_split("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_0123456789");
                
                //get the list of all used characters
                $chars = str_split($this->name);
                
                //iterate each character to search for unallowed chars
                reset($chars);
                while ($c_char = current($chars)) {
                    //if an invalid character is found.....
                    if (array_search($c_char, $valid_chars) === FALSE)
                    {   return FALSE;   } //flag the invalid forced name 
                    
                    next($chars);
                }
                
                $uppername = strtoupper($this->name);
                return (($uppername != "CREATE") && 
                        ($uppername != "TABLE") && 
                        ($uppername != "IF") && 
                        ($uppername != "NOT") && 
                        ($uppername != "EXISTS") && 
                        ($uppername != "INSERT") && 
                        ($uppername != "INTO") && 
                        ($uppername != "DELETE") && 
                        ($uppername != "UPDATE") && 
                        ($uppername != "FROM") && 
                        ($uppername != "WHERE") && 
                        ($uppername != "ORDER") && 
                        ($uppername != "BY") && 
                        ($uppername != "DESC") && 
                        ($uppername != "ASC") && 
                        ($uppername != "JOIN"));
            }
        }
        
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
        }
        
        /**
         * Check weather a the primary key of the current table has already 
         * been declared and registered
         * 
         * @return boolean TRUE if a field has beed registered as a primary key
         */
        public function hasPrimaryKey() {
            //check if the primary key is a valid primary key
            return ($this->getPrimaryKey() != NULL);
        }
        
        /**
         * Get the primary key of the current table.
         * 
         * If the current table lacks in primary key NULL will be returned
         * 
         * @return \Gishiki\ORM\Common\Field the primary key field
         */
        public function getPrimaryKey() {
            return $this->primary_key;
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
            {   $this->array[] = $table_field;   }
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