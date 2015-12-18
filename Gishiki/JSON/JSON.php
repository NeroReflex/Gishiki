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
     * The basic JSON helper class. This class is designed to be simple and fast
     * as it is one of the core component of Gishiki
     *
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    abstract class JSON {
        /**
         * Deserialize an array that represents a JSON content decoded by PHP's
         * own json_decode() function
         * 
         * @param array $nativeSerialization an array of values to be deserialized
         * @return \Gishiki\JSON\JSONObject the Gishiki-compatible JSON format
         * @throws \Gishiki\JSON\JSONException the error that prevents this JSON to be deserialized
         */
        static function DeSerializeFromArray($nativeSerialization) {
            //create the new JSON object
            $newJSONObj = new JSONObject();
            
            //to perform the requested operation a cycle is necessary
            reset($nativeSerialization);
                
            //get the number of JSON properties of the main JSON object
            $elementsNumber = count($nativeSerialization);
                
            for ($analyzedElements = 0; $analyzedElements < $elementsNumber; $analyzedElements++) {
                //fetch the current JSON property
                $currentElement = current($nativeSerialization);
                    
                //serialize the current JSON value
                $currentSerializedValue = NULL;
                switch (gettype($currentElement)) {
                    case "array";
                       $currentSerializedValue = self::DeSerializeFromArray($currentElement);
                        break;
                    
                    case "string":
                        $currentSerializedValue = new JSONString($currentElement);
                        break;
                        
                    case "integer":
                        $currentSerializedValue = new JSONInteger($currentElement);
                        break;
                    
                    case "double":
                        $currentSerializedValue = new JSONFloat($currentElement);
                        break;
                    
                    case "boolean";
                        $currentSerializedValue = new JSONBoolean($currentElement);
                        break;
                        
                    case "NULL":
                        $currentSerializedValue = new JSONValue();
                        break;
                    
                    default:
                        throw new JSONException("Unexpected JSON content", 2);
                }
                    
                //create a JSON property from the current JSON value
                $currentProperty = new JSONProperty((string)key($nativeSerialization), $currentSerializedValue);
                
                //and join that property to the current object
                $newJSONObj->AddProperty($currentProperty);
                
                //move forward
                next($nativeSerialization);
            }
            
            //return the deserialization result
            return $newJSONObj;
        }
        
        /**
         * Deserialize a string that represents a JSON valid content into a 
         * JSONObject object
         * 
         * @param string $jsonAsString the json encoded as a string
         * @return \Gishiki\JSON\JSONObject the Gishiki-compatible JSON format
         * @throws \Gishiki\JSON\JSONException the error that prevents this JSON to be deserialized
         */
        static function DeSerializeFromString($jsonAsString) {
            //check if the given data is a valid string
            if (gettype($jsonAsString) != "string") {
                throw new JSONException("The given data is not a valid utf8 string", 0);
            }
            
            //try decoding the string
            $nativeSerialization = json_decode($jsonAsString, TRUE);
            
            //and check for the result
            if (json_last_error() == JSON_ERROR_NONE) {
                if (gettype($nativeSerialization) != "array") $nativeSerialization = [];
                return self::DeSerializeFromArray($nativeSerialization);
            } else {
                throw new JSONException("The given string is not a valid JSON content", 1);
            }
        }
        
        /**
         * Serialize a JSON object into a valid JSON string.
         * 
         * @param \Gishiki\JSON\JSONObject $jsonAsObject the JSON object to be serialized
         * @return string the serialized JSON
         * @throws \Gishiki\JSON\JSONException the error that prevents this JSON object to be serialized
         */
        static function SerializeToString(JSONObject &$jsonAsObject) {
            //start serializing the JSON
            $serializationResult = "{ ";
            
            //start cycling from the first element
            $jsonAsObject->ResetProperties();
            
            //and cycle each element in the JSON Object
            while (!$jsonAsObject->EndOfProperties()) {
                //fetch the currently active property
                $currentProperty = $jsonAsObject->GetNextProperty();
                
                //add the property name
                $serializationResult .= "\"".$currentProperty->GetName()."\": ";
                
                //add the property value
                switch ($currentProperty->GetValue()->GetType()) {
                    case JSONValueType::OBJECT_VALUE:
                        $JSONobj = $currentProperty->GetValue();
                        $serializationResult .= self::SerializeToString($JSONobj);
                        break;
                    
                    case JSONValueType::INTEGER_VALUE:
                        $serializationResult .= $currentProperty->GetValue()->GetInteger();
                        break;
                    
                    case JSONValueType::FLOAT_VALUE:
                        $serializationResult .= $currentProperty->GetValue()->GetFloat();
                        break;
                    
                    case JSONValueType::BOOL_VALUE:
                        if ($currentProperty->GetValue()->GetBoolean()) {
                            $serializationResult .= "true";
                        } else {
                            $serializationResult .= "false";
                        }
                        break;
                    
                    case JSONValueType::NULL_VALUE:
                        $serializationResult .= "null";
                        break;
                    
                    case JSONValueType::STRING_VALUE:
                        $serializationResult .= "\"".$currentProperty->GetValue()->GetMessageAsUTF8()."\"";
                        break;
                    default:
                        throw new JSONException("Unexpected JSON content", 3);
                }
                
                //add a separator comma if necessary
                if (!$jsonAsObject->EndOfProperties()) {
                    $serializationResult .= ", ";
                }
            }
            
            //end the serialization
            $serializationResult .= " }";
            
            //and return the result
            return $serializationResult;
        }
    }
}
