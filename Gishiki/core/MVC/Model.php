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

namespace Gishiki\Core\MVC {
    
    /**
     * The Gishiki base model. Every model inherit from this class
     * 
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    class Gishiki_Model {
        /** this is the unique ID of this object that will be used, from now on to identify a single object*/
        protected $uniqueIDOfSelf = NULL;
        
        /** the schema of this current model (loaded at runtime) */
        protected $schemaOfSelf;
        
        /**
         * Load the schema that will be used to adap data to the database
         */
        private function LoadSchema() {
            //get the schema name
            $schemaName = (string)str_replace("_Model", "", get_class($this));
            
            //load the schema using the name of the object instantiated from a class that has inherited from this one
            $this->schemaOfSelf = new \Gishiki\Database\Schema($schemaName);
        }
        
        /**
         * Setup a new instance of the model. The class constructor (for the current 
         * class) will be called. You need to pass an array of arguments that
         * will be passed to the class constructor.
         * 
         * @param array $args argument unpacking will be applied when calling the class constructor
         * @return mixed returns a new instance of the class that inherits from Gishiki_model
         */
        public static function Create($args = []) {
            //reflect the model class and its schema loader
            $model_class = new \ReflectionClass(get_called_class());
            $load_method = $model_class->getMethod('LoadSchema');
            
            //create a new model object from the reflected class
            $mdlNew = $model_class->newInstanceArgs($args);
            
            //prepare the scheme loader and invoke it
            $load_method->setAccessible(TRUE);
            $load_method->invoke($mdlNew);
            
            //return the newly created model
            return $mdlNew;
        }
        
        /**
         * Store the current model status inside the database binded with 
         * the current application
         * 
         * @throws ModelException the error occurred while adding/updating
         * the model entry on the application database
         */
        public function Save() {
            //get the data to be written on the database
            $dataToBeWritten = $this->schemaOfSelf->DataMapping($this);
            //structured as specified by the previously loaded schema
            
            if ($this->uniqueIDOfSelf === NULL) {
                /*    data needs to be inserted into the loaded database    */
                
                //generate a really unique random ID using the uniqid in conjunction with a strong
                $this->uniqueIDOfSelf = uniqid(base64_encode(openssl_random_pseudo_bytes(16)), true);
                //(hopefully) random number generator
            
                //add the newly generated ID to the data group to write
                $dataToBeWritten["__uniqueObjectID"] = $this->uniqueIDOfSelf;
                
                try {
                    //attempt to write the structured data to the database
                    \Gishiki\Core\Environment::GetCurrentEnvironment()->GetDatabaseDriver()->Insert($this->schemaOfSelf->GetSchemaName(), $dataToBeWritten);
                } catch (\Gishiki\Database\DatabaseException $dbGishikiException) {
                    throw new ModelException($dbGishikiException->getMessage(), 0);
                }
            } else {
                /*    data needs to be updated into the loaded database    */
                
                //prepare the description of what needs to be removed
                $IDToRemove = ["__uniqueObjectID" => $this->uniqueIDOfSelf];
                
                try {
                    //and perform the operation on the database
                    \Gishiki\Core\Environment::GetCurrentEnvironment()->GetDatabaseDriver()->Update($this->schemaOfSelf->GetSchemaName(), $dataToBeWritten, $IDToRemove);
                } catch (\Gishiki\Database\DatabaseException $dbGishikiException) {
                    throw new ModelException($dbGishikiException->getMessage(), 1);
                }
            }
        }
        
        /**
         * Remove the current model instance from the application database
         * 
         * @throws ModelException the error occurred while removing the model
         * entry on the application database
         */
        public function Remove() {
            /*    data can be deleted from the database (using the unique ID)    */
            if ($this->uniqueIDOfSelf !== NULL) {
                //prepare the descriptioj of what needs to be removed
                $IDToRemove = ["__uniqueObjectID" => $this->uniqueIDOfSelf];
                
                try {
                    //and perform the operation on the database
                    \Gishiki\Core\Environment::GetCurrentEnvironment()->GetDatabaseDriver()->Remove($this->schemaOfSelf->GetSchemaName(), $IDToRemove);

                    //the operation is performed. The removed ID is not used anymore
                    $this->uniqueIDOfSelf = NULL;
                } catch (\Gishiki\Database\DatabaseException $dbGishikiException) {
                    throw new ModelException($dbGishikiException->getMessage(), 2);
                }
            } else {
                throw new ModelException("It is impossible to remove an object that was not saved", 3);
            }
        }

