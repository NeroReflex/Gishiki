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

use Gishiki\Algorithms\Collections\DeserializationException;
use Gishiki\Algorithms\Collections\SerializableCollection;
use Gishiki\Algorithms\Strings\Manipulation;

/**
 * Represents the application configuration.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class Configuration extends SerializableCollection
{
    /**
     * Parse the given configuration file.
     *
     * Read the given file in JSON format and produce a Configuration
     * from it, where placeholders have been replaced with correct values.
     *
     * @param string|null $filename the name of the file to be used or null for default
     * @return Configuration the parsed configuration
     * @throws Exception the given file cannot be found
     */
    public static function loadFromFile($filename = null) : Configuration
    {
        $filename = (is_null($filename)) ? Application::getCurrentDirectory() . "settings.json" : $filename;

        if (!file_exists($filename)) {
            throw new Exception("The given configuration file cannot be read", 100);
        }

        try {
            $config = SerializableCollection::deserialize(file_get_contents($filename));
        } catch (DeserializationException $ex) {
            throw new Exception("The given configuration file contains syntax error(s)", 101);
        }

        return new self($config->all());
    }

    /**
     * Load Application configuration.
     *
     * Load configuration from the given data,
     * read documentation for more.
     *
     * @param array $data configuration data
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);

        //finalize configuration
        self::completeSettings($this->data);
    }

    /**
     * Complete the configuration resolving every value placeholder.
     *
     * Read more on documentation.
     *
     * @param  array $collection the configuration to be finished
     * @return array the completed configuration
     */
    private static function completeSettings(array &$collection) : array
    {
        foreach ($collection as &$value) {
            //check for substitution
            if ((is_string($value)) && ((strpos($value, '{{@') === 0) && (strpos($value, '}}') !== false))) {
                $value = (($toReplace = Manipulation::getBetween($value, '{{@', '}}')) != '') ?
                    self::getValueFromEnvironment($toReplace) : $value;
            } elseif (is_array($value)) {
                $value = self::completeSettings($value);
            }
        }

        return $collection;
    }

    /**
     * Get the value of an environment variable from its name.
     *
     * @param  string $key the name of environment variable
     * @return string the value of the environment variable
     */
    private static function getValueFromEnvironment($key) : string
    {
        $value = (Environment::has($key)) ? Environment::get($key) : constant($key);

        return $value;
    }
}
