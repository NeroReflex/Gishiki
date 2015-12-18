<?php
/****************************************************************************
  Copyright 2015 Benato Denis

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

namespace Gishiki\Database {

    /**
     * Parser of connection strings
     *
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    abstract class ConnectionString {

        /**
         * Create an array of connection details that can be used by the 
         * DatabaseConnector abstract class
         * 
         * @param string $connectionString the connection string
         * @return array the connection details extracted from the given connection string
         * @throws DatabaseException the exception occurred while opening the database
         */
        static function Parse($connectionString) : array {
            //explode the string at the first ':' to get the database type
            $firstExplosion = explode("://", $connectionString, 2);

            if ((strtolower($firstExplosion[0]) == "mongodb") || (strtolower($firstExplosion[0]) == "mongo")) {
                return ConnectionString::ParseMongoDBConnectionString($firstExplosion[1]);
            } else if ((strtolower($firstExplosion[0]) == "sqlite") || ((strtolower($firstExplosion[0]) == "sqlite3"))) {
                $connectionDetails = ConnectionString::ParseSQLiteConnectionString($firstExplosion[1]);

                return array(
                    'database_type' => 'sqlite',
                    'database_file' => $connectionDetails["dbName"]
                );
            } else if ((strtolower($firstExplosion[0]) == "mysql") || (strtolower($firstExplosion[0]) == "mariadb") || (strtolower($firstExplosion[0]) == "maria")) {
                $connectionDetails = ConnectionString::ParseMySQLConnectionString($firstExplosion[1]);

                return array(
                    'database_type' => "mysql",
                    'database_name' => $connectionDetails["dbName"],
                    'server' => $connectionDetails["dbHost"],
                    'port' => intval($connectionDetails["dbHostPort"]),
                    'username' => $connectionDetails["userName"],
                    'password' => $connectionDetails["userPassword"],
                    'charset' => 'utf8',
                );
            } else if (strtolower($firstExplosion[0]) == "pgsql") {
                $connectionDetails = ConnectionString::ParsePostgreSQLConnectionString($firstExplosion[1]);

                return array(
                    'database_type' => "pgsql",
                    'database_name' => $connectionDetails["dbName"],
                    'server' => $connectionDetails["dbHost"],
                    'port' => intval($connectionDetails["dbHostPort"]),
                    'username' => $connectionDetails["userName"],
                    'password' => $connectionDetails["userPassword"],
                    'charset' => 'utf8',
                );
            } else if (strtolower($firstExplosion[0]) == "sqlsrv") {
                $connectionDetails = ConnectionString::ParseMicrosoftSQLConnectionString($firstExplosion[1]);

                return array(
                    'database_type' => "mssql",
                    'database_name' => $connectionDetails["dbName"],
                    'server' => $connectionDetails["dbHost"],
                    'port' => intval($connectionDetails["dbHostPort"]),
                    'username' => $connectionDetails["userName"],
                    'password' => $connectionDetails["userPassword"],
                    'charset' => 'utf8',
                );
            } else if (strtolower($firstExplosion[0]) == "oracle") {
                $connectionDetails = ConnectionString::ParseOracleConnectionString($firstExplosion[1]);

                return array(
                    'database_type' => "oracle",
                    'database_name' => $connectionDetails["dbName"],
                    'server' => $connectionDetails["dbHost"],
                    'port' => intval($connectionDetails["dbHostPort"]),
                    'username' => $connectionDetails["userName"],
                    'password' => $connectionDetails["userPassword"],
                    'charset' => 'utf8',
                );
            } else if (strtolower($firstExplosion[0]) == "sybase") {
                $connectionDetails = ConnectionString::ParseSybaseConnectionString($firstExplosion[1]);

                return array(
                    'database_type' => "sybase",
                    'database_name' => $connectionDetails["dbName"],
                    'server' => $connectionDetails["dbHost"],
                    'port' => intval($connectionDetails["dbHostPort"]),
                    'username' => $connectionDetails["userName"],
                    'password' => $connectionDetails["userPassword"],
                    'charset' => 'utf8',
                );
            } else {
                throw new DatabaseException("Unrecognized database type, check the connection string", 1);
            }
        }

        /**
         * extract a SQLite v3 connection details
         *
         * @param string $connString the connection string without the database type
         * @return array the connection details extracted from the given connection string
         */
        static function ParseSQLiteConnectionString($connString) : array
        {
            //start building the connection details array used for sonnection
            //and for a possible connection save
            $connectionDetails = [
                "dbName" => "",
            ];

            //split the database name and password (formatted as: "dbname:dbpass")
            $databaseConnectionString = str_split($connString);
            //into an array of characters

            //get the database name and password
            $length = count($databaseConnectionString);
            for ($i = 0; $i < $length; $i++) {
                $connectionDetails["dbName"] = $connectionDetails["dbName"].$databaseConnectionString[$i];
            }

            //if the database name was not given, than an in-memory database was requested
            if ($connectionDetails["dbName"] == "") {
                $connectionDetails["dbName"] = ":memory:";
            }

            //return connection details
            return $connectionDetails;
        }

        /**
         * Extract a MySQL connection details
         * 
         * @param string $connString the connection string without the database type
         * @return array the connection details extracted from the given connection string
         */
        static function ParseMySQLConnectionString($connString) : array
        {
            /* what part of the connection string is currently being read?
             * 0 = db name
             * 1 = db host
             * 2 = host port
             * 3 = user username
             * 4 = user password
             */
            $reading = 0;

            //start building the connection details array used for sonnection and for
            //a possible connection save
            $connectionDetails = [
                "dbName" => "",
                "dbHost" => "",
                "dbHostPort" => "",
                "userName" => "",
                "userPassword" => "",
            ];

            //split the database connection string
            $databaseConnectionString = str_split($connString);
            //into an array of characters

            //get the database connection info
            $length = count($databaseConnectionString);
            for ($i = 0; $i < $length; $i++) {
                switch ($reading) {
                    case 0:
                        if ($databaseConnectionString[$i] != '@') {
                            $connectionDetails["dbName"] = $connectionDetails["dbName"] . $databaseConnectionString[$i];
                        } else {
                            $reading += 1;
                        }
                        break;
                    case 1:
                        if (($databaseConnectionString[$i] != ':') && ($databaseConnectionString[$i] != '/')) {
                            $connectionDetails["dbHost"] = $connectionDetails["dbHost"] . $databaseConnectionString[$i];
                        } else if ($databaseConnectionString[$i] == '/') {
                            $reading += 2;
                        } else if ($databaseConnectionString[$i] == ':') {
                            $reading += 1;
                        }
                        break;
                    case 2:
                        if ($databaseConnectionString[$i] != '/') {
                            $connectionDetails["dbHostPort"] = $connectionDetails["dbHostPort"] . $databaseConnectionString[$i];
                        } else {
                            $reading += 1;
                        }
                        break;
                    case 3:
                        if ($databaseConnectionString[$i] != ':') {
                            $connectionDetails["userName"] = $connectionDetails["userName"] . $databaseConnectionString[$i];
                        } else {
                            $reading += 1;
                        }
                        break;
                    default: //reading password
                        $connectionDetails["userPassword"] = $connectionDetails["userPassword"] . $databaseConnectionString[$i];
                        break;
                }
            }

            //return connection details
            return $connectionDetails;
        }
        
        /**
         * Extract a MongoDB connection details
         * 
         * @param string $connString the connection string without the database type
         * @return array the connection details extracted from the given connection string
         */
        static function ParseMongoDBConnectionString($connString) : array
        {
            /* what part of the connection string is currently being read?
             * 0 = db name
             * 1 = db host
             * 2 = host port
             * 3 = user username
             * 4 = user password
             */
            $reading = 0;

            //start building the connection details array used for sonnection and for
            //a possible connection save
            $connectionDetails = [
                "database_type" => "mongo",
                "dbName" => "",
                "dbHost" => "",
                "dbHostPort" => "",
                "userName" => "",
                "userPassword" => "",
            ];

            //split the database connection string
            $databaseConnectionString = str_split($connString);
            //into an array of characters

            //get the database connection info
            $length = count($databaseConnectionString);
            for ($i = 0; $i < $length; $i++) {
                switch ($reading) {
                    case 0:
                        if ($databaseConnectionString[$i] != '@') {
                            $connectionDetails["dbName"] = $connectionDetails["dbName"].$databaseConnectionString[$i];
                        } else {
                            $reading+=1;
                        }
                        break;
                    case 1:
                        if (($databaseConnectionString[$i] != ':') && ($databaseConnectionString[$i] != '/')) {
                            $connectionDetails["dbHost"] = $connectionDetails["dbHost"].$databaseConnectionString[$i];
                        } else if ($databaseConnectionString[$i] == '/') {
                            $reading+=2;
                        } else if ($databaseConnectionString[$i] == ':') {
                            $reading+=1;
                        }
                        break;
                    case 2:
                        if ($databaseConnectionString[$i] != '/') {
                            $connectionDetails["dbHostPort"] = $connectionDetails["dbHostPort"].$databaseConnectionString[$i];
                        } else {
                            $reading+=1;
                        }
                        break;
                    case 3:
                        if ($databaseConnectionString[$i] != ':') {
                            $connectionDetails["userName"] = $connectionDetails["userName"].$databaseConnectionString[$i];
                        } else {
                            $reading+=1;
                        }
                        break;
                    default: //reading password
                        $connectionDetails["userPassword"] = $connectionDetails["userPassword"].$databaseConnectionString[$i];
                        break;
                }
            }

            //return connection details
            return $connectionDetails;
        }

        /**
         * Extract a PostgreSQL connection details
         *
         * @param string $connString the connection string without the database type
         * @return array the connection details extracted from the given connection string
         */
        static function ParsePostgreSQLConnectionString($connString) : array
        {
            /* what part of the connection string is currently being read?
             * 0 = db name
             * 1 = db host
             * 2 = host port
             * 3 = user username
             * 4 = user password
             */
            $reading = 0;

            //start building the connection details array used for sonnection and for
            //a possible connection save
            $connectionDetails = [
                "dbName" => "",
                "dbHost" => "",
                "dbHostPort" => "",
                "userName" => "",
                "userPassword" => "",
            ];

            //split the database connection string
            $databaseConnectionString = str_split($connString);
            //into an array of characters

            //get the database connection info
            $length = count($databaseConnectionString);
            for ($i = 0; $i < $length; $i++) {
                switch ($reading) {
                    case 0:
                        if ($databaseConnectionString[$i] != '@') {
                            $connectionDetails["dbName"] = $connectionDetails["dbName"].$databaseConnectionString[$i];
                        } else {
                            $reading+=1;
                        }
                        break;
                    case 1:
                        if (($databaseConnectionString[$i] != ':') && ($databaseConnectionString[$i] != '/')) {
                            $connectionDetails["dbHost"] = $connectionDetails["dbHost"].$databaseConnectionString[$i];
                        } else if ($databaseConnectionString[$i] == '/') {
                            $reading+=2;
                        } else if ($databaseConnectionString[$i] == ':') {
                            $reading+=1;
                        }
                        break;
                    case 2:
                        if ($databaseConnectionString[$i] != '/') {
                            $connectionDetails["dbHostPort"] = $connectionDetails["dbHostPort"].$databaseConnectionString[$i];
                        } else {
                            $reading+=1;
                        }
                        break;
                    case 3:
                        if ($databaseConnectionString[$i] != ':') {
                            $connectionDetails["userName"] = $connectionDetails["userName"].$databaseConnectionString[$i];
                        } else {
                            $reading+=1;
                        }
                        break;
                    default: //reading password
                        $connectionDetails["userPassword"] = $connectionDetails["userPassword"].$databaseConnectionString[$i];
                        break;
                }
            }

            //return connection details
            return $connectionDetails;
        }

        /**
         * Extract a MicrosoftSQL connection details
         *
         * @param string $connString the connection string without the database type
         * @return array the connection details extracted from the given connection string
         */
        static function ParseMicrosoftSQLConnectionString($connString)
        {
            /* what part of the connection string is currently being read?
             * 0 = db name
             * 1 = db host
             * 2 = host port
             * 3 = user username
             * 4 = user password
             */
            $reading = 0;

            //start building the connection details array used for sonnection and for
            //a possible connection save
            $connectionDetails = [
                "dbName" => "",
                "dbHost" => "",
                "dbHostPort" => "",
                "userName" => "",
                "userPassword" => "",
            ];

            //split the database connection string
            $databaseConnectionString = str_split($connString);
            //into an array of characters

            //get the database connection info
            $length = count($databaseConnectionString);
            for ($i = 0; $i < $length; $i++) {
                switch ($reading) {
                    case 0:
                        if ($databaseConnectionString[$i] != '@') {
                            $connectionDetails["dbName"] = $connectionDetails["dbName"].$databaseConnectionString[$i];
                        } else {
                            $reading+=1;
                        }
                        break;
                    case 1:
                        if (($databaseConnectionString[$i] != ':') && ($databaseConnectionString[$i] != '/')) {
                            $connectionDetails["dbHost"] = $connectionDetails["dbHost"].$databaseConnectionString[$i];
                        } else if ($databaseConnectionString[$i] == '/') {
                            $reading+=2;
                        } else if ($databaseConnectionString[$i] == ':') {
                            $reading+=1;
                        }
                        break;
                    case 2:
                        if ($databaseConnectionString[$i] != '/') {
                            $connectionDetails["dbHostPort"] = $connectionDetails["dbHostPort"].$databaseConnectionString[$i];
                        } else {
                            $reading+=1;
                        }
                        break;
                    case 3:
                        if ($databaseConnectionString[$i] != ':') {
                            $connectionDetails["userName"] = $connectionDetails["userName"].$databaseConnectionString[$i];
                        } else {
                            $reading+=1;
                        }
                        break;
                    default: //reading password
                        $connectionDetails["userPassword"] = $connectionDetails["userPassword"].$databaseConnectionString[$i];
                        break;
                }
            }

            //return connection details
            return $connectionDetails;
        }

        /**
         * Extract a Oracle connection details
         *
         * @param string $connString the connection string without the database type
         * @return array the connection details extracted from the given connection string
         */
        static function ParseOracleConnectionString($connString)
        {
            /* what part of the connection string is currently being read?
             * 0 = db name
             * 1 = db host
             * 2 = host port
             * 3 = user username
             * 4 = user password
             */
            $reading = 0;

            //start building the connection details array used for sonnection and for
            //a possible connection save
            $connectionDetails = [
                "dbName" => "",
                "dbHost" => "",
                "dbHostPort" => "",
                "userName" => "",
                "userPassword" => "",
            ];

            //split the database connection string
            $databaseConnectionString = str_split($connString);
            //into an array of characters

            //get the database connection info
            $length = count($databaseConnectionString);
            for ($i = 0; $i < $length; $i++) {
                switch ($reading) {
                    case 0:
                        if ($databaseConnectionString[$i] != '@') {
                            $connectionDetails["dbName"] = $connectionDetails["dbName"].$databaseConnectionString[$i];
                        } else {
                            $reading+=1;
                        }
                        break;
                    case 1:
                        if (($databaseConnectionString[$i] != ':') && ($databaseConnectionString[$i] != '/')) {
                            $connectionDetails["dbHost"] = $connectionDetails["dbHost"].$databaseConnectionString[$i];
                        } else if ($databaseConnectionString[$i] == '/') {
                            $reading+=2;
                        } else if ($databaseConnectionString[$i] == ':') {
                            $reading+=1;
                        }
                        break;
                    case 2:
                        if ($databaseConnectionString[$i] != '/') {
                            $connectionDetails["dbHostPort"] = $connectionDetails["dbHostPort"].$databaseConnectionString[$i];
                        } else {
                            $reading+=1;
                        }
                        break;
                    case 3:
                        if ($databaseConnectionString[$i] != ':') {
                            $connectionDetails["userName"] = $connectionDetails["userName"].$databaseConnectionString[$i];
                        } else {
                            $reading+=1;
                        }
                        break;
                    default: //reading password
                        $connectionDetails["userPassword"] = $connectionDetails["userPassword"].$databaseConnectionString[$i];
                        break;
                }
            }

            //return connection details
            return $connectionDetails;
        }

        /**
         * Extract a Sybase connection details
         *
         * @param string $connString the connection string without the database type
         * @return array the connection details extracted from the given connection string
         */
        static function ParseSybaseConnectionString($connString)
        {
            /* what part of the connection string is currently being read?
             * 0 = db name
             * 1 = db host
             * 2 = host port
             * 3 = user username
             * 4 = user password
             */
            $reading = 0;

            //start building the connection details array used for sonnection and for
            //a possible connection save
            $connectionDetails = [
                "dbName" => "",
                "dbHost" => "",
                "dbHostPort" => "",
                "userName" => "",
                "userPassword" => "",
            ];

            //split the database connection string
            $databaseConnectionString = str_split($connString);
            //into an array of characters

            //get the database connection info
            $length = count($databaseConnectionString);
            for ($i = 0; $i < $length; $i++) {
                switch ($reading) {
                    case 0:
                        if ($databaseConnectionString[$i] != '@') {
                            $connectionDetails["dbName"] = $connectionDetails["dbName"].$databaseConnectionString[$i];
                        } else {
                            $reading+=1;
                        }
                        break;
                    case 1:
                        if (($databaseConnectionString[$i] != ':') && ($databaseConnectionString[$i] != '/')) {
                            $connectionDetails["dbHost"] = $connectionDetails["dbHost"].$databaseConnectionString[$i];
                        } else if ($databaseConnectionString[$i] == '/') {
                            $reading+=2;
                        } else if ($databaseConnectionString[$i] == ':') {
                            $reading+=1;
                        }
                        break;
                    case 2:
                        if ($databaseConnectionString[$i] != '/') {
                            $connectionDetails["dbHostPort"] = $connectionDetails["dbHostPort"].$databaseConnectionString[$i];
                        } else {
                            $reading+=1;
                        }
                        break;
                    case 3:
                        if ($databaseConnectionString[$i] != ':') {
                            $connectionDetails["userName"] = $connectionDetails["userName"].$databaseConnectionString[$i];
                        } else {
                            $reading+=1;
                        }
                        break;
                    default: //reading password
                        $connectionDetails["userPassword"] = $connectionDetails["userPassword"].$databaseConnectionString[$i];
                        break;
                }
            }

            //return connection details
            return $connectionDetails;
        }

    }
}