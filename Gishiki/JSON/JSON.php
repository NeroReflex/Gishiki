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

namespace Gishiki\JSON {

    /**
     * This class provides helpers subroutines used when it is necessary to
     * serialize and deserialize JSON data.
     *
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    abstract class JSON
    {
        /**
         * Deserialize a string that represents a valid JSON content into a 
         * standard PHP-array.
         * 
         * @param string $jsonAsString the json encoded as a string
         *
         * @return array the PHP-compatible JSON format
         *
         * @throws \Gishiki\JSON\JSONException the error that prevents this JSON to be deserialized
         */
        public static function DeSerialize($jsonAsString)
        {
            //try decoding the string
            $nativeSerialization = json_decode($jsonAsString, true);

            //and check for the result
            if (json_last_error() != JSON_ERROR_NONE) {
                throw new JSONException('The given string is not a valid JSON content', 1);
            }

            //the deserialization result MUST be an array
            if (gettype($nativeSerialization) != 'array') {
                $nativeSerialization = [];
            }

            //return the deserialization result if everything went right
            return $nativeSerialization;
        }

        /**
         * Serialize a standard PHP-array into a valid JSON string.
         * 
         * @param array $jsonAsObject the PHP-array to be serialized
         *
         * @return string the serialized JSON
         *
         * @throws \Gishiki\JSON\JSONException the error that prevents this JSON object to be serialized
         */
        public static function Serialize($jsonAsObject)
        {
            //try encoding the json in a string
            $serializationResult = json_encode($jsonAsObject, JSON_PRETTY_PRINT);

            //and check for the result
            if (json_last_error() != JSON_ERROR_NONE) {
                throw new JSONException('The given data cannot be serialized in JSON content', 2);
            }

            //and return the result
            return $serializationResult;
        }
    }
}
