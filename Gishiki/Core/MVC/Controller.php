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

namespace Gishiki\Core\MVC;

use Gishiki\Core\Environment;
use Gishiki\HttpKernel\Request;
use Gishiki\HttpKernel\Response;
use Gishiki\Algorithms\Collections\GenericCollection;

/**
 * The Gishiki base controller.
 *
 * Every controller (controllers used to generate an application for the
 * client) inherits from this class
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class Controller
{
    /**
     * Execute the given controller.
     *
     * @param string            $action    the name of the controller and action to be used
     * @param Request           $request   the request to serve
     * @param Response          $response  the response to the given request
     * @param GenericCollection $arguments the list of passed arguments
     *
     * @throws \InvalidArgumentException the given action identifier does not identify a valid action
     */
    public static function Execute($action, Request &$request, Response &$response, GenericCollection &$arguments)
    {
        //check for bad action
        if ((!is_string($action)) || ((strpos($action, '@') === false) && (strpos($action, '->') === false))) {
            throw new \InvalidArgumentException("The name of the controller to be executed must be expressed as: 'action@controller' or 'controller->action'");
        }

        //get the name of the action and the controller to be executed
        $controller = (strpos($action, '@') !== false) ? explode('@', $action) : explode('->', $action);

        //check for bad names
        if ((strlen($controller[0]) <= 0) || (strlen($controller[1]) <= 0)) {
            throw new \InvalidArgumentException('The name of the action to be taken and controller to be selectad cannot be empty names');
        }

        if (strpos($action, '->') !== false) {
            $temp = $controller[1];
            $controller[1] = $controller[0];
            $controller[0] = $temp;
        }

        $controllerName = $controller[1];
        $controllerAction = $controller[0];

        //and check for a class with the given name
        if ((!class_exists($controllerName)) && (!is_null(Environment::GetCurrentEnvironment()))) {
            //get the name of the controller file
            $controllerFilepath = Environment::GetCurrentEnvironment()->GetConfigurationProperty('CONTROLLER_DIR')
                    .$controllerName.'.php';

            if (!file_exists($controllerFilepath)) {
                throw new \InvalidArgumentException('The given controller cannot be found on your application directory');
            }

            //include the controller file
            include $controllerFilepath;
        }

        //and re-check for the given controller name
        if (!class_exists($controllerName)) {
            throw new \InvalidArgumentException('The given controller ('.$controllerName.') does NOT identify a valid controller');
        }

        //reflect the given controller class
        $reflectedController = new \ReflectionClass($controllerName);

        //and create a new instance of it
        $controllerMethod = $reflectedController->newInstanceArgs([&$request, &$response, &$arguments]);

        //reflect the requested action
        $reflected_action = new \ReflectionMethod($controllerName, $controllerAction);
        $reflected_action->setAccessible(true); //can invoke private methods :)

        //and execute it
        $reflected_action->invoke($controllerMethod);
    }

    /***************************************************************************
     *                                                                         *
     *                             Controller                                  *
     *                                                                         *
     **************************************************************************/

    /**
     * This is a clone of the request the client have send to this server.
     *
     * @var Request the request the controller must fulfill
     */
    protected $Request;

    /**
     * This is the respone that will be sent back to the client from this server.
     *
     * @var Response the response the controller must generate
     */
    protected $Response;

    /**
     * This is the collection of arguments passed to the URI.
     *
     * @var GenericCollection the collection of arguments passed to the URI
     */
    protected $Arguments;

    /**
     * Create a new controller that will fulfill the given request filling the given response.
     *
     * @param Request           $request   the request arrived from the client
     * @param Response          $response  the response to be given to the client
     * @param GenericCollection $arguments the collection of catched URI params
     */
    public function __construct(Request &$request, Response &$response, GenericCollection &$arguments)
    {
        //save the request
        $this->Request = $request;

        //save the response
        $this->Response = $response;

        //save the arguments collection
        $this->Arguments = $arguments;
    }
}
