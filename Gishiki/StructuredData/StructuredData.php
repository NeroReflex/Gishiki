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

namespace Gishiki\StructuredData;

use Gishiki\Algorithms\Collections\GenericCollection;

/**
 * The Gishiki base controller.
 * 
 * Every controller (controllers used to generate an application for the 
 * client) inherits from this class
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class StructuredData extends GenericCollection
{
    protected static function serializeValue($data) {
        $result = null;
        switch (gettype($data)) {
            case "array":
                $result = new StructuredData($data);
                break;
                
            default:
                $result = $data;
                break;
        }
        return $result;
    }
    
    public function __construct($data = array())
    {
        //check if the given items list is a valid items list
        if (!is_array($data)) {
            throw new \InvalidArgumentException("The collection of properties and nested data must be expressed as an array");
        }

        //this is the serialized data to be inserted into the collection
        $serializedData = array();
        
        //serialize the current element
        foreach ($data as $elementName => $elementValue) {
            $serializedData["".$elementName] = static::serializeValue($elementValue);
        }
        
        //save the array
        $this->data = $serializedData;
    }
    
    
    
}