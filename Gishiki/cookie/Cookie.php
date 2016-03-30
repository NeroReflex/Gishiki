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
 * The class that represents an unsecure cookie: the cookie is accessible from 
 * HTTP, HTTPS and client-side, because the cookie is not encrypted. 
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class Cookie {
    
    //the cookie name
    protected $name;
    
    //the cookie value
    protected $value;
    
    /**
     * Creates a cookie with the given name and value, but doen't save it.
     * 
     * @param string $cookieName 
     * @param mixed $cookieValue
     */
    public function __construct($cookieName, $cookieValue = NULL) {        
        //store the cookie name
        $this->name = $cookieName;
        
        //and its value
        $this->value = DirectSerialization::SerializeValue($cookieValue);
    }
    
    /**
     * store the new value inside the cookie, overwriting the previously stored one
     * 
     * @param mixed $newValue the new cookie value
     */
    public function setValue($newValue) {
        //store the cookie value
        $this->value = DirectSerialization::SerializeValue($newValue);
    }
    
    /**
     * Return the value assigned to the cookie
     * 
     * @return mixed the cookie value as it was given to Cookie::setValue()
     */
    public function getValue() {
        //get the cookie value
        return DirectSerialization::DeserializeValue($this->value);
    }
    
    /**
     * Internal use only: this function returns a cookie value serialized 
     * 
     * @return string the cookie value as it is stored
     */
    protected function inspectSerializedValue() {
        //get the serialized value
        return $this->value;
    }
    
    /**
     * Get the name of the current cookie
     * 
     * @return string the cookie name
     */
    public function getName() {
        //get the serialized value
        return $this->name;
    }
}
