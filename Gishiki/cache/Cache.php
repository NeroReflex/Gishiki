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

namespace Gishiki\Caching {

    /**
     * Provide basic caching using memcached or redis.
     *
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    abstract class Cache
    {
        private static $persistence_ID = "lf4e2d";
        private static $connected = FALSE;

        /**
         * The connection to the cache server specified in the application configuration file.
         */
        protected static $cacheServer = [];

        /**
         * Initialize the caching engine for the current request.
         * This function is automatically called by the framework.
         * Another call to this function won't produce any effects.
         */
        static function Initialize() {
            //initialize the caching engine only if it is needed
            if (\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('CACHING_ENABLED')) {
                //parse the string stored to the application settings file
                self::$cacheServer["details"] = CacheConnectionString::Parse(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty("CACHE_CONNECTION_STRING"));

                switch (self::$cacheServer["details"]["server_type"]) {
                    case "memcached":
                        if (\Gishiki\Core\Environment::ExtensionSupport("MEMCACHED")) {
                            //open a new memcached instance
                            self::$cacheServer["connection"] = new \Memcached(self::$persistence_ID);
                            self::$cacheServer["connection"]->setOption(\Memcached::OPT_RECV_TIMEOUT, 1000);
                            self::$cacheServer["connection"]->setOption(\Memcached::OPT_SEND_TIMEOUT, 1000);
                            self::$cacheServer["connection"]->setOption(\Memcached::OPT_TCP_NODELAY, true);
                            self::$cacheServer["connection"]->setOption(\Memcached::OPT_SERVER_FAILURE_LIMIT, 50);
                            self::$cacheServer["connection"]->setOption(\Memcached::OPT_CONNECT_TIMEOUT, 500);
                            self::$cacheServer["connection"]->setOption(\Memcached::OPT_RETRY_TIMEOUT, 300);

                            //suppress a warning on Memcached::append()
                            self::$cacheServer["connection"]->setOption(\Memcached::OPT_COMPRESSION, false);

                            /* self::$cacheServer["connection"]->setOption(\Memcached::OPT_DISTRIBUTION, Memcached::DISTRIBUTION_CONSISTENT);
                            self::$cacheServer["connection"]->setOption(\Memcached::OPT_LIBKETAMA_COMPATIBLE, true); */

                            //connect to the memcached server
                            self::$cacheServer["connection"]->addServer(self::$cacheServer["details"]["server_address"], self::$cacheServer["details"]["server_port"]);

                            //was the server connected?
                            self::$connected = count(self::$cacheServer["connection"]->getStats()) > 0;
                        }
                        break;

                    case "redis":

                        break;

                    default:
                        break;
                }
            }
        }

        /**
         * Store data on the cache server. Complex elaboration results should be stored into the cache,
         * This way a lot of computational time can be saved!
         *
         * @param string $cacheName the name of the cache fragment
         * @param mixed $cacheValue the value of the cache fragment
         */
        static function Store($cacheName, $cacheValue) {
            //if a caching server is connected, and the cache fragment has a valid name
            if ((self::$connected) && (gettype($cacheName) == "string") && ($cacheName != "")) {

                //chose the proper way of storing the cache fragment
                if (self::$cacheServer["details"]["server_type"] == "memcached") {
                    self::$cacheServer["connection"]->set($cacheName, serialize($cacheValue));
                }
            }
        }

        /**
         * Check if a cache fragment with the given name exists in the cache server
         *
         * @param string $cacheName the name of the cache fragment
         * @return boolean TRUE if the cache fragment exists, FALSE otherwise
         */
        public static function Exists($cacheName) {
            //is the cache fragment existent?
            $exists = FALSE;

            //if a caching server is connected, and the cache fragment has a valid name
            if ((self::$connected) && (gettype($cacheName) == "string") && ($cacheName != "")) {

                //chose the proper way of storing the cache fragment
                if (self::$cacheServer["details"]["server_type"] == "memcached") {
                    if (self::$cacheServer["connection"]->append($cacheName, "") != TRUE)
                        $exists = self::$cacheServer["connection"]->getResultCode() !== \Memcached::RES_NOTSTORED;
                    else
                        $exists = TRUE;
                }
            }

            return $exists;
        }

        /**
         * Fetch the value of the cache fragment with the given name
         *
         * @param string $cacheName the name of the cache fragment
         * @return mixed the value of the cache fragment can be (string, boolean, integer and float), NULL on error
         */
        public static function Fetch($cacheName) {
            //if a caching server is connected, and the cache fragment has a valid name
            if ((self::$connected) && (gettype($cacheName) == "string") && ($cacheName != "")) {

                //chose the proper way of fetching the cache fragment
                if (self::$cacheServer["details"]["server_type"] == "memcached") {
                    return unserialize(self::$cacheServer["connection"]->get($cacheName));
                }
            }

            return NULL;
        }

        /**
         * Delete the cache fragment with the given name
         *
         * @param string $cacheName the name of the cache fragment
         */
        public static function Delete($cacheName) {
            //if a caching server is connected, and the cache fragment has a valid name
            if ((self::$connected) && (gettype($cacheName) == "string") && ($cacheName != "")) {

                //chose the proper way of removing the cache fragment
                if (self::$cacheServer["details"]["server_type"] == "memcached") {
                    self::$cacheServer["connection"]->delete($cacheName);
                }
            }
        }
    }
}