<?php
/**************************************************************************
Copyright 2016 Benato Denis

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

    private static $adaptersMap = [
        'Mongo' => 'Mongodb',
    ];

    /**
     * Create a new database handler and connect it to a read database.
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

        /*
        //split user:pass from host:port/dbname
        $temp = explode('@', $temp[1]);
        $userPass = explode(':', $temp[0]);
        $temp = explode('/', $temp[1]);
        $hostPort = explode(':', $temp[0]);

        //generate the connection array
        $connectionDetails = [
            'host' => $hostPort[0],
            'port' => $hostPort[1],
            'database' => $temp[1],
            'username' => $userPass[0],
            'password' => $userPass[1],
        ];
        */

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
}
