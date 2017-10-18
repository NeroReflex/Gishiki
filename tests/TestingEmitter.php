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

use Zend\Diactoros\Response\EmitterInterface;
use Psr\Http\Message\ResponseInterface;

class TestingEmitter implements EmitterInterface
{
    /**
     * @var ResponseInterface the response
     */
    private $response;

    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }

    public function getBodyContent()
    {
        $body = $this->response->getBody();
        $body->rewind();
        return (string)$body;
    }

    public function emit(ResponseInterface $response)
    {
        $this->response = clone $response;
    }


}