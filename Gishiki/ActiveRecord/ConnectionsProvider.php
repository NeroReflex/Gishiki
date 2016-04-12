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
abstract class ConnectionsProvider
{
    private static $connections = array();
    
    //define the name of the default database connection
    private static $name_of_default = 'default';
    
    /**
     * Create a connection using the given connection query and register the 
     * connection for future usage by an ActiveModel
     * 
     * @param string $connection_name the connection string
     * @param array  $connection      the connection query
     */
    public static function Register($connection_name, $connection)
    {
        if ((isset($connection['driver'])) && (isset($connection['query']))) {
            //get the name of the adapter
            $adapter_name = ucwords(strtolower($connection['driver']));
            $adapter_class_name = "Gishiki\\ActiveRecord\\Adapter\\".$adapter_name."Adapter";
            if (!class_exists($adapter_class_name)) {
                throw new DatabaseException("Unable to find a suitable database adapter", 0);
            }
                
            //reflect the database adapter class
            $reflected_adapter = new \ReflectionClass($adapter_class_name);
            self::$connections[$connection_name] = $reflected_adapter->newInstanceArgs([
                    0 => $connection['query'],
                    1 => (isset($connection['ssl_key'])) ? $connection['ssl_key']: null,
                    2 => (isset($connection['ssl_cert'])) ? $connection['ssl_cert']: null,
                    3 => (isset($connection['ssl_ca'])) ? $connection['ssl_ca']: null,
                ]);
        } else {
            throw new DatabaseException("Empty connection queries are not allowed", 1);
        }
    }
    
    /**
     * Register a connection for each element of the connections group.
     * 
     * <code>
     * ConnectionsProvider::RegisterGroup(['default' => ['driver' => 'mysql', 'query' =>"root:mypass@localhost/db"]], 
     *                                      'development' => ['driver' => 'sqlite', 'query' =>"/var/www/database.db"]]);
     * </code>
     * 
     * @param array $connections_group the array of connection
     */
    public static function RegisterGroup($connections_group)
    {
        foreach ($connections_group as $connection_name => $connection) {
            self::Register($connection_name, $connection);
        }
    }
    
    /**
     * Get the conenction with the given name, if the name is not give the default
     * one is used
     * 
     * @param  string                                  $connection_name the connection name
     * @return mixed                                   a connection that implements the DatabaseAdapter interface
     * @throws \Gishiki\ActiveRecord\DatabaseException the connection cannot be found
     */
    public static function FetchConnection($connection_name = null)
    {
        //get the connection name
        $connection_name = ($connection_name === null) ? self::$name_of_default : $connection_name;
        
        //return the connection
        $connection = (in_array($connection_name, array_keys(self::$connections))) ? self::$connections[$connection_name] : null;
        
        if (!$connection) {
            throw new \Gishiki\ActiveRecord\DatabaseException("Unknown database connection: ".$connection_name, 3);
        }
        
        return $connection;
    }
    
    /**
     * Change the default database connection, affecting each model using the default connection.
     * 
     * This function MUST NOT be called after the usage of a connection
     * 
     * @param string $connection_name the name of the connection that will be used as the default one
     */
    public static function ChangeDefaultConnection($connection_name = null)
    {
        if (($connection_name !== null) && (strlen($connection_name) > 0)) {
            self::$name_of_default = $connection_name;
        }
    }
}
