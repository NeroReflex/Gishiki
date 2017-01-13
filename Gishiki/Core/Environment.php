<?php
/**************************************************************************
Copyright 2017 Benato Denis

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
    use Gishiki\Algorithms\Collections\SerializableCollection;
    use Gishiki\HttpKernel\Request;
    use Gishiki\HttpKernel\Response;
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
         * @param array $userData Array of custom environment keys and values
         *
         * @return Environment
         */
        public static function mock(array $userData = [], $selfRegister = false, $loadApplication = false)
        {
            $data = array_merge([
                'SERVER_PROTOCOL' => 'HTTP/1.1',
                'REQUEST_METHOD' => 'GET',
                'SCRIPT_NAME' => '',
                'REQUEST_URI' => '',
                'QUERY_STRING' => '',
                'SERVER_NAME' => 'localhost',
                'SERVER_PORT' => 80,
                'HTTP_HOST' => 'localhost',
                'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8',
                'HTTP_ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.3',
                'HTTP_USER_AGENT' => 'Unknown',
                'REMOTE_ADDR' => '127.0.0.1',
                'REQUEST_TIME' => time(),
                'REQUEST_TIME_FLOAT' => microtime(true),
            ], $userData);

            return new self($data, $selfRegister, $loadApplication);
        }

        /** each environment has its configuration */
        private $configuration;

        /** this is the currently active environment */
        private static $currentEnvironment;

        /**
         * Setup a new environment instance used to fulfill the client request.
         *
         * @param bool $selfRegister TRUE if the environment must be assigned as the currently valid one
         */
        public function __construct(array $userData = [], $selfRegister = false, $loadApplication = false)
        {
            //call the collection constructor of this own class
            parent::__construct($userData);

            //register the current environment
            if ($selfRegister) {
                self::RegisterEnvironment($this);
            }

            if ($loadApplication) {
                //load the server configuration
                $this->loadConfiguration();
            }
        }

        public static function getValueFromEnvironment(array $collection)
        {
            foreach ($collection as &$value) {
                //check for sobstitution
                if ((is_string($value)) && ((strpos($value, '{{@') === 0) && (strpos($value, '}}') !== false))) {
                    if (($toReplace = Manipulation::getBetween($value, '{{@', '}}')) != '') {
                        $value = getenv($toReplace);
                        if ($value !== false) {
                            $value = $value;
                        } elseif (defined($toReplace)) {
                            $value = constant($toReplace);
                        }
                    }
                } elseif (is_array($value)) {
                    $value = self::getValueFromEnvironment($value);
                } elseif ($value instanceof GenericCollection) {
                    $value = self::getValueFromEnvironment($value->all());
                }
            }

            return $collection;
        }

        /**
         * Read the application configuration (settings.json) and return the
         * parsing result.
         *
         * @return array the application configuration
         */
        public static function getApplicationSettings()
        {
            //get the json encoded application settings
            $config = file_get_contents(APPLICATION_DIR.'settings.json');

            //parse the settings file
            $appConfiguration = SerializableCollection::deserialize($config)->all();

            //complete settings
            $appCompletedConfiguration = self::getValueFromEnvironment($appConfiguration);

            //return the application configuration
            return $appCompletedConfiguration;
        }

        /**
         * Check if the application to be executed exists, is valid and has the
         * configuration file.
         *
         * @return bool the application existence
         */
        public static function applicationExists()
        {
            //return the existence of an application directory and a configuratio file
            return (file_exists(APPLICATION_DIR)) && (file_exists(APPLICATION_DIR.'settings.json'));
        }

        /**
         * Register the currently active environment.
         *
         * @param Environment $env the currently active environment
         */
        public function RegisterEnvironment(Environment &$env)
        {
            //register the currently active environment
            self::$currentEnvironment = $env;
        }

        /**
         * Fullfill the request made by the client.
         */
        public function FulfillRequest()
        {
            //get current request...
            $currentRequest = Request::createFromEnvironment(self::$currentEnvironment);

            //...and serve it
            $response = new Response();

            try {
                //trigger the exception if data is malformed!
                $currentRequest->getDeserializedBody();

                //include the list of routes (if it exists)
                if (file_exists(APPLICATION_DIR.'routes.php')) {
                    include APPLICATION_DIR.'routes.php';
                }

                //...and serve it
                $response = Route::run($currentRequest);
            } catch (\RuntimeException $ex) {
                $response = $response->withStatus(400);
                $response = $response->write($ex->getMessage());
            }

            //send response to the client
            $response->send();
        }

        /**
         * Return the currenlty active environment used to run the controller.
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
         * format kwnown to the framework.
         */
        private function loadConfiguration()
        {
            //get the security configuration of the current application
            $config = [];
            if (self::applicationExists()) {
                $config = self::getApplicationSettings();
                //General Configuration
                $this->configuration = [
                    //get general environment configuration
                    'DEVELOPMENT_ENVIRONMENT' => (isset($config['general']['development'])) ? $config['general']['development'] : false,
                    'AUTOLOG_URL' => (isset($config['general']['autolog'])) ? $config['general']['autolog'] : 'null',

                    //Security Settings
                    'SECURITY' => [
                        'MASTER_SYMMETRIC_KEY' => $config['security']['serverPassword'],
                        'MASTER_ASYMMETRIC_KEY' => $config['security']['serverKey'],
                    ],

                    'CONNECTIONS' => (array_key_exists('connections', $config)) ? $config['connections'] : array()
                ];
            }

            //check for the environment configuration
            if ($this->configuration['DEVELOPMENT_ENVIRONMENT']) {
                ini_set('display_errors', 1);
                error_reporting(E_ALL);
            } else {
                ini_set('display_errors', 0);
                error_reporting(0);
            }

            //connect every db connection
            foreach ($this->configuration['CONNECTIONS'] as $connection) {
                \Gishiki\Database\DatabaseManager::Connect($connection['name'], $connection['query']);
            }
        }

        /**
         * Return the configuration property.
         *
         * @param string $property the requested configuration property
         *
         * @return the requested configuration property or NULL
         */
        public function GetConfigurationProperty($property)
        {
            switch (strtoupper($property)) {
                case 'DEVELOPMENT':
                    return $this->configuration['DEVELOPMENT_ENVIRONMENT'];
                    
                case 'LOG_CONNECTION_STRING':
                    return $this->configuration['AUTOLOG_URL'];

                case 'DATA_CONNECTIONS':
                    return $this->configuration['DATABASE_CONNECTIONS'];

                case 'MASTER_ASYMMETRIC_KEY':
                    return $this->configuration['SECURITY']['MASTER_ASYMMETRIC_KEY'];

                case 'MASTER_SYMMETRIC_KEY':
                    return $this->configuration['SECURITY']['MASTER_SYMMETRIC_KEY'];

                case 'RESOURCE_DIR':
                case 'RESOURCE_DIRECTORY':
                    return APPLICATION_DIR.'Resources'.DS;

                case 'MODEL_DIR':
                    return APPLICATION_DIR.'Models';

                case 'VIEW_DIR':
                case 'VIEW_DIRECTORY':
                    return APPLICATION_DIR.'Views'.DS;

                case 'CONTROLLER_DIR':
                case 'CONTROLLER_DIRECTORY':
                    return APPLICATION_DIR.'Controllers'.DS;

                case 'KEYS_DIR':
                case 'KEYS_DIRECTORY':
                case 'ASYMMETRIC_KEYS':
                    return APPLICATION_DIR.'Keyring'.DS;

                case 'APPLICATION_DIR':
                case 'APPLICATION_DIRECTORY':
                    return APPLICATION_DIR;

                default:
                    return;
            }
        }
    }
}
