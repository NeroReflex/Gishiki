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

use Gishiki\Algorithms\Collections\DeserializationException;
use Gishiki\Core\MVC\Controller\Plugin;
use Gishiki\Algorithms\Collections\SerializableCollection;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * This is a plugin used to parse data transported by the HTTP request.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class RequestDeserializer extends Plugin
{
    /**
     * List of request body parsers (e.g., url-encoded, JSON, XML, multipart).
     *
     * @var \Callable[]
     */
    protected $bodyParsers = [];

    /**
     * Register media type parser.
     *
     * @param string[] $mediaTypes A HTTP media type (excluding content-type
     *                             params)
     * @param callable $callable   A callable that returns parsed contents for
     *                             media type
     */
    public function registerMediaTypeParser(array $mediaTypes, callable $callable)
    {
        if ($callable instanceof \Closure) {
            $callable = $callable->bindTo($this);
        }

        foreach ($mediaTypes as $mediaType) {
            $this->bodyParsers[(string)$mediaType] = &$callable;
        }
    }

    /**
     * Deserializer constructor:
     * setup the middleware importing deserializers
     *
     * @param RequestInterface  $request  the HTTP request
     * @param ResponseInterface $response the HTTP response
     */
    public function __construct(RequestInterface &$request, ResponseInterface &$response)
    {
        //this is important, NEVER forget!
        parent::__construct($request, $response);

        $this->registerMediaTypeParser([
            'text/yaml',
            'text/x-yaml',
            'application/yaml',
            'application/x-yaml',
        ], function ($input) : SerializableCollection {
            return SerializableCollection::deserialize($input, SerializableCollection::YAML);
        });

        $this->registerMediaTypeParser([
            'application/json',
            'text/json',
        ], function ($input) : SerializableCollection {
            return SerializableCollection::deserialize($input, SerializableCollection::JSON);
        });

        $this->registerMediaTypeParser([
            'text/xml',
            'application/xml',
        ], function ($input) : SerializableCollection {
            return SerializableCollection::deserialize($input, SerializableCollection::XML);
        });

        $this->registerMediaTypeParser([
            'application/x-www-form-urlencoded',
            'multipart/form-data',
        ], function ($input) : SerializableCollection {
            $data = [];
            parse_str($input, $data);
            return new SerializableCollection($data);
        });
    }

    /**
     * Get request content type.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string|null The request content type, if known
     */
    public function getRequestContentType()
    {
        $result = $this->getRequest()->getHeader('Content-Type');
        return $result ? $result[0] : null;
    }

    /**
     * Get request media type, if known.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string|null The request media type, minus content-type params
     */
    public function getRequestMediaType()
    {
        $contentType = $this->getRequestContentType();
        if ($contentType) {
            $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);
            return strtolower($contentTypeParts[0]);
        }

        return null;
    }

    /**
     * Retrieve any parameters provided in the request body.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, this method MUST
     * return the contents of $_POST.
     *
     * Otherwise, this method may return any results of deserializing
     * the request body content.
     *
     * @return SerializableCollection The deserialized body parameters, if any.
     *                                These will typically be an array or object
     *
     * @throws DeserializationException if the request body is invalid
     */
    public function getRequestDeserialized() : SerializableCollection
    {
        $body = (string)$this->getRequest()->getBody();
        $mediaType = $this->getRequestMediaType();

        $bodyParsed = null;

        if ((strlen($mediaType) > 0) && (array_key_exists($mediaType, $this->bodyParsers))) {
            $bodyParsed = $this->bodyParsers[$mediaType]($body);
        }

        if (!($bodyParsed instanceof SerializableCollection)) {
            throw new DeserializationException("Malformed data", 100);
        }

        return $bodyParsed;
    }
}