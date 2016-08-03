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

namespace Gishiki\Pipeline;

use Gishiki\Database\DatabaseException;

/**
 * Give runtime support to the pipeline component.
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class PipelineSupport
{
    private static $connectionHandler = null;

    private static $tableName = null;

    private static $initialized = false;
    
    private static $activeRuntime = null;

    /**
     * Internal use ONLY!
     * Setup the PipelineSupport from the global Environment.
     * 
     * @param string $connectionName the name of the database connection
     * @param string $tableName      the name of the table inside the database connection
     *
     * @throws DatabaseException         the exception occurred while fetching the database connection
     * @throws \InvalidArgumentException given arguments are not valid strings
     */
    public static function Initialize($connectionName, $tableName)
    {
        //only works for the first time (prevent user from doing bad things)
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;

        if ((!is_string($connectionName)) || (!is_string($tableName))) {
            throw new \InvalidArgumentException('Database connection name and table must be given as strings');
        }

        //retrieve the database connection
        self::$connectionHandler = \Gishiki\Database\DatabaseManager::Retrieve($connectionName);

        //store the table name
        self::$tableName = $tableName;
    }

    
    public static function RegisterRuntime(\Gishiki\Pipeline\PipelineRuntime &$runtime)
    {
        //get the runtime
        self::$activeRuntime = &$runtime;
    }
    
    
    public static function UnregisterRuntime()
    {
        //get the runtime
        self::$activeRuntime = null;
    }
}
