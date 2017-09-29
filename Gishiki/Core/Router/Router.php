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

namespace Gishiki\Core\Router;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;
use Gishiki\Algorithms\Strings\SimpleLexer;
use Gishiki\Algorithms\Collections\GenericCollection;

/**
 * This component represents the application as a set of HTTP rules.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class Router
{
    /**
     * @var array a list of registered Gishiki\Core\Route ordered my method to allow faster search
     */
    private $routes = [
        Route::GET => [],
        Route::POST => [],
        Route::PUT => [],
        Route::DELETE => [],
        Route::HEAD => [],
        Route::OPTIONS => [],
        Route::PATCH => []
    ];

    /**
     * Register a route within this router.
     *
     * @param Route $route the route to be registered
     */
    public function register(Route $route)
    {
        //put a reference to the object inside allowed methods for a faster search
        foreach ($route->getMethods() as $method) {
            if ((strcmp($method, Route::GET) == 0) ||
                (strcmp($method, Route::POST) == 0) ||
                (strcmp($method, Route::PUT) == 0) ||
                (strcmp($method, Route::DELETE) == 0) ||
                (strcmp($method, Route::HEAD) == 0) ||
                (strcmp($method, Route::OPTIONS) == 0) ||
                (strcmp($method, Route::PATCH) == 0))
            {
                $this->routes[$method][] = &$route;
            }
        }
    }

    /**
     * Run the router and serve the current request.
     *
     * This function is __CALLED INTERNALLY__ and, therefore
     * it __MUST NOT__ be called by the user!
     *
     * @param RequestInterface $requestToFulfill the request to be served/fulfilled
     *
     * @return ResponseInterface the result
     */
    public function run(RequestInterface &$requestToFulfill)
    {
        foreach ($this->routes[$requestToFulfill->getMethod()] as $currentRoute) {
            $decodedUri = urldecode($requestToFulfill->getUri()->getPath());

            $params = null;

            //if the current URL matches the current URI
            if (self::matches($currentRoute->getURI(), $decodedUri, $params)) {
                //derive the response from the current request
                $response = new Response();

                //execute the router call
                $request = clone $requestToFulfill;

                //this will hold the parameters passed on the URL
                $deductedParams = new GenericCollection($params);

                $currentRoute($request, $response, $deductedParams);

                //this function have to return a response
                return $response;
            }
        }
    }

    /**
     * Check if a piece of URL matches a parameter of the given type.
     * List of types:
     *  - 0 unsigned integer
     *  - 1 signed integer
     *  - 2 float
     *  - 3 string
     *  - 4 email
     *
     * @param $urlSplit  string the piece of URL to be checked
     * @param $type      int    the type of accepted parameter
     *
     * @return bool true on success, false otherwise
     */
    private static function paramCheck($urlSplit, $type) : bool
    {
        switch ($type)
        {
            case 0:
                return SimpleLexer::isUnsignedInteger($urlSplit);

            case 1:
                return SimpleLexer::isSignedInteger($urlSplit);

            case 2:
                return SimpleLexer::isFloat($urlSplit);

            case 3:
                return SimpleLexer::isString($urlSplit);

            case 4:
                return SimpleLexer::isEmail($urlSplit);

            default:
                return false;
        }
    }

    /**
     * Check weather a piece of an URL matches the corresponding piece of URI
     *
     * @param  string $uriSplit the slice of URI to be checked
     * @param  string $urlSplit the slice of URL to be checked
     * @param  array $params   used to register the correspondence (if any)
     * @return bool  true if the URL slice matches the URI slice, false otherwise
     */
    private static function matchCheck($uriSplit, $urlSplit, array &$params) : bool
    {
        $result = false;

        if ((strlen($uriSplit) >= 7) && ($uriSplit[0] == '{') && ($uriSplit[strlen($uriSplit) - 1] == '}')) {
            $uriSplitRev = substr($uriSplit, 1, strlen($uriSplit) - 2);
            $uriSplitExploded = explode(':', $uriSplitRev);
            $uriParamType = strtolower($uriSplitExploded[1]);

            $type = null;

            if (strcmp($uriParamType, 'uint') == 0) {
                $type = 0;
            } else if (strcmp($uriParamType, 'int') == 0) {
                $type = 1;
            } else if ((strcmp($uriParamType, 'str') == 0) || (strcmp($uriParamType, 'string') == 0)) {
                $type = 3;
            } else if (strcmp($uriParamType, 'float') == 0) {
                $type = 2;
            } else if ((strcmp($uriParamType, 'email') == 0) || (strcmp($uriParamType, 'mail') == 0)) {
                $type = 4;
            }

            //check the url piece against one of the given model
            if (self::paramCheck($urlSplit, $type)) {
                //matched url piece with the correct type: "1" checked against a string has to become 1
                $urlSplitCType = $urlSplit;
                $urlSplitCType = (($type == 0) || ($type == 1)) ? intval($urlSplit) : $urlSplitCType;
                $urlSplitCType = ($type == 2) ? floatval($urlSplit) : $urlSplitCType;

                $result = true;
                $params[$uriSplitExploded[0]] = $urlSplitCType;
            }
        } else if (strcmp($uriSplit, $urlSplit) == 0) {
            $result = true;
        }

        return  $result;
    }

    /**
     * Check if the given URL matches the route URI.
     * $matchedExpr is given as an associative array: name => value
     *
     * @param string $uri         the URI to be matched against the given URL
     * @param string $url         the URL to be matched
     * @param mixed  $matchedExpr an *empty* array
     * @return bool true if the URL matches the URI, false otherwise
     */
    public static function matches($uri, $url, &$matchedExpr) : bool
    {
        if ((!is_string($url)) || (strlen($url) <= 0)) {
            throw new \InvalidArgumentException("The URL must be given as a non-empty string");
        }

        if ((!is_string($uri)) || (strlen($uri) <= 0)) {
            throw new \InvalidArgumentException("The URI must be given as a non-empty string");
        }

        $matchedExpr = [];
        $result = true;

        $urlSlices = explode('/', $url);
        $uriSlices = explode('/', $uri);

        $slicesCount = count($uriSlices);
        if ($slicesCount != count($urlSlices)) {
            return false;
        }

        for ($i = 0; ($i < $slicesCount) && ($result); $i++) {
            //try matching the current URL slice with the current URI slice
            $result = self::matchCheck($uriSlices[$i], $urlSlices[$i], $matchedExpr);
        }

        return $result;
    }
}