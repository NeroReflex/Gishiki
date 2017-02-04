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
 * Collection Interface extended from Slim's one to better fit Gishiki.
 *
 * Benato Denis <benato.denis96@gmail.com>
 */
interface CollectionInterface extends \ArrayAccess, \Countable
{
    /**
     * Set collection item.
     *
     * @param string $key   The data key
     * @param mixed  $value The data value
     */
    public function set($key, $value);

    /**
     * Get collection item for key.
     *
     * @param string $key     The data key
     * @param mixed  $default The default value to return if data key does not exist
     *
     * @return mixed The key's value, or the default value
     */
    public function get($key, $default = null);

    /**
     * Add item to collection.
     *
     * @param array $items Key-value array of data to append to this collection
     */
    public function replace(array $items);

    /**
     * Get all items in collection.
     *
     * @return array The collection's source data
     */
    public function all();

    /**
     * Does this collection have a given key?
     *
     * @param string $key The data key
     *
     * @return bool
     */
    public function has($key);

    /**
     * Remove item from collection.
     *
     * @param string $key The data key
     */
    public function remove($key);

    /**
     * Remove all items from collection.
     */
    public function clear();

    /**
     * Get collection keys.
     *
     * @return array The collection's source data keys
     */
    public function keys();
}
