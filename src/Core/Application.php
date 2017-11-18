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

use Gishiki\Algorithms\Collections\SerializableCollection;
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
     * @var Config the application configuration
     */
    protected $configuration;

    /**
     * @var string the path of the current directory
     */
    protected $currentDirectory;

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
     * @param EmitterInterface|null $emitter the emitter to be used when producing output
     */
    public function __construct(EmitterInterface $emitter = null)
    {
        //setup the emitter (dependency-injection style)
        $this->emitter = $emitter;

        //if a valid one was not provided...
        if (is_null($this->emitter)) {
            $this->emitter = new Response\SapiEmitter();
        }

        //get the root path
        $documentRoot = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT');
        $this->currentDirectory = (strlen($documentRoot) > 0) ? $documentRoot : getcwd();
        $this->currentDirectory .= DIRECTORY_SEPARATOR;

        //load application configuration
        if (file_exists($this->currentDirectory . "settings.json")) {
            $this->configuration = new Config($this->currentDirectory . "settings.json");

            $this->applyConfiguration();
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
    }

    /**
     * Get the directory containing the application
     *
     * @return string the current directory
     */
    public function getCurrentDirectory() : string
    {
        return $this->currentDirectory;
    }

    /**
     * Apply the application configuration.
     */
    protected function applyConfiguration()
    {
        //apply the database configuration
        $connections = $this->configuration->getConfiguration()->get('connections');
        if (is_array($connections)) {
            $this->connectDatabase($connections);
        }

        //parse databases structure
        $structures = $this->configuration->getConfiguration()->get('structures');
        if (is_array($structures)) {
            foreach ($structures as $structureFile) {
                $description = file_get_contents($this->currentDirectory . $structureFile);

                $importedDescription = SerializableCollection::deserialize($description);

                $this->registerDatabaseStructure($importedDescription);
            }
        }
        //populateStructures

        //apply the logging configuration
        $loggers = $this->configuration->getConfiguration()->get('logging')['interfaces'];
        if (is_array($loggers)) {
            $this->connectLogger($loggers, $this->configuration->getConfiguration()->get('logging')['automatic']);
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
