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

namespace Gishiki\Algorithms;

/**
 * This is a generic implementation of a class that is a container for something
 * else, and what is stored inside can be accessed either as an array or with
 * a foreach cycle
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class CyclableCollection implements \Iterator {
    
    /**
     * All functions of this base class point to this array
     * 
     * @var array the array all punctions are pointing to
     */
    protected $array = array();
    
    /**
     * Setup the new collection with provided key=>value pairs
     * 
     * @param array $setup_values the array containing properties and values
     */
    public function __construct($setup_values = []) {
        if (gettype($setup_values) == "array")
        //type is very important here!
        {   $this->array = $setup_values;   }
    }
    
    /**
     * Start up a new iteration cycle: reset the currently active collection element
     */
    public function rewind() {
        reset($this->array);
    }
        
    /**
     * Get the currently active collection element in an iteration cycle
     * 
     * Return FALSE if the array is empty
     * 
     * @return mixed the currently active array element
     */
    public function current() {
        return current($this->array);
    }
        
    /**
     * Get the index of the currently active collection element
     * 
     * @return integer the index of the currently active array element
     */
    public function key() {
        return key($this->array);
    }
        
    /**
     * Get the next collection element after the currently active one and switch
     * the currently active collection element with the following one
     * 
     * Return FALSE if the array is empty
     * 
     * @return mixed the next array element
     */
    public function next() {
        return next($this->array);
    }
        
    /**
     * Get an element of the collection as it would be an object property
     * 
     * Return NULL if the array doesn't contain the given key
     * 
     * @param integer|string $key the index of the array element to be accessed
     * @return mixed the requested array element or NULL
     */
    public function &__get($key) {
        //return the chosen field
        if ($this->exists_key($key)) {     return $this->array[$key];  }
        else {                      return NULL;                }
    }
    
    /**
     * Set an element of the collection as it would be an object property
     * 
     * @param string $key the of the property to be modified
     * @param mixed $value the value to be assigned to the property
     */
    public function __set($key, $value) {
        $this->array[$key] = $value;
    }
        
    /**
     * Check if the currently active collection element is a valid and existing one
     * 
     * @return boolean TRUE if the currently accessed field is a valid one
     */
    public function valid()
    {
        $key = key($this->array);
        return ((array_key_exists($key, $this->array)) && ($key !== NULL && $key !== FALSE));
    }
    
    /**
     * Get the previous field preceding the currently active one and switch
     * the currently active field with the preceding one
     * 
     * Return FALSE if the array is empty
     * 
     * @return mixed the previous field
     */
    public function previous() {
        return prev($this->array);
    }
    
    /**
     * Get the last element of the array and change the currently active array 
     * element with the last one.
     * 
     * Return FALSE if the array is empty
     * 
     * @return mixed the last element of the array
     */
    public function end() {
        return end($this->array);
    }
    
    /**
     * Perform a search withing the current collection to find the given value.
     * 
     * Return FALSE if the array doesn't contains the searched value
     * 
     * @param mixed $value the value to be searched
     * @return integer|string|boolean the index of the searched value, or FALSE
     */
    public function search($value) {
        return array_search($value, $this->array);
    }
    
    /**
     * Check if the given key is part of the current collection
     * 
     * @param integer|string $key the key to be checked
     * @return boolean TRUE if the given key exists
     */
    public function exists_key($key) {
        return array_key_exists($key, $this->array);
    }
    
    /**
     * Insert the given element into the collection
     * 
     * @param mixed $value the value to insert
     */
    public function emplace($value) {
        $this->array[] = $value;
    }
}

