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


/**
 * The model loader and and manager
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class ModelManager {
    /** an instance of the model class */
    private $Model;

    /**
     * Prepare the model to be used, and load a model if a model name is given
     * 
     * @param string $modelName the name if the model to be loaded
     */
    static function LoadNewModel($modelName) {
        return new ModelManager($modelName);
    }
    
    /**
     * Prepare the model to be used, and load a model if a model name is given
     * 
     * @param string $modelName the name if the model to be loaded
     */
    public function __construct($modelName = NULL) {
        //initialize an empty model
        $this->Model = NULL;
        
        //load the model, if a model name is given
        if (gettype($modelName) == "string") {
            $this->LoadModel($modelName);
        }
    }
    
    /**
     * Check if a model is currently loaded and reasy to be used
     * 
     * @return boolean TRUE if a model is loaded, false otherwise
     */
    public function isLoaded() {
        return is_object($this->Model);
    }

    /**
     * Load a model from its class name
     * 
     * @param string $modelName the model name, without '_Model'
     * @throws ModelException the error occurred while importing and initializing the model
     */
    public function LoadModel($modelName) {
        if (file_exists(MODEL_DIR.$modelName.".php")) {
            //include the desired model
            require_once(MODEL_DIR.$modelName.".php");
            
            //prepare the name of the class to instantiate the model object from
            $mdlName = $modelName."_Model";

            //check if the given model exists
            if (class_exists($mdlName)) {
                //if the given model is a valid model
                if (get_parent_class($mdlName) == "Gishiki_Model") {
                    //instantiate a new object from the model class
                    $this->Model = new $mdlName();
                } else {
                    //the model is not valid
                    throw new ModelException("The requested model doen't extends Gishiki_Model, so it is not a valid model", 2);
                }
            } else {
                //the model is not existent
                throw new ModelException("The requested model cannot be found or it doesn't exists", 1);
            }
        } else {
            throw new ModelException("The requested model cannot be used because it doesn't exists", 0);
        }
    }
    
    /**
     * Executes an operation defined inside the loaded model
     * 
     * @param strig $operation the name of the model operation
     * @param mixed $data the data to be passed to the model operation
     * @return mixed the return value returned by the model operation
     * @throws ModelException the exception that prevents the operation to be executed correcltly
     */
    public function ExecuteOperation($operation, $data = NULL) {
        //check if the requested operation can be performed
        if (method_exists($this->Model, $operation)) {
            //and perform it, returning the returned value by the executed operation
            return $this->Model->$operation($data);
        } else {
            throw new ModelException("The requested operation cannot be performed because the operation doesn't exists", 3);
        }
    }
}