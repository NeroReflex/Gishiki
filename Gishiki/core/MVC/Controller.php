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
    class Gishiki_Controller {
        //an array with request details
        protected $receivedDetails;

        //the HTTP Status Code to be sent to the client
        private $httpStatusCode;

        /**
         * Initialize the controller. Each controller MUST call this constructor
         */
        public function __construct() {
            //this is an OK response by default
            $this->httpStatusCode = "200";
        }

        /**
         * Set a new HTTP status code for the response
         * 
         * @param mixed $code the new HTTP status code, can be given as a number or as a string
         * @throws Exception the exception that prevent the new status to be used
         */
        protected function ChangeHTTPStatus($code) {
            //check the given input
            $codeType = gettype($code);
            if (($codeType != "string") && ($codeType != "integer")) {
                throw new \Exception("The http error code must be given a string or an integer value, ".$codeType." given");
            }

            //make the $code a string-type variable
            $code = (string)$code;

            //check if the given code is a valid one
            if (!array_key_exists("".$code, getHTTPResponseCodes())) {
                throw new \Exception("The given error code is not recognized as a valid one");
            }

            //change the status code
            $this->httpStatusCode = $code;
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
            //get the supported php status code
            $httpStatusList = getHTTPResponseCodes();

            //build the http status code message
            $httpStatusMessage = $httpStatusList[$this->httpStatusCode];
            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
            header($protocol.' '.$this->httpStatusCode.' '.$httpStatusMessage);

            //set the http status code
            $GLOBALS['http_response_code'] = $this->httpStatusCode;
        }
    }
}