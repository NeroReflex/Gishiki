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

namespace Gishiki\Database\Adapters\Utils\ConnectionParser;

use Gishiki\Database\Adapters\ConnectionParser\ConnectionParserException;

/**
 * Describe the implementation of a connection string parser.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
interface ConnectionParserInterface
{
    /**
     * Parse the given connection string.
     *
     * The given connection string cannot contains dbtype://,
     * but can either be in standard or PDO format.
     *
     * @param  string $connection the connection string
     * @throws ConnectionParserException the error preventing parsing
     * @throws \InvalidArgumentException the connection parameter is not a valid string
     */
    public function parse($connection);

    /**
     * Get the list of arguments to be passed to the PDO constructor in order
     * to connect the specified database.
     *
     * @return array the list of arguments to be passed to the pdo driver
     */
    public function getPDOConnection();
}
