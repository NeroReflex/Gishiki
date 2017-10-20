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

/**
* Class PgsqlConnectionParser
*
* @package Gishiki\Database\Adapters\Utils\ConnectionParser
*/
final class SqliteConnectionParser implements ConnectionParserInterface
{
    protected $file;

    protected function getPDODriverName() : string
    {
        return 'sqlite';
    }

    public function parse($connection)
    {
        $this->file = $connection;
    }

    public function getPDOConnection()
    {
        $query = $this->getPDODriverName() . ':' . $this->file;
        return [
            $query,
            null,
            null,
            null
        ];
    }
}
