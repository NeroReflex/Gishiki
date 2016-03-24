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

namespace Gishiki\Core {
    
    /**
     * Represent the environment used to run controllers.
     * 
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    class Environment {
        /** each environment has its configuration */
        private $configuration;

        /** this is the currently active environment */
        private static $currentEnvironment;

        /** an array containing the request before and after the routing */
        private $request;

        /** additional details given by the client */
        private $resourceDetails;

        /** The cookie functions provider */
        public $Cookies;
        
        /**
         * Setup a new environment instance used to fulfill the client request
         * 
         * @param boolean $selfRegister TRUE if the environment must be assigned as the currently valid one
         */
        public function __construct($selfRegister = FALSE) {
            //register the current environment
            if ($selfRegister) {
                Environment::RegisterEnvironment($this);
            }

            //load the server configuration
            $this->LoadConfiguration();

            //this will be initialized later on if needed
            $this->resourceDetails = NULL;

            //initialize the caching engine
            \Gishiki\Caching\Cache::Initialize();

            //prepare the cookie manager
            $this->Cookies = new \CookieProvider();
        }

        /**
         * Test if the current connection uses HTTP over SSL
         * 
         * @return boolean TRUE if SSL is enabled, false otherwise
         */
        public function SecureConnectionEnabled() {
            return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off');
        }

        /**
         * Register the currently active environment
         * 
         * @param Environment $env the currently active environment
         */
        public function RegisterEnvironment(Environment &$env) {
            //register the currently active environment
            Environment::$currentEnvironment = $env;
        }

        /**
         * Fullfill the request made by the client
         * 
         * @param string $nonRoutedResource the non re-routed request
         */
        public function FulfillRequest($nonRoutedResource) {
            //check the route an active the router if it is enabled
            $rerouted = $nonRoutedResource;
            if (Routing::IsEnabled()) {
                $rerouted = Routing::Route($rerouted);
            }

            //save the request before and after re-routing
            $this->request = [
                0 => $nonRoutedResource,
                1 => $rerouted
            ];

            //split the requested resource string to
            $decoded = explode("/", $this->request[1]);
            //analyze it

            if ((strtoupper($decoded[0]) == "RESOURCE") || (strtoupper($decoded[0]) == "STATIC")) {
                //serve the static resource
                $this->ServeStaticResource($decoded);
            } else if ((strtoupper($decoded[0]) == "SERVICE") || (strtoupper($decoded[0]) == "API")) {
                //the resource that must be invoked
                $resource = NULL;

                //get the controller name and the action to be performed
                $argn = count($decoded);
                if ($argn >= 3) {
                    $resource = [
                        "controllerClass" => $decoded[1],
                        "controllerAction" => $decoded[2]
                    ];
                } else if ($argn == 2) {
                    $resource = [
                        "controllerClass" => $decoded[1],
                        "controllerAction" => "Index"
                    ];
                } else {
                    $resource = [
                        "controllerClass" => "Default",
                        "controllerAction" => "Index"
                    ];
                }

                //fill the additional resource details
                for ($counter = 3; $counter < count($decoded); $counter++) {
                    $this->resourceDetails[] = $decoded[$counter];
                }

                $serializedRequest = "[]";
                $received_json_data = filter_input(INPUT_POST, 'data');
                if ((isset($received_json_data)) && ($received_json_data != "")) {
                    $serializedRequest = $received_json_data;
                }

                //initialize and execute the controller
                $this->ExecuteService($resource, $serializedRequest);
            } else {
                //the resource that must be invoked
                $resource = NULL;

                //get the controller name and the action to be performed
                $argn = count($decoded);
                if ($argn >= 2) {
                    $resource = [
                        "controllerClass" => $decoded[0],
                        "controllerAction" => $decoded[1]
                    ];
                } else if ($argn == 1) {
                    $resource = [
                        "controllerClass" => $decoded[0],
                        "controllerAction" => "Index"
                    ];
                } else {
                    $resource = [
                        "controllerClass" => "Default",
                        "controllerAction" => "Index"
                    ];
                }

                //fill the additional resource details
                for ($counter = 2; $counter < count($decoded); $counter++) {
                    $this->resourceDetails[] = $decoded[$counter];
                }

                //initialize and execute the controller
                $this->ExecuteWebController($resource);
            }
        }
        
