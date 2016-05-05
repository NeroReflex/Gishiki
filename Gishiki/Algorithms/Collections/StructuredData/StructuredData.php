<?php
/**************************************************************************
Copyright 2015 Benato Denis

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

namespace Gishiki\Algorithms\Collections\StructuredData;

use Gishiki\Algorithms\Collections\GenericCollection;

/**
 * The structured data management class.
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class StructuredData extends GenericCollection
{
    protected static function serializeValue($data) {
        $result = null;
        switch (gettype($data)) {
            case "array":
                $result = new StructuredData($data);
                break;
                
            default:
                $result = $data;
                break;
        }
        return $result;
    }
    
    /**
     * Create structured data collection from the given array.
     * 
     * @param  array                     $data the collection of properties
     * @throws \InvalidArgumentException       an invalid collection was given
     */
    public function __construct($data = array())
    {
        parent::__construct($data);
    }
    
    public function set($key, $value) {
        parent::set($key, static::serializeValue($value));
    }
    
}