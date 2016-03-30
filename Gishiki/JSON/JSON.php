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

/**
 * Description of JSON
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class JSON {
    //the unserialized JSON
    private $json;
    
    /**
     * Build a JSON from a given JSON-encoded content or a PHP array
     * 
     * @param string $JSONSerializable the string containing a JSON encoded data
     */
    public function __construct($JSONSerializable = "") {
        //start building the JSON
        $this->json = array();
        
        //deserialize the JSON encoded string if possible
        $JSONType = gettype($JSONSerializable);
        if (($JSONType == "string") && (strlen($JSONSerializable) > 6))
        {   $this->Deserialize($JSONSerializable);                             }
        else if ($JSONType == "array")
        {   $this->JSONFromArray($JSONSerializable);                           }
    }
    
    /**
     * Creates a compatible array from the given JSON encoded string
     * 
     * @param string $JSONEncoded the JSON encoded string
     * @return array the resulting array
     * @throws JSONException the error while decoding the string
     */
    private function JSONEncodedToArray($JSONEncoded) {
        //do nothing if not serializable
        if (strlen($JSONEncoded) < 7)
        {
            throw new JSONException("It is not possible to decode a JSON content that doesn't contains at least 7 characters", -2);
        }
            
        //the shortening process result
        $shortenedJSON = $JSONEncoded;
        
        //short the JSON string to obtain maximum speed at the decode stage
        $replaceTimes = 1;
        while ($replaceTimes != 0)
        {    $shortenedJSON = str_replace("  ", " ", $shortenedJSON, $replaceTimes);      }
        $replaceTimes = 1;
        while ($replaceTimes != 0)
        {    $shortenedJSON = str_replace("\n", "", $shortenedJSON, $replaceTimes);      }
        $replaceTimes = 1;
        while ($replaceTimes != 0)
        {    $shortenedJSON = str_replace("\t", "", $shortenedJSON, $replaceTimes);      }
        
        $JSONIsCleanAndReady = FALSE;
        
        while (!$JSONIsCleanAndReady) {
            $JSONIsCleanAndReady = TRUE;
            
            //delete the starting space
            if ($shortenedJSON[0] == ' ') {
                $shortenedJSON = substr($shortenedJSON, 1);
                $JSONIsCleanAndReady = FALSE;
            }
                

            //delete the final space
            $shortenedJSONLength = strlen($shortenedJSON);
            if ($shortenedJSON[$shortenedJSONLength - 1] == ' ') {
                $shortenedJSON = substr($shortenedJSON, 0, $shortenedJSONLength - 1);
                $JSONIsCleanAndReady = FALSE;
            }
            
            $shortenedJSONLength = strlen($shortenedJSON);
            if ($shortenedJSON[0] == '{') {
                $JSONIsCleanAndReady = FALSE;
                $parenthesis = 1;
                
                for ($i = 1; $i < $shortenedJSONLength; $i++) {
                    if ($shortenedJSON[$i] == '{') {
                        $parenthesis++;
                    } else if ($shortenedJSON[$i] == '}') {
                        $parenthesis--;
                        
                        if ($parenthesis == 0) {
                            $shortenedJSON = substr($shortenedJSON, 1, $i - 1);
                            break;
                        }
                    }
                }
            }
        }
        
        /*      check for json validity         */
        $pars = 0;
        $shortenedJSONLength = strlen($shortenedJSON);
        for ($i = 0; $i < $shortenedJSONLength; $i++)
        {
            if ($shortenedJSON[$i] == '{')
                $pars++;
            else if ($shortenedJSON[$i] == '}')
                $pars--;
            
            if ($pars < 0) {
                throw new JSONException("The given JSON cannot be deserialized", -9);
            }
        }
        if ($pars > 0) {
            throw new JSONException("The given JSON cannot be deserialized", -9);
        }
        
        //split the shortened string into characters
        $JSONChars = str_split($shortenedJSON);
        $JSONCharsNumber = count($JSONChars);
        
        $endOfJSONObject = FALSE;
        
        //setup an empty array
        $resultingArray = array();
        
        //what is the cycle currently parsing?
        /* $parsing Value   |   meaning
         *      0           |   nothing
         *      1           |   name
         *      2           |   value
         *      3           |   aftername
         */
        $parsing = 0;
        
        //the last parsed property name
        $lastPropertyNameParsed = "";
        
        //the last parsed property value
        $lastPropertyValueParsed = "";
        
        //the last parsed property type
        /* $lastPropertyValueParsedType Value   |   meaning
         *              0                       |   null
         *              1                       |   string
         *              2                       |   number
         *              3                       |   object
         *              4                       |   boolean
         */
        $lastPropertyValueParsedType = -1;
        
        //used to read JSON objects inside other objects
        $parenthesis = 0;
        $lastSaved = FALSE;
        
        //cycle each character starting from the first one
        reset($JSONChars);
        for ($i = 0; $i < $JSONCharsNumber; $i++)
        {
            //get the current character
            $currentCharacter = current($JSONChars);
            
            if ((($currentCharacter == ' ') && ($lastPropertyValueParsedType == 1) && ($parsing == 2)) || (($currentCharacter == ' ') && ($lastPropertyValueParsedType == 3) && ($parsing == 2)) || ($currentCharacter != ' ') || (($currentCharacter == ' ') && ($parsing == 1)) || (($currentCharacter == ',') && ($parsing != 0)))
            {
                if ($parsing == 0)
                {
                    if ($currentCharacter != '"')
                    {    
                        throw new JSONException("Unexpected character '".$currentCharacter."', expected character '\"' instead", -6);
                    } else {
                        $parsing = 1;
                        $lastPropertyNameParsed = "";
                    }
                } else if ($parsing == 1) {
                    if ($currentCharacter != '"')
                    {
                        $lastPropertyNameParsed = $lastPropertyNameParsed.$currentCharacter;
                    } else {
                        $parsing = 3;
                        $lastSaved = FALSE;
                    }
                }  else if ($parsing == 3) {
                    if ($currentCharacter != ':')
                    {   throw new JSONException("The JSON is not valid: unexpected character '".$currentCharacter."', after a json property name the ':' character is expected", -7);     }
                    else
                    {   $parsing = 2; $lastPropertyValueParsed = "";      }
                } else if ($parsing == 2) {
                    //get the json property value type
                    if ((strlen($lastPropertyValueParsed) == 0) && ($lastPropertyValueParsedType == -1)) {
                        if ($currentCharacter == "\"") {
                            $lastPropertyValueParsedType = 1;
                            $lastPropertyValueParsed = "";
                        } else if ($currentCharacter == 'n') {
                            $lastPropertyValueParsed = "n";
                            $lastPropertyValueParsedType = 0;
                        } else if (($currentCharacter == 't') || ($currentCharacter == 'f')) {
                            $lastPropertyValueParsed = "".$currentCharacter;
                            $lastPropertyValueParsedType = 4;
                        } else if (($currentCharacter == '0') || ($currentCharacter == '1') || ($currentCharacter == '2') || ($currentCharacter == '3') || ($currentCharacter == '4') || ($currentCharacter == '5') || ($currentCharacter == '6') || ($currentCharacter == '7') || ($currentCharacter == '8') || ($currentCharacter == '9')) {
                            $lastPropertyValueParsed = "".$currentCharacter;
                            $lastPropertyValueParsedType = 2;
                        } else if ($currentCharacter == '{') {
                            $lastPropertyValueParsed = "";
                            $lastPropertyValueParsedType = 3;
                            $parenthesis = 1;
                        } else {
                            throw new JSONException("Unrecognized JSON property type.", -8);
                        }
                    } else {
                        if  ((($lastPropertyValueParsedType != 1) && ($lastPropertyValueParsedType != 3)) && ($currentCharacter == ',')) {
                            //end of number, boolean or null parsing
                            $parsing = 0;
                        } else if (($lastPropertyValueParsedType == 1) && ($currentCharacter == '"')) {
                            //end of string parsing
                            $parsing = 0;
                            
                            while ((current($JSONChars) != ",") && ($i < $JSONCharsNumber)) {
                                next($JSONChars);
                                $i++;
                            }
                        } else {
                            if ($lastPropertyValueParsedType == 3)
                            {
                                //watch out for parenthesis
                                if ($currentCharacter == '{')
                                    $parenthesis++;
                                else if ($currentCharacter == '}')
                                    $parenthesis--;

                                if ($parenthesis == 0)
                                {
                                    $endOfJSONObject = TRUE;

                                    //end of object parsing
                                    $parsing = 0;
                                }
                            }
                            
                            //store the last character
                            if (!$endOfJSONObject)
                                $lastPropertyValueParsed = $lastPropertyValueParsed.$currentCharacter;

                            $endOfJSONObject = FALSE;
                        }

                        if ($parsing == 0) {
                            $lastSaved = TRUE;
                            
                            if ($lastPropertyValueParsedType == 1)
                            {
                                $resultingArray[$lastPropertyNameParsed] = $lastPropertyValueParsed;
                            }
                            else if ($lastPropertyValueParsedType == 0)
                            {   
                                if ($lastPropertyValueParsed == "null")
                                    $resultingArray[$lastPropertyNameParsed] = NULL;
                                else
                                    throw new JSONException("Unrecognized JSON value, expected \"null\", found \"".$lastPropertyValueParsed."\"", -9);
                            }
                            else if ($lastPropertyValueParsedType == 2)
                            {
                                if (is_numeric($lastPropertyValueParsedType)) {
                                    if (strpos($lastPropertyValueParsed, '.') == FALSE)
                                        $resultingArray[$lastPropertyNameParsed] = intval($lastPropertyValueParsed);
                                    else
                                        $resultingArray[$lastPropertyNameParsed] = floatval($lastPropertyValueParsed);
                                } else {
                                    throw new JSONException("Invalid representation of a number", -10);
                                }
                            }
                            else if ($lastPropertyValueParsedType == 3)
                            {
                                $resultingArray[$lastPropertyNameParsed] = $this->JSONEncodedToArray($lastPropertyValueParsed);
                            }
                            else if ($lastPropertyValueParsedType == 4)
                            {
                                if ($lastPropertyValueParsed == "true") {
                                    $resultingArray[$lastPropertyNameParsed] = TRUE;
                                } else if ($lastPropertyValueParsed == "false") {
                                    $resultingArray[$lastPropertyNameParsed] = FALSE;
                                } else {
                                    throw new JSONException("Unexpected JSON value: true or false expected, ".$lastPropertyValueParsed." found", -9);
                                }
                            }
                            
                            $lastPropertyValueParsedType = -1;
                        }
                    }
                }
            }
            
            //jump to the next character
            next($JSONChars);
        }
        
        if (!$lastSaved) {  
            if ($lastPropertyValueParsedType == 1)
            {
                $resultingArray[$lastPropertyNameParsed] = $lastPropertyValueParsed;
            }
            else if ($lastPropertyValueParsedType == 0)
            {   
                if ($lastPropertyValueParsed == "null")
                    $resultingArray[$lastPropertyNameParsed] = NULL;
                else
                    throw new JSONException("Unrecognized JSON value, expected \"null\", found \"".$lastPropertyValueParsed."\"", -9);
            }
            else if ($lastPropertyValueParsedType == 2)
            {
                if (is_numeric($lastPropertyValueParsedType)) {
                    if (strpos($lastPropertyValueParsed, '.') == FALSE)
                        $resultingArray[$lastPropertyNameParsed] = intval($lastPropertyValueParsed);
                    else
                        $resultingArray[$lastPropertyNameParsed] = floatval($lastPropertyValueParsed);
                } else {
                    throw new JSONException("Invalid representation of a number", -10);
                }
            }
            else if ($lastPropertyValueParsedType == 3)
            {
                $resultingArray[$lastPropertyNameParsed] = $this->JSONEncodedToArray($lastPropertyValueParsed);
            }
            else if ($lastPropertyValueParsedType == 4)
            {
                if ($lastPropertyValueParsed == "true") {
                    $resultingArray[$lastPropertyNameParsed] = TRUE;
                } else if ($lastPropertyValueParsed == "false") {
                    $resultingArray[$lastPropertyNameParsed] = FALSE;
                } else {
                    throw new JSONException("Unexpected JSON value: true or false expected, ".$lastPropertyValueParsed." found", -9);
                }
            }    
            $lastPropertyValueParsedType = -1;
        }
        
        //return the given array
        return $resultingArray;
    }
    
    /**
     * Deserialize the given JSON encoded string and add its content
     * to this JSON. The JSON mustn't begin with '{' and end with '}'
     * 
     * @param string $JSONEncoded the JSON encoded string
     * @throws JSONException the exception while reading the string
     */
    public function Deserialize($JSONEncoded) {
        //create an array from the given JSON encoded string
        $JSONArray = $this->JSONEncodedToArray($JSONEncoded);
        
        //and add that array to the json object
        $this->JSONFromArray($JSONArray);
    }
    
    /**
     * Deserialize the given PHP array and add its content
     * to this JSON. The JSON mustn't begin with '{' and end with '}'
     * 
     * @param string $JSONArray the PHP array
     */
    public function JSONFromArray($JSONArray) {
        //check for the array compatibility
        if (!JSON::IsArrayCompatible($JSONArray))
            throw new JSONException("An array to be imported into a JSON managed object cannot contains objects or resources", -1);
        
        //start to cycle from the first element
        reset($JSONArray);
        
        //get the number of array elements
        $JSONArrayCount = count($JSONArray);
        
        //cycle each array element
        for ($i = 0; $i < $JSONArrayCount; $i++)
        {
            //get name and value of the current array element
            $name = (string)key($JSONArray);
            $value = current($JSONArray);
            $valueType = gettype($value);
            
            //stor the JSON property
            $this->json[$name] = $value;
            
            //jump to the next array element
            next($JSONArray);
        }
    }
    
    /**
     * Check for the validity of a PHP array as a JSON object
     * 
     * @param array $array the array to check
     * @return boolean the array validity
     */
    static function IsArrayCompatible($array)
    {
        //check for the input to be an array
        if (gettype($array) != "array")
        {   return FALSE;   }
        
        //start to cycle from the first element
        reset($array);
        
        //get the number of array elements
        $ArrayCount = count($array);
        
        //cycle each array element
        for ($i = 0; $i < $ArrayCount; $i++)
        {
            //get name and value of the current array element
            $value = current($array);
            $valueType = gettype($value);
            
            //objects and resources are incompatibles
            if (($valueType == "object") || ($valueType == "resource") || ($valueType == "unknown type" ))
                return FALSE;
            else if ($valueType == "array")
                if (!JSON::IsArrayCompatible($value))
                    return FALSE;
                
            //jump to the next array element
            next($array);
        }
        
        //if arrived here than no incompatible types were found
        return TRUE;
    }
    
    /**
     * Serialize to a JSON encoded string a compatible JSON array
     * 
     * @param array $array the compatible JSON array
     */
    private function SerializeArray($array) {
        //start building the JSON encoded string
        $serialized = "{";
        
        //start to cycle from the first element
        reset($array);
        
        //get the number of array elements
        $ArrayCount = count($array);
        
        //cycle each array element
        for ($i = 0; $i < $ArrayCount; $i++)
        {
            //get name and value of the current array element
            $name = (string)key($array);
            $value = current($array);
            $valueType = gettype($value);
            
            //add the json property name
            $serialized = $serialized."\"".$name."\": ";
            
            //add the json property value
            if ($valueType == "string")
            {   $serialized = $serialized."\"".$value."\"";                    }
            else if (($valueType == "integer") || ($valueType == "double"))
            {   $serialized = $serialized.(string)$value;                      }
            else if ($valueType == "boolean")
            {   if ($value)
                    $serialized = $serialized."true";
                else
                    $serialized = $serialized."false";                         }
            else if ($valueType == "array")
            {   $serialized = $serialized.$this->SerializeArray($value);       }
            else if ($valueType == "NULL")
            {   $serialized = $serialized."null";                              }
            
            
            //add a separator from the following json attribute
            if ($i != ($ArrayCount - 1))
            {   $serialized = $serialized.", ";                                }
            
            //jump to the next array element
            next($array);
        }
        
        //end building the JSON encoded string
        $serialized = $serialized."}";
        
        //return the json encoded string
        return $serialized;
    }
    
    /**
     * Serialize this JSON object into a JSON encoded string
     * 
     * @return string the serialized JSON
     */
    public function Serialize() {
        //check for the array compatibility
        if (!JSON::IsArrayCompatible($this->json))
        {   throw new JSONException("The json object cannot be serialized because an incompatible type was found", -3);    }
        
        //serialize the current json object
        $serialized = "";
        $serialized = $serialized.$this->SerializeArray($this->json);
        
        //return the serialized JSON
        return $serialized;
    }
    
    /**
     * Retrive the deserialized JSON content
     */
    public function GetDeserializedJSON() {
        return $this->json;
    }
    
    /**
     * Add a property to the json object or update an existing property if it already exists
     * 
     * @param string $propertyName the JSON property name
     * @param anytype $propertyValue the JSON property value
     * @throws JSONException the error occurred (incompatible types)
     */
    public function UpdateProperty($propertyName, $propertyValue = NULL)
    {
        //get the property type of the value
        $propertyValueType = gettype($propertyValue);
        
        //if it is an array check it for incompatibilities
        if ($propertyValueType == "array")
        {
            if (!JSON::IsArrayCompatible($propertyValue))
            {   throw new JSONException("The json property cannot be serialized because an incompatible type was found inside the given array", -4);    }
        }
        
        //check for incompatibles types
        if (($propertyValueType == "object") || ($propertyValueType == "resource") || ($propertyValueType == "unknown type" ))
        {   throw new JSONException("The json property cannot be serialized because it has an incompatible type", -5);     }
        
        //store/update the json property
        $this->json[$propertyName] = $propertyValue;
    }
}
