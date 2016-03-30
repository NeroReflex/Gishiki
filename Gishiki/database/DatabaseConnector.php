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
 * Provide the ability to open database connections
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class DatabaseConnector {
    
    /**
     * Create a database connection from a parsed connection string
     * 
     * @param array $connectionDetails the result of a connection string parsing
     * @param integer $databaseType the type of the database opened
     */
    static function Connect($connectionDetails, &$databaseType) {
        //get the type of the database to connect to
        $dbType = $connectionDetails["dbType"];
        
        if ($dbType == "sqlite3") {
            $databaseType = DatabaseType::SQLite3;
            return DatabaseConnector::ConnectSQLite3Database($connectionDetails);
        } else if ($dbType == "sqlite") {
            $databaseType = DatabaseType::SQLite;
            return DatabaseConnector::ConnectMySQLDatabase($connectionDetails);
        } else if ($dbType == "mysql") {
            $databaseType = DatabaseType::MySQL;
            return DatabaseConnector::ConnectMySQLDatabase($connectionDetails);
        } else if ($dbType == "pgsql") {
            $databaseType = DatabaseType::PostgreSQL;
            return DatabaseConnector::ConnectPostgreSQLDatabase($connectionDetails);
        } else if ($dbType == "sqlsrv") {
            $databaseType = DatabaseType::MicrosoftSQL;
            return DatabaseConnector::ConnectMicrosoftSQLDatabase($connectionDetails);
        } else if ($dbType == "firebird") {
            $databaseType = DatabaseType::Firebird;
            return DatabaseConnector::ConnectFirebirdDatabase($connectionDetails);
        }
    }
    
    /**
     * Create a Firebird database connection from a parsed connection string
     * 
     * @param array $connectionDetails the result of a connection string parsing
     * @param integer $databaseType the type of the database opened
     */
    static function ConnectFirebirdDatabase($connectionDetails) {
        if (!in_array("firebird", Environment::GetCurrentEnvironment()->GetDatabaseDrivers())) {
            throw new DatabaseException("Firebird PDO driver is not installed", 2);
        }

        //build the data source name
        $dsn = "firebird:dbname=".$connectionDetails["dbHost"]."/".$connectionDetails["dbHostPort"].":".$connectionDetails["dbName"];
        
        //try to create a Firebase connection
        try {
            return new PDO($dsn, $connectionDetails["userName"], $connectionDetails["userPassword"]);
        } catch (PDOException $ex) {
            throw new DatabaseException($ex->getMessage(), $ex->getCode());
        }
    }
    
    /**
     * Create a MS SQL database connection from a parsed connection string
     * 
     * @param array $connectionDetails the result of a connection string parsing
     * @param integer $databaseType the type of the database opened
     */
    static function ConnectMicrosoftSQLDatabase($connectionDetails) {
        if (!in_array("sqlsrv", Environment::GetCurrentEnvironment()->GetDatabaseDrivers())) {
            throw new DatabaseException("MicrosoftSQL PDO driver is not installed", 2);
        }

        //build the data source name
        $dsn = "sqlsrv:Server=".$connectionDetails["dbHost"].",".$connectionDetails["dbHostPort"].";Database=".$connectionDetails["dbName"].";TrustServerCertificate=1;Encrypt=1";
        
        //try to create a MS SQL connection
        try {
            return new PDO($dsn, $connectionDetails["userName"], $connectionDetails["userPassword"]);
        } catch (PDOException $ex) {
            throw new DatabaseException($ex->getMessage(), $ex->getCode());
        }
    }
    
    /**
     * Create a SQLite3 database connection from a parsed connection string
     * 
     * @param array $connectionDetails the result of a connection string parsing
     * @param integer $databaseType the type of the database opened
     */
    static function ConnectSQLite3Database($connectionDetails) {
        //try to create a SQLite3 connection
        try {
            return new SQLite3(DATABASE_DIR.$connectionDetails["dbName"].".db", SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE, $connectionDetails["dbPassword"]);
        } catch (Exception $ex) {
            throw new DatabaseException($ex->getMessage(), $ex->getCode());
        }
    }
    
    /**
     * Create a SQLite2 database connection from a parsed connection string
     * 
     * @param array $connectionDetails the result of a connection string parsing
     * @param integer $databaseType the type of the database opened
     */
    static function ConnectSQLiteDatabase($connectionDetails) {
        if (!in_array("sqlite", Environment::GetCurrentEnvironment()->GetDatabaseDrivers())) {
            throw new DatabaseException("SQLite PDO driver is not installed", 2);
        }

        //build the data source name
        $dsn = "sqlite:".DATABASE_DIR.$connectionDetails["dbName"].".sqlite";
        
        //connection properties
        $options = array(
            PDO::ATTR_PERSISTENT => true,
        );
        
        //try to create a SQLite connection
        try {
            return new PDO($dsn, NULL, NULL, $options);
        } catch (PDOException $ex) {
            throw new DatabaseException($ex->getMessage(), $ex->getCode());
        }
    }
    
    /**
     * Create a MySQL database connection from a parsed connection string
     * 
     * @param array $connectionDetails the result of a connection string parsing
     * @param integer $databaseType the type of the database opened
     */
    static function ConnectMySQLDatabase($connectionDetails) {
        if (!in_array("mysql", Environment::GetCurrentEnvironment()->GetDatabaseDrivers())) {
            throw new DatabaseException("MySQL PDO driver is not installed", 2);
        }

        //build the data source name
        $dsn = "mysql:host=".$connectionDetails["dbHost"].";port=".$connectionDetails["dbHostPort"].";dbname=".$connectionDetails["dbName"];
        
        //connection properties
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        );
        
        //try to create a MySQL connection
        try {
            return new PDO($dsn, $connectionDetails["userName"], $connectionDetails["userPassword"], $options);
        } catch (PDOException $ex) {
            throw new DatabaseException($ex->getMessage(), $ex->getCode());
        }
    }
    
    /**
     * Create a PostgreSQL database connection from a parsed connection string
     * 
     * @param array $connectionDetails the result of a connection string parsing
     * @param integer $databaseType the type of the database opened
     */
    static function ConnectPostgreSQLDatabase($connectionDetails) {
        if (!in_array("pgsql", Environment::GetCurrentEnvironment()->GetDatabaseDrivers())) {
            throw new DatabaseException("PostgreSQL PDO driver is not installed", 2);
        }

        //build the data source name
        $dsn = "pgsql:host=".$connectionDetails["dbHost"].";port=".$connectionDetails["dbHostPort"].";dbname=".$connectionDetails["dbName"].";sslmode=prefer";
        
        //connection properties
        $options = array(
            PDO::ATTR_PERSISTENT => true,
        );
        
        //try to create a MySQL connection
        try {
            return new PDO($dsn, $connectionDetails["userName"], $connectionDetails["userPassword"], $options);
        } catch (PDOException $ex) {
            throw new DatabaseException($ex->getMessage(), $ex->getCode());
        }
    }
}
