<?php
/**************************************************************************
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
    static function Parse($connectionString) {
        //eplode the string at the first ':' to get the database type 
        $firstExplosion = explode("://", $connectionString, 2);
        
        if ($firstExplosion[0] == "memory") {
            return array(
                "dbType" => "sqlite3",
                "dbName" => ":memory:",
                "dbPassword" => ""
                );
        } else if ($firstExplosion[0] == "sqlite3") {
            return ConnectionString::ParseSQLite3ConnectionString($firstExplosion[1]);
        } else if (($firstExplosion[0] == "sqlite2") || (($firstExplosion[0] == "sqlite"))) {
            return ConnectionString::ParseSQLiteConnectionString($firstExplosion[1]);
        } else if ($firstExplosion[0] == "mysql") {
            return ConnectionString::ParseMySQLConnectionString($firstExplosion[1]);
        } else if ($firstExplosion[0] == "pgsql") {
            return ConnectionString::ParsePostgreSQLConnectionString($firstExplosion[1]);
        } else if ($firstExplosion[0] == "sqlsrv") {
            return ConnectionString::ParseMicrosoftSQLConnectionString($firstExplosion[1]);
        } else if ($firstExplosion[0] == "firebird") {
            return ConnectionString::ParseFirebirdConnectionString($firstExplosion[1]);
        } else {
            throw new DatabaseException("Unrecognized database type, check the connection string", 1);
        }
    }
    
    /**
     * extract a Firebase connection details
     * 
     * @param string $connString the connection string without the database type
     * @return array the connection details extracted from the given connection string
     */
    static function ParseFirebirdConnectionString($connString) {
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
        $connectionDetails = array(
            "dbType" => "firebird",
            "dbName" => "",
            "dbHost" => "",
            "dbHostPort" => "",
            "userName" => "",
            "userPassword" => "",
        );
                    
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
        
        //use the default mysql port if one was not given
        if ($connectionDetails["dbHostPort"] == "") {
            $connectionDetails["dbHostPort"] = "3050";
        }
        
        //return connection details
        return $connectionDetails;
    }
    
    /**
     * extract a MySQL connection details
     * 
     * @param string $connString the connection string without the database type
     * @return array the connection details extracted from the given connection string
     */
    static function ParseMicrosoftSQLConnectionString($connString) {
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
        $connectionDetails = array(
            "dbType" => "sqlsrv",
            "dbName" => "",
            "dbHost" => "",
            "dbHostPort" => "",
            "userName" => "",
            "userPassword" => "",
        );
                    
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
        
        //use the default mysql port if one was not given
        if ($connectionDetails["dbHostPort"] == "") {
            $connectionDetails["dbHostPort"] = "1433";
        }
        
        //return connection details
        return $connectionDetails;
    }
    
    /**
     * extract a PostgreSQL connection details
     * 
     * @param string $connString the connection string without the database type
     * @return array the connection details extracted from the given connection string
     */
    static function ParsePostgreSQLConnectionString($connString) {
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
        $connectionDetails = array(
            "dbType" => "pgsql",
            "dbName" => "",
            "dbHost" => "",
            "dbHostPort" => "",
            "userName" => "",
            "userPassword" => "",
        );
                    
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
        
        //use the default mysql port if one was not given
        if ($connectionDetails["dbHostPort"] == "") {
            $connectionDetails["dbHostPort"] = "5432";
        }
        
        //return connection details
        return $connectionDetails;
    }
    
    /**
     * extract a MySQL connection details
     * 
     * @param string $connString the connection string without the database type
     * @return array the connection details extracted from the given connection string
     */
    static function ParseMySQLConnectionString($connString) {
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
        $connectionDetails = array(
            "dbType" => "mysql",
            "dbName" => "",
            "dbHost" => "",
            "dbHostPort" => "",
            "userName" => "",
            "userPassword" => "",
        );
                    
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
        
        //use the default mysql port if one was not given
        if ($connectionDetails["dbHostPort"] == "") {
            $connectionDetails["dbHostPort"] = "3306";
        }
        
        //return connection details
        return $connectionDetails;
    }
    
    /**
     * extract a SQLite v3 connection details
     * 
     * @param string $connString the connection string without the database type
     * @return array the connection details extracted from the given connection string
     */
    static function ParseSQLite3ConnectionString($connString) {
        //start building the connection details array used for sonnection 
        //and for a possible connection save
        $connectionDetails = array(
            "dbType" => "sqlite3",
            "dbName" => "",
            "dbPassword" => ""
        );
        
        //is the cycle reading the db name (TRUE)
        $savingName = TRUE;
        //or the db password (FALSE)
                    
        //split the database name and password (formatted as: "dbname:dbpass")
        $databaseConnectionString = str_split($connString);
        //into an array of characters
                    
        //get the database name and password
        $length = count($databaseConnectionString);
        for ($i = 0; $i < $length; $i++) {
            if ($savingName) {
                if ($databaseConnectionString[$i] != ':') {
                    $connectionDetails["dbName"] = $connectionDetails["dbName"].$databaseConnectionString[$i];
                } else {
                    $savingName = FALSE;
                }
            } else {
                $connectionDetails["dbPassword"] = $connectionDetails["dbPassword"].$databaseConnectionString[$i];
            }
        }
        
        //if the database name was not given, than an in-memory database was requested
        if ($connectionDetails["dbName"] == "") {
            $connectionDetails["dbName"] = ":memory:";
        }
        
        //return connection details
        return $connectionDetails;
    }
    
    /**
     * extract a SQLite v3 connection details
     * 
     * @param string $connString the connection string without the database type
     * @return array the connection details extracted from the given connection string
     */
    static function ParseSQLiteConnectionString($connString) {
        //start building the connection details array used for sonnection 
        //and for a possible connection save
        $connectionDetails = array(
            "dbType" => "sqlite",
            "dbName" => "",
            );
                    
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
}
