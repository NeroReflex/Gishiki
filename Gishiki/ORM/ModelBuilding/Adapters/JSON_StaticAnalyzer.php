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

namespace Gishiki\ORM\ModelBuilding\Adapters {
    
    /**
     * The static analysis of a database structure is performed by this component
     * using a JSON file that maps the database structure
     *
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    class JSON_StaticAnalyzer implements \Gishiki\ORM\ModelBuilding\StaticAnalyzerInterface {
        //this is the path to the JSON file containing the structure of the database
        private $file_schemata;
        
        //was the Analyze function been called
        private $analysis_performed;
        
        //this is the figured-out database structure
        private $database_structure;

        /**
         * Analyze the given JSON file and create a representation of the database 
         * structure.
         * 
         * @param string $schemata_filename the full path to the JSON schemata file
         */
        public function __construct($schemata_filename) {
            //store the path to the database schemata
            $this->file_schemata = $schemata_filename;
            
            //Analyze function has not being called (yet)
            $this->analysis_performed = FALSE;
            
            //the database structure is not analyzed (yet)
            $this->database_structure = NULL;
        }
        
        public function Analyzed() {
            //has Analyze function being called successfully?
            return ($this->analysis_performed == TRUE);
        }

        public function Result() {
            //return the analysis result
            return $this->database_structure;
        }
        
        public function Analyze() {
            //load the schema of the database
            try {
                \Gishiki\JSON\JSON::DeSerialize($this->file_schemata);
            } catch (\Gishiki\JSON\JSONException $ex) {
                throw new \Gishiki\ORM\ModelBuilding\ModelBuildingException("in file ".$this->file_schemata.": invalid JSON structure (".$ex->getMessage().")", 8);
            }
            
            
            
            //the database structure has been figured out with no errors
            $this->analysis_performed = TRUE;
        }
    }

}