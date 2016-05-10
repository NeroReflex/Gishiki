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

namespace Gishiki\Algorithms\Collections;

use Gishiki\Algorithms\Collections\GenericCollection;

/**
 * The structured data management class.
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class SerializableCollection extends GenericCollection
{
    /********************************************
     *               Serializators              *
     ********************************************/
    const JSON      = 0;
    //const XML       = 1;
    
    /**
     * Create serializable data collection from the given array.
     * 
     * @param  array                     $data the collection of properties
     * @throws \InvalidArgumentException       an invalid collection was given
     */
    public function __construct($data = array())
    {
        parent::__construct($data);
    }
    
    /**
     * Serialize the current data collection
     * 
     * @param  integer                $format an integer representing one of the allowed formats
     * @throw  SerializationException         the error occurred while serializing the collection in json format
     * @return string                         the collection serialized
     */
    public function serialize($format = self::JSON)
    {
        if ($format == self::JSON) {
            //try json encoding
            $result = json_encode($this->all(), JSON_PRETTY_PRINT);
            
            //and check for the result
            if (json_last_error() != JSON_ERROR_NONE) {
                throw new SerializationException('The given data cannot be serialized in JSON content', 2);   
            }
            
            return $result;
        }/* elseif ($this->format == self::XML) {
            return $this->nativeSerializator->asXML();
        }*/
    }
    
    /**
     * Deserialize the given data collectionand create a serializable data collection.
     * 
     * @param  string                 $message the string containing the serialized data
     * @param  integer                $format  an integer representing one of the allowed formats
     * @return SerializableCollection          the deserialization result
     * @throws DeserializationException        the error preventing the data deserialization
     */
    public static function deserialize($message, $format = self::JSON)
    {
        if ($format == self::JSON) {
            //try decoding the string
            $nativeSerialization = json_decode($message, true, 512);

            //and check for the result
            if (json_last_error() != JSON_ERROR_NONE) {
                throw new DeserializationException('The given string is not a valid JSON content', 1);
            }

            //the deserialization result MUST be an array
            $serializationResult = (is_array($nativeSerialization)) ? $nativeSerialization : [];

            //return the deserialization result if everything went right
            return new SerializableCollection($serializationResult);
        }
    }
    
    /**
     * Get the serialization result using the default format
     * 
     * @return string the serialization result
     */
    public function __toString()
    {
        return $this->serialize();
    }
}