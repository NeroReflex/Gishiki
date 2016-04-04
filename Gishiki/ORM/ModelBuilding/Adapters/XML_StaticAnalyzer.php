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
     * using an XML file that maps the database structure
     *
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    class XML_StaticAnalyzer implements \Gishiki\ORM\ModelBuilding\StaticAnalyzerInterface {
        //this is the path to the XML file containing the structure of the database
        private $file_schemata;
        
        //was the Analyze function been called
        private $analysis_performed;
        
        //this is the figured-out database structure
        private $database_structure;

        /**
         * Analyze the given XML file and create a representation of the database 
         * structure.
         * 
         * @param string $schemata_filename the full path to the XML schemata file
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
            //load the schema of a database
            $schemata = simplexml_load_file($this->file_schemata);
            
            if (!$schemata) //check for the loading result
            {   throw new \Gishiki\ORM\ModelBuilding\ModelBuildingException("in file ".$this->file_schemata.": invalid XML structure", 7);  }
            
            //get the database connection name
            $connection_name = $schemata->attributes()["connection"][0]."";
            
            //setup an empty database schemata
            $this->database_structure = new \Gishiki\ORM\Common\Database($connection_name);
            
            foreach ($schemata->table as $table) {
                //get the name of the current table
                $table_name = "".$table->attributes()["name"][0];

                if (($table_name == NULL) || (strlen($table_name) <= 0))
                {   throw new \Gishiki\ORM\ModelBuilding\ModelBuildingException("in file ".$this->file_schemata.": one or more tables have an invalid name", 1);  }

                //create the table from its name
                $current_table = new \Gishiki\ORM\Common\Table($table_name);

                //add fields to currently analyzed the table
                foreach ($table->column as $field) {
                    $current_field = new \Gishiki\ORM\Common\Field();

                    //read every attribute of the field
                    foreach ($field->attributes() as $attribute_name => $value) {
                        //get the real stringed-type value
                        $attribute_value = "".$value;

                        switch (strtolower($attribute_name)) {
                            case "name":
                                //set the name of the current field
                                if (!$current_field->hasValidName())
                                {   $current_field->setName($attribute_value);  }
                                else
                                {   throw new \Gishiki\ORM\ModelBuilding\ModelBuildingException("in file ".$this->file_schemata.": in table ".$current_table.": field '".$current_field."' already have a name", 2);    }
                                break;
                            case "primarykey":
                                if ($value == "true") //mark the current field as primary key
                                {   $current_field->markAsPrimaryKey(); }
                                break;
                            case "required":
                                if ($value == "true") //mark the current field as primary key
                                {   $current_field->setDataRequired(); }
                                break;
                            case "type":
                                $type_name = strtolower($attribute_value);
                                if (($type_name == "integer") || ($type_name == "int"))
                                {   $current_field->setDataType(\Gishiki\ORM\Common\DataType::INTEGER); }
                                else if (($type_name == "string") || ($type_name == "str"))
                                {   $current_field->setDataType(\Gishiki\ORM\Common\DataType::STRING); }
                                else if (($type_name == "float") || ($type_name == "double"))
                                {   $current_field->setDataType(\Gishiki\ORM\Common\DataType::FLOAT); }
                                else if (($type_name == "boolean") || ($type_name == "bool"))
                                {   $current_field->setDataType(\Gishiki\ORM\Common\DataType::BOOLEAN); }
                                else if (($type_name == "date") || ($type_name == "time") || ($type_name == "datetime"))
                                {   $current_field->setDataType(\Gishiki\ORM\Common\DataType::DATETIME); }
                                else //unknown data type
                                {   throw new \Gishiki\ORM\ModelBuilding\ModelBuildingException("in file ".$this->file_schemata.": in table ".$current_table.": unknown data type '".$type_name."' for a field", 3);   }
                                break;
                                
                            default:
                                throw new \Gishiki\ORM\ModelBuilding\ModelBuildingException("in file ".$this->file_schemata.": in table ".$current_table.": unknown attribute '".$attribute_name."' for a field", 9);
                        }
                    }

                    //each filed must have a name
                    if (!$current_field->hasValidName())
                    {   throw new \Gishiki\ORM\ModelBuilding\ModelBuildingException("in file ".$this->file_schemata.": invalid name for one or more fields", 4);  }

                    //test for an already registered primary key
                    if (($current_field->markedAsPrimaryKey()) && ($current_table->hasPrimaryKey()))
                    {   throw new \Gishiki\ORM\ModelBuilding\ModelBuildingException("in file ".$this->file_schemata.": double primary key for the table '".$current_table."'", 5);  }

                    //register the field (automatically becomes the primary key if marked as such)
                    $current_table->RegisterField($current_field);
                }

                //add the analyzed table to the list of tables
                $this->database_structure->RegisterTable($current_table);
            }
            
            //the database structure has been figured out with no errors
            $this->analysis_performed = TRUE;
        }
    }

}