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
 * The base environment for running controllers and models
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class Environment {
    
    /** each environment has its configuration */
    private $configuration;
    
    /** this is the currently active environment */
    private static $currentEnvironment;
    
    /** an array containing the request befor and after the routing */
    private $request;
    
    /** the list of supported database drivers */
    private $databaseDrivers;
    
    /** additional details given by the client */
    private $resourceDetails;
    
    /** The cookie functions provider */
    public $cookies;
    
    /**
     * Setup a new environment instance used to fulfill the client request
     * 
     * @param boolean $selfRegister TRUE if the environment must be assigned as the currently valid one
     */
    public function __construct($selfRegister = FALSE) {
        //load the server configuration
        $this->configuration = self::LoadConfiguration();
        
        //detect database drivers
        $this->DetectDatabaseDrivers();
        
        //this will be initialized later on if needed
        $this->resourceDetails = NULL;
        
        //register the current environment
        if ($selfRegister) {
            Environment::RegisterEnvironment($this);
        }
        
        //prepare the cookie manager
        $this->cookies = new CookieProvider();
        
        //setup the caching system
        CacheManager::Initialize();
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
        $this->request = array(
            0 => $nonRoutedResource,
            1 => $rerouted
        );
        
        //split the requested resource string to
        $decoded = explode("/", $this->request[1]);
        //analyze it
        
        if (strtoupper($decoded[0]) == "RESOURCE") {
            //serve the static resource
            $this->ServeStaticResource($decoded);
        } else {
            //the resource that must be invoked
            $resource = NULL;
            
            //get the controller name and the action to be performed
            $argn = count($decoded);
            if ($argn >= 2) {
                $resource = array(
                    "controllerClass" => $decoded[0],
                    "controllerAction" => $decoded[1]
                );
            } else if ($argn == 1) {
                $resource = array(
                    "controllerClass" => $decoded[0],
                    "controllerAction" => "Index"
                );
            } else {
                $resource = array(
                    "controllerClass" => "Default",
                    "controllerAction" => "Index"
                );
            }
            
            //fulfill the additional resource details
            for ($counter = 2; $counter < count($decoded); $counter++) {
                $this->resourceDetails[] = $decoded[$counter];
            }
            
            //initialize and execute the controller
            $this->ExecuteController($resource);
        }
    }
    
    /**
     * Execute the requested controller or the error one
     * 
     * @param type $resource the array filled by Environment::FulfillRequest()
     */
    private function ExecuteController($resource) {
        if ((file_exists(CONTROLLER_DIR.$resource["controllerClass"].".php")) && (file_exists(MODEL_DIR.$resource["controllerClass"].".php")))
        {
            //require the controller file
            require_once(CONTROLLER_DIR.$resource["controllerClass"].".php");

            //check for the class existence
            if (class_exists($resource["controllerClass"]."_Controller")) {
                if (get_parent_class($resource["controllerClass"]."_Controller") == "Gishiki_Controller") {
                    //prepare the name of the class and reflect the class with the given name
                    $reflectedControllerClass = new ReflectionClass($resource["controllerClass"]."_Controller");
                        
                    //instantiate a new object from the reflected controller class
                    $ctrl = $reflectedControllerClass->newInstance();
                        
                    //instantiate a new object from the managed model class
                    $mdl = new ModelManager($resource["controllerClass"]);
                        
                    //bind the model to the current controller
                    $reflectedControllersModel = new ReflectionProperty($ctrl, "Model");
                    $reflectedControllersModel->setAccessible(TRUE);
                    $reflectedControllersModel->setValue($ctrl, $mdl);
                        
                    //bind the additional request details to the current controller
                    $reflectedControllersDetails = new ReflectionProperty($ctrl, "receivedDetails");
                    $reflectedControllersDetails->setAccessible(TRUE);
                    $reflectedControllersDetails->setValue($ctrl, $this->resourceDetails);
                        
                    //check for action existence
                    if (method_exists($ctrl, $resource["controllerAction"])) {
                        //call the method inside the controller instantiated object
                        $ctrl->$resource["controllerAction"]();
                    } else { //display the custom error page
                        $errorResource = array(
                            "controllerClass" => "Error",
                            "controllerAction" => "InvalidAction"
                        );
                            
                        if ($resource["controllerClass"] != "Error") {
                            $this->ExecuteController($errorResource);
                        } else {
                            exit("The requested resource cannot be found and the error controller is not deployed");
                        }
                    }
                } else { //the controller is not a valid controller
                    exit("a valid controller must inherit from the Gishiki_Controller class.");    
                }
            } else { //display the custom error page
                $errorResource = array(
                    "controllerClass" => "Error",
                    "controllerAction" => "InvalidController"
                );
                            
                if ($resource["controllerClass"] != "Error") {
                    $this->ExecuteController($errorResource);
                } else {
                    exit("The requested controller AND/OR associated model cannot be found and the error controller is not deployed");
                }
            }
        } else {
            $errorResource = array(
                "controllerClass" => "Error",
                "controllerAction" => "ControllerNotFound"
            );
            
            if ($resource["controllerClass"] != "Error") {
                $this->ExecuteController($errorResource);
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
        
        $resourcePath = RESOURCE_DIR;
        
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
            $info = new SplFileInfo($resourcePath);
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
                $minifiedResource = CachedMinification::MinifyJavaScript($resourcePath);
                
                //and serve it
                header('Content-Length: '.strlen($minifiedResource));
                echo($minifiedResource);
            } else { //minify the css file
                //get the minified css script
                $minifiedResource = CachedMinification::MinifyCascadingSheetStyle($resourcePath);
                
                //and serve it
                header('Content-Length: '.strlen($minifiedResource));
                echo($minifiedResource);
            }
        } else {
            //build&run error page
            $errorResource = array(
                "controllerClass" => "Error",
                "controllerAction" => "ResourceNotFound"
            );
                            
            $this->ExecuteController();
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
     * format kwnow to the framework
     * 
     * @return array the parsed configuration
     */
    static function LoadConfiguration() {
        //import general php configuration
        global $Development;
        global $Compression;
        
        //import routing configuration
        global $RoutingEnabled;
        global $RoutingRules;
        global $RoutingRegex;
        
        //import cookies configuration
        global $CookiesPrefix;
        global $CookiesEncryptedPrefix;
        global $CookiesKey;
        global $DefaultCookiesExpiration;
        global $CookiesPath;
        
        //import security configuration
        global $DefaultPassword;
        global $RSAServerUniqueKey;
        
        //import filesystem configuration
        global $ChachingDir;
        global $applicationDir;
        global $controllerDir;
        global $modelDir;
        global $viewDir;
        global $classDir;
        global $resourcesDir;
        global $keyDir;
        global $databaseDir;
        global $databaseConnectionsDir;
        
        //check for users that cannot read comments:
        if (gettype($Development) != "boolean")
            exit("Fatal Error: the Development setting in config.php bust be a boolean value");

        //check for the environment configuration
        if ($Development)
        {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            ini_set('display_errors', 0);
        }
        
        //check for security settings validity
        if (gettype($DefaultPassword) != "string")
            exit("The default password for the server must be encoded as a string, ".gettype($DefaultPassword)." given");
        else if (strlen($DefaultPassword) < 32)
            exit("The default password for the server must be at least 32 cheracters long, ".strlen($DefaultPassword)." given");
        
        //check for the routing validity
        if (gettype($RoutingEnabled) != "boolean")
            exit("The routing enabler parameter in the server configuration must be boolean, ".gettype($RoutingEnabled)." found");
        
        //get directory fullpath
        $cache_Dir = ROOT.$ChachingDir.DS;
        $app_Dir = ROOT.$applicationDir.DS;
        $keys_Dir = $app_Dir.$keyDir.DS;
        $controllers_Dir = $app_Dir.$controllerDir.DS;
        $models_Dir = $app_Dir.$modelDir.DS;
        $views_Dir = $app_Dir.$viewDir.DS;
        $classes_Dir = $app_Dir.$classDir.DS;
        $resources_Dir = $app_Dir.$resourcesDir.DS;
        $databases_Dir = $app_Dir.$databaseDir.DS;
        $databaseConnections_Dir = $app_Dir.$databaseConnectionsDir.DS;
        
        //fill the environment configuration
        $loadedconfiguration = array(
            //General Configuration
            "ENVIRONMENT_TYPE" => $Development,
            "ACTIVE_COMPRESSION" => $Compression,
            
            //Routing Configuration
            "ROUTING" => array(
                "ENABLED" => $RoutingEnabled,
                "CONSTANT_ROUTING" => $RoutingRules,
                "ACTIVE_ROUTING" => $RoutingRegex,
            ),
            
            //Security Settings
            "SECURITY" => array(
                "MASTER_SYMMETRIC_KEY" => $DefaultPassword,
                "MASTER_ASYMMETRIC_KEY_REFERENCE" => $RSAServerUniqueKey,
            ),
            
            //Cookies Configuration
            "COOKIES" => array(
                "PREFIX" => $CookiesPrefix,
                "ENCRYPTION_MARK" => $CookiesEncryptedPrefix,
                "ENCRYPTION_KEY" => $CookiesKey,
                "DEFAULT_LIFETIME" => $DefaultCookiesExpiration,
                "VALIDITY_PATH" => $CookiesPath,
            ),
            
            //Filesystem Configuration
            "FILESYSTEM" => array(
                "CACHE_DIRECTORY" => $cache_Dir,
                "APPLICATION_DIRECOTRY" => $app_Dir,
                "CONTROLLERS_DIRECTORY" => $controllers_Dir,
                "MODELS_DIRECTORY" => $models_Dir,
                "VIEWS_DIRECTORY" => $views_Dir,
                "CLASSES_DIRECTORY" => $classes_Dir,
                "RESOURCES_DIRECTORY" => $resources_Dir,
                "KEYS_DIRECTORY" => $keys_Dir,
                "DATABASES_DIRECTORY" => $databases_Dir,
                "DATABASE_CONNECTIONS_DIRECTORY" => $databaseConnections_Dir,
            ),
        );
        
        //return the loaded configuration
        return $loadedconfiguration;
    }
    
    /**
     * Return the configuration property
     * 
     * @param mixed $property the requested configuration property
     */
    public function GetConfigurationProperty($property) {
        switch(strtoupper($property)) {
            case "ACTIVE_COMPRESSION_ON_RESPONSE":
                return $this->configuration["ACTIVE_COMPRESSION"];
            
            case "MASTER_ASYMMETRIC_KEY_NAME":
                return $this->configuration["SECURITY"]["MASTER_ASYMMETRIC_KEY_REFERENCE"];
                
            case "ROUTING_ACTIVE_CONFIGURATION":
                return $this->configuration["ROUTING"]["ACTIVE_ROUTING"];
            
            case "ROUTING_CONSTANT_CONFIGURATION":
                return $this->configuration["ROUTING"]["CONSTANT_ROUTING"];
            
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
                
            case "CACHE_DIR":
            case "CACHE_DIRECTORY":
                return $this->configuration["FILESYSTEM"]["CACHE_DIRECTORY"];
            
            case "RESOURCE_DIR":
            case "RESOURCE_DIRECTORY":
                return $this->configuration["FILESYSTEM"]["RESOURCES_DIRECTORY"];
            
            case "CLASS_DIR":
            case "CLASS_DIRECTORY":
                return $this->configuration["FILESYSTEM"]["CLASSES_DIRECTORY"];
                
            case "VIEW_DIR":
            case "VIEW_DIRECTORY":
                return $this->configuration["FILESYSTEM"]["VIEWS_DIRECTORY"];
            
            case "MODEL_DIR":
            case "MODEL_DIRECTORY":
                return $this->configuration["FILESYSTEM"]["MODELS_DIRECTORY"];
                
            case "CONTROLLER_DIR":
            case "CONTROLLER_DIRECTORY":
                return $this->configuration["FILESYSTEM"]["CONTROLLERS_DIRECTORY"];
                
            case "KEYS_DIR":
            case "KEYS_DIRECTORY":
            case "ASYMMETRIC_KEYS":
                return $this->configuration["FILESYSTEM"]["KEYS_DIRECTORY"];
            
            case "DATABASE_CONNECTION_DIR":
            case "DATABASE_CONNECTION_DIRECTORY":
                return $this->configuration["FILESYSTEM"]["DATABASE_CONNECTIONS_DIRECTORY"];
            
            case "DATABASE_DIR":
            case "DATABASE_DIRECTORY":
                return $this->configuration["FILESYSTEM"]["DATABASES_DIRECTORY"];
            
            case "APPLICATION_DIR":
            case "APPLICATION_DIRECTORY":
                return $this->configuration["FILESYSTEM"]["APPLICATION_DIRECOTRY"];
            
            default:
                return NULL;
        }
    }
    
    /**
     * Create the list of supported database types and store it
     */
    private function DetectDatabaseDrivers() {
        //this is the list of supported database types
        $this->databaseDrivers = array();
        
        //get the list of database that can be used from PDO
        if (class_exists("PDO")) {
            $this->databaseDrivers = PDO::getAvailableDrivers();
        }
        
        //and add sqlite3 if the native sqlite3 extension is enabled
        if (class_exists("SQLite3")) {
            $this->databaseDrivers[] = "sqlite3";
        }
    }
    
    /**
     * Get the list of supported database types
     * 
     * @return array the array of supported databases
     */
    public function GetDatabaseDrivers() {
        //return the filled list of database drivers
        return $this->databaseDrivers;
    }
    
    /**
     * Detect the disponibility of a php extension or feature
     * 
     * @param string $extensionAlias the extension alias (NOT THE EXTENSION NAME)
     * @return boolean true if the extension is enabled, false otherwise
     */
    static function ExtensionSupport($extensionAlias) {
        switch (strtoupper($extensionAlias)) {
            case 'APC':
                return ((function_exists("apc_fetch")) && (function_exists("apc_store")));
                
            case 'OPENSSL':
                return ((function_exists("openssl_pkey_get_private")) && (function_exists("openssl_verify")));
                
            case 'ZLIB':
                return function_exists("zlib_encode");
                
            case 'FILEINFO':
                return function_exists("finfo_file");
                
            case 'SIMPLEXML':
                return class_exists("SimpleXMLElement");
                
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