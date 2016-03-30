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
 * Provide functions to work with stored database connections
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class XMLConnection {
    
    /**
     * Parse a database connection stored into an XML file
     * 
     * @param string $connectionName the name of the stored connection
     * @return array the connection details extracted from the given stored connection
     * @throws DatabaseException the exception occurred while parsing the database
     */
    static function Parse($connectionName) {
        //if the database connection exists and can be read
        if (file_exists(DATABASE_CONNECTION_DIR.$connectionName.'.xml')) {
            //restore the connection info from a saved connection
            $xmlAbstract = simplexml_load_file(DATABASE_CONNECTION_DIR.$connectionName.'.xml', "SimpleXMLElement", LIBXML_NOERROR | LIBXML_NOWARNING);

            //cache the db type
            $dbtype = $xmlAbstract->type[0]->__toString();
            
            //check for the database type
            if ($dbtype == "SQLite3") {
                return XMLConnection::ParseSQLite3XML($xmlAbstract);
            } else if ($dbtype == "SQLite2") {
                return XMLConnection::ParseSQLiteXML($xmlAbstract);
            } else if ($dbtype == "MySQL") {
                return XMLConnection::ParseMySQLXML($xmlAbstract);
            } else if ($dbtype == "MicrosoftSQL") {
                return XMLConnection::ParseMicrosoftSQLXML($xmlAbstract);
            } else if ($dbtype == "Firebird") {
                return XMLConnection::ParseFirebirdXML($xmlAbstract);
            } else if ($dbtype == "PostgreSQL") {
                return XMLConnection::ParsePostgreSQLXML($xmlAbstract);
            } else {
                throw new DatabaseException("The given connection cannot be used because an incompatible database type connection is used", -4);
            }
        } else {
            throw new DatabaseException("A database connection with the given name cannot be found", -3);
        }
    }
    
    /**
     * Parse a SQLite3 database connection stored into an XML parsed file
     * 
     * @param SimpleXMLElement $xmlResource the xml parsed file
     * @return array the connection details extracted from the given stored connection
     */
    static function ParseSQLite3XML(SimpleXMLElement &$xmlResource) {
        //prepare the crypter, used to decrypt the database password:
        $crypter = new SymmetricCipher(SymmetricCipher::AES192);
        
        //start building the connection details array used for sonnection and for
        //a possible connection save
        $connectionDetails = array(
            //the type of the database is already known
            "dbType" => "sqlite3",
            
            //get the file name of the sqlite3 database
            "dbName"  =>   $xmlResource->database[0]->__toString(),
            
            //get the decrypted password of the sqlite3 database
            "dbPassword" => $crypter->Decrypt($xmlResource->password[0]->__toString())
        );
        
        //return connection details
        return $connectionDetails;
    }
    
    /**
     * Parse a SQLite database connection stored into an XML parsed file
     * 
     * @param SimpleXMLElement $xmlResource the xml parsed file
     * @return array the connection details extracted from the given stored connection
     */
    static function ParseSQLiteXML(SimpleXMLElement &$xmlResource) {
        //start building the connection details array used for sonnection and for
        //a possible connection save
        $connectionDetails = array(
            //the type of the database is already known
            "dbType" => "sqlite",
            
            //get the file name of the sqlite database
            "dbName"  =>   $xmlResource->database[0]->__toString()
        );
        
        //return connection details
        return $connectionDetails;
    }
    
    /**
     * Parse a MySQL database connection stored into an XML parsed file
     * 
     * @param SimpleXMLElement $xmlResource the xml parsed file
     * @return array the connection details extracted from the given stored connection
     */
    static function ParseMySQLXML(SimpleXMLElement &$xmlResource) {
        //prepare the crypter, used to decrypt the database password:
        $crypter = new SymmetricCipher(SymmetricCipher::AES192);
        
        //start building the connection details array used for sonnection and for
        //a possible connection save
        //start building the connection details array used for sonnection and for
        //a possible connection save
        $connectionDetails = array(
            //the type of the database is already known
            "dbType" => "mysql",
            
             //get the file name of the MySQL database
            "dbName" => $xmlResource->database[0]->__toString(),
            
            //get the host of the MySQL database
            "dbHost" => $xmlResource->host[0]->__toString(),
            
            //get the port used by the host of the MySQL database
            "dbHostPort" => $xmlResource->port[0]->__toString(),
            
            //get the username of the database user
            "userName" => $xmlResource->user[0]->__toString(),
            
            //get the decrypted password of the database user
            "userPassword" => $crypter->Decrypt($xmlResource->password[0]->__toString()),
        );
        
        //return connection details
        return $connectionDetails;
    }
    
    /**
     * Parse a MS SQL database connection stored into an XML parsed file
     * 
     * @param SimpleXMLElement $xmlResource the xml parsed file
     * @return array the connection details extracted from the given stored connection
     */
    static function ParseMicrosoftSQLXML(SimpleXMLElement &$xmlResource) {
        //prepare the crypter, used to decrypt the database password:
        $crypter = new SymmetricCipher(SymmetricCipher::AES192);
        
        //start building the connection details array used for sonnection and for
        //a possible connection save
        $connectionDetails = array(
            //the type of the database is already known
            "dbType" => "sqlsrv",
            
             //get the file name of the MicrosoftSQL database
            "dbName" => $xmlResource->database[0]->__toString(),
            
            //get the host of the MicrosoftSQL database
            "dbHost" => $xmlResource->host[0]->__toString(),
            
            //get the port used by the host of the MicrosoftSQL database
            "dbHostPort" => $xmlResource->port[0]->__toString(),
            
            //get the username of the MicrosoftSQL database user
            "userName" => $xmlResource->user[0]->__toString(),
            
            //get the decrypted password of the MicrosoftSQL database user
            "userPassword" => $crypter->Decrypt($xmlResource->password[0]->__toString()),
        );
        
        //return connection details
        return $connectionDetails;
    }
    
    /**
     * Parse a Firebird database connection stored into an XML parsed file
     * 
     * @param SimpleXMLElement $xmlResource the xml parsed file
     * @return array the connection details extracted from the given stored connection
     */
    static function ParseFirebirdXML(SimpleXMLElement &$xmlResource) {
        //prepare the crypter, used to decrypt the database password:
        $crypter = new SymmetricCipher(SymmetricCipher::AES192);

        //start building the connection details array used for sonnection and for
        //a possible connection save
        $connectionDetails = array(
            //the type of the database is already known
            "dbType" => "firebird",
            
             //get the file name of the Firebird database
            "dbName" => $xmlResource->database[0]->__toString(),
            
            //get the host of the Firebird database
            "dbHost" => $xmlResource->host[0]->__toString(),
            
            //get the port used by the host of the Firebird database
            "dbHostPort" => $xmlResource->port[0]->__toString(),
            
            //get the username of the Firebird database user
            "userName" => $xmlResource->user[0]->__toString(),
            
            //get the decrypted password of the Firebird database user
            "userPassword" => $crypter->Decrypt($xmlResource->password[0]->__toString()),
        );
        
        //return connection details
        return $connectionDetails;
    }
    
    /**
     * Parse a PostgreSQL database connection stored into an XML parsed file
     * 
     * @param SimpleXMLElement $xmlResource the xml parsed file
     * @return array the connection details extracted from the given stored connection
     */
    static function ParsePostgreSQLXML(SimpleXMLElement &$xmlResource) {
        //prepare the crypter, used to decrypt the database password:
        $crypter = new SymmetricCipher(SymmetricCipher::AES192);

        //start building the connection details array used for sonnection and for
        //a possible connection save
        $connectionDetails = array(
            //the type of the database is already known
            "dbType" => "pgsql",
            
             //get the file name of the PostgreSQL database
            "dbName" => $xmlResource->database[0]->__toString(),
            
            //get the host of the PostgreSQL database
            "dbHost" => $xmlResource->host[0]->__toString(),
            
            //get the port used by the host of the PostgreSQL database
            "dbHostPort" => $xmlResource->port[0]->__toString(),
            
            //get the username of the PostgreSQL database user
            "userName" => $xmlResource->user[0]->__toString(),
            
            //get the decrypted password of the PostgreSQL database user
            "userPassword" => $crypter->Decrypt($xmlResource->password[0]->__toString()),
        );
        
        //return connection details
        return $connectionDetails;
    }
    
    /**
     * Store a connection from its connection details/parsed connection string.
     * 
     * @param string $connectionName the name of the connection when stored
     * @param array $connectionDetails the connection details of the currently opened connection
     */
    static function Store($connectionName, &$connectionDetails) {
        //start the connection XML
        $connectionXML =
<<<XML
<connection></connection>
XML;
        //setup a SimpleXML instance from the empty XML connection 
        $xmlAbstract = new SimpleXMLElement($connectionXML);
            
        //setup the XML connection using the proper driver
        switch ($connectionDetails["dbType"]) {
            case "sqlite3":
                XMLConnection::StoreSQLite3Connection($xmlAbstract, $connectionDetails);
                break;
            case "sqlite":
                XMLConnection::StoreSQLiteConnection($xmlAbstract, $connectionDetails);
                break;
            case "mysql":
                XMLConnection::StoreMySQLConnection($xmlAbstract, $connectionDetails);
                break;
            case "pgsql":
                XMLConnection::StorePostgreSQLConnection($xmlAbstract, $connectionDetails);
                break;
            case "sqlsrv":
                XMLConnection::StoreMicrosoftSQLConnection($xmlAbstract, $connectionDetails);
                break;
            case "firebird":
                XMLConnection::StoreFirebirdConnection($xmlAbstract, $connectionDetails);
                break;
        }
        
        //store the XML connection
        $xmlAbstract->saveXML(DATABASE_CONNECTION_DIR.$connectionName.'.xml');
    }
    
    /**
     * Fill the xml structure that will be written to the disk.
     * 
     * @param SimpleXMLElement $connectionXML the semi-elaborated XML file
     * @param array $connectionDetails the connection details of the currently opened connection
     */
    static function StoreSQLite3Connection(SimpleXMLElement &$connectionXML, &$connectionDetails) {
        //prepare the crypter, used to encrypt the database password:
        $crypter = new SymmetricCipher(SymmetricCipher::AES192);
        
        //store the database type
        $connectionXML->addChild('type', "SQLite3");

        //store the database name
        $connectionXML->addChild('database', $connectionDetails["dbName"]);

        //store the encrypted database password
        $connectionXML->addChild('password', $crypter->Encrypt($connectionDetails["dbPassword"]));
    }
    
    /**
     * Fill the xml structure that will be written to the disk.
     * 
     * @param SimpleXMLElement $connectionXML the semi-elaborated XML file
     * @param array $connectionDetails the connection details of the currently opened connection
     */
    static function StoreSQLiteConnection(SimpleXMLElement &$connectionXML, &$connectionDetails) {
        //store the database type
        $connectionXML->addChild('type', "SQLite2");

        //store the database name
        $connectionXML->addChild('database', $connectionDetails["dbName"]);
    }
    
    /**
     * Fill the xml structure that will be written to the disk.
     * 
     * @param SimpleXMLElement $connectionXML the semi-elaborated XML file
     * @param array $connectionDetails the connection details of the currently opened connection
     */
    static function StoreMySQLConnection(SimpleXMLElement &$connectionXML, &$connectionDetails) {
        //prepare the crypter, used to encrypt the database password:
        $crypter = new SymmetricCipher(SymmetricCipher::AES192);
        
        //store the database type
        $connectionXML->addChild('type', "MySQL");

        //store the database name
        $connectionXML->addChild('database', $connectionDetails["dbName"]);
        
        //store the database host
        $connectionXML->addChild('host', $connectionDetails["dbHost"]);
        
        //store the database host port
        $connectionXML->addChild('port', $connectionDetails["dbHostPort"]);
        
        //store the database user
        $connectionXML->addChild('user', $connectionDetails["userName"]);

        //store the username password encrypted
        $connectionXML->addChild('password', $crypter->Encrypt($connectionDetails["userPassword"]));
    }
    
    /**
     * Fill the xml structure that will be written to the disk.
     * 
     * @param SimpleXMLElement $connectionXML the semi-elaborated XML file
     * @param array $connectionDetails the connection details of the currently opened connection
     */
    static function StorePostgreSQLConnection(SimpleXMLElement &$connectionXML, &$connectionDetails) {
        //prepare the crypter, used to encrypt the database password:
        $crypter = new SymmetricCipher(SymmetricCipher::AES192);
        
        //store the database type
        $connectionXML->addChild('type', "PostgreSQL");

        //store the database name
        $connectionXML->addChild('database', $connectionDetails["dbName"]);
        
        //store the database host
        $connectionXML->addChild('host', $connectionDetails["dbHost"]);
        
        //store the database host port
        $connectionXML->addChild('port', $connectionDetails["dbHostPort"]);
        
        //store the database user
        $connectionXML->addChild('user', $connectionDetails["userName"]);

        //store the username password encrypted
        $connectionXML->addChild('password', $crypter->Encrypt($connectionDetails["userPassword"]));
    }
    
    /**
     * Fill the xml structure that will be written to the disk.
     * 
     * @param SimpleXMLElement $connectionXML the semi-elaborated XML file
     * @param array $connectionDetails the connection details of the currently opened connection
     */
    static function StoreMicrosoftSQLConnection(SimpleXMLElement &$connectionXML, &$connectionDetails) {
        //prepare the crypter, used to encrypt the database password:
        $crypter = new SymmetricCipher(SymmetricCipher::AES192);
        
        //store the database type
        $connectionXML->addChild('type', "MicrosoftSQL");

        //store the database name
        $connectionXML->addChild('database', $connectionDetails["dbName"]);
        
        //store the database host
        $connectionXML->addChild('host', $connectionDetails["dbHost"]);
        
        //store the database host port
        $connectionXML->addChild('port', $connectionDetails["dbHostPort"]);
        
        //store the database user
        $connectionXML->addChild('user', $connectionDetails["userName"]);

        //store the username password encrypted
        $connectionXML->addChild('password', $crypter->Encrypt($connectionDetails["userPassword"]));
    }
    
    /**
     * Fill the xml structure that will be written to the disk.
     * 
     * @param SimpleXMLElement $connectionXML the semi-elaborated XML file
     * @param array $connectionDetails the connection details of the currently opened connection
     */
    static function StoreFirebirdConnection(SimpleXMLElement &$connectionXML, &$connectionDetails) {
        //prepare the crypter, used to encrypt the database password:
        $crypter = new SymmetricCipher(SymmetricCipher::AES192);
        
        //store the database type
        $connectionXML->addChild('type', "Firebird");

        //store the database name
        $connectionXML->addChild('database', $connectionDetails["dbName"]);
        
        //store the database host
        $connectionXML->addChild('host', $connectionDetails["dbHost"]);
        
        //store the database host port
        $connectionXML->addChild('port', $connectionDetails["dbHostPort"]);
        
        //store the database user
        $connectionXML->addChild('user', $connectionDetails["userName"]);

        //store the username password encrypted
        $connectionXML->addChild('password', $crypter->Encrypt($connectionDetails["userPassword"]));
    }
}
