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

use Gishiki\Logging\LoggerManager;

/**
 * This is a working implementation of PSR-3 logger connections handler for the Application class.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
trait ApplicationLoggerTrait
{
    /**
     * @var LoggerManager the logger manager
     */
    protected $loggersConnections;

    /**
     * @var string the name of the default logger to be used when logging an unhandled exception
     */
    protected $defaultLoggerName;

    /**
     * Get the PSR-3 loggers manager used within the current application.
     *
     * @return LoggerManager the collection of loggers
     */
    public function &getLoggerManager() : LoggerManager
    {
        return $this->loggersConnections;
    }

    /**
     * Set the name of the default logger.
     *
     * @param string $name the name of the default connection
     */
    public function setDefaultLoggerName($name)
    {
        //set the default logger connection
        if ((is_string($name)) && (strlen($name) > 0)) {
            $this->defaultLoggerName = $name;
        }
    }

    /**
     * Get the name of the default logger.
     *
     * @return string the name of the default logger
     */
    public function getDefaultLoggerName() : string
    {
        return !is_null($this->defaultLoggerName) ? $this->defaultLoggerName : "default";
    }

    /**
     * Initialize the application internal logger handler
     */
    protected function initializeLoggerHandler()
    {
        //setup the logger manager
        if (!$this->isInitializedLoggerHandler()) {
            $this->loggersConnections = new LoggerManager();
        }
    }

    /**
     * Check if the logger handler has been initialized.
     *
     * @return bool true if the logger handler is initialized
     */
    protected function isInitializedLoggerHandler() : bool
    {
        return !is_null($this->loggersConnections);
    }

    /**
     * Prepare every logger instance setting the default one.
     *
     * If the default logger name is given it will be set as the default one.
     *
     * @param array  $connections the array of connections
     * @param string $default     the name of the default connection
     */
    public function connectLogger(array $connections, $default)
    {
        if (!$this->isInitializedLoggerHandler()) {
            $this->initializeLoggerHandler();
        }

        //connect every logger instance
        foreach ($connections as $connectionName => &$connectionDetails) {
            $this->loggersConnections->connect($connectionName, $connectionDetails);
        }

        $this->setDefaultLoggerName($default);
    }
}
