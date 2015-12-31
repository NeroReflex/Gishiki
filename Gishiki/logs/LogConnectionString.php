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
     * Provide log source connection string parsing.
     *
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    abstract class LogConnectionString
    {

        /**
         * Parse the log source connection string
         *
         * @param $connectionString string the cache server connection string
         * @return array the parsing results
         */
        static function Parse($connectionString)/* : array*/
        {
            //create some empty log collection source details
            $conectionDetails = [
                "source_type" => "",
            ];

            //if the connection string is not empty.....
            if (strlen($connectionString) > 0) {
                //try fetching the log collection source type, address and port
                $strings = explode("://", $connectionString, 2);
                if ((strtolower($strings[0]) == "xml") || (strtolower($strings[0]) == "json")) {
                    //update what is going to be returned
                    $conectionDetails = [
                        "source_type" => strtolower($strings[0]),
                        "source_file" => $strings[1]
                    ];
                } else if (strtolower($strings[0]) == "gelf") {
                    //update what is going to be returned
                    $conectionDetails = self::ParseGelf($strings[1]);
                }
            }

            //return the connection details in form of an array
            return $conectionDetails;
        }
    }
}