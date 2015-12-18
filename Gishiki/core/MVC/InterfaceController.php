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
     * The Gishiki base interface controller. Every application interface 
     * inherit from this class.
     * 
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    class Gishiki_InterfaceController extends Gishiki_Controller {
        /**
         * This is what a generic client has requested to your application
         * 
         * @var \Gishiki\JSON\JSONObject the request made by the application through HTTP 
         */
        protected $Request;
        
        /**
         * This is the response that will be given automatically to the client
         * (at the end of the controller lifetime),
         * the purpose of an interface controller is filling this JSON object 
         * 
         * @var \Gishiki\JSON\JSONObject the response that is going to be sent to the client 
         */
        protected $Response;

        /**
         * Initialize the interface controller. Each interface controller MUST call this constructor
         */
        public function __construct() {
            //call the parent constructor
            parent::__construct();
            
            
        }

        /**
         * serialize the response, set the content type to json
         * and send it to the client
         */
        public function __destruct() {
            //call the parent destructor
            parent::__destruct();

            //give the response to the client
            echo \Gishiki\JSON\JSON::SerializeToString($this->Response);
        }
    }
}