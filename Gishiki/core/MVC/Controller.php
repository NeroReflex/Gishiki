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
     * The Gishiki base controller. Every controller inherit from this class
     * 
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    class Controller {
        //an array with request details
        protected $receivedDetails;

        /**
         * Initialize the controller. Each controller MUST call this constructor
         */
        public function __construct() {
            
        }
        
        /**
         * Return what the request detail at the given index
         * 
         * @param integer $argumentNumber the index number of the searched argument
         * @return mixed Data on the given index or NULL
         */
        protected function GetRequestDetail($argumentNumber) {
            if (isset($this->receivedDetails[$argumentNumber])) {
                return $this->receivedDetails[$argumentNumber];
            } else {
                return NULL;
            }
        }
        
        /**
         * Return the number of additional details that the client gave to the 
         * called controller
         * 
         * @return integer the number of received details
         */
        protected function RequestDetailsCount() {
            return count($this->receivedDetails);
        }
        
        /**
         * Change the HTTP status code
         */
        public function __destruct() {
            
        }
    }
}