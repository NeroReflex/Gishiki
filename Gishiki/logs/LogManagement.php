<?php
/****************************************************************************
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
 *******************************************************************************/

namespace Gishiki\Logging {

    /**
     * An helper class for storing logs of what happens on the server
     *
     * Benato Denis <benato.denis96@gmail.com>
     */
    abstract class LogManagement
    {
        private static $connected = FALSE;

        /**
         * This is used to interract with the log collection, but the way it's done depends on the log used method
         */
        protected static $logCollection;

        /**
         * Initialize the logging engine for the current request.
         * This function is automatically called by the framework.
         * Another call to this function won't produce any effects.
         */
        static function Initialize() {
            if (!self::$connected) {
                //initialize the logging engine only if it is needed
                if (\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('LOGGING_ENABLED')) {
                    //parse the collection source string of log entries
                    self::$logCollection["details"] = LogConnectionString::Parse(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty("LOGGING_COLLECTION_SOURCE"));

                    //connect the log collection source
                    switch (self::$logCollection["details"]["source_type"]) {
                        case "xml":
                        case "json":
                            //build the complete path to the log file
                            self::$logCollection["connection"] = \Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty("APPLICATION_DIR").self::$logCollection["details"]["source_file"];

                            //create the file if it doesn't exists
                            if (!file_exists(self::$logCollection["connection"]))
                                touch(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty("APPLICATION_DIR").self::$logCollection["details"]["source_file"]);

                            //the source is connected
                            self::$connected = TRUE;
                        break;

                        case "graylog2":
                            //build the connection to the server
                            self::$logCollection["connection"] = new \GELFMessagePublisher(self::$logCollection["details"]["host"], self::$logCollection["details"]["port"]);

                            //the source is connected
                            self::$connected = TRUE;
                            break;
                    }
                }
            }
        }

        /**
         * Store a log entry to the log server/file.
         *
         * @param Log $entry the log entry to be saved/stored
         */
        static function Save(Log &$entry) {
            //save the log entry only if the connection have been established
            if (self::$connected) {
                //choose the correct way of writing to the log collection
                switch (self::$logCollection["details"]["source_type"]) {
                    case "xml":

                        break;

                    case "json":

                        break;

                    case "graylog2":
                        //build the GELF message
                        $message = new \GELFMessage();

                        //fill the message
                        $message->setShortMessage($entry->GetShortMessage());
                        $message->setFullMessage($entry->GetLongMessage());
                        $message->setFacility($entry->GetFacility());

                        //publish the log entry
                        self::$logCollection["connection"]->publish($message);
                        break;
                }
            }
        }
    }
}