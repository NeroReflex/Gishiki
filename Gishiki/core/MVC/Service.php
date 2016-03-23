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
     * The Gishiki base controller for web services.
     * 
     * Every service offered by the application should
     * a function inside a class that inherits from
     * this class.
     * 
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    class Service extends Controller {

        /**
         * Initialize the services container instance.
         * 
         * Each interface controller should call this constructor.
         */
        public function __construct() {
            //call the parent constructor
            parent::__construct();
        }

        /**
         * Dispose the service container instance.
         * 
         * Each interface controller should call this destructor.
         */
        public function __destruct() {
            //call the parent destructor
            parent::__destruct();
        }
    }
}