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

namespace Gishiki\Database\Adapters;

use Gishiki\Database\Adapters\Utils\QueryBuilder\PostgreSQLQueryBuilder;
use Gishiki\Database\RelationalDatabaseInterface;
use Gishiki\Database\Adapters\Utils\PDODatabase;

/**
 * Represent a PostgreSQL database.
 *
 * The documentation is available on the implemented interfaces (look for see also).
 *
 * @see RelationalDatabaseInterface Documentation.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class Pgsql implements RelationalDatabaseInterface
{
    use PDODatabase;

    protected function getPDODriverName() : string
    {
        return 'pgsql';
    }

    /**
     * Get the query builder for PostgreSQL.
     *
     * @return PostgreSQLQueryBuilder the query builder for the used pdo adapter
     */
    protected function getQueryBuilder() : PostgreSQLQueryBuilder
    {
        return new PostgreSQLQueryBuilder();
    }
}
