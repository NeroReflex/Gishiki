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

namespace Gishiki\Caching {

    /**
     * Provide caching server connection string parsing.
     *
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    abstract class CacheConnectionString
    {

        /**
         * Parse the cache server connection string
         *
         * @param $connectionString string the cache server connection string
         * @return array the parsing results
         */
        public static function Parse($connectionString)/* : array*/
        {
            //create some empty connection details
            $conectionDetails = [
                "server_type" => "",
            ];
            
            //if the connection string is not empty.....
            if (strlen($connectionString) > 0) {
                //try fetching the caching server type, address and port
                $strings = explode("://", $connectionString, 2);
                if ($strings[0] == "memcached") {
                    //update what is going to be returned
                    $conectionDetails = self::ParseMemcached($strings[1]);
                } elseif (($strings[0] == "directory") || ($strings[0] == "filesystem")) {
                    //update what is going to be returned
                    $conectionDetails = self::ParseFilesystem($strings[1]);
                }
            }
            
            //return the connection details in form of an array
            return $conectionDetails;
        }

        /**
         * Get the address and the port of the given memcached server connection string.
         * The string is given in form of "address:port" without "memcached://" prepended
         *
         * @param  string $memcachedConnectionString the connection string
         * @return array  the connection details
         */
        public static function ParseMemcached($memcachedConnectionString = "")/* : array*/
        {
            //split the server into address and port
            $explosion = explode(":", $memcachedConnectionString, 2);

            //provide a default connection if no port or no address were furnished
            if ((!isset($explosion[0])) || ($explosion[0] == "")) {
                $explosion[0] = "localhost";
            }
            if ((!isset($explosion[1])) || ($explosion[1] == "")) {
                $explosion[1] = "11211";
            }

            //return the split result
            return [
                "server_type" => "memcached",
                "server_address" => $explosion[0],
                "server_port" => intval($explosion[1]),
            ];
        }

        /**
         * Get the directory to be used as the caching entry.
         * The string is given in form of "directory/" without "filesystem://" prepended
         *
         * @param  string $directory the connection string
         * @return array  the connection details
         */
        public static function ParseFilesystem($directory = "")/* : array*/
        {
            $directoryPath = "".$directory;
            if ($directoryPath[strlen($directoryPath) - 1] != DS) {
                $directoryPath .= DS;
            }

            //return the split result
            return [
                "server_type" => "filesystem",
                "directory" => $directoryPath,
            ];
        }
    }
}
