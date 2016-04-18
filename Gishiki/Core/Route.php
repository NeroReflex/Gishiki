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

    use Gishiki\HttpKernel\Request;
    
    /**
     * This class is used to provide a small layer of Laravel-compatibility
     * and ease of routing usage
     *
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    final class Route
    {
        /**
         * This is the list of added routes
         *
         * @var array a collection of routes
         */
        private static $routes = [];
        
        /**
         * Add a route to the route redirection list
         * 
         * @param \Gishiki\Core\Route $route the route to be added
         */
        public static function addRoute(Route $route)
        {
            //add the given route to the routes list
            self::$routes[] = $route;
        }
        
        /**
         * Used when the router were unable to route the request to a suitable
         * controller/action because the URI couldn't be matched.
         */
        const NOT_FOUND     = 0;
        
        
        const GET      = 'GET';
        const POST     = 'POST';
        const DELETE   = 'DELETE';
        const HEAD     = 'HEAD';
        const PUT      = 'PUT';
        
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
            self::addRoute(new Route($URI, $function, [
                self::GET,
                self::PUT,
                self::POST,
                self::DELETE,
                self::HEAD,
            ]));
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
        public static function match(array $methods, $URI, $function)
        {
            if (count($methods) >= 1) {
                self::addRoute(new Route($URI, $function, $methods));
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
            self::addRoute(new Route($URI, $function, [self::GET]));
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
            self::addRoute(new Route($URI, $function, [self::POST]));
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
            self::addRoute(new Route($URI, $function, [self::PUT]));
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
            self::addRoute(new Route($URI, $function, [self::DELETE]));
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
            self::addRoute(new Route($URI, $function, [self::HEAD]));
        }
        
        /**
         * Registers a callback function to be executed when a certain error occurs.
         * 
         * <code>
         * use \Gishiki\Core\Route;
         * 
         * Route::error(Route::NOT_FOUND, function ($params) {
         *      //perform your failback amazing magic here!
         * });
         * </code>
         * 
         * @param int      $error_type the error that brings the callback function to be executed
         * @param function $callback   this is the function automatically called when the error type occurs
         */
        public static function error($error_type, $callback)
        {
            self::addRoute(new Route(intval($error_type), $callback));
        }
        
        /**
         * Run the router and serve the current request.
         * 
         * This function is __CALLED INTERNALLY__ and, therefore
         * it __MUST NOT__ be called! 
         * 
         * @param Request $to_fulfill the request to be served/fulfilled
         */
        public static function run(Request &$to_fulfill)
        {
            //var_dump($to_fulfill->getUri()->getPath());
            
        }
        
        
        /***********************************************************************
         * 
         *                    NON-Static class members
         * 
         **********************************************************************/
        
        /**
         * @var string the URI for the current route
         */
        private $URI;
        
        /**
         * @var mixed the anonymous function to be executed or the name of the action@controller
         */
        private $action;
        
        /**
         * @var array the list of allowed methods to be routed using the route URI 
         */
        private $methods;
        
        /**
         * Create route instance that should be registered to the valid routes
         * list:
         * 
         * <code>
         * $my_route = new Route("/user/{username}", function () {
         *      //make good things here
         * });
         * 
         * Route::addRoute($my_route);
         * </code>
         * 
         * @param string  $URI        the URI to be matched in order to take the given action
         * @param mixed   $action     the action to be performed on URI match
         * @param array   $methods    the list of allowed method for the current route
         */
        public function __construct($URI, $action, array $methods = [self::GET, self::DELETE, self::POST, self::PUT, self::HEAD])
        {
            //build-up the current route
            $this->URI = strval($URI);
            $this->action = $action;
            $this->methods = $methods;
        }
    }
}
