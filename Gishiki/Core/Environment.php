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
    
    use Gishiki\Algorithms\Collections\GenericCollection;
    use Gishiki\HttpKernel\Request;
    use Gishiki\Algorithms\Manipulation;
    
    /**
     * Represent the environment used to run controllers.
     * 
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    final class Environment extends GenericCollection
    {
        /**
         * Create a mock / fake environment from the given data.
         * 
         * The given data is organized as the $_SERVER variable is
         *
         * @param  array $userData Array of custom environment keys and values
         * @return Environment
         */
        public static function mock(array $userData = [], $selfRegisterOfNewInstance = false, $loadApplication = false)
        {
            $data = array_merge([
                'SERVER_PROTOCOL'      => 'HTTP/1.1',
                'REQUEST_METHOD'       => 'GET',
                'SCRIPT_NAME'          => '',
                'REQUEST_URI'          => '',
                'QUERY_STRING'         => '',
                'SERVER_NAME'          => 'localhost',
                'SERVER_PORT'          => 80,
                'HTTP_HOST'            => 'localhost',
                'HTTP_ACCEPT'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8',
                'HTTP_ACCEPT_CHARSET'  => 'ISO-8859-1,utf-8;q=0.7,*;q=0.3',
                'HTTP_USER_AGENT'      => 'Unknown',
                'REMOTE_ADDR'          => '127.0.0.1',
                'REQUEST_TIME'         => time(),
                'REQUEST_TIME_FLOAT'   => microtime(true),
            ], $userData);
                
            return new Environment($data, $selfRegisterOfNewInstance, $loadApplication);
        }
        
        /** each environment has its configuration */
        private $configuration;

        /** this is the currently active environment */
        private static $currentEnvironment;
        
        /**
         * Setup a new environment instance used to fulfill the client request
         * 
         * @param bool $selfRegister TRUE if the environment must be assigned as the currently valid one
         */
        public function __construct(array $userData = [], $selfRegister = false, $loadApplication = false)
        {
            //call the collection constructor of this own class
            parent::__construct($userData);
            
            //register the current environment
            if ($selfRegister) {
                Environment::RegisterEnvironment($this);
            }

            if ($loadApplication) {
                //load the server configuration
                $this->LoadConfiguration();
            }
        }
        
        /**
         * Read the application configuration (settings.ini) and return the 
         * parsing result
         * 
         * @return array the application configuration
         */
        public static function GetApplicationSettings()
        {
            //get the json encoded application settings
            $settings_configuration = file_get_contents(APPLICATION_DIR."settings.json");

            //update every environment placeholder
            while (strpos($settings_configuration, '{{@'))
            {
                 if (($to_be_replaced = Manipulation::get_between($settings_configuration, '{{@', '}}')) != '') {
                    $value = getenv($to_be_replaced);
                    if ($value !== false) {
                        $settings_configuration = str_replace('{{@'.$to_be_replaced.'}}', $value, $settings_configuration);
                    } elseif (defined($to_be_replaced)) {
                        $settings_configuration = str_replace('{{@'.$to_be_replaced.'}}', constant($to_be_replaced), $settings_configuration);
                    } else {
                        die ("Unknown environment var: ".$to_be_replaced);
                    }
                }
            }

            //parse the settings file
            $appConfiguration = \Gishiki\JSON\JSON::DeSerialize($settings_configuration);

            //return the application configuration
            return $appConfiguration;
        }

        /**
         * Check if the application to be executed exists, is valid and has the
         * configuration file
         * 
         * @return bool the application existence
         */
        public static function ApplicationExists()
        {
            //return the existence of an application directory and a configuratio file
            return ((file_exists(APPLICATION_DIR)) && (file_exists(APPLICATION_DIR."settings.json")));
        }
        
        /**
         * Register the currently active environment
         * 
         * @param Environment $env the currently active environment
         */
        public function RegisterEnvironment(Environment &$env)
        {
            //register the currently active environment
            Environment::$currentEnvironment = $env;
        }

        /**
         * Fullfill the request made by the client
         */
        public function FulfillRequest()
        {
            $current_request = Request::createFromEnvironment(Environment::$currentEnvironment);
            
            //split the requested resource string to analyze it
            $decoded = explode("/", $current_request->getUri()->getPath());

            if ((count($decoded)) && ((strtoupper($decoded[0]) == "SERVICE") || (strtoupper($decoded[0]) == "API"))) {
                die("Unimplemented (yet)");
            } else {
                //include the list of routes
                include(APPLICATION_DIR."routes.php");
                
                //current request
                $current_request = Request::createFromEnvironment(Environment::$currentEnvironment);
                Route::run($current_request);
            }
        }
        
        /**
         * Return the currenlty active environment used to run the controller
         * 
         * @return Environment the current environment
         */
        public static function GetCurrentEnvironment()
        {
            //return the currently active environment
            return self::$currentEnvironment;
        }

        /**
         * Load the framework configuration from the config file and return it in an
         * format kwnown to the framework
         */
        private function LoadConfiguration()
        {
            //get the security configuration of the current application
            $config = [];
            if (self::ApplicationExists()) {
                $config = self::GetApplicationSettings();
                //General Configuration
                $this->configuration = [
                    //get general environment configuration
                    "DEVELOPMENT_ENVIRONMENT" => $config["general"]["development"],
                    "AUTOLOG_URL" => (isset($config["general"]["autolog"])) ? $config["general"]["autolog"] : 'null',
                    
                    //Security Settings
                    "SECURITY" => [
                        "MASTER_SYMMETRIC_KEY" => $config["security"]["serverPassword"],
                        "MASTER_ASYMMETRIC_KEY_REFERENCE" => $config["security"]["serverKey"],
                    ]
                ];
            }
            
            //check for the environment configuration
            if ($this->configuration["DEVELOPMENT_ENVIRONMENT"]) {
                ini_set('display_errors', 1);
                error_reporting(E_ALL);
            } else {
                ini_set('display_errors', 0);
                error_reporting(0);
            }
        }
        
        /**
         * Return the configuration property
         * 
         * @param  string $property the requested configuration property
         * @return the    requested configuration property or NULL
         */
        public function GetConfigurationProperty($property)
        {
            switch (strtoupper($property)) {
                case "LOG_CONNECTION_STRING":
                    return $this->configuration["AUTOLOG_URL"];
                    
                case "MODEL_DIR":
                    return APPLICATION_DIR."Models";
                    
                case "DATA_CONNECTIONS":
                    return $this->configuration["DATABASE_CONNECTIONS"];
                
                case "LOGGING_ENABLED":
                    return $this->configuration["LOG"]["ENABLED"];

                case "LOGGING_COLLECTION_SOURCE":
                    return $this->configuration["LOG"]["SOURCES"];

                case "MASTER_ASYMMETRIC_KEY_NAME":
                    return $this->configuration["SECURITY"]["MASTER_ASYMMETRIC_KEY_REFERENCE"];

                case "SECURITY_MASTER_SYMMETRIC_KEY":
                    return $this->configuration["SECURITY"]["MASTER_SYMMETRIC_KEY"];

                case "RESOURCE_DIR":
                case "RESOURCE_DIRECTORY":
                    return APPLICATION_DIR."Resources".DS;

                case "VIEW_DIR":
                case "VIEW_DIRECTORY":
                    return APPLICATION_DIR."Views".DS;

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
                    
                default:
                    return null;
            }
        }

        /**
         * Detect the disponibility of a php extension or feature
         * 
         * @param  string $extensionAlias the extension alias (NOT THE EXTENSION NAME)
         * @return bool   true if the extension is enabled, false otherwise
         */
        public static function ExtensionSupport($extensionAlias)
        {
            switch (strtoupper($extensionAlias)) {
                case 'MEMCACHED':
                    return class_exists("Memcached");

                case 'OPENSSL':
                    return ((function_exists("openssl_pkey_get_private")) && (function_exists("openssl_verify")));

                case 'ZLIB':
                    return function_exists("zlib_encode");

                case 'SIMPLEXML':
                    return in_array('simplexml', get_loaded_extensions());

                case 'SQL':
                    return extension_loaded('PDO');
                    
                default:
                    return false;
            }
        }

        /**
         * Detect if the http request was done using AJAX. Note that this function may fail at detecting ajax calls.
         * 
         * @return bool TRUE if this is for sure an ajax request, FALSE otherwise
         */
        public function IsRequestAJAX()
        {
            //filter $_SERVER (accessing superglobals directly is a bad idea)
            $_server_filtered = filter_input_array(INPUT_SERVER);
            
            return (!empty($_server_filtered['HTTP_X_REQUESTED_WITH']) && strtolower($_server_filtered['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
        }
    }
}
