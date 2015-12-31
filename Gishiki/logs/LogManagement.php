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

            }
        }

        /**
         * Store a log entry to the log server/file.
         *
         * @param Log $entry the log entry to be saved/stored
         */
        static function Save(Log &$entry) {

        }
    }
}