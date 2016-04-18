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

/**
 * This is a generic implementation of a class that is a container for something
 * else, and what is stored inside can be accessed either as an array or with
 * a foreach cycle
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class CyclableCollection extends GenericCollection implements \Iterator
{
    /***************************************************************************
     * Iterator interface
     **************************************************************************/
    
    
    /**
     * Start up a new iteration cycle: reset the currently active collection element
     */
    public function rewind() {
        reset($this->data);
    }
    
    /**
     * Get the currently active collection element in an iteration cycle
     * 
     * Return FALSE if the array is empty
     * 
     * @return mixed the currently active array element
     */
    public function current()
    {
        return current($this->data);
    }
    
    /**
     * Get the next collection element after the currently active one and switch
     * the currently active collection element with the following one
     * 
     * Return FALSE if the array is empty
     * 
     * @return mixed the next array element
     */
    public function next()
    {
        return next($this->data);
    }
    
    /**
     * Check if the currently active collection element is a valid and existing one
     * 
     * @return bool TRUE if the currently accessed field is a valid one
     */
    public function valid()
    {
        $key = key($this->data);
        return ((array_key_exists($key, $this->data)) && ($key !== null && $key !== false));
    }
    
    /**
     * Get the index of the currently active collection element
     * 
     * @return string|int the index of the currently active array element
     */
    public function key()
    {
        return key($this->data);
    }
}
