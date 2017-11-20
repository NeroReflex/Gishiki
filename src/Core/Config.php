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

use Gishiki\Algorithms\Strings\Manipulation;
use Gishiki\Algorithms\Collections\SerializableCollection;

/**
 * Used to parse an application file and to generate a valid configuration.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class Config
{
    /**
     * @var array|null the application configuration
     */
    protected $configuration = null;

    /**
     * @var string|null the name of the configuration file
     */
    protected $filename = null;

    /**
     * @var \Memcached|null
     */
    protected $cache = null;

    /**
     * Config constructor.
     * Parse the application configuration file when cached
     * content from a previous parse is not available.
     *
     * @param null|string     $filename the path and name of the file, or null for default
     * @param \Memcached|null $cache    the memcached caching instance. Speedup loading when provided.
     */
    public function __construct($filename = null, \Memcached $cache = null)
    {
        //set the filename
        $this->setFilename($filename);

        //set the cache
        if (!is_null($cache)) {
            $this->cache = $cache;
        }

        //load settings
        $this->finalizeLoading();
    }

    /**
     * Load configuration faster than possible.
     *
     * If caching is enabled attempt to load configuration from there.
     * It configuration cannot be found in cache load from file and
     * update the cache.
     */
    protected function finalizeLoading()
    {
        if (!is_null($this->cache)) {
            $cacheContent = $this->cache->get(sha1($this->getFilename()));

            if ($this->cache->getResultCode() != \Memcached::RES_NOTFOUND) {
                $this->configuration = unserialize($cacheContent);
            }
            return;
        }

        //load setting using the old-fashioned way :)
        $this->loadSettingsFromFile();

        if (!is_null($this->cache)) {
            $this->cache->set(sha1($this->getFilename()), serialize($this->configuration));
        }
    }

    /**
     * Get the path of the file containing the application configuration.
     *
     * @return string the current configuration file path
     */
    public function getFilename() : string
    {
        return $this->filename;
    }

    /**
     * Get the path that contains the application configuration file.
     *
     * @return string the settings file directory
     */
    public function getDirectory() : string
    {
        return dirname($this->getFilename());
    }

    /**
     * Set the path of the file containing the application configuration.
     *
     * @param  string|null $filename the path and name of the file, or null for default
     * @throws Exception the error preventing the file to be read
     */
    public function setFilename($filename = null)
    {
        $filename = (is_null($filename)) ? Application::getCurrentDirectory() . "settings.json" : $filename;

        if (!file_exists($filename)) {
            throw new Exception("The given configuration file cannot be read", 100);
        }

        $this->filename = $filename;
    }

    /**
     * Load the configuration from the loaded file.
     *
     * @throws Exception the error preventing the file to be read
     */
    protected function loadSettingsFromFile()
    {
        //get the json encoded application settings
        $configContent = file_get_contents($this->filename);

        //parse the settings file
        $incompleteConfig = SerializableCollection::deserialize($configContent)->all();

        //complete the request
        $this->configuration = $this->completeSettings($incompleteConfig);
    }

    /**
     * Get the application configuration.
     *
     * @return SerializableCollection the configuration
     */
    public function getConfiguration() : SerializableCollection
    {
        return new SerializableCollection($this->configuration);
    }

    /**
     * Complete the configuration resolving every value placeholder.
     *
     * Read more on documentation.
     *
     * @param  array $collection the configuration to be finished
     * @return array the completed configuration
     */
    protected function completeSettings(array &$collection) : array
    {
        foreach ($collection as &$value) {
            //check for substitution
            if ((is_string($value)) && ((strpos($value, '{{@') === 0) && (strpos($value, '}}') !== false))) {
                $value = (($toReplace = Manipulation::getBetween($value, '{{@', '}}')) != '') ?
                    $this->getValueFromEnvironment($toReplace) : $value;
            } elseif (is_array($value)) {
                $value = $this->completeSettings($value);
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
    protected function getValueFromEnvironment($key) : string
    {
        if (($value = getenv($key)) === false) {
            $value = constant($key);
        }

        return (string)$value;
    }
}
