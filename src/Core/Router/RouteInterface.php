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
use Gishiki\Algorithms\Collections\GenericCollection;
use Gishiki\Core\Application;

/**
 * This interface describes how a route must be implemented
 * in its basic implementation.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
interface RouteInterface
{
    const GET = 'GET';
    const POST = 'POST';
    const DELETE = 'DELETE';
    const HEAD = 'HEAD';
    const PUT = 'PUT';
    const PATCH = 'PATCH';
    const OPTIONS = 'OPTIONS';

    const OK = 200;
    const NOT_FOUND = 404;
    const NOT_ALLOWED = 405;

    /**
     * Execute the callback associated with the current route.
     *
     * This function is called __AUTOMATICALLY__ by the framework when the
     * route can be used to fulfill the given request.
     *
     * @param RequestInterface  $request        a copy of the request made to the application
     * @param ResponseInterface $response       the action must filled, and what will be returned to the client
     * @param GenericCollection $arguments      a list of reversed URI parameters
     * @param array             $controllerArgs an array containing data created from the application initialization
     * @param Application|null  $app      the current application instance
     */
    public function __invoke(RequestInterface &$request, ResponseInterface &$response, GenericCollection &$arguments, $controllerArgs = [], Application $app = null);

    /**
     * Get the URI mapped by this Route
     *
     * @return string the URI of this route
     */
    public function getURI() : string;

    /**
     * Get the status code mapped by this Route
     *
     * @return integer the status code of this route
     */
    public function getStatus() : int;

    /**
     * Get the URI mapped by this Route
     *
     * @return string[] the list of HTTP verbs allowed
     */
    public function getMethods() : array;

    /**
     * @param  string $method      the HTTP method used on the Request
     * @param  string $url         the URL invoked
     * @param  mixed  $matchedExpr will be filled with an associative array of paramName => urlValue
     * @return bool true if the given method and url matches this route
     */
    public function matches($method, $url, &$matchedExpr) : bool;
}
