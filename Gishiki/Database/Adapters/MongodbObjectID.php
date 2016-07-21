<?php
/**************************************************************************
Copyright 2016 Benato Denis

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

namespace Gishiki\Database\Adapters;

use Gishiki\Database\ObjectIDInterface;

/**
 * Represent the a mongodb unique ID of a document.
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class MongodbObjectID implements ObjectIDInterface
{
    /**
     * @var string the string representation of the objectID of a document
     */
    private $oid = '';

    public function __construct($native)
    {
        //check for objectID validity
        if ((!($native instanceof \MongoDB\BSON\ObjectID)) && (!is_string($native))) {
            throw new \InvalidArgumentException('The given object id is not valid');
        }

        //store the objectID as a string
        $this->oid = ''.$native;
    }

    public function Valid()
    {
        return strlen($this->oid) > 0;
    }
}
