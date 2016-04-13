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

namespace Gishiki\Core {

    /**
     * This class is used to provide a small layer of Laravel-compatibility
     * and ease of routing usage
     *
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    abstract class Route extends Routing
    {
        
        const NotFound = parent::NotFoudCallback;
        
        /**
         * Convinient proxy function to call:
         * 
         *  - Routing::setRoute(Routing::GET, ..., ...)
         * 
         *  - Routing::setRoute(Routing::POST, ..., ...)
         * 
         *  - Routing::setRoute(Routing::DELETE, ..., ...)
         * 
         *  - Routing::setRoute(Routing::HEAD, ..., ...)
         * 
         *  - Routing::setRoute(Routing::PUT, ..., ...)
         * 
         * <code>
         * use \Gishiki\Core\Route;
         * 
         * Route::any("/user/{id}", function ($params) {
         *      //perform your amazing magic here
         * });
         * </code>
         * 
         * @see \Gishiki\Core\Routing\setRoute
         * 
         * @param string   $URI      the URI that will bring to the function execution
         * @param function $function the function executed when the URL is called
         */
        public static function any($URI, $function)
        {
            parent::setRoute(parent::GET, $URI, $function);
            parent::setRoute(parent::POST, $URI, $function);
            parent::setRoute(parent::DELETE, $URI, $function);
            parent::setRoute(parent::HEAD, $URI, $function);
            parent::setRoute(parent::PUT, $URI, $function);
        }
        
        /**
         * Convinient proxy function to call Routing::setRoute multiple time with 
         * multiple request methods.
         * 
         * <code>
         * use \Gishiki\Core\Route;
         * 
         * Route::match([Route::GET, Route::POST], "/user/{id}", function ($params) {
         *      //perform your amazing magic here
         * });
         * </code>
         * 
         * @see \Gishiki\Core\Routing\setRoute
         * 
         * @param string   $URI      the URI that will bring to the function execution
         * @param function $function the function executed when the URL is called
         */
        public static function match($methods, $URI, $function)
        {
            if (count($methods) >= 1) {
                foreach ($methods as $method) {
                    parent::setRoute($method, $URI, $function);
                }
            }
        }
        
        /**
         * Convinient proxy function to call Routing::setRoute(Routing::GET, ..., ...)
         * 
         * <code>
         * use \Gishiki\Core\Route;
         * 
         * Route::get("/user/{id}", function ($params) {
         *      //perform your amazing magic here
         * });
         * </code>
         * 
         * @see \Gishiki\Core\Routing\setRoute
         * 
         * @param string   $URI      the URI that will bring to the function execution
         * @param function $function the function executed when the URL is called
         */
        public static function get($URI, $function)
        {
            parent::setRoute(parent::GET, $URI, $function);
        }
        
        /**
         * Convinient proxy function to call Routing::setRoute(Routing::POST, ..., ...)
         * 
         * <code>
         * use \Gishiki\Core\Route;
         * 
         * Route::post("/user/{id}", function ($params) {
         *      //perform your amazing magic here
         * });
         * </code>
         * 
         * @see \Gishiki\Core\Routing\setRoute
         * 
         * @param string   $URI      the URI that will bring to the function execution
         * @param function $function the function executed when the URL is called
         */
        public static function post($URI, $function)
        {
            parent::setRoute(parent::POST, $URI, $function);
        }
        
        /**
         * Convinient proxy function to call Routing::setRoute(Routing::PUT, ..., ...)
         * 
         * <code>
         * use \Gishiki\Core\Route;
         * 
         * Route::put("/user/{id}", function ($params) {
         *      //perform your amazing magic here
         * });
         * </code>
         * 
         * @see \Gishiki\Core\Routing\setRoute
         * 
         * @param string   $URI      the URI that will bring to the function execution
         * @param function $function the function executed when the URL is called
         */
        public static function put($URI, $function)
        {
            parent::setRoute(parent::PUT, $URI, $function);
        }
        
        /**
         * Convinient proxy function to call Routing::setRoute(Routing::DELETE, ..., ...)
         * 
         * <code>
         * use \Gishiki\Core\Route;
         * 
         * Route::delete("/user/{id}", function ($params) {
         *      //perform your amazing magic here
         * });
         * </code>
         * 
         * @see \Gishiki\Core\Routing\setRoute
         * 
         * @param string   $URI      the URI that will bring to the function execution
         * @param function $function the function executed when the URL is called
         */
        public static function delete($URI, $function)
        {
            parent::setRoute(parent::DELETE, $URI, $function);
        }
        
        /**
         * Convinient proxy function to call Routing::setRoute(Routing::HEAD, ..., ...)
         * 
         * <code>
         * use \Gishiki\Core\Route;
         * 
         * Route::head("/user/{id}", function ($params) {
         *      //perform your amazing magic here
         * });
         * </code>
         * 
         * @see \Gishiki\Core\Routing\setRoute
         * 
         * @param string   $URI      the URI that will bring to the function execution
         * @param function $function the function executed when the URL is called
         */
        public static function head($URI, $function)
        {
            parent::setRoute(parent::HEAD, $URI, $function);
        }
        
        /**
         * Registers a callback function to be executed when a certain error occurs.
         * 
         * <code>
         * use \Gishiki\Core\Route;
         * 
         * Route::error(Route::NotFound, function ($params) {
         *      //perform your failback amazing magic here!
         * });
         * </code>
         * 
         * @param int      $error_type the error that brings the callback function to be executed
         * @param function $callback   this is the function automatically called when the error type occurs
         */
        public static function error($error_type, $callback)
        {
            parent::setErrorCallback($error_type, $callback);
        }
    }
}
