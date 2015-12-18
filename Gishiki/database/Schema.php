<?php
/****************************************************************************
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

namespace Gishiki\Database {

    /**
     * A schema used to represent the data structure in the database.
     * A schema is loaded from a valid XML file formatted as described in the 
     * documentation
     *
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    class Schema {
        
        /** the name of the collection/table inside the database */
        private $collectionName;
        
        /** name of fields inside the database */
        private $fieldsNames;
        
        /** fields type (or preferred type) inside the database */
        private $fieldsType;
        
        /** A list of loaded schemas (used to avoid infinite inclusion schemas) */
        private $loadedSchemas;
        
        /**
         * Create an empty schema, and fill it with the given schema.
         * Giving a schema name is optional 
         * 
         * @param string $schemaName the name of the schema to be loaded
         * @throws SchemaException the error occurred while importing the schema
         */
        public function __construct($schemaName = "")
        {
            //setup an empty schema
            $this->collectionName   = NULL;
            $this->fieldsNames      = [];
            $this->fieldsType       = [];
            $this->loadedSchemas    = [];
            
            if (gettype($schemaName) == "string") {
                //call the schema loader
                $this->LoadSchema($schemaName);
            }
        }
        
        /**
         * Load the schema with the given name
         * 
         * @param string $schemaName the name of the schema to be loaded
         * @throws SchemaException the error occurred while importing the schema
         */
        public function LoadSchema($schemaName) {
            //check if the schema was already loaded or is currently loading
            if (in_array($schemaName, $this->loadedSchemas, TRUE)) {
                return;
            } else {
                //add the current schema name to the list of loaded schemas
                $this->loadedSchemas[] = $schemaName;
            }
            
            //build the complete path of the xml file to be parsed
            $xmlSchemaFilePath = \Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty("SCHEMATA_DIR").((string)$schemaName).".xml";
            
            //stop if the file doesn't exists
            if (!file_exists($xmlSchemaFilePath)) {
                throw new SchemaException("Schema not found: the schema descriptor file for '".$schemaName."' doesn't exists or an higher privilege level is required to read it", 0);
            }
            
            //read the schema file content
            $XMLEncodedSchemaDescriptor = file_get_contents($xmlSchemaFilePath);
            
            //try building the schema from the xml file
            $XMLSchemaDescriptor = @simplexml_load_string($XMLEncodedSchemaDescriptor);
            if (!$XMLSchemaDescriptor) {
                throw new SchemaException("The schema '".$schemaName."' cannot be retrived from the current XML file, because it is not a valid XML file", 1);
            }
            
            //load basics schemas (if any)
            if ($XMLSchemaDescriptor->extends) {
                foreach ($XMLSchemaDescriptor->extends as $extension) {
                    $this->LoadSchema($extension);
                }
            }
            //load the collection name if it is possible
            if (is_object($XMLSchemaDescriptor->{"collection"}[0])) {
                $this->collectionName = (string)$XMLSchemaDescriptor->{"collection"}[0];
            }
            
            //read fields names and data types (if any)
            if ($XMLSchemaDescriptor->fields) {
                //cycle each field on the current field collection
                foreach ($XMLSchemaDescriptor->fields->field as $currentField) {
                    //add the field to the fields list only if a field with 
                    //the same name wasn't stored yet 
                    if (!in_array($currentField->{"name"}, $this->fieldsNames, TRUE)) {
                        $this->fieldsNames[] = $currentField->{"name"};

                        switch (strtoupper($currentField->{"type"})) {
                            case "STRING":
                            case "STR":
                                $this->fieldsType[] = DataTypes::STRING;
                                break;

                            case "SMALL INTEGER":
                            case "SMALL INT":
                            case "SMALL_INT":
                            case "SMALL_INTEGER":
                            case "SMALLINTEGER":
                            case "SMALLINT":
                                $this->fieldsType[] = DataTypes::SMALL_INTEGER;
                                break;
                            
                            case "BIG INTEGER":
                            case "BIG INT":
                            case "BIG_INT":
                            case "BIG_INTEGER":
                            case "BIGINTEGER":
                            case "BIGINT":
                                $this->fieldsType[] = DataTypes::BIG_INTEGER;
                                break;
                            
                            case "INTEGER":
                            case "INT":
                                $this->fieldsType[] = DataTypes::INTEGER;
                                break;
                            
                            case "BOOL":
                            case "BOOLEAN":
                                $this->fieldsType[] = DataTypes::BOOLEAN;
                                break;
                                
                            case "DATE":
                            case "TIME":
                                $this->fieldsType[] = DataTypes::TIME;
                                break;
                                
                            case "FLOAT":
                            case "FLOATING":
                            case "FLOATING POINT":
                            case "REAL":
                            case "DOUBLE":
                                $this->fieldsType[] = DataTypes::REAL;
                                break;
                            
                            default:
                                $this->fieldsType[] = DataTypes::BLOB;
                                break;
                        }
                    }
                }
            }
        }
        
        /**
         * Get a boolean value (flag) used to check the validity of the schema loaded
         * 
         * @return boolean TRUE if a schema was already loaded, FALSE otherwise
         */
        public function IsLoaded() {
            return ((count($this->fieldsNames) != 0) && ($this->collectionName != NULL));
        }
        
        /**
         * Adapt a generic collection of data to the currently loaded schema (or
         * schemas network).
         * 
         * @param array $dataCollection the collection of that that has to match data schema
         * @return array the input array filled with correctly typed data
         * @throws SchemaException the error occurred while adapting data to schema
         */
        private function Adapt($dataCollection) {
            //check for the schema to be loaded
            if (!$this->IsLoaded()) {
                throw new SchemaException("The given data collection cannot be adapted to a schema because no schemas were loaded", 2);
            }
            
            //this will contain the adapted data
            $adaptedData = [];
            
            //cycle each element on the data collection
            reset($dataCollection);
            $dataCollectionLength = count($dataCollection);
            
            //and adapt each element to the loaded schema
            for ($i = 0; $i < $dataCollectionLength; $i++){
                $dataFieldName = key($dataCollection);
                
                $indexOfFieldName = array_search($dataFieldName, $this->fieldsNames);
                if ($indexOfFieldName !== FALSE) {
                    $value = NULL;
                    
                    switch($this->fieldsType[$indexOfFieldName]) {
                        case DataTypes::BIG_INTEGER:
                        case DataTypes::SMALL_INTEGER:
                        case DataTypes::INTEGER:
                        case DataTypes::TIME:
                            if (($this->fieldsType[$indexOfFieldName] == DataTypes::TIME) && (gettype(current($dataCollection)) == "string")) {
                                $adaptedData[$dataFieldName] = @strtotime(current($dataCollection));
                            } else {
                                $adaptedData[$dataFieldName] = intval(current($dataCollection));
                            }
                            break;
                        
                        case DataTypes::REAL:
                            $adaptedData[$dataFieldName] = floatval(current($dataCollection));
                            break;
                        
                        case DataTypes::STRING:
                            $adaptedData[$dataFieldName] = "".(string)(current($dataCollection))."";
                            break;
                        
                        case DataTypes::BLOB:
                            $adaptedData[$dataFieldName] = base64_encode("".(string)(current($dataCollection))."");
                            break;
                        
                        case DataTypes::BOOLEAN:
                            $adaptedData[$dataFieldName] = (current($dataCollection) == TRUE);
                            break;
                        
                        default:
                            break;
                    }
                }/* else {
                    throw new SchemaException("The given data is not adaptable to the given schema(".$dataFieldName.")", 3);
                }*/
                
                next($dataCollection);
            }
            
            return $adaptedData;
        }
        
        /**
         * Get the name of the currently loaded schematized collection
         * 
         * @return string the name of the current schematized collection
         * @throws SchemaException exception thrown if the current schema hasn't a name
         */
        public function GetSchemaName() {
            if (!$this->IsLoaded()) {
                throw new SchemaException("The current schema has not a name", 4);
            }
            
            //return the name of the collection
            return $this->collectionName;
        }
        
        /**
         * Create a representation of the given object conformant to the loaded schema
         * 
         * @param object $object the object to be mapped
         * @return mixed an array of mapped data or NULL on error
         * @throws SchemaException the error occurred
         */
        public function DataMapping(&$object) {
            if (!$this->IsLoaded()) {
                throw new SchemaException("A schema must be loaded before mapping an object", 5);
            }
            
            //check if the object can be mapped
            if (is_object($object)) {
                //prepare the mappable data
                $mappableData = [];
                
                //prepare the reflection for the given object
                $reflect = new \ReflectionClass($object);
                $props = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);

                //build the array of data that can be mapped in memory
                foreach ($props as $prop) {
                    $prop->setAccessible(TRUE);
                    if (!$prop->isStatic()) {
                        $mappableData[$prop->getName()] = $prop->getValue($object);
                    }
                }

                //adapt the mappable data to the currently loaded schema
                $mappedData = $this->Adapt($mappableData);
                
                //return that array
                return $mappedData;
            } else {
                return NULL;
            }
        }
    }
}