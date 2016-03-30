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
 * The Gishiki base controller. Every controller inherit from this class
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class Gishiki_Controller {
    //an array with request details
    protected $receivedDetails;

    //an instance of the model
    protected $Model;

    //the HTML to be sent to the client
    private $rawContent;

    //the HTTP Status Code to be sent to the client
    private $httpStatusCode;
    
    /**
     * Initialize the controller. Each controller MUST call this constructor
     */
    public function __construct() {

        //setup an empty content
        $this->rawContent = "";
        
        //this is an OK response by default
        $this->httpStatusCode = "200";
        
        //scan the class inclusion directory
        $scannedClassDir = scandir(CLASS_DIR);
        
        //cycle each file/folder contained into the class directory
        reset($scannedClassDir);
        while ($currentFileOrDir = current($scannedClassDir))
        {
            //if a PHP file is found load it
            if (pathinfo(CLASS_DIR.$currentFileOrDir, PATHINFO_EXTENSION) == "php") {
                require_once(CLASS_DIR.$currentFileOrDir);
                
                //and check for the class to exists
                $className = pathinfo(CLASS_DIR.$currentFileOrDir, PATHINFO_BASENAME);
                
                if (strpos($className,'.php') !== false) {
                    $className = substr($className, 0, strlen($className) - 4);
                }
                
                if (!class_exists($className)) {
                    exit("Fatal error: a PHP file included inside the classes directory must contains a class that is called as the PHP file.");
                }
            }
            
            //jump to the next file or directory
            next($scannedClassDir);
        }
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
            throw new Exception("The http error code must be given a string or an integer value, ".$codeType." given");
        }
        
        //make the $code a string-type variable
        $code = "".$code;
        
        //check if the given code is a valid one
        if (!array_key_exists("".$code, getHTTPResponseCodes())) {
            throw new Exception("The given error code is not recognized as a valid one");
        }
        
        //change the status code
        $this->httpStatusCode = $code;
    }
    
    /**
     * Calculate the number of characters on the view, at the moment of the 
     * function call
     * 
     * @return integer the number of characters in the display buffer
     */
    protected function GetCurrentBufferDimension() {
        return strlen($this->rawContent);
    }
    
    /**
     * Process a partial view and store to the output buffer that will be given 
     * to the client at the end of the controller lifetime
     * 
     * @param string $viewName the name of the partial view WITHOUT '.html' or '.json'
     * @param array $dataSubset an array of sobstitution strings
     * @throws Exception an exception is thrown if the partial view cannot be found
     */
    protected function ProcessPartialView($viewName, $dataSubset = NULL) {
        //check for the partial view existence
        if ((file_exists(VIEW_DIR.$viewName.".html")) || (file_exists(VIEW_DIR.$viewName.".json"))) {
            //is the view an html view?
            $isHTML = FALSE;
            
            //get the raw partial view
            $content = "";
            if (file_exists(VIEW_DIR.$viewName.".html")) {
                $content = file_get_contents(VIEW_DIR.$viewName.".html");
                $isHTML = TRUE;
            } else {
                $content = file_get_contents(VIEW_DIR.$viewName.".json");
            }
            
            //if the view processed is an html view it may include less and/or scss files:
            if ($isHTML) {
                //compile & include every less file included in the current view
                lessIntegration::IncludeAnyLESS($content);

                //compile & include every scss file included in the current view
                scssIntegration::IncludeAnySCSS($content);
            }

            //for each data subset query update the partial view
            if (gettype($dataSubset) == "array")
            {
                //perform swaps/replacements
                $currentData = NULL;
                while ($currentData = current($dataSubset)) {
                    $currentDataIndex = key($dataSubset);
                    $content = str_replace("{{".$currentDataIndex."}}", $currentData, $content);
                    next($dataSubset);
                }
            }

            //store the partial view
            $this->rawContent = $this->rawContent.$content;
        } else {
            throw new Exception("The partial view \'".$viewName."\' cannot be found");
        }
    }

    /**
     * Build the page, send it to the browser and release the memory
     * for a future php script
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
        
        //try to decode a json object
        json_decode($this->rawContent);

        //is the content a valid json content?
        $isJSON = FALSE;

        //if the decode operation is a success then the content is a json content
        if (json_last_error() == JSON_ERROR_NONE)
        //and, since there isn't any render operation previously performed
        {
            //an header with the content type must be added
            header('Content-Type: application/json');

            //the content type is a valid JSON
            $isJSON = TRUE;
        }

        //if the compression is enabled
        if (Environment::GetCurrentEnvironment()->GetConfigurationProperty('ACTIVE_COMPRESSION_ON_RESPONSE')) {
            //if the compression cannot be used
            if((strstr($_SERVER['HTTP_USER_AGENT'], 'W3C_Validator') !== FALSE) || (strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') === FALSE) || ($isJSON)){
                //print out the uncompressed content
                echo($this->rawContent);
            } else {
                //send GZIP compressed data
                //tell the browser the response is gzipped
                header("Content-Encoding: gzip");

                //gzip the response
                print("\x1f\x8b\x08\x00\x00\x00\x00\x00"); 
                $contents = gzcompress($this->rawContent, 9);
                $contents = substr($contents, 0, strlen($this->rawContent));

                //send the gzipped response
                echo($contents);
            }
        } else {
            //print out the uncompressed content
            echo($this->rawContent);
        }
    }
}