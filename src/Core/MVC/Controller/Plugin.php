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

/**
 * This is the base class for a middleware.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class Plugin
{
    /**
     * @var RequestInterface reference to the HTTP request
     */
    protected $request;

    /**
     * @var ResponseInterface reference to the HTTP response
     */
    protected $response;

    /**
     * Plugin constructor.
     *
     * __Warning:__ you should *never* attempt to use another construction in your plugin,
     * unless it calls parent::__construct()
     *
     * @param RequestInterface  $request  the HTTP request
     * @param ResponseInterface $response the HTTP response
     */
    public function __construct(RequestInterface &$request, ResponseInterface &$response)
    {
        $this->request = &$request;
        $this->response = &$response;
    }

    /**
     * Get the HTTP response.
     *
     * @return ResponseInterface the HTTP response
     */
    public function &getResponse() : ResponseInterface
    {
        return $this->response;
    }

    /**
     * Get the HTTP request.
     *
     * @return RequestInterface the HTTP request
     */
    public function &getRequest() : RequestInterface
    {
        return $this->request;
    }
}