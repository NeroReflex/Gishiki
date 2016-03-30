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
 * Functions to help with serialization and deserialization
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class Serialization {
    
    /**
     * Build an ID that describes univocally a serialized object
     * 
     * @param array $serializedObject an object serialized by Serialization::SerializeObject()
     * @throws SerializationException the error encountered while building the ID
     */
    static function BuildObjectDescriptorID($serializedObject) {
        if (gettype($serializedObject) == "array") {
            //fill this string with values of all object properties
            $globalObjectDescriptorID = "";

            if (ksort($serializedObject)) {
                $elementsCount = count($serializedObject);
                
                reset($serializedObject);
                
                for ($i = 0; $i < $elementsCount; $i++)
                {
                    //continue building the object descriptor 
                    $globalObjectDescriptorID = $globalObjectDescriptorID.key($serializedObject).":".current($serializedObject).", ";
                    
                    //go to the next serialized property
                    next($serializedObject);
                }
            } else {
                throw new SerializationException("Internal error: unsortable serialized data", 1);
            }
            
            //get a list of available message digesting algorythms
            $digestingAlgorythms = openssl_get_md_methods(FALSE);

            if (!defined('ALGO_SEPARATOR'))
                define('ALGO_SEPARATOR', "%");
            
            //hash of the $globalObjectDescriptorID
            $hashedGlobalObjectDescriptorID = "";
            if (in_array("sha512", $digestingAlgorythms)) {
                $hashedGlobalObjectDescriptorID = /*"sha512".ALGO_SEPARATOR.*/openssl_digest($globalObjectDescriptorID, "sha512");
            } else if (in_array("whirlpool", $digestingAlgorythms)) {
                $hashedGlobalObjectDescriptorID = /*"whirlpool".ALGO_SEPARATOR.*/openssl_digest($globalObjectDescriptorID, "whirlpool");
            } else if (in_array("dsaEncryption", $digestingAlgorythms)) {
                $hashedGlobalObjectDescriptorID = /*"dsaEncryption".ALGO_SEPARATOR.*/openssl_digest($globalObjectDescriptorID, "dsaEncryption");
            } else if (in_array("ripemd160", $digestingAlgorythms)) {
                $hashedGlobalObjectDescriptorID = /*"ripemd160".ALGO_SEPARATOR.*/openssl_digest($globalObjectDescriptorID, "ripemd160");
            } else if (in_array("DSA-SHA", $digestingAlgorythms)) {
                $hashedGlobalObjectDescriptorID = /*"DSA-SHA".ALGO_SEPARATOR.*/openssl_digest($globalObjectDescriptorID, "DSA-SHA");
            } else if (function_exists("md5")) {
                $hashedGlobalObjectDescriptorID = /*"embedded".ALGO_SEPARATOR.*/md5($globalObjectDescriptorID);
            } else {
                $hashedGlobalObjectDescriptorID = $globalObjectDescriptorID;
            }
            
            //return the hashed descriptor
            return str_replace(":", "", $hashedGlobalObjectDescriptorID);
        } else {
            throw new SerializationException("The object descriptor ID can be obtained only from serialized objects", 2);
        }
    }
    
    /**
     * Serialize an an object and put the result on the stack
     * 
     * @param object $object the object to be serialized. Cannot have members that are resources, and arrays cannot contain objects or resources
     * @param Stack $stack an empty instance of the Stack class
     * @throws SerializationException the error encountered while serializing the object
     */
    static function SerializeObject($object, Stack &$stack) {
        //check if the given data is an object
        if (!is_object($object))
            throw new SerializationException("Only objects can be stored through the Store function", 3);
        
            //the serialized data
            $serialized = array();
            
            //reflect the object
            $reflectedObject = new ReflectionObject($object);
            
            //get object properties 
            $objectProperties = $reflectedObject->getProperties(ReflectionProperty::IS_STATIC | ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE);
            
            //prepare to cycle each property
            reset($objectProperties);
            
            //get the number of properties
            $propertiesNumber = count($objectProperties);
            
            //cycle each property
            for ($counter = 0; $counter < $propertiesNumber; $counter++) {
                //get the currently processed property
                $currentProperty = current($objectProperties);

                //set the accessibility of the current property
                $currentProperty->setAccessible(TRUE);
                
                //get the name and the value of the current property
                $propertyName = $currentProperty->getName();
                $propertyValue = $currentProperty->getValue($object);
                $propertySerializedValue = NULL;
                
                //check for the $propertyValue type (cannot be a resource or an object)
                $propertyType = gettype($propertyValue);
                if ($propertyType == "resource") {
                    //a resource cannot be serialized natively in any way
                    throw new SerializationException("An object or a resource inside an object cannot be serialized", 4);
                } else if ($propertyType == "object") {
                    //serialize the object inside the current object
                    Serialization::SerializeObject($propertyValue, $stack);
                    
                    //the result will be stored inside the stack, and WILL NOT be written to the database,
                    //that must be done elsewhere
                    $propertySerializedValue = $stack->POP(); //get, from the stack, the reference of the last serialized object
                    //$stack->POP() is important because each serialized object has an unique ID,
                    //that ID is pushed to the stack AFTER the serialized object, this means that on the stack
                    //there will be a lot of arrays representing objects to be written after, one string, and then
                    //the last array after the last (first launched) SerializeObject is resolved
                } else if ($propertyType == "array") {
                    //resources, objects and arrays cannot be nested inside an array
                    $jsonObject = new JSON($propertyValue);
                    $jsonSerialized = $jsonObject->Serialize();
                    $propertySerializedValue = "jsn:".$jsonSerialized;
                    
                } else {
                    $propertySerializedValue = $propertyValue;
                }
                
                //serialization completed, store the result
                if ($propertyType != "NULL")
                {
                    //jsons/arrays, object references, booleans and strings are texts in the databse,
                    //so a method to distinguish them is necessary, i can't do miracles....
                    //.....right now.......
                    
                    //this is to respect the PHP and its variable multitype stupid system
                    if ($propertyType == "string") {
                        $propertySerializedValue = "str:".$propertySerializedValue;
                    } else if ($propertyType ==  "boolean") {
                        $propertySerializedValue = "boo:";
                        
                        if ($propertyValue)
                            $propertySerializedValue = $propertySerializedValue."true";
                        else
                            $propertySerializedValue = $propertySerializedValue."false";
                    }
                    
                    $serialized[$propertyName] = $propertySerializedValue;
                }
                
                //get the next property to process
                next($objectProperties);
            }
            
            //get the object class
            $objectClassName = get_class($object);
            
            //get the unique (hopefully) object descriptor ID
            $THISObjectUniqueID = Serialization::BuildObjectDescriptorID($serialized);
            
            //store inside the currently serializing object its unique object ID
            //used to restore it lately
            $serialized["ObjectDescriptor"] = $objectClassName.":".$THISObjectUniqueID;

            //push the serialized array
            $stack->PUSH($serialized);
            
            //and then the unique object ID
            $stack->PUSH("obj:".$serialized["ObjectDescriptor"]);
    }
    
    /**
     * Deserialize a previously serialized object
     * 
     * @param Stack $stack a stack instance filled by Serialization::SerializeObject()
     * @return object the deserialized object
     * @throws SerializationException the error encountered while deserializing the object
     */
    static function DeserializeOject(Stack &$stack) {
        //the stack, filled by Serialization::SerializeObject bust be reversed
        //to be used and to correctly resolve every object-inside-object
        $stack->Revert();
        
        //check if the stack exists
        if (gettype($stack) != "NULL") {
            //check if the stack is populated
            if (!$stack->IsEmpty()) {
                //a list of deserialized objects
                $deserializedObjectList = array();

                //the object descriptor ID of the object to return
                $objDescriptorID = "";

                while (!$stack->IsEmpty()) {

                    //get a serialized object from the stack
                    $serializedObject = $stack->POP();
                    $serializedObjectType = gettype($serializedObject);
                    $currentObjectDescriptorID = "";
                    if ($serializedObjectType == "array") {
                        $objectDescriptorExploded = explode(":", $serializedObject["ObjectDescriptor"]);
                        $className = $objectDescriptorExploded[0];

                        //save the current object descriptor ID
                        $exploded = explode(":", $serializedObject["ObjectDescriptor"]);
                        $currentObjectDescriptorID = $exploded[count($exploded) - 1];

                        if (class_exists($className)) {
                            //reflect the given class
                            $classReflected = new ReflectionClass($className);

                            //create the empty object
                            $object = $classReflected->newInstanceWithoutConstructor();

                            /*          fill the object                 */
                            //reflect the object
                            $reflectedObject = new ReflectionObject($object);

                            //get object properties 
                            $objectProperties = $reflectedObject->getProperties(ReflectionProperty::IS_STATIC | ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE);

                            //prepare to cycle each property
                            reset($objectProperties);

                            //get the number of properties
                            $propertiesNumber = count($objectProperties);

                            //cycle each property
                            for ($counter = 0; $counter < $propertiesNumber; $counter++) {
                                //get the currently processed property
                                $currentProperty = current($objectProperties);

                                //set the accessibility of the current property
                                $currentProperty->setAccessible(TRUE);

                                //get the name and the value of the current property
                                $propertyName = $currentProperty->GetName();

                                //deserialize the value without warnings
                                if (array_key_exists($propertyName, $serializedObject)) {
                                    $deserializedValue = $serializedObject[$propertyName];
                                } else {
                                    $deserializedValue = NULL;
                                }

                                //deserialize the value
                                $serializedValueType = gettype($deserializedValue);
                                if (($serializedValueType != "NULL") && ($serializedValueType != "integer") && ($serializedValueType != "double")){
                                    if ($serializedValueType == "string") {
                                        $explode = explode(":", $deserializedValue, 2);

                                        if ($explode[0] == "str") {
                                            $deserializedValue = $explode[1];
                                        } else if ($explode[0] == "boo") {
                                            if ($explode[1] == "true")
                                            {
                                                $deserializedValue = TRUE;
                                            } else {
                                                $deserializedValue = FALSE;
                                            }
                                        } else if ($explode[0] == "jsn") {
                                            $jsonPHPArray = new JSON();
                                            $jsonPHPArray->Deserialize($explode[1]);
                                            $deserializedValue = $jsonPHPArray->GetDeserializedJSON();
                                        } else if ($explode[0] == "obj") {
                                            $explode = explode(":", $deserializedValue);
                                            $deserializedValue = $deserializedObjectList[$explode[count($explode) - 1]];
                                        }
                                    }
                                }

                                //restore the value
                                $currentProperty->setValue($object, $deserializedValue);

                                //get the next property to process
                                next($objectProperties);
                            }

                            //save the deserialized object
                            $deserializedObjectList[$currentObjectDescriptorID] = $object;
                        } else {
                            throw new SerializationException("No class with the given name (".$className.") exists in the current context", 5);
                        }
                    } else { //string found
                        //the found string is the only string into the stack, 
                        //and it is the object descriptor ID of the object to return
                        $explodedID = explode(":", $serializedObject);
                        $objDescriptorID = $explodedID[count($explodedID) - 1];
                    }
                }
                return $deserializedObjectList[$objDescriptorID];
            } else {
                throw new SerializationException("The stack cannot be empty: it must be a valid stack filled by Serialization::SerializeObject", 6);
            }
        } else {
            throw new SerializationException("The stack cannot be a NULL stack", 7);
        }
    }
}
