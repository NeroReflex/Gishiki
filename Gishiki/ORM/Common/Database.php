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
    class Database {
        //this is the name of the current database
        private $name;
        
        //this is the list of tables inside the current database
        private $tables;
        
        /**
         * Create a database structure that has the name of the given database.
         * 
         * The newly created database structure must be filled by a 
         * static analyzer (a component that implements the StaticAnalyzerInterface)
         * 
         * @param string $database_name the name of the current database
         */
        public function __construct($database_name) {
            //store the name of the current database
            $this->name = $database_name;
        }
        
        /**
         * Register a table that is part of the current database
         * 
         * @param \Gishiki\ORM\Common\Table $database_table the table to be registered
         */
        public function RegisterTable(Table &$database_table) {
            //add the given table
            $this->tables[] = $database_table;
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