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

namespace Gishiki\ActiveRecord;

/**
 * This is the database conections provider for the ActiveRecord engine
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class ConnectionsProvider {
    private static $connections = array();
    
    //define the name of the default database connection
    private static $name_of_default = 'default';
    
    /**
     * Create a connection using the given connection query and register the 
     * connection for future usage by an ActiveModel
     * 
     * @param string $connection_name the connection string
     * @param string $connection_string the connection query
     */
    static function Register($connection_name, $connection_string = "sqlite://:memory:") {
        $protocol_connection = explode("://", $connection_string, 2);
        
        if (count($protocol_connection) > 1 ) {
            //get the name of the adapter
            $adapter_name = ucwords(strtolower($protocol_connection[0]));
            $adapter_class_name = "Gishiki\\ActiveRecord\\Adapter\\" . $adapter_name . "Adapter";
            if (!class_exists($adapter_class_name)) {
                //what is the file of the database adapter?
                $adapter_php_filepath = ROOT."Gishiki".DS."ActiveRecord".DS."Adapter".DS.$adapter_name."Adapter.php";
                
                if (file_exists($adapter_php_filepath)) {
                    include($adapter_php_filepath);
                } else {
                    throw new DatabaseException("Unable to find a suitable database adapter", 0);
                }
                
                //reflect the database adapter class
                $reflected_adapter = new \ReflectionClass($adapter_class_name);
                self::$connections[$connection_name] = $reflected_adapter->newInstance($protocol_connection[1]);
            }
        } else {
            throw new DatabaseException("Empty connection query are not allowed", 1);
        }
        
    }
    
    /**
     * Register a connection for each element of the connections group.
     * 
     * <code>
     * ConnectionsProvider::RegisterGroup(['default' => "mysql://root:mypass@localhost/db", 
     *                                      'development' => "sqlite://database.php"]);
     * </code>
     * 
     * @param array $connections_group the array of connection
     */
    static function RegisterGroup($connections_group) {
        foreach ($connections_group as $connection_name => $connection_string) {
            self::Register($connection_name, $connection_string);
        }
    }
    
    /**
     * Get the conenction with the given name, if the name is not give the default
     * one is used
     * 
     * @param string $connection_name the connection name
     * @return a connection that implements the DatabaseAdapter insterface or NULL
     */
    static function FetchConnection($connection_name = null) {
        //get the connection name
        $connection_name = ($connection_name === null) ? self::$name_of_default : $connection_name;
        
        //return the connection
        $connection = (in_array($connection_name, array_keys(self::$connections))) ? self::$connections[$connection_name] : null;
        
        if (!$connection) {
            throw new \Gishiki\ActiveRecord\DatabaseException("Unknown database connection: " . $connection_name, 3);
        }
        
        return $connection;
    }
}