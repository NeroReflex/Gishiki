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

namespace Gishiki\Core;

use Gishiki\Core\Router\Router;
use Gishiki\Database\DatabaseManager;
use Gishiki\Logging\LoggerManager;
use Gishiki\Security\Encryption\Asymmetric\PrivateKey;
use Zend\Diactoros\Response\SapiStreamEmitter;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

/**
 * The Gishiki action starter and framework entry point.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class Application
{
    /**
     * @var Config the application configuration
     */
    protected $configuration;

    /**
     * @var string the path of the current directory
     */
    protected $currentDirectory;

    /**
     * Initialize the Gishiki engine and prepare for
     * the execution of a framework instance.
     */
    public function __construct()
    {
        //get the root path
        $documentRoot = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT');

        $this->currentDirectory = (strlen($documentRoot) > 0) ?
            filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') : getcwd();

        $this->currentDirectory .= DIRECTORY_SEPARATOR;

        if (file_exists($this->currentDirectory.'openssl.cnf')) {
            PrivateKey::$openSSLConf = $this->currentDirectory.'openssl.cnf';
        }

        //load application configuration
        if (file_exists($this->currentDirectory . "settings.json")) {
            $this->configuration = new Config($this->currentDirectory . "settings.json");

            $this->applyConfiguration();
        }
    }

    /**
     * Apply the application configuration.
     */
    protected function applyConfiguration()
    {
        $this->setDevelopmentEnv($this->configuration->getConfiguration()->get('general')['development']);
        $this->setTimeLimit($this->configuration->getConfiguration()->get('general')['timelimit']);

        $connections = $this->configuration->getConfiguration()->get('connections');
        if (is_array($connections)) {
            $this->connectDatabase($connections);
        }

        $loggers = $this->configuration->getConfiguration()->get('loggers');
        if (is_array($loggers)) {
            $this->connectLogger($loggers, $this->configuration->getConfiguration()->get('general')['autolog']);
        }
    }

    /**
     * Prepare every logger instance setting the default one.
     *
     * If the default logger name is given it will be set as the default one.
     *
     * @param array  $connections the array of connections
     * @param string $default     the name of the default connection
     */
    protected function connectLogger(array $connections, $default)
    {
        //connect every logger instance
        foreach ($connections as $connectionName => &$connectionDetails) {
            LoggerManager::connect($connectionName, $connectionDetails);
        }

        //set the default logger connection
        if (is_string($default) && (strlen($default) > 0) && (array_key_exists($default, $connections))) {
            LoggerManager::setDefault($default);
        }
    }

    /**
     * Prepare connections to databases.
     *
     * @param array $connections the array of connections
     */
    protected function connectDatabase(array $connections)
    {
        //connect every db connection
        foreach ($connections as $connection) {
            DatabaseManager::connect($connection['name'], $connection['query']);
        }
    }

    /**
     * Remove the PHP time limit for a request on false.
     *
     * @param $val bool if false remove the time limit
     */
    protected function setTimeLimit($val)
    {
        if (!$val) {
            //remove default execution time
            set_time_limit(0);
            return;
        }
    }

    /**
     * Set all development output on true.
     *
     * @param $val bool if true development enabled
     */
    protected function setDevelopmentEnv($val)
    {
        //development configuration
        if ($val) {
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
            return;
        }

        ini_set('display_errors', 0);
        error_reporting(0);
    }

    /**
     * Execute the requested operation.
     *
     * @param $application Router
     */
    public function run(Router &$router)
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
            $response = $router->run($currentRequest);
        } catch (\Exception $ex) {
            $response = new Response();
            $response = $response->withStatus(400);
            $response = $response->getBody()->write($ex->getMessage());
        }

        //...and serve it
        $emitter = new SapiStreamEmitter();
        $emitter->emit($response);
    }
}
