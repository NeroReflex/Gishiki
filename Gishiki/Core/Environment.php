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
    use Gishiki\Database\DatabaseManager;
    use Zend\Diactoros\Response\SapiStreamEmitter;
    use Zend\Diactoros\Response;
    use Zend\Diactoros\ServerRequestFactory;
    use Gishiki\Algorithms\Strings\Manipulation;
    use Gishiki\Logging\LoggerManager;
    use Gishiki\Core\Router\Router;

    /**
     * Represent the environment used to run controllers.
     *
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    final class Environment extends GenericCollection
    {
        /**
         * @var mixed each environment has its configuration
         */
        private $configuration;

        /**
         * @var Environment this is the currently active environment
         */
        private static $currentEnvironment;

        /**
         * Setup a new environment instance used to fulfill the client request.
         *
         * @param array $userData        the filtered $_SERVER array
         * @param bool  $selfRegister    TRUE if the environment must be assigned as the currently valid one
         * @param bool  $loadApplication loads the entire application configuration if TRUE
         */
        public function __construct(array $userData = [], $selfRegister = false, $loadApplication = false)
        {
            //call the collection constructor of this own class
            parent::__construct($userData);

            //register the current environment
            if ($selfRegister) {
                self::registerEnvironment($this);
            }

            if ($loadApplication) {
                //load the server configuration
                $this->loadConfiguration();
            }
        }

        public static function getValueFromEnvironment(array $collection)
        {
            foreach ($collection as &$value) {
                //check for substitution
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
            $appComplConfig = self::getValueFromEnvironment($appConfiguration);

            //return the application configuration
            return $appComplConfig;
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
            return file_exists(APPLICATION_DIR.'settings.json');
        }

        /**
         * Register the currently active environment.
         *
         * @param Environment $env the currently active environment
         */
        public function registerEnvironment(Environment &$env)
        {
            //register the currently active environment
            self::$currentEnvironment = $env;
        }

        /**
         * Fulfill the request made by the client.
         */
        public function fulfillRequest(Router &$application)
        {
            //get current request...
            $currentRequest = ServerRequestFactory::fromGlobals(
                $_SERVER,
                $_GET,
                $_POST,
                $_COOKIE,
                $_FILES
            );

            //...generate the response
            try {
                $response = $application->run($currentRequest);
            } catch (\Exception $ex) {
                $response = new Response();
                $response = $response->withStatus(400);
                $response = $response->write($ex->getMessage());
            }

            //...and serve it
            $emitter = new SapiStreamEmitter();
            $emitter->emit($response);
        }

        /**
         * Return the currently active environment used to run the controller.
         *
         * @return Environment the current environment
         */
        public static function getCurrentEnvironment()
        {
            //return the currently active environment
            return self::$currentEnvironment;
        }

        /**
         * Load the framework configuration from the config file and return it in an
         * format known to the framework.
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
                    'LOG_DEFAULT' => (isset($config['general']['autolog'])) ? $config['general']['autolog'] : null,

                    //Security Settings
                    'SECURITY' => [
                        'MASTER_SYMMETRIC_KEY' => $config['security']['serverPassword'],
                        'MASTER_ASYMMETRIC_KEY' => $config['security']['serverKey'],
                    ],

                    //Logger connections
                    'LOGGERS' => (array_key_exists('loggers', $config)) ? $config['loggers'] : [],

                    //Database connection
                    'CONNECTIONS' => (array_key_exists('connections', $config)) ? $config['connections'] : [],
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

            //connect every logger instance
            foreach ($this->configuration['LOGGERS'] as $connectionName => &$connectionDetails) {
                LoggerManager::connect($connectionName, $connectionDetails);
            }

            //set the default logger connection
            LoggerManager::setDefault($this->configuration['LOG_DEFAULT']);

            //connect every db connection
            foreach ($this->configuration['CONNECTIONS'] as $connection) {
                DatabaseManager::connect($connection['name'], $connection['query']);
            }
        }

        /**
         * Return the configuration property.
         *
         * @param string $property the requested configuration property
         *
         * @return the requested configuration property or NULL
         */
        public function getConfigurationProperty($property)
        {
            switch (strtoupper($property)) {
                case 'DEVELOPMENT':
                    return $this->configuration['DEVELOPMENT_ENVIRONMENT'];

                case 'LOG_CONNECTION_STRING':
                    return $this->configuration['LOG_DEFAULT'];

                case 'DATA_CONNECTIONS':
                    return $this->configuration['DATABASE_CONNECTIONS'];

                case 'MASTER_ASYMMETRIC_KEY':
                    return $this->configuration['SECURITY']['MASTER_ASYMMETRIC_KEY'];

                case 'MASTER_SYMMETRIC_KEY':
                    return $this->configuration['SECURITY']['MASTER_SYMMETRIC_KEY'];

                case 'RESOURCE_DIR':
                case 'RESOURCE_DIRECTORY':
                    return APPLICATION_DIR.'Resources'.DIRECTORY_SEPARATOR;

                case 'MODEL_DIR':
                    return APPLICATION_DIR.'Models';

                case 'VIEW_DIR':
                case 'VIEW_DIRECTORY':
                    return APPLICATION_DIR.'Views'.DIRECTORY_SEPARATOR;

                case 'CONTROLLER_DIR':
                case 'CONTROLLER_DIRECTORY':
                    return APPLICATION_DIR.'Controllers'.DIRECTORY_SEPARATOR;

                case 'KEYS_DIR':
                case 'KEYS_DIRECTORY':
                case 'ASYMMETRIC_KEYS':
                    return APPLICATION_DIR.'Keyring'.DIRECTORY_SEPARATOR;

                case 'APPLICATION_DIR':
                case 'APPLICATION_DIRECTORY':
                    return APPLICATION_DIR;

                default:
                    return;
            }
        }
    }
}
