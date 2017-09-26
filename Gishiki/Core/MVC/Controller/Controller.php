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

namespace Gishiki\Core\MVC\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Gishiki\Algorithms\Collections\GenericCollection;

/**
 * The Gishiki base controller:
 * every controller inherits from this class
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class Controller
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
     * @var ControllerResponse the helper used to build the response
     */
    protected $generator;

    /**
     * Create a new controller that will fulfill the given request filling the given response.
     *
     * __Warning:__ you should *never* attempt to use another construction in your controllers,
     * unless it calls parent::__contruct(), and it doesn't accept arguments!
     *
     * @param RequestInterface  $controllerRequest   the request arrived from the client
     * @param ResponseInterface $controllerResponse  the response to be given to the client
     * @param GenericCollection $controllerArguments the collection of matched URI params
     */
    public function __construct(RequestInterface &$controllerRequest, ResponseInterface &$controllerResponse, GenericCollection &$controllerArguments)
    {
        //save the request
        $this->request = $controllerRequest;

        //save the response
        $this->response = $controllerResponse;

        //save the arguments collection
        $this->arguments = $controllerArguments;

        //setup the response generator helper
        $this->generator = new ControllerResponse();
    }

    /**
     * Use a component function to complete the controller response.
     *
     * @param  string $componentName     the name of the component to be used
     * @param  string $componentFunction the name of the action to be performed
     * @param  array  $args              the list of parameters to be passed to the action
     * @throws ControllerException the error preventing the body to be written
     */
    public function appendToResponse($componentName, $componentFunction, array $args = [])
    {
        $this->generator->import($componentName, $componentFunction, $args);
    }

    /**
     * Generate the response automatically from the controller response generator:
     *
     * <code>
     * class MyController extends Controller
     * {
     *     public function myAction()
     *     {
     *         $this->appendToResponse(TimeComponent::class, 'getTime');
     *         $this->appendToResponse(AuthComponent::class, 'getUserInfo');
     *
     *         $this->generateResponse();
     *     }
     * }
     * </code>
     * @param string|null $template the file name for the template or null
     * @throws ControllerException the error preventing the body to be written
     */
    public function generateResponse($template = null)
    {
        $this->generator->compile($this->response, $template);
    }
}
