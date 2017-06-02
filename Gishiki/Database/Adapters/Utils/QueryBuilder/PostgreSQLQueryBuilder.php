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

namespace Gishiki\Database\Adapters\Utils\QueryBuilder;

use Gishiki\Database\Adapters\Utils\SQLGenerator\PostgreSQLWrapper;

/**
 * Uses SQL generators to generate valid SQL queries for PostgreSQL.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class PostgreSQLQueryBuilder extends SQLQueryBuilder
{
    /**
     * @return PostgreSQLWrapper the SQLite specialized query builder
     */
    protected function getQueryBuilder()
    {
        return new PostgreSQLWrapper();
    }

    public function insertQuery($collection, array $adaptedData)
    {
        $returning = 'id';
        $returning =  (in_array('rowid', array_keys($adaptedData))) ? 'rowid' : $returning;
        $returning =  (in_array('_rowid', array_keys($adaptedData))) ? '_rowid' : $returning;
        $returning =  (in_array('id', array_keys($adaptedData))) ? 'id' : $returning;
        $returning =  (in_array('ID', array_keys($adaptedData))) ? 'ID' : $returning;
        $returning =  (in_array('_id', array_keys($adaptedData))) ? '_id' : $returning;
        $returning =  (in_array($collection.'_id', array_keys($adaptedData))) ? $collection.'_id' : $returning;

        //build the sql query
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->insertInto($collection)->values($adaptedData)->returning($returning);

        return $queryBuilder;
    }
}