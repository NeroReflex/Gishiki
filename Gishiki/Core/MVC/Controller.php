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

use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
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
     * This is a clone of the request the client have send to this server.
     *
     * @var Request the request the controller must fulfill
     */
    protected $request;

    /**
     * This is the response that will be sent back to the client from this server.
     *
     * @var Response the response the controller must generate
     */
    protected $response;

    /**
     * This is the collection of arguments passed to the URI.
     *
     * @var GenericCollection the collection of arguments passed to the URI
     */
    protected $arguments;

    /**
     * Create a new controller that will fulfill the given request filling the given response.
     *
     * @param Request           $controllerRequest   the request arrived from the client
     * @param Response          $controllerResponse  the response to be given to the client
     * @param GenericCollection $controllerArguments the collection of catched URI params
     */
    public function __construct(Request &$controllerRequest, Response &$controllerResponse, GenericCollection &$controllerArguments)
    {
        //save the request
        $this->request = $controllerRequest;

        //save the response
        $this->response = $controllerResponse;

        //save the arguments collection
        $this->arguments = $controllerArguments;
    }
}
