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

namespace Gishiki;

use Gishiki\Core\Environment;

/**
 * The Gishiki action starter and framework entry point.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class Gishiki
{
    //this is the environment used to fulfill the incoming request
    public static $executionEnvironment = null;
    
    //was the Run function already being executed?
    private static $executed = false;

    /**
     * Initialize the Gishiki engine and prepare for
     * the execution of a framework instance.
     */
    public static function Initialize()
    {
        //remove default execution time
        set_time_limit(0);

        //get directory separator
        if (!defined('DS')) {
            if (defined('DIRECTORY_SEPARATOR')) {
                define('DS', DIRECTORY_SEPARATOR);
            } else {
                define('DS', '/');
            }
        }

        //get the root path
        if ((!defined('ROOT')) || (ROOT == '') || (ROOT == null)) {
            define('ROOT', filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').DS);
        }

        //the name of the directory that contains model, view and controller (must be placed in the root)
        if (!defined('APPLICATION_DIR')) {
            define('APPLICATION_DIR', ROOT.'application'.DS);
        }
    }

    /**
     * Execute the requested operation.
     */
    public static function Run()
    {
        //avoid double executions
        if (self::$executed) {
            return;
        }
        
        //initialize the framework
        self::Initialize();

        //each Gishiki instance is binded with a newly created Environment
        if (!is_object(self::$executionEnvironment)) {
            self::$executionEnvironment = new Environment(
                filter_input_array(INPUT_SERVER), true, true);
        }

        //if the framework needs to be installed.....
        if (Environment::applicationExists()) {
            //fulfill the client request
            Environment::GetCurrentEnvironment()->FulfillRequest();
        } else {
            if (!defined('CLI_TOOLKIT')) {
                //show the no application page!
                echo file_get_contents(__DIR__.DS.'no_application.html');
            }
        }
        
        //the framework execution is complete
        self::$executed = true;
    }
}
