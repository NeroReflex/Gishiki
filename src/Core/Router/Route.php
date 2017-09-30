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
use Gishiki\Core\MVC\Controller\Plugin;
use Gishiki\Core\MVC\Controller\Plugins\RequestDeserializer as DeserializerPlugin;
use Gishiki\Core\MVC\Controller\Plugins\ResponseSerializer as SerializerPlugin;
use Gishiki\Core\MVC\Controller\Plugins\ResponseAssembler as AssemblerPlugin;

/**
 * This class is used to provide a small layer of Laravel-compatibility
 * and ease of routing usage.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class Route
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
     * @var array the route definition
     */
    private $route = [
        "plugins" => [
            "deserializer" => DeserializerPlugin::class,
            "serializer" => SerializerPlugin::class,
            "assembler" => AssemblerPlugin::class,
        ]
    ];

    /**
     * Build a new route to be registered within a Gishiki\Core\Router instance.
     *
     * An usage example is:
     * <code>
     * $route = new Route([
     *     "verbs" => [
     *          Route::GET
     *      ],
     *      "uri" => "/",
     *      "status" => Route::OK,
     *      "controller" => MyController::class,
     *      "action" => "index",
     * ]);
     * </code>
     *
     * @param array $options The URI for the current route and more options
     *
     * @throws RouterException The route is malformed
     */
    public function __construct(array $options)
    {
        foreach ($options as $key => &$value)
        {
            if (is_string($key))
            {
                if (strcmp(strtolower($key), "verbs") == 0) {
                    $this->route["verbs"] = $value;
                } else if (strcmp(strtolower($key), "uri") == 0) {
                    $this->route["uri"] = $value;
                } else if (strcmp(strtolower($key), "action") == 0) {
                    $this->route["action"] = $value;
                } else if (strcmp(strtolower($key), "status") == 0) {
                    $this->route["status"] = $value;
                } else if (strcmp(strtolower($key), "controller") == 0) {
                    $this->route["controller"] = $value;
                } else if (strcmp(strtolower($key), "action") == 0) {
                    $this->route["action"] = $value;
                } else if ((strcmp(strtolower($key), "plugins") == 0) && (is_array($value))) {
                    $this->route["plugins"] = array_merge($this->route["plugins"], $value);
                }
            }
        }

        if (!is_string($this->route["uri"])) {
            throw new RouterException("Invalid URI", 1);
        }

        if (!is_array($this->route["verbs"])) {
            throw new RouterException("Invalid HTTP Verbs", 2);
        }

        if (!is_integer($this->route["status"])) {
            throw new RouterException("Invalid HTTP Status code", 3);
        }

        if (!is_string($this->route["controller"])) {
            throw new RouterException("Invalid Controller: not a class name", 4);
        }

        if (!class_exists($this->route["controller"])) {
            throw new RouterException("Invalid Controller: class ".$this->route["controller"]." does't exists", 4);
        }

        if (!is_string($this->route["action"])) {
            throw new RouterException("Invalid Action: not a function name", 5);
        }

        if (!method_exists($this->route["controller"], $this->route["action"])) {
            throw new RouterException("Invalid Action: ".$this->route["action"]." is not a valid function of the ".$this->route["controller"]." class", 6);
        }

        foreach ($this->route["plugins"] as $id => &$middleware) {
            if ((!is_string($middleware)) || (!class_exists($middleware)) || (!is_subclass_of($middleware, Plugin::class))) {
                throw new RouterException("The ".$id." plugin is not valid", 8);
            }
        }
    }

    /**
     * Execute the router callback, may it be a string (for controller->action)
     * or an anonymous function.
     *
     * This function is called __AUTOMATICALLY__ by the framework when the
     * route can be used to fulfill the given request.
     *
     * @param RequestInterface  $request   a copy of the request made to the application
     * @param ResponseInterface $response  the action must fille, and what will be returned to the client
     * @param GenericCollection $arguments a list of reversed URI parameters
     */
    public function __invoke(RequestInterface &$request, ResponseInterface &$response, GenericCollection &$arguments, array $controllerArgs = [])
    {
        //import middleware
        $plugins = $this->route["plugins"];

        //start filling the response with the default status code
        $response = $response->withStatus($this->getStatus());

        //import controller name and action
        $controllerName = $this->route["controller"];
        $controllerAction = $this->route["action"];

        //reflect the given controller class
        $reflectedController = new \ReflectionClass($controllerName);

        //and create a new instance of it
        $controller = $reflectedController->newInstanceArgs([&$request, &$response, &$arguments, &$plugins]);

        //register additional arguments
        $controller->loadDependencies($controllerArgs);

        //reflect the requested action
        $reflectedAction = new \ReflectionMethod($controllerName, $controllerAction);
        $reflectedAction->setAccessible(true); //can invoke private methods :)

        //and execute it
        $reflectedAction->invoke($controller);
    }

    /**
     * Get the URI mapped by this Route
     *
     * @return string the URI of this route
     */
    public function getURI() : string
    {
        return $this->route["uri"];
    }

    /**
     * Get the status code mapped by this Route
     *
     * @return integer the status code of this route
     */
    public function getStatus() : int
    {
        return $this->route["status"];
    }

    /**
     * Get the URI mapped by this Route
     *
     * @return array the list of HTTP verbs allowed
     */
    public function getMethods() : array
    {
        return $this->route["verbs"];
    }
}
