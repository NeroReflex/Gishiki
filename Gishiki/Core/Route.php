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
    use Gishiki\HttpKernel\Response;
    use Gishiki\Algorithms\Manipulation;
    use Gishiki\Algorithms\Collections\GenericCollection;
    
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
        
        /*
         * Used when the router were unable to route the request to a suitable
         * controller/action because the URI couldn't be matched.
         */
        const NOT_FOUND     = 0;
        
        
        /*
         * Commons requests methods (aka HTTP/HTTPS verbs)
         */
        const GET      = 'GET';
        const POST     = 'POST';
        const DELETE   = 'DELETE';
        const HEAD     = 'HEAD';
        const PUT      = 'PUT';
        
        /**
         * Convinient proxy function to call Route::addRoute( ... )
         * 
         * <code>
         * use \Gishiki\Core\Route;
         * 
         * Route::any("/user/{id}", function ($params) {
         *      //perform your amazing magic here
         * });
         * 
         * 
         * </code>
         * 
         *  @see \Gishiki\Core\Route\addRoute
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
         * Convinient proxy function to call Route::addRoute( ... )
         * 
         * <code>
         * use \Gishiki\Core\Route;
         * 
         * Route::match([Route::GET, Route::POST], "/user/{id}", function ($params) {
         *      //perform your amazing magic here
         * });
         * 
         * //you can also route an error:
         * Route::match([Route::GET, Route::POST], Route::NOT_FOUND, function ($params) {
         *      //perform your failback amazing magic here!
         * });
         * </code>
         * 
         *  @see \Gishiki\Core\Route\addRoute
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
         * Convinient proxy function to call Route::addRoute( ... )
         * 
         * <code>
         * use \Gishiki\Core\Route;
         * 
         * Route::get("/user/{id}", function ($params) {
         *      //perform your amazing magic here
         * });
         * 
         * //you can also route an error:
         * Route::get(Route::NOT_FOUND, function ($params) {
         *      //perform your failback amazing magic here!
         * });
         * </code>
         * 
         *  @see \Gishiki\Core\Route\addRoute
         * 
         * @param string   $URI      the URI that will bring to the function execution
         * @param function $function the function executed when the URL is called
         */
        public static function get($URI, $function)
        {
            self::addRoute(new Route($URI, $function, [self::GET]));
        }
        
        /**
         * Convinient proxy function to call Route::addRoute( ... )
         * 
         * <code>
         * use \Gishiki\Core\Route;
         * 
         * Route::post("/user/{id}", function ($params) {
         *      //perform your amazing magic here
         * });
         * //you can also route an error:
         * Route::post(Route::NOT_FOUND, function ($params) {
         *      //perform your failback amazing magic here!
         * });
         * </code>
         * 
         *  @see \Gishiki\Core\Route\addRoute
         * 
         * @param string   $URI      the URI that will bring to the function execution
         * @param function $function the function executed when the URL is called
         */
        public static function post($URI, $function)
        {
            self::addRoute(new Route($URI, $function, [self::POST]));
        }
        
        /**
         * Convinient proxy function to call Route::addRoute( ... )
         * 
         * <code>
         * use \Gishiki\Core\Route;
         * 
         * Route::put("/user/{id}", function ($params) {
         *      //perform your amazing magic here
         * });
         * //you can also route an error:
         * Route::put(Route::NOT_FOUND, function ($params) {
         *      //perform your failback amazing magic here!
         * });
         * </code>
         * 
         *  @see \Gishiki\Core\Route\addRoute
         * 
         * @param string   $URI      the URI that will bring to the function execution
         * @param function $function the function executed when the URL is called
         */
        public static function put($URI, $function)
        {
            self::addRoute(new Route($URI, $function, [self::PUT]));
        }
        
        /**
         * Convinient proxy function to call Route::addRoute( ... )
         * 
         * <code>
         * use \Gishiki\Core\Route;
         * 
         * Route::delete("/user/{id}", function ($params) {
         *      //perform your amazing magic here
         * });
         * 
         * //you can also route an error:
         * Route::delete(Route::NOT_FOUND, function ($params) {
         *      //perform your failback amazing magic here!
         * });
         * </code>
         * 
         *  @see \Gishiki\Core\Route\addRoute
         * 
         * @param string   $URI      the URI that will bring to the function execution
         * @param function $function the function executed when the URL is called
         */
        public static function delete($URI, $function)
        {
            self::addRoute(new Route($URI, $function, [self::DELETE]));
        }
        
        /**
         * Convinient proxy function to call Route::addRoute( ... )
         * 
         * <code>
         * use \Gishiki\Core\Route;
         * 
         * Route::head("/user/{id}", function ($params) {
         *      //perform your amazing magic here
         * });
         * </code>
         * 
         * @see \Gishiki\Core\Route\addRoute
         * 
         * @param string   $URI      the URI that will bring to the function execution
         * @param function $function the function executed when the URL is called
         */
        public static function head($URI, $function)
        {
            self::addRoute(new Route($URI, $function, [self::HEAD]));
        }
        
        /**
         * Run the router and serve the current request.
         * 
         * This function is __CALLED INTERNALLY__ and, therefore
         * it __MUST NOT__ be called! 
         * 
         * @param  Request  $to_fulfill the request to be served/fulfilled
         * @return Response $to_fulfill the request to be served/fulfilled
         */
        public static function run(Request &$to_fulfill)
        {
            $response = new Response();
            $URI_decoded = urldecode($to_fulfill->getUri()->getPath());
            $URI_found  = false; // was the URI being found?
            
            foreach (self::$routes as $key_current_route => $current_route) {
                //check for used HTTP verb:
                if (in_array($to_fulfill->getMethod(), $current_route->getMethods())) {
                    //get the regex and parameters placeholders
                    $regex_and_info = $current_route->getRegex();

                    //try matching the regex against the currently requested URI
                    $matches = [];
                    if ((is_string($regex_and_info["regex"])) && (preg_match($regex_and_info["regex"], $URI_decoded, $matches))) {
                        $reversed_URI = [];
                        foreach ($regex_and_info["params"] as $current_match_key => $current_match_name) {
                            $reversed_URI[$current_match_name] = $matches[$current_match_key + 1];
                        }

                        //build a collection from the current reverser URI
                        $reversed_params = new GenericCollection($reversed_URI);

                        //execute the requested action!
                        $current_route->take_action(clone $to_fulfill, $response, $reversed_params);
                        
                        //stop searching for a suitable URI to be matched against the current one
                        $URI_found = true;
                        break;
                    }
                }
            }
            
            //oh.... seems like we have a 404 Not Found....
            if (!$URI_found) {
                $response->withStatus(404);
                
                foreach (self::$routes as $current_route) {
                    //get the regex
                    $regex_and_info = $current_route->getRegex();
                    
                    if ((($regex_and_info["regex"] === self::NOT_FOUND)) &&
                            (in_array($to_fulfill->getMethod(), $current_route->getMethods())))
                    {
                        //execute the failback action!
                        $current_route->take_action(clone $to_fulfill, $response, $reversed_params);
                    }
                }
            }
            
            //this function have to return a response
            return $response;
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
            $this->URI = (is_string($URI))? '/'.trim($URI, '/') : $URI;
            $this->action = $action;
            $this->methods = $methods;
        }
        
        /**
         * Return the list of methods allowed to be routed with the given URI.
         * 
         * The return value is an array of allowed method (as strings):
         * <code>
         * //this is an example:
         * array(
         *     'GET',
         *     'DELETE'
         * );
         * </code>
         * 
         * @return array the list of allowed methods
         */
        public function getMethods()
        {
            return $this->methods;
        }
        
        /**
         * build a regex out of the URI of the current Route and adds name of
         * regex placeholders.
         * 
         * Example:
         * <code>
         * array(
         *     "regex"  => "...",
         *     "params" => array("name", "surname")
         * )
         * </code>
         * 
         * __Note:__ if the regex field of the returned array is an integer, then
         * the router is a special callback
         * 
         * @return array the regex version of the URI and additional info
         */
        public function getRegex()
        {
            //fix the URI
            $regexURI = $this->URI;
            
            //start building the regex
            $regexURI = "/^".preg_quote($regexURI, "/")."$/";
            $param_array = [];
            
            //this will contain the matched expressions placeholders
            $params = array();
            //detect if regex are involved in the furnished URI
            if (preg_match_all('/\\\{([a-zA-Z]|\d|\_|\.|\:)+\\\}/', $regexURI, $params)) {
                //substitute a regex for each matching group:
                foreach ($params[0] as $mathing_group) {
                    //extract the regex to be used
                    $param = Manipulation::get_between($mathing_group, '\{', '\}');
                    $current_regex = explode(':', $param, 2);
                    if ((count($current_regex) == 2) && ($current_regex[1])) {
                        $current_regex = $current_regex[1];
                        $param = $current_regex[0];
                    } else {
                        $current_regex = '';
                    }
                    
                    switch ($current_regex) {
                        case 'mail':
                        case 'email':
                            $current_regex = "[a-zA-Z0-9_-.+]+@[a-zA-Z0-9-]+.[a-zA-Z]+";
                            break;
                        
                        default:
                            $current_regex = '[^\/]+';
                    }
                    
                    $regexURI = str_replace($mathing_group, "(".$current_regex.")", $regexURI);
                    $param_array[] = $param;
                }
                
                array(
                    "regex"  => $regexURI,
                    "params" => $params[0]
                );
            }
            
            //return the built regex + additionals info
            return array(
                "regex"  => $regexURI,
                "params" => $param_array
            );
        }
        
        /**
         * Execute the router callback, may it be a string (for action@controller)
         * or an anonymous function.
         * 
         * This function is called __AUTOMATICALLY__ by the framework when the
         * route can be used to fulfill the given request.
         * 
         * This function is provided for logical organization of the program and
         * testing only!
         * 
         * @param Request           $copy_of_request  a copy of the request made to the application
         * @param Response          $response         the action must fille, and what will be returned to the client
         * @param GenericCollection $arguments        a list of reversed URI parameters 
         */
        protected function take_action(Request $copy_of_request, Response &$response, GenericCollection &$arguments)
        {
            if (is_callable($this->action)) {
                call_user_func_array($this->action, [$copy_of_request, &$response, &$arguments]);
            } elseif (is_string($this->action)) {
                //execute the controller
            } else {
                //what are you fucking doing?
            }
        }
    }
}
