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
     * Abstract representation of a database structure to be used 
     * on a data mapper ORM
     *
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    class Database extends \Gishiki\Algorithms\CyclableCollection {
        //this is the name of the current database
        private $name;
        
        //this is the connection string
        private $connection;
        
        /**
         * Create a database structure that has the name of the given database.
         * 
         * The newly created database structure must be filled by a 
         * static analyzer (a component that implements the StaticAnalyzerInterface)
         * 
         * @param string $database_name the name of the current database
         * @param string $database_connection the name of the database connection
         */
        public function __construct($database_name, $database_connection) {
            //store the name of the current database
            $this->name = $database_name;
            
            //store the name of the database connection
            $this->connection = $database_connection;
        }
        
        /**
         * Get the name of the connection that needs to be used to reach the 
         * current database
         * 
         * @return string the name of the connection
         */
        public function getConnection() {
            //return the reference to the connection
            return $this->connection;
        }
        
        /**
         * Check weather the database name can be used within any supported RDBMS
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
         * Register a table that is part of the current database
         * 
         * @param \Gishiki\ORM\Common\Table $database_table the table to be registered
         */
        public function RegisterTable(Table &$database_table) {
            //add the given table
            $this->array[] = $database_table;
        }
        
        /**
         * Get the name of the database if used as a string
         * 
         * @return string the name of the current database
         */
        public function __toString() {
            return $this->name;
        }
    }
}