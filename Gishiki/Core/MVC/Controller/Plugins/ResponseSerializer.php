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
use Gishiki\Core\MVC\Controller\Plugin;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * This is a plugin used to generate data to be transported by the HTTP response.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class ResponseSerializer extends Plugin
{
    /**
     * Serializer constructor:
     * setup the plugin importing serializers
     *
     * @param RequestInterface  $request  the HTTP request
     * @param ResponseInterface $response the HTTP response
     */
    public function __construct(RequestInterface &$request, ResponseInterface &$response)
    {
        //this is important, NEVER forget!
        parent::__construct($request, $response);

    }

    public function setSerializedResponse(SerializableCollection $data)
    {
        
    }
}