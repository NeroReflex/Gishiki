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

namespace Gishiki;

/**
 * The Gishiki action starter and framework entry point
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class Gishiki
{
    
    //this is the environment used to fulfill the incoming request
    public static $executionEnvironment = null;
    
    /**
     * Initialize the Gishiki engine and prepare for
     * the execution of a framework instance
     */
    public static function Initialize()
    {
        //the name of the directory that contains model, view and controller (must be placed in the root)
        if (!defined('APPLICATION_DIR')) {
            define('APPLICATION_DIR', ROOT."application".DS);
        }
        
        //each Gishiki instance is binded with a new created Environment
        self::$executionEnvironment = new \Gishiki\Core\Environment(true);
    }
    
    /**
     * Execute the requested operation.
     */
    public static function Run()
    {
        //if the framework needs to be installed.....
        if (!\Gishiki\Core\Application::Exists()) {
            //setup the new application
            if (\Gishiki\Core\Application::CreateNew()) {
            } else {
                exit("<div><br /><b>Check for the environment, delete the created application directory and retry the installation</b></div>");
            }
        } else {
            //fulfill the client request
            self::$executionEnvironment->FulfillRequest();
        }
    }
}
