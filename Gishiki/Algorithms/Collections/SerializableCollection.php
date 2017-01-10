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

namespace Gishiki\Algorithms\Collections;

/**
 * The structured data management class.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class SerializableCollection extends GenericCollection
{
    /********************************************
     *               Serializators              *
     ********************************************/
    const JSON = 0;
    const XML = 1;
    const YAML = 2;

    /**
     * Create serializable data collection from the given array.
     *
     * @param array $data the collection of properties
     *
     * @throws \InvalidArgumentException an invalid collection was given
     */
    public function __construct($data = array())
    {
        if (is_array($data)) {
            parent::__construct($data);
        } elseif ($data instanceof \Gishiki\Algorithms\Collections\CollectionInterface) {
            $this->data = $data->all();
        }
    }

    /**
     * Serialize the current data collection.
     *
     * @param int $format an integer representing one of the allowed formats
     * @throw  SerializationException         the error occurred while serializing the collection in json format
     *
     * @return string the collection serialized
     */
    public function serialize($format = self::JSON)
    {
        $result = '';
        switch ($format) {

            case self::JSON:
                //try json encoding
                $result = json_encode($this->all(), JSON_PRETTY_PRINT);

                //and check for the result
                if (json_last_error() != JSON_ERROR_NONE) {
                    throw new SerializationException('The given data cannot be serialized in JSON content', 2);
                }
                break;

            case self::XML:
                $xml = new \Gishiki\Algorithms\XmlDomConstructor('1.0', 'utf-8');
                $xml->xmlStandalone = true;
                $xml->formatOutput = true;
                $xml->fromMixed($this->all());
                $xml->normalizeDocument();
                $result = str_replace('standalone="yes"?>', 'standalone="yes"?><data>', $xml->saveXML());
                $result .= "\n</data>";
                break;

            case self::YAML:
                $result = \Symfony\Component\Yaml\Yaml::dump($this->all());
                break;

            default:
                throw new SerializationException('Invalid serialization format selected', 7);
        }

        return $result;
    }

    /**
     * Deserialize the given data collectionand create a serializable data collection.
     *
     * @param string|array|CollectionInterface $message the string containing the serialized data or the array of data
     * @param int                              $format  an integer representing one of the allowed formats
     *
     * @return SerializableCollection the deserialization result
     *
     * @throws DeserializationException the error preventing the data deserialization
     */
    public static function deserialize($message, $format = self::JSON)
    {
        if ((is_array($message)) || ($message instanceof CollectionInterface)) {
            return new self($message);
        } elseif ($format === self::JSON) {
            if (!is_string($message)) {
                throw new DeserializationException('The given content is not a valid JSON content', 3);
            }

            //try decoding the string
            $nativeSerialization = json_decode($message, true, 512);

            //and check for the result
            if (json_last_error() != JSON_ERROR_NONE) {
                throw new DeserializationException('The given string is not a valid JSON content', 1);
            }

            //the deserialization result MUST be an array
            $serializationResult = (is_array($nativeSerialization)) ? $nativeSerialization : [];

            //return the deserialization result if everything went right
            return new self($serializationResult);
        } elseif ($format === self::XML) {
            if (!is_string($message)) {
                throw new DeserializationException('The given content is not a valid XML content', 3);
            }

            //resolve CDATA
            $messageParsed = preg_replace_callback('/<!\[CDATA\[(.*)\]\]>/', function ($matches) {
                return trim(htmlspecialchars($matches[1]));
            }, $message);

            //load the xml from the cleaned string
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($messageParsed);

            if ((count(libxml_get_errors()) > 0)/* || (!$xml)*/) {
                //clear the error list to avoid interferences
                libxml_clear_errors();

                throw new DeserializationException('The given content is not a valid XML content', 3);
            }

            //use the json engine to deserialize the object
            $string = json_encode($xml);
            $nativeSerialization = json_decode($string, true);

            //return the deserialization result
            return new self($nativeSerialization);
        } elseif ($format === self::YAML) {
            if (!is_string($message)) {
                throw new DeserializationException('The given content is not a valid YAML content', 8);
            }

            //attempt to deserialize the yaml file
            $nativeSerialization = null;
            try {
                $nativeSerialization = \Symfony\Component\Yaml\Yaml::parse($message, true, true);
            } catch (\Symfony\Component\Yaml\Exception\ParseException $ex) {
                throw new DeserializationException('The given YAML content cannot be deserialized', 9);
            }

            //check for the result type
            if (!is_array($nativeSerialization)) {
                throw new DeserializationException('The YAML deserialization result cannot be used to build a collection', 10);
            }

            //return the deserialization result
            return new self($nativeSerialization);
        }

        //impossible to serialize the message
        throw new DeserializationException('It is impossible to deserialize the given message', 2);
    }

    /**
     * Get the serialization result using the default format.
     *
     * @return string the serialization result
     */
    public function __toString()
    {
        //use the default serializator
        return $this->serialize();
    }
}