        /**
         * Remove a model from the database and from memory: this way the object is permanently lost.
         * You can use Gishiki_Model::Destroy to destroy every model, regardless of its type
         *
         * @param $object the model that will be destroyed
         * @throws ModelException the error that prevents the model to being destroyed
         */
        public static function Destroy(&$object) {
            //check if the model can be destroyed
            if ((is_object($object)) && (method_exists($object, "Remove"))) {
                //destroy the object in the database
                $object->Remove();

                //destroy the object from memory
                $object = NULL;
            } else {
                throw new ModelException("It is impossible to remove something that is not a model", 4);
            }
        }
        
        /**
         * Fetch an array of models from the given criteria.
         * Be aware that criteria MUST be given as an array, each array index 
         * must have the name of a property that is descripted inside the model
         * schema, and that the return will be given as an array of models
         * 
         * @param array $criteria an array of models or NULL
         * @return ModelsCollection the collection of fetched models
         * @throws ModelException the error that prevents models to being retrived
         */
        public static function Retrive($criteria)/* : ModelsCollection*/ {
            //this is the model rebuild result
            $models = [];
            
            //this is the fetch result
            $fetchResult = [];
            
            /*    try fetching data from the application database    */
            try {
                //load the model schema
                $dataSchema = new \Gishiki\Database\Schema((string)str_replace("_Model", "", get_called_class()));
                
                //and perform the operation on the database
                $fetchResult = \Gishiki\Core\Environment::GetCurrentEnvironment()->GetDatabaseDriver()->Fetch($dataSchema->GetSchemaName(), $criteria);
            } catch (\Gishiki\Database\DatabaseException $dbGishikiException) {
                throw new ModelException($dbGishikiException->getMessage(), 2);
            }
            
            //load the deserializer
            $dataSchemaAdapter = new \ReflectionMethod($dataSchema, "Adapt");
            $dataSchemaAdapter->setAccessible(TRUE);
            
            //use the fetch result and the loaded schema to rebuild models that were store into the database
            foreach ($fetchResult as $serializedModel) {
                //adapt the data to the schema
                $objectRepresentation = $dataSchemaAdapter->invokeArgs($dataSchema, [$serializedModel]);
                
                //create an empty model instance, that will be filled, without calling the model constructor
                $modelReflection = new \ReflectionClass(get_called_class());
                $currentModel = $modelReflection->newInstanceWithoutConstructor();
                
                //reflect the object storing model's schema
                $schemaOfSelf = new \ReflectionProperty($currentModel, "schemaOfSelf");
                $schemaOfSelf->setAccessible(TRUE);
                $schemaOfSelf->setValue($currentModel, $dataSchema);
                
                //reflect the unique object ID
                $uoIDReflected = new \ReflectionProperty($currentModel, "uniqueIDOfSelf");
                $uoIDReflected->setAccessible(TRUE);
                $uoIDReflected->setValue($currentModel, $serializedModel["__uniqueObjectID"]);
                
                //remove unneeded properties
                unset($serializedModel["__uniqueObjectID"]);
                
                //use reflection to rebuild the object
                reset($serializedModel);
                for ($i = 0; $i < count($serializedModel); $i++) {
                    //get the current property name
                    $propertyName = key($serializedModel);
                    
                    //if the property name is something that is not a reserved ID set it
                    if (($propertyName != "_id") && ($propertyName != "ID")) {
                        //set the model property
                        $modelProperty = new \ReflectionProperty($currentModel, $propertyName);
                        $modelProperty->setAccessible(TRUE);
                        $modelProperty->setValue($currentModel, current($serializedModel));
                    }
                    
                    //move to the next model property
                    next($serializedModel);
                }
                
                //insert the model in the model collection list
                $models[] = $currentModel;
            }

            if (count($models) > 0) {
                //return the collection of models that match the criteria
                return new ModelsCollection($models);
            } else {
                throw new ModelException("A collection of models cannot be returned, because no models were retrived", 5);
            }
        }
    }
}