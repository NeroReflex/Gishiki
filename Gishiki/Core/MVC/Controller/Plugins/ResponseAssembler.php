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

namespace Gishiki\Core\MVC\Controller\Plugins;

use Gishiki\Algorithms\Collections\SerializableCollection;
use Gishiki\Core\MVC\Controller\ControllerException;
use Gishiki\Core\MVC\Controller\Plugin;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * This is a plugin used to generate data to be serialized before being transported by an HTTP Response.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class ResponseAssembler extends Plugin
{

    /**
     * @var SerializableCollection the data to be filled
     */
    protected $data;

    /**
     * Assembler constructor:
     * setup the response ready to be filled nd used
     *
     * @param RequestInterface  $request  the HTTP request
     * @param ResponseInterface $response the HTTP response
     */
    public function __construct(RequestInterface &$request, ResponseInterface &$response)
    {
        //this is important, NEVER forget!
        parent::__construct($request, $response);

        $this->data = new SerializableCollection();
    }

    /**
     * Execute the given task in order to fulfill the given request
     *
     * @param  mixed    $input    input value for generating the response
     * @param  \Closure $callable the task to be ran
     * @return mixed the value returned from the task
     * @throws ControllerException the error preventing function execution
     */
    public function assemblyWith(\Closure $callable, $input)
    {
        $return = null;
        try {
             $return = $callable($input, $this->assembly());
        } catch (\Error $ex) {
            throw new ControllerException("The given function doesn't accept passed parameters", 103);
        }

        return $return;
    }

    /**
     * Get what has been built by successive calls to assemblyWith.
     *
     * @return SerializableCollection what has been build
     */
    public function &assembly() : SerializableCollection
    {
        return $this->data;
    }
}