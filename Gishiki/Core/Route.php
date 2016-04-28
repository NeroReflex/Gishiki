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
    use Gishiki\Core\MVC\Controller;

    /**
     * This class is used to provide a small layer of Laravel-compatibility
     * and ease of routing usage.
     *
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    final class Route
    {
        /**
         * This is the list of added routes.
         *
         * @var array a collection of routes
         */
        private static $routes = [];

        /**
         * This is the list of added callback routes.
         * 
         * @var array a collection of callback routes
         */
        private static $callbacks = [];

        /**
         * Add a route to the route redirection list.
         * 
         * @param Route $route the route to be added
         *
         * @return Route the added route
         */
        public static function &addRoute(Route &$route)
        {
            //add the given route to the routes list
            return ($route->isSpecialCallback() === false) ?
                self::$routes[] = $route :
                self::$callbacks[] = $route;
        }

        /*
         * Used when the router were unable to route the request to a suitable
         * controller/action because the URI couldn't be matched.
         */
        const NOT_FOUND = 0;

        /*
         * Commonly used requests methods (aka HTTP/HTTPS verbs)
         */
        const ANY = 'any'; //not an http verb, used internally
        const GET = 'GET';
        const POST = 'POST';
        const DELETE = 'DELETE';
        const HEAD = 'HEAD';
        const PUT = 'PUT';
        const PATCH = 'PATCH';
        const OPTIONS = 'OPTIONS';

        /**
         * Convinient proxy function to call Route::addRoute( ... ).
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
         * @param string   $uri      the URI that will bring to the function execution
         * @param function $function the function executed when the URL is called
         */
        public static function &any($uri, $function)
        {
            $route = new self($uri, $function, [
                self::ANY,
            ]);
            self::addRoute($route);

            return $route;
        }

        /**
         * Convinient proxy function to call Route::addRoute( ... ).
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
         * @param string   $uri      the URI that will bring to the function execution
         * @param function $function the function executed when the URL is called
         */
        public static function &match(array $methods, $uri, $function)
        {
            $route = null;
            if (count($methods) >= 1) {
                $route = new self($uri, $function, $methods);
                self::addRoute($route);
            }

            return $route;
        }

        /**
         * Convinient proxy function to call Route::addRoute( ... ).
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
         * @param string   $uri      the URI that will bring to the function execution
         * @param function $function the function executed when the URL is called
         */
        public static function &get($uri, $function)
        {
            $route = new self($uri, $function, [self::GET]);
            self::addRoute($route);

            return $route;
        }

        /**
         * Convinient proxy function to call Route::addRoute( ... ).
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
         * @param string   $uri      the URI that will bring to the function execution
         * @param function $function the function executed when the URL is called
         */
        public static function &post($uri, $function)
        {
            $route = new self($uri, $function, [self::POST]);
            self::addRoute($route);

            return $route;
        }

        /**
         * Convinient proxy function to call Route::addRoute( ... ).
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
         * @param string   $uri      the URI that will bring to the function execution
         * @param function $function the function executed when the URL is called
         */
        public static function &put($uri, $function)
        {
            $route = new self($uri, $function, [self::PUT]);
            self::addRoute($route);

            return $route;
        }

        /**
         * Convinient proxy function to call Route::addRoute( ... ).
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
         * @param string   $uri      the URI that will bring to the function execution
         * @param function $function the function executed when the URL is called
         */
        public static function &delete($uri, $function)
        {
            $route = new self($uri, $function, [self::DELETE]);
            self::addRoute($route);

            return $route;
        }

        /**
         * Convinient proxy function to call Route::addRoute( ... ).
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
         * @param string   $uri      the URI that will bring to the function execution
         * @param function $function the function executed when the URL is called
         */
        public static function &head($uri, $function)
        {
            $route = new self($uri, $function, [self::HEAD]);
            self::addRoute($route);

            return $route;
        }

        /**
         * Run the router and serve the current request.
         * 
         * This function is __CALLED INTERNALLY__ and, therefore
         * it __MUST NOT__ be called! 
         * 
         * @param Request $reqestToFulfill the request to be served/fulfilled
         *
         * @return Response $reqestToFulfill the request to be served/fulfilled
         */
        public static function run(Request &$reqestToFulfill)
        {
            $response = new Response();
            $uri_decoded = urldecode($reqestToFulfill->getUri()->getPath());
            $reversed_params = null;

            //this is the route that reference the action to be taken
            $actionRuote = null;

            //test/try matching every route
            foreach (self::$routes as $currentRoute) {
                //build a collection from the current reverser URI (of detect the match failure)
                $reversed_params = $currentRoute->matchURI($uri_decoded, $reqestToFulfill->getMethod());
                if (is_object($reversed_params)) {
                    //execute the requested action!
                    $actionRuote = $currentRoute;

                    //stop searching for a suitable URI to be matched against the current one
                    break;
                } else {
                    $reversed_params = new GenericCollection();
                }
            }

            //oh.... seems like we have a 404 Not Found....
            if (!is_object($actionRuote)) {
                $response = $response->withStatus(404);

                foreach (self::$callbacks as $currentRoute) {
                    //check for a valid callback
                    if (is_object($currentRoute->matchURI(self::NOT_FOUND, $reqestToFulfill->getMethod()))) {
                        //flag the execution of this failback action!
                        $actionRuote = $currentRoute;

                        //found what I was looking for, break the foreach
                        break;
                    }
                }
            }

            //execute the router call
            $request = clone $reqestToFulfill;
            (is_object($actionRuote)) ?
                $actionRuote($request, $response, $reversed_params) : null;

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
        private $uri;

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
         * list:.
         * 
         * <code>
         * $my_route = new Route("/user/{username}", function () {
         *      //make good things here
         * });
         * 
         * Route::addRoute($my_route);
         * </code>
         * 
         * @param string         $uri     the URI to be matched in order to take the given action
         * @param Closure|string $action  the action to be performed on URI match
         * @param array          $methods the list of allowed method for the current route
         */
        public function __construct($uri, $action, array $methods = [self::GET, self::DELETE, self::POST, self::PUT, self::HEAD])
        {
            //build-up the current route
            $this->URI = (is_string($uri)) ? '/'.trim($uri, '/') : $uri;
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
         * Get the type of the current route.
         * 
         * The route type can be an integer for special callbacks
         * (for example NOT_FOUND) or a boolean false for a valid string URI
         * 
         * 
         * @return int|bool the callback type or false if it is a valid URI
         */
        public function isSpecialCallback()
        {
            return (is_numeric($this->URI)) ? $this->URI : false;
        }

        /**
         * Attempt to match the given URI and mathod combination
         * with the current route.
         * 
         * @param string $uri    the URI to be mtched
         * @param string $method the used method
         *
         * @return GenericCollection|null the match result
         */
        public function matchURI($uri, $method)
        {
            $reversed_params = null;

            if ($this->isSpecialCallback() === false) {
                $regexData = $this->getRegex();

                //try matching the regex against the currently requested URI
                $matches = [];
                if (((in_array($method, $this->methods)) || (in_array(self::ANY, $this->methods))) && (preg_match($regexData['regex'], $uri, $matches))) {
                    $reversed_URI = [];
                    $skipNum = 1;
                    foreach ($regexData['params'] as $currentKey => $current_match_name) {
                        //get the value of the matched URI param
                        $value = $matches[$currentKey + $skipNum];

                        //filter the value of the matched URI param
                        switch ($regexData['param_types'][$currentKey]) {
                            case 'signed_integer' :
                                $value = intval($value);
                                break;

                            default : //should be used for 'email', 'default', etc.
                                $value = strval($value);
                        }

                        //store the value of the matched URI param
                        $reversed_URI[$current_match_name] = $value;
                        $skipNum += $regexData['skipping_params'][$currentKey];
                    }

                    //build a collection from the current reverser URI
                    $reversed_params = new GenericCollection($reversed_URI);
                }
            } elseif ((in_array($method, $this->methods)) && ($uri == $this->isSpecialCallback())) {
                $reversed_params = new GenericCollection();
            }

            return $reversed_params;
        }

        /**
         * Keeps a substitution table for regex and the relative groups.
         *
         * @var array the table of regex and regex groups
         */
        private static $regex_table = [
            'default' => ['[^\/]+', 0],
            'email' => ['([a-zA-Z0-9_\-.+]+)\@([a-zA-Z0-9-]+)\.([a-zA-Z]+)((\.([a-zA-Z]+))?)', 6],
            'signed_integer' => ['(\+|\-)?(\d)+', 2],
        ];

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
         * __Note:__ if the regex field of the returned array is an empty string,
         * then the router is a special callback
         * 
         * @return array the regex version of the URI and additional info
         */
        public function getRegex()
        {
            //fix the URI
            $regexURI = null;

            $paramArray = [];
            $paramSkip = [];
            $paramTypes = [];

            if ($this->isSpecialCallback() === false) {
                //start building the regex
                $regexURI = '/^'.preg_quote($this->URI, '/').'$/';

                //this will contain the matched expressions placeholders
                $params = [];
                //detect if regex are involved in the furnished URI
                if (preg_match_all("/\\\{([a-zA-Z]|\d|\_|\.|\:|\\\\)+\\\}/", $regexURI, $params)) {
                    //substitute a regex for each matching group:
                    foreach ($params[0] as $mathingGroup) {
                        //extract the regex to be used
                        $param = Manipulation::getBetween($mathingGroup, '\{', '\}');
                        $regexId = explode('\\:', $param, 2);

                        $currentRegex = '';
                        if (count($regexId) == 2) {
                            $currentRegex = strval($regexId[1]);
                        }

                        $param = $regexId[0];
                        $regexTableId = 'default';
                        switch (strtolower($currentRegex)) {
                            case 'mail':
                            case 'email':
                                $regexTableId = 'email';
                                break;

                            case 'number':
                            case 'integer':
                            case 'signed_integer':
                                $regexTableId = 'signed_integer';
                                break;

                            default:
                                $regexTableId = 'default';
                        }

                        $regexURI = str_replace($mathingGroup, '('.self::$regex_table[$regexTableId][0].')', $regexURI);
                        $paramArray[] = $param;
                        $paramTypes[] = $regexTableId;
                        $paramSkip[] = self::$regex_table[$regexTableId][1];
                    }
                }
            }

            //return the built regex + additionals info
            return [
                'regex' => (!is_null($regexURI)) ? $regexURI : '',
                'params' => $paramArray,
                'param_types' => $paramTypes,
                'skipping_params' => $paramSkip,
            ];
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
         * @param Request           $request   a copy of the request made to the application
         * @param Response          $response  the action must fille, and what will be returned to the client
         * @param GenericCollection $arguments a list of reversed URI parameters 
         */
        public function __invoke(Request &$request, Response &$response, GenericCollection &$arguments)
        {
            if (is_callable($this->action)) {
                //execute the given action
                call_user_func_array($this->action, [&$request, &$response, &$arguments]);
            } elseif (is_string($this->action)) {
                //execute the controller
                Controller::Execute($this->action, $request, $response, $arguments);
            } else {
                //what are you fucking doing?
                $response = $response->withStatus(500);
                $response->write('Undefined route behaviour');
            }
        }
    }
}
