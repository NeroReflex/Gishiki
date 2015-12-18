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
 * The cache memory engine. Provide basic caching functionalities on every system, 
 * using the best way possible on the current machine
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class CacheManager {
    /**
     * Store data on the cache, this avoid to re-calculate data
     * 
     * @param string $cacheName the name of the cache fragment
     * @param anytype $cacheValue the value of the cache fragment (string, boolean, integer and float)
     */
    public static function Store($cacheName, $cacheValue) {
        //use the best way to store the cache
        if (\Gishiki\Core\Environment::ExtensionSupport('APC')) {
            //use APC if possible
            apc_store($cacheName, $cacheValue);
        } else {
            //use a file if APC is not installed
            $formattedCacheValue = \Gishiki\Serialization\DirectSerialization::SerializeValue($cacheValue);
            
            //store the given value
            if (file_exists(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('CACHE_DIR'))) {
                file_put_contents(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('CACHE_DIR').$cacheName, gzcompress($formattedCacheValue, 3), FILE_APPEND | LOCK_EX);
            }
        }
    }
    
    /**
     * Fetch the value of the given cache fragment 
     * 
     * @param string $cacheName the name of the cache fragment
     * @return anytype the value of the cache fragment can be (string, boolean, integer and float), NULL on error
     */
    public static function Fetch($cacheName) {
        //check for the method used to store the cache
        if (\Gishiki\Core\Environment::ExtensionSupport('APC')) {
            if (apc_exists($cacheName))
                return apc_fetch($cacheName);
            else
                return NULL;
        } else {
            //check if the requested cache fragment exists
            if (file_exists(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('CACHE_DIR').$cacheName)) {
                //read the cache
                $formattedCacheValue = gzuncompress(file_get_contents(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('CACHE_DIR').$cacheName));
                
                return \Gishiki\Serialization\DirectSerialization::DeserializeValue($formattedCacheValue);
            } else {
                //it the cache fragment doesn't exists return NULL
                return NULL;
            }
        }
    }
    
    /**
     * Delete a cache fragment
     * 
     * @param string $cacheName the name of the cache fragment
     */
    public static function Delete($cacheName) {
        //check for the method used to store the cache
        if (\Gishiki\Core\Environment::ExtensionSupport('APC')) {
            if (apc_exists($cacheName))
                apc_delete ($cacheName);
        } else {
            if (file_exists(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('CACHE_DIR').$cacheName))
                unlink(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('CACHE_DIR').$cacheName);
        }
    }
    
    /**
     * Check if a cache fragment with the given name exists
     * 
     * @param string $cacheName the name of the cache fragment
     * @return boolean true if the cache fragment exists, false otherwise
     */
    public static function Exists($cacheName) {
        //is the cache fragment existent?
        $exists = FALSE;

        //check for the method used to store the cache
        if (\Gishiki\Core\Environment::ExtensionSupport('APC')) {
            $exists = apc_exists($cacheName);
        } else {
            $exists = file_exists(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('CACHE_DIR').$cacheName);
        }
        
        return $exists;
    }
}