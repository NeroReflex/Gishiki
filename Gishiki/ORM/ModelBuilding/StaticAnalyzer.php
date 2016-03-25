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

namespace Gishiki\ORM\ModelBuilding {
    
    /**
     * The static analysis of a database structure is performed by this component
     * using an XML file that maps the database structure
     *
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    class StaticAnalyzer {
        /** This is the path to the XML file containing the 
         * structure of the database
         */
        protected $file_schemata;
        
        /** This is the structure of the database */
        protected $database_schemata;

        /**
         * Analyze the given XML file and create a representation of the database 
         * structure.
         * 
         * The resulting structure is no validated and almost no 
         * errors check is performed 
         * 
         * @param string $schemata_filename the full path to the XML schemata file
         * @throws ModelBuildingException the error encountered while building the database structure
         */
        function __construct($schemata_filename) {
            //store the path to the database schemata
            $this->file_schemata = $schemata_filename;
            
            //setup an empty database schemata
            $this->database_schemata = array();

            //load the schema of a database
            $schemata = simplexml_load_file($this->file_schemata);
            
            if (!$schemata) //check for the loading result
            {   throw new ModelBuildingException("the file ".$this->file_schemata." couldn't be loaded", 0);  }
            
            foreach ($schemata->table as $table) {
                //get the name of the current table
                $table_name = "".$table->attributes()["name"][0];

                if (($table_name == NULL) || (strlen($table_name) <= 0))
                {   throw new ModelBuildingException("in file ".$this->file_schemata.": one or more tables have an invalid name", 1);  }

                //create the table from its name
                $current_table = new Table($table_name);

                //add fields to currently analyzed the table
                foreach ($table->field as $field) {
                    $current_field = new Field();

                    //read every attribute of the field
                    foreach ($field->attributes() as $attribute_name => $value) {
                        //get the real stringed-type value
                        $attribute_value = "".$value;

                        switch ($attribute_name) {
                            case "name":
                                //set the name of the current field
                                $current_field->setName($attribute_value);
                                break;
                            case "primaryKey":
                                if ($value == "true") //mark the current field as primary key
                                {   $current_field->markAsPrimaryKey(); }
                                break;

                            default:
                                //die("unknown field attribute");
                                break;
                        }
                    }

                    //each filed must have a name
                    if (!$current_field->hasValidName())
                    {   die("invalid field name");  }

                    //try registering the current field as the table primary key
                    if ($current_field->markedAsPrimaryKey()) {
                        if (!$current_table->hasPrimaryKey()) //register the primary key
                        {   $current_table->registerPrimaryKey($current_field);  }
                        else //error: we have a second primary key inside the same table
                        {   die("double primary key for the same table");  }
                    } else  //add the current field to the standard fields list
                    {   $current_table->registerField($current_field);  }
                }

                //add the analyzed table to the list of tables
                $this->database_schemata[] = $current_table;
            }
            //the database structure has been figured out
        }


    }

}