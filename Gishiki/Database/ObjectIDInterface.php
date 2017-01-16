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

namespace Gishiki\Database;

/**
 * Represent the abstraction of the unique ID of a document/row.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
interface ObjectIDInterface
{
    /**
     * Create the Object ID representation fron a driver-native representation.
     *
     * @param mixed $native the object id in a native format
     *
     * @throws \InvalidArgumentException the object is is not valid
     */
    public function __construct($native, $collection);

    /**
     * Check if the current Object ID is valid.
     *
     * @return bool TRUE only if the object ID is valid
     */
    public function valid();

    /**
     * Get the name of th collection containint the  key.
     *
     * @return string the name of the collection
     */
    public function getTableName();

    /**
     * Export the object ID in a driver-native format.
     *
     * @return mixed the native object ID
     */
    public function export();

    /**
     * Fetch the not-native (string encoded) representation of the
     * currently managed Object ID.
     *
     * @return string the string representation of the object ID
     */
    public function __toString();
}
