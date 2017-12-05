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

use Symfony\Component\Debug\Debug;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;
use Gishiki\Core\Router\Router;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

/**
 * The Gishiki action starter and framework entry point.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class Application
{
    use ApplicationDatabaseTrait;
    use ApplicationLoggerTrait;

    /**
     * @var Configuration the application configuration
     */
    protected $configuration;

    /**
     * @var RequestInterface the request sent to the framework
     */
    protected $request;

    /**
     * @var EmitterInterface the response emitter
     */
    protected $emitter;

    /**
     * @var ResponseInterface the response to be emitted
     */
    protected $response;

    /**
     * Begins an application lifecycle.
     *
     * Initialize the Gishiki engine, prepare for
     * the execution of a framework instance and result
     * emitter.
     *
     * The suggester emitter is Zend\Diactoros\Response\SapiEmitter;
     *
     * @param EmitterInterface|null $emitter  the emitter to be used when producing output
     * @param string|array|null     $settings the path of the settings file
     */
    public function __construct(EmitterInterface $emitter = null, $settings = null)
    {
        //load application configuration
        $this->configuration = (is_array($settings)) ? new Configuration($settings) : Configuration::loadFromFile($settings);
        $this->applyConfiguration();

        //setup the emitter (dependency-injection style)
        $this->emitter = $emitter;

        //if a valid one was not provided...
        if (is_null($this->emitter)) {
            $this->emitter = new Response\SapiEmitter();
        }

        //get current request...
        $this->request = ServerRequestFactory::fromGlobals(
            $_SERVER,
            $_GET,
            $_POST,
            $_COOKIE,
            $_FILES
        );

        $this->response = new Response();

        //initialize the database handler
        $this->initializeDatabaseHandler();

        //initialize the logger handler
        $this->initializeLoggerHandler();
    }

    /**
     * Get the current working directory
     *
     * @return string the current directory
     */
    public static function getCurrentDirectory() : string
    {
        return getcwd() . DIRECTORY_SEPARATOR;
    }

    /**
     * Get the application configuration.
     *
     * @return Configuration the configuration
     */
    public function getConfiguration() : Configuration
    {
        return clone $this->configuration;
    }

    /**
     * Apply the application configuration.
     */
    protected function applyConfiguration()
    {
        if ($this->getConfiguration()->get('debug', false) == true) {
            Debug::enable();
            ErrorHandler::register();
            ExceptionHandler::register();
        }

        //apply the database configuration
        $connections = $this->configuration->get('connections');
        if (is_array($connections)) {
            $this->connectDatabase($connections);
        }

        //apply the logging configuration
        $loggers = $this->configuration->get('logging')['interfaces'];
        if (is_array($loggers)) {
            $this->connectLogger($loggers, $this->configuration->get('logging')['automatic']);
        }
    }

    /**
     * Execute the requested operation.
     *
     * @param $router Router the router configured
     */
    public function run(Router &$router)
    {
        //...generate the response
        try {
            $router->run($this->request, $this->response, [
                'connections' => $this->getDatabaseManager(),
                'loggers'     => $this->getLoggerManager(),
            ], $this);
        } catch (\Exception $ex) {
            //generate the response
            $this->response = $this->response->withStatus(500);
            $this->response = $this->response->getBody()->write("<h1>500 Internal Server Error</h1>");

            //write a log entry if necessary
            if ($this->getLoggerManager()->isConnected($this->getDefaultLoggerName())) {
                //retrieve the default logger instance
                $logger = $this->getLoggerManager()->retrieve($this->getDefaultLoggerName());

                if ($logger instanceof LoggerInterface) {
                    //write the log of the exception
                    $logger->error(get_class($ex).
                        ' thrown at: '.$ex->getFile().
                        ': '.$ex->getLine().
                        ' with message('.$ex->getCode().
                        '): '.$ex->getMessage()
                    );
                }
            }
        }
    }

    /**
     * Emit the response generated by calling the run() function.
     *
     * @throws \RuntimeException the response is not valid
     */
    public function emit()
    {
        if (!($this->response instanceof ResponseInterface)) {
            throw new \RuntimeException('Invalid response type ('.gettype($this->response).'): cannot be emitted');
        }

        $this->emitter->emit($this->response);
    }
}
