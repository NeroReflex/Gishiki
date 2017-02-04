<?php
/**************************************************************************
Copyright 2017 Benato Denis

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
 * This is a Generic Collection Implementation modificed from the Slim's one.
 *
 * Benato Denis <benato.denis96@gmail.com>
 */
class GenericCollection implements CollectionInterface, \IteratorAggregate
{
    /**
     * The source data.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Create new collection from the given properties collection.
     *
     * @param array $items Pre-populate collection with this key-value array
     *
     * @throws \InvalidArgumentException an invalid collection was given
     */
    public function __construct($items = [])
    {
        //check if the given items list is a valid items list
        if (!is_array($items)) {
            throw new \InvalidArgumentException('The collection of properties and nested data must be expressed as an array');
        }

        foreach ($items as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Get the currently managed collection as a native array.
     *
     * @return array the current collection
     */
    public function __invoke()
    {
        return $this->data;
    }

    /**
     * Get an element of the collection as it would be an object property.
     *
     * Return null if the array doesn't contain the given key
     *
     * @param int|string $key the index of the array element to be accessed
     *
     * @return mixed the requested array element or NULL
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Set an element of the collection as it would be an object property.
     *
     * @param string $key   the of the property to be modified
     * @param mixed  $value the value to be assigned to the property
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    /***************************************************************************
     * Collection interface
     **************************************************************************/

    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function get($key, $default = null)
    {
        return $this->has($key) ? $this->data[$key] : $default;
    }

    public function replace(array $items)
    {
        foreach ($items as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Get the representation of the current collection as an associative array.
     *
     * @return array the collection as an associative array
     */
    public function all()
    {
        return $this->data;
    }

    public function keys()
    {
        return array_keys($this->data);
    }

    public function has($key)
    {
        return array_key_exists($key, $this->data);
    }

    public function remove($key)
    {
        unset($this->data[$key]);
    }

    public function clear()
    {
        $this->data = [];
    }

    /***************************************************************************
     * ArrayAccess interface
     **************************************************************************/

    /**
     * Does this collection have a given key?
     *
     * @param string $key The data key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Get collection item for key.
     *
     * @param string $key The data key
     *
     * @return mixed The key's value, or the default value
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set collection item.
     *
     * @param string $key   The data key
     * @param mixed  $value The data value
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Remove item from collection.
     *
     * @param string $key The data key
     */
    public function offsetUnset($key)
    {
        $this->remove($key);
    }

    /**
     * Get number of items in collection.
     *
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }

    /***************************************************************************
     * IteratorAggregate interface
     **************************************************************************/

    /**
     * Get collection iterator.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }
}
