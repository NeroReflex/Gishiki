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
    const ALLOWED_TYPES = [
        'text/yaml' => SerializableCollection::YAML,
        'text/x-yaml' => SerializableCollection::YAML,
        'application/yaml' => SerializableCollection::YAML,
        'application/x-yaml' => SerializableCollection::YAML,
        'application/json' => SerializableCollection::JSON,
        'text/json' => SerializableCollection::JSON,
        'text/xml' => SerializableCollection::XML,
        'application/xml' => SerializableCollection::XML,
    ];

    const DEFAULT_TYPE = 'application/json';

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

    /**
     * Get the best accepted serialization format.
     *
     * If a compatible one is not provided than the default one is returned.
     *
     * @return string the accepted content type OR the default one
     */
    public function getRequestAcceptedType() : string
    {
        $candidates = $this->getRequest()->getHeader('Accepted');

        foreach ($candidates as $candidate) {
            $contentTypeParts = preg_split('/\s*[;,]\s*/', $candidate);
            $candidateMimeType = strtolower($contentTypeParts[0]);

            if (array_key_exists($candidateMimeType, self::ALLOWED_TYPES)) {
                return $candidateMimeType;
            }
        }

        return self::DEFAULT_TYPE;
    }

    /**
     * Set the used serialization format.
     *
     * This function should be called only once by setResponseSerialized().
     *
     * @param string $type the used serialization format
     */
    public function setResponseContentType($type)
    {
        $this->response = $this->getResponse()->withHeader('Content-Type', $type);
    }

    /**
     * Serialize given data using the best suitable serialization format.
     *
     * Write the result to the response body and place the format into the
     * Content-Type HTTP header.
     *
     * @param SerializableCollection $data the data to be serialized
     */
    public function setResponseSerialized(SerializableCollection $data)
    {
        //get a valid accepted format
        $encodedType = $this->getRequestAcceptedType();

        //get the serialization format
        $format = self::ALLOWED_TYPES[$encodedType];

        //serialize and send response....
        $this->getResponse()->getBody()->write(
            $data->serialize($format)
        );

        //...telling the client bout the used serialization format
        $this->setResponseContentType($encodedType);
    }
}