        /**
         * Execute the requested interface controller
         * 
         * @param array $resource the array filled by Environment::FulfillRequest()
         * @param string $jsonRequest the request encoded as a valid json string
         */
        private function ExecuteService($resource, $jsonRequest) {
            //the response will be in json format
            header('Content-Type: application/json');
            
            //setup the json response
            $response = array(
                //append the timestamp to the response
                "TIMESTAMP" => time()
            );

            try {
                //deserialize the request
                $request = \Gishiki\JSON\JSON::DeSerializeFromString($jsonRequest);
                
                if (file_exists(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('CONTROLLER_DIR').$resource["controllerClass"].".php"))
                {
                    //require the controller file
                    include(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('CONTROLLER_DIR').$resource["controllerClass"].".php");

                    //check for the class existence
                    if (class_exists($resource["controllerClass"]."_Controller")) {
                        //prepare the name of the class and reflect the class with the given name
                        $reflectedControllerClass = new \ReflectionClass($resource["controllerClass"]."_Controller");

                        //instantiate a new object from the reflected controller class
                        $ctrl = $reflectedControllerClass->newInstance();

                        //check for action existence
                        if (method_exists($ctrl, $resource["controllerAction"])) {
                            //call the method inside the controller instantiated object
                            //binding the additional request details to the current controller
                            $action = new \ReflectionMethod($ctrl, $resource["controllerAction"]);
                            $action->setAccessible(TRUE);
                            $response = $action->invoke($ctrl, [$request]);
                        } else { //display the error
                               
                        }
                    } else { //display the error
                        
                    }
                } else { //dispay the error
                    
                }
            } catch (\Gishiki\JSON\JSONException $ex) {
                //add "Error": 1 to the JSON response
                $response["Error"] = 1;
                
                //add the error message
                $response["ErrorDetails"] = $ex->getMessage();
            }
            
            //give the result to the client in a JSON format
            echo(\Gishiki\JSON\JSON::SerializeToString($response));
        }
        
        /**
         * Execute the requested web controller or the error one
         * 
         * @param array $resource the array filled by Environment::FulfillRequest()
         */
        private function ExecuteWebController($resource) {
            if (file_exists(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('WEB_CONTROLLER_DIR').$resource["controllerClass"].".php"))
            {
                //require the controller file
                include(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('WEB_CONTROLLER_DIR').$resource["controllerClass"].".php");

                //check for the class existence
                if (class_exists($resource["controllerClass"]."_Controller")) {
                    //prepare the name of the class and reflect the class with the given name
                    $reflectedControllerClass = new \ReflectionClass($resource["controllerClass"]."_Controller");

                    //instantiate a new object from the reflected controller class
                    $ctrl = $reflectedControllerClass->newInstance();

                    //bind the additional request details to the current controller
                    $reflectedControllersDetails = new \ReflectionProperty($ctrl, "receivedDetails");
                    $reflectedControllersDetails->setAccessible(TRUE);
                    $reflectedControllersDetails->setValue($ctrl, $this->resourceDetails);

                    //check for action existence
                    if (method_exists($ctrl, $resource["controllerAction"])) {
                        //call the method inside the controller instantiated object
                        $action = new \ReflectionMethod($ctrl, $resource["controllerAction"]);
                        $action->setAccessible(TRUE);
                        $action->invoke($ctrl);
                    } else { //display the custom error page
                        $errorResource = [
                            "controllerClass" => "Error",
                            "controllerAction" => "InvalidAction"
                        ];

                        if ($resource["controllerClass"] != "Error") {
                            $this->ExecuteWebController($errorResource);
                        } else {
                            exit("The requested resource cannot be found and the error controller is not deployed");
                        }
                    }
                } else { //display the custom error page
                    $errorResource = [
                        "controllerClass" => "Error",
                        "controllerAction" => "InvalidController"
                    ];

                    if ($resource["controllerClass"] != "Error") {
                        $this->ExecuteWebController($errorResource);
                    } else {
                        exit("The requested controller cannot be found and the error controller is not deployed");
                    }
                }
            } else {
                $errorResource = [
                    "controllerClass" => "Error",
                    "controllerAction" => "ControllerNotFound"
                ];

                if ($resource["controllerClass"] != "Error") {
                    $this->ExecuteWebController($errorResource);
                } else {
                    exit("The requested controller cannot be found and the error controller is not deployed");
                }
            }
        }

        /**
         * Serve a static resource to the client
         * 
         * @param array $resource the exploded resource to be served
         * @param boolean $asAttachment should the resource be provided as an attachment?
         */
        private function ServeStaticResource($resource, $asAttachment = FALSE) {
            //import the array of MIME Types
            $mime_types_map = getMIMETypes();

            $resourcePath = \Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('RESOURCE_DIR');

            //build the resource full system path
            $n = count($resource);
            for ($i = 1; $i < $n; $i++) {
                $resourcePath = $resourcePath.$resource[$i];

                if ($i != ($n - 1)) {
                    $resourcePath = $resourcePath.DS;
                }
            }

            //give the file to the client if it exists
            if (file_exists($resourcePath)) {
                //get the file extension to provide the MIME Type
                $info = new \SplFileInfo($resourcePath);
                $extension = $info->getExtension();

                //get the correct mime type

                if (array_key_exists($extension, $mime_types_map)) {
                    //give to the client the correct mime type
                    header('Content-Type: '.$mime_types_map[$extension]);
                } else {
                    $asAttachment = TRUE;
                }

                //provide the file as an attachment if it is requested or necessary due to the unknown mime type
                if ($asAttachment) { header('Content-Disposition: attachment; filename="'.$resource[n - 1].'"'); }

                if (($extension != "js") && ($extension != "css")) {
                    //give to the client the file length
                    header('Content-Length: '.filesize($resourcePath));

                    //give to the client the file
                    readfile($resourcePath);
                } else if ($extension == "js") { //minify the js file
                    //get the minified js script
                    $minifiedResource = \CachedMinification::MinifyJavaScript($resourcePath);

                    //and serve it
                    header('Content-Length: '.strlen($minifiedResource));
                    echo($minifiedResource);
                } else { //minify the css file
                    //get the minified css script
                    $minifiedResource = \CachedMinification::MinifyCascadingSheetStyle($resourcePath);

                    //and serve it
                    header('Content-Length: '.strlen($minifiedResource));
                    echo($minifiedResource);
                }
            } else {
                //build&run error page
                $errorResource = [
                    "controllerClass" => "Error",
                    "controllerAction" => "ResourceNotFound"
                ];

                $this->ExecuteWebController($errorResource);
            }
        }

        /**
         * Return the currenlty active environment used to run the controller
         * 
         * @return Environment the current environment
         */
        static function GetCurrentEnvironment() {
            //return the currently active environment
            return self::$currentEnvironment;
        }

        /**
         * Load the framework configuration from the config file and return it in an
         * format kwnown to the framework
         */
        private function LoadConfiguration() {
            //setup a bare minimum configuration
            $this->configuration = [
                "FILESYSTEM" => [
                    "APPLICATION_DIRECTORY" => APPLICATION_DIR,
                ],
            ];

            //get the security configuration of the current application
            $config = [];
            if (Application::Exists()) {
                $config = Application::GetSettings();
                //General Configuration
                $this->configuration = [
                    //Security Settings
                    "SECURITY" => [
                        "MASTER_SYMMETRIC_KEY" => $config["security"]["serverPassword"],
                        "MASTER_ASYMMETRIC_KEY_REFERENCE" => $config["security"]["serverKey"],
                    ],

                    //Cookies Configuration
                    "COOKIES" => [
                        "PREFIX" => $config["cookies"]["cookiesPrefix"],
                        "ENCRYPTION_MARK" => $config["cookies"]["cookiesEncryptedPrefix"],
                        "ENCRYPTION_KEY" => $config["cookies"]["cookiesKey"],
                        "DEFAULT_LIFETIME" => $config["cookies"]["cookiesExpiration"],
                        "VALIDITY_PATH" => $config["cookies"]["cookiesPath"],
                    ],

                    //Filesystem Configuration
                    "FILESYSTEM" => [
                        "PASSIVE_ROUTING_FILE" => APPLICATION_DIR.$config["routing"]["passiveRules"],
                        "ACTIVE_ROUTING_FILE" => APPLICATION_DIR.$config["routing"]["activeRules"]
                    ],

                    //Caching Configuration
                    "CACHE" => [
                        "ENABLED" => (($config["cache"]["enabled"] != NULL) && ($config["cache"]["enabled"] == TRUE)),
                        "SERVER" => $config["cache"]["server"],
                    ],

                    //Routing Configuration
                    "ROUTING" => [
                        "ENABLED" => FALSE,
                        "PASSIVE_ROUTING" => [],
                        "ACTIVE_ROUTING" => [],
                    ],
                ];
            }
            
            if (count($config) > 2) {
                //get general environment configuration
                $this->configuration["DEVELOPMENT_ENVIRONMENT"] = $config["general"]["development"];

                //load the routing configuration
                $this->configuration["ROUTING"]["ENABLED"] = $config["routing"]["routing"];
                $this->configuration["ROUTING"]["PASSIVE_ROUTING"] = Routing::GetConfiguration($this->configuration["FILESYSTEM"]["PASSIVE_ROUTING_FILE"]);
                $this->configuration["ROUTING"]["ACTIVE_ROUTING"] = Routing::GetConfiguration($this->configuration["FILESYSTEM"]["ACTIVE_ROUTING_FILE"]);
                
                //load the service interface external source
                $protocol = "http://";
                if ($config["filesystem"]["interfaceControllerHostSSL"]) {
                    $protocol = "https://";
                }
                $this->configuration["FILESYSTEM"]["SERVICE_HOST"] = $protocol.$config["filesystem"]["interfaceControllerHost"];
                
            }
            
            //check for the environment configuration
            if (isset($this->configuration["DEVELOPMENT_ENVIRONMENT"])) {
                if ($this->configuration["DEVELOPMENT_ENVIRONMENT"])
                {
                    ini_set('display_errors', 1);
                    error_reporting(E_ALL);
                } else {
                    ini_set('display_errors', 0);
                    error_reporting(0);
                }
            }
        }
        
