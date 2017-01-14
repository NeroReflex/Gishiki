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
 * Represent the database manager of the entire framework.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class DatabaseManager
{
    private static $connections = array();

    //used to give a sencond name to an adapter
    private static $adaptersMap = [
        // 'Firstname' => 'Secondname'
    ];

    /**
     * Create a new database connection and store the newly generated connection.
     *
     * @param string $connectionName   the name of the database connection
     * @param string $connectionString the connection string
     *
     * @throws \InvalidArgumentException invalid name or connection string
     *
     * @return DatabaseInterface the connected database instance
     */
    public static function Connect($connectionName, $connectionString)
    {
        //check for malformed input
        if ((!is_string($connectionName)) || (strlen($connectionName) <= 0) || (!is_string($connectionString)) || (strlen($connectionString) <= 0)) {
            throw new \InvalidArgumentException('The connection name and the connection details must be given as two string');
        }

        //get the adapter name
        $temp = explode('://', $connectionString);
        $adapterTemp = ucfirst($temp[0]);
        $adapter = (array_key_exists($adapterTemp, self::$adaptersMap)) ?
            self::$adaptersMap[$adapter] : $adapterTemp;
        $connectionQuery = $temp[1];

        try {
            //reflect the adapter
            $reflectedAdapter = new \ReflectionClass('Gishiki\\Database\\Adapters\\'.$adapter);

            //and use the adapter to enstabilish the database connection and return the connection handler
            self::$connections[$connectionName] = $reflectedAdapter->newInstance($connectionQuery);

            return self::$connections[$connectionName];
        } catch (\ReflectionException $ex) {
            throw new DatabaseException('The given connection query requires an nonexistent adapter', 0);
        }
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
    public static function Retrieve($connectionName = "default")
    {
        //check for malformed input
        if ((!is_string($connectionName)) || (strlen($connectionName) <= 0)) {
            throw new \InvalidArgumentException('The name of the connection to be retrieved must be given as a string');
        }

        //check if the connection was enstabilished
        if (!array_key_exists($connectionName, self::$connections)) {
            throw new DatabaseException("The given connection doesn't exists", 1);
        }

        //return the enstabilished conenction
        return self::$connections[$connectionName];
    }
}
