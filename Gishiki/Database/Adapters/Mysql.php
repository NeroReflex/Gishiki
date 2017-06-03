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

use Gishiki\Database\Adapters\Utils\QueryBuilder\MySQLQueryBuilder;


/**
 * Represent a MySQL database.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class Mysql extends PDODatabase
{
    /**
     * {@inheritdoc}
     */
    protected function getPDODriverName()
    {
        return 'mysql';
    }

    protected function generateConnectionQuery($details)
    {
        if (!is_string($details)) {
            throw new \InvalidArgumentException("connection information provided are invalid");
        }

        $user = null;
        $password = null;

        $userPosition = strpos($details, 'user=');
        if ($userPosition !== false) {
            $firstUserCharPosition = $userPosition + strlen('user=');
            $endingUserCharPosition = strpos($details, ';', $firstUserCharPosition);
            $lasUserCharPosition = ($endingUserCharPosition !== false) ?
                $endingUserCharPosition : strlen($details);
            $user = substr($details, $firstUserCharPosition, $lasUserCharPosition - $firstUserCharPosition);
            $details = ($endingUserCharPosition !== false) ?
                str_replace('user=' . $user . ';', '', $details) :
                str_replace('user=' . $user, '', $details);
        }

        $passwordPosition = strpos($details, 'password=');
        if ($passwordPosition !== false) {
            $firstPassCharPosition = $passwordPosition + strlen('password=');
            $endingPassCharPosition = strpos($details, ';', $firstPassCharPosition);
            $lasPassCharPosition = ($endingPassCharPosition !== false) ?
                $endingPassCharPosition : strlen($details);
            $password = substr($details, $firstPassCharPosition, $lasPassCharPosition - $firstPassCharPosition);
            $details = ($endingPassCharPosition !== false) ?
                str_replace('password=' . $password . ';', '', $details) :
                str_replace('password=' . $password, '', $details);
        }

        return [
            $this->getPDODriverName().':'.$details,
            $user,
            $password,
            null
        ];
    }

    /**
     * Get the query builder for SQLite.
     *
     * @return SQLiteQueryBuilder the query builder for the used pdo adapter
     */
    protected function getQueryBuilder()
    {
        return new MySQLQueryBuilder();
    }

}