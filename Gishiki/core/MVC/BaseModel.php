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
 * The Gishiki base model. Every model inherit from this class
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class Gishiki_Model extends Database {

    /**
     * Setup the model and its database connection
     * @param string $connectionName the name of the connection to the database
     */
    public function __construct() {
        //start initializing the database
        parent::__construct();

        //try to use the default controller connection to the database
        //fail silently if the database connection cannot be opened
        try {
            //get the connection name
            $databaseConnectionName = get_class($this);
            $databaseConnectionName = substr($databaseConnectionName, 0, strlen($databaseConnectionName) - strlen("_Model"));

            //load the proper database connection (if possible)
            $this->UseConnection($databaseConnectionName);
        } catch (Exception $ex) {
            
            
        } catch (DatabaseException $ex) {

        }
            
        
	//if the model-reserved connection cannot be applied....
        if (!$this->CheckConnection()) {
            //try to use the application default connection to the database
            //fail silently if the database connection cannot be opened
            try {
                //load the global database connection (if possible)
                $this->UseConnection();
            } catch (Exception $ex) {

            } catch (DatabaseException $ex) {

            }
        }
    }
}