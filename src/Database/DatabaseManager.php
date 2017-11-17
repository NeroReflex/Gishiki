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

use Gishiki\Database\Adapters\Sqlite;
use Gishiki\Database\Adapters\Pgsql;
use Gishiki\Database\Adapters\Mysql;
use Gishiki\Database\Adapters\Utils\ConnectionParser\ConnectionParserException;

/**
 * Represent the database manager of the entire framework.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class DatabaseManager
{
    //used to give a second name to an adapter
    const ADAPTERS_MAP = [
        'sqlite' => Sqlite::class,
        'sqlite3' => Sqlite::class,
        'pgsql' => Pgsql::class,
        'postgres' => Pgsql::class,
        'postgre' => Pgsql::class,
        'mysql' => Mysql::class,
        'mariadb' => Mysql::class,
    ];

    /**
     * @var array the list of database connections as an associative array
     */
    protected $connections = [];

    /**
     * Create a new database connection and store the newly generated connection.
     *
     * @param string $connectionName     the name of the database connection
     * @param string $connectionString   the connection string
     * @throws \InvalidArgumentException invalid name or connection string
     * @throws DatabaseException         the error occurred while opening the database connection
     * @throws ConnectionParserException a database adapter with the given name doesn't exists
     * @return DatabaseInterface         the connected database instance
     */
    public function connect($connectionName, $connectionString)
    {
        //check for malformed input
        if ((!is_string($connectionName)) || (strlen($connectionName) <= 0) || (!is_string($connectionString)) || (strlen($connectionString) <= 0)) {
            throw new \InvalidArgumentException('The connection name and the connection details must be given as two string');
        }

        //get the adapter name
        $temp = explode('://', $connectionString);
        $adapterTemp = strtolower($temp[0]);

        if (!array_key_exists($adapterTemp, self::ADAPTERS_MAP)) {
            throw new ConnectionParserException("The given database type is not valid or not supported.", 40);
        }

        //get the adapter and the connection query
        $adapter = $temp[0];
        $connectionQuery = $temp[1];

        //reflect the adapter
        $reflectedAdapter = new \ReflectionClass(self::ADAPTERS_MAP[$adapter]);

        //and use the adapter to establish the database connection and return the connection handler
        $this->connections[sha1($connectionName)] = $reflectedAdapter->newInstance($connectionQuery);

        return $this->connections[sha1($connectionName)];
    }

    /**
     * Retrieve the connection with the given name from the list of performed conenctions.
     * If the name is not specified the default one is retrieved.
     *
     * @param string $connectionName the name of the selected connection
     *
     * @return DatabaseInterface the connected database instance
     *
     * @throws \InvalidArgumentException the collection name has not be given as a string
     * @throws DatabaseException         the given connection name is not registered as a valid collection
     */
    public function retrieve($connectionName = 'default') : DatabaseInterface
    {
        //check for malformed input
        if ((!is_string($connectionName)) || (strlen($connectionName) <= 0)) {
            throw new \InvalidArgumentException('The name of the connection to be retrieved must be given as a string');
        }

        //check if the connection was established
        if (!array_key_exists(sha1($connectionName), $this->connections)) {
            throw new DatabaseException("The given connection doesn't exists", 1);
        }

        //return the establish connection
        return $this->connections[sha1($connectionName)];
    }
}
