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

namespace Gishiki\Core;

/**
 * Represents the current execution environment.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class Environment
{
    /**
     * Check if the current execution environment
     * contains the given entry.
     *
     * @param string $key the key to be searched
     * @return bool true whether the given key is defined
     * @throws \InvalidArgumentException the given key is not valid
     */
    public static function has($key) : bool
    {
        if ((!is_string($key)) || (strlen($key) < 0)) {
            throw new \InvalidArgumentException("The given environment key must be a non-empty valid string");
        }

        return (($value = getenv($key)) !== false);
    }

    /**
     * Return the value associated with the given key
     * in the current execution environment.
     *
     * @param string $key     the key to be read
     * @param mixed  $default the default value returned when the key is not found
     * @return array|false|string the value held by the environment
     * @throws \InvalidArgumentException the given key is not valid
     */
    public static function get($key, $default = null)
    {
        if ((!is_string($key)) || (strlen($key) < 0)) {
            throw new \InvalidArgumentException("The given environment key must be a non-empty valid string");
        }

        return (static::has($key)) ? getenv($key) : $default;
    }

    /**
     * Set in the current execution environment
     * a new value with the given value: create the
     * key if it doesn't exists.
     *
     * @param string $key the key to be added
     * @param string $value the value to be associated with the key
     * @throws \InvalidArgumentException the given key is not valid
     */
    public static function set($key, $value)
    {
        if ((!is_string($key)) || (strlen($key) < 0)) {
            throw new \InvalidArgumentException("The given environment key must be a non-empty valid string");
        }

        putenv("$key=".$value);
    }

    /**
     * Remove in the current execution environment the given entry.
     *
     * @param string $key the key to be removed
     * @throws \InvalidArgumentException the given key is not valid
     */
    public static function remove($key)
    {
        if ((!is_string($key)) || (strlen($key) < 0)) {
            throw new \InvalidArgumentException("The given environment key must be a non-empty valid string");
        }

        putenv("$key");
    }
}