        /**
         * Return the configuration property
         * 
         * @param string $property the requested configuration property
         * @return the requested configuration property or NULL
         */
        public function GetConfigurationProperty($property) {
            switch(strtoupper($property)) {
                case "INTERFACE_SERVICE_HOST":
                    return $this->configuration["FILESYSTEM"]["SERVICE_HOST"];
                
                case "LOGGING_ENABLED":
                    return $this->configuration["LOG"]["ENABLED"];

                case "LOGGING_COLLECTION_SOURCE":
                    return $this->configuration["LOG"]["SOURCES"];

                case "CACHING_ENABLED":
                    if (isset($this->configuration["CACHE"]["ENABLED"]))
                    {   return $this->configuration["CACHE"]["ENABLED"];    }
                    else {  return false;    }

                case "CACHE_CONNECTION_STRING":
                    return $this->configuration["CACHE"]["SERVER"];

                case "MASTER_ASYMMETRIC_KEY_NAME":
                    return $this->configuration["SECURITY"]["MASTER_ASYMMETRIC_KEY_REFERENCE"];

                case "ROUTING_ACTIVE_CONFIGURATION":
                    return $this->configuration["ROUTING"]["ACTIVE_ROUTING"];

                case "ROUTING_CONSTANT_CONFIGURATION":
                    return $this->configuration["ROUTING"]["PASSIVE_ROUTING"];

                case "ROUTING_ENABLED":
                    return $this->configuration["ROUTING"]["ENABLED"];

                case "SECURITY_MASTER_SYMMETRIC_KEY":
                    return $this->configuration["SECURITY"]["MASTER_SYMMETRIC_KEY"];

                case "COOKIE_VALIDITY_PATH":
                    return $this->configuration["COOKIES"]["VALIDITY_PATH"];

                case "COOKIE_PREFIX":
                    return $this->configuration["COOKIES"]["PREFIX"];

                case "COOKIE_ENCRYPTION_MARK":
                    return $this->configuration["COOKIES"]["ENCRYPTION_MARK"];

                case "COOKIE_ENCRYPTION_KEY":
                    return $this->configuration["COOKIES"]["ENCRYPTION_KEY"];

                case "COOKIE_DEFAULT_LIFETIME":
                    return $this->configuration["COOKIES"]["DEFAULT_LIFETIME"];

                case "RESOURCE_DIR":
                case "RESOURCE_DIRECTORY":
                    return APPLICATION_DIR."Resources".DS;

                case "VIEW_DIR":
                case "VIEW_DIRECTORY":
                    return APPLICATION_DIR."Views".DS;
                    
                case "WEB_CONTROLLER_DIR":
                case "WEB_CONTROLLER_DIRECTORY":
                    return APPLICATION_DIR."Controllers".DS;

                case "CONTROLLER_DIR":
                case "CONTROLLER_DIRECTORY":
                    return APPLICATION_DIR."Services".DS;

                case "KEYS_DIR":
                case "KEYS_DIRECTORY":
                case "ASYMMETRIC_KEYS":
                    return APPLICATION_DIR."Keyring".DS;

                case "APPLICATION_DIR":
                case "APPLICATION_DIRECTORY":
                    return APPLICATION_DIR;

                case "PASSIVE_ROUTING_FILE":
                    return ["FILESYSTEM"]["PASSIVE_ROUTING_FILE"];
                
                case "ACTIVE_ROUTING_FILE":
                    return ["FILESYSTEM"]["ACTIVE_ROUTING_FILE"];
                    
                default:
                    return NULL;
            }
        }

        /**
         * Detect the disponibility of a php extension or feature
         * 
         * @param string $extensionAlias the extension alias (NOT THE EXTENSION NAME)
         * @return boolean true if the extension is enabled, false otherwise
         */
        static function ExtensionSupport($extensionAlias) {
            switch (strtoupper($extensionAlias)) {
                case 'MEMCACHED':
                    return class_exists("Memcached");

                case 'OPENSSL':
                    return ((function_exists("openssl_pkey_get_private")) && (function_exists("openssl_verify")));

                case 'ZLIB':
                    return function_exists("zlib_encode");

                case 'FILEINFO':
                    return function_exists("finfo_file");

                case 'SIMPLEXML':
                    return class_exists("SimpleXMLElement");

                case 'SQL':
                    return extension_loaded('PDO');
                    
                default:
                    return FALSE;
            }
        }

        /**
         * Detect if the http request was done using AJAX. Note that this function may fail at detecting ajax calls.
         * 
         * @return boolean TRUE if this is for sure an ajax request, FALSE otherwise
         */
        public function IsRequestAJAX() {
            return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
        }
    }
}