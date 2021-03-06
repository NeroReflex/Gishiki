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

use Gishiki\Database\Runtime\SelectionCriteria;
use Gishiki\Database\Runtime\ResultModifier;
use Gishiki\Database\Schema\Table;

/**
 * Represents how a SQL query builder must be implemented when designing a new database connector.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
interface SQLQueryBuilderInterface
{
    public function createTableQuery(Table $table);

    public function insertQuery($collection, array $adaptedData);

    public function updateQuery($collection, array $adaptedData, SelectionCriteria $where);

    public function deleteQuery($collection, SelectionCriteria $where);

    public function deleteAllQuery($collection);

    public function readQuery($collection, SelectionCriteria $where, ResultModifier $mod);

    public function selectiveReadQuery($collection, $fields, SelectionCriteria $where, ResultModifier $mod);
}
