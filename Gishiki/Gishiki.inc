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

//include basic common algorithms
include(ROOT."Gishiki".DS."algorithms".DS."CyclableCollection.php");
include(ROOT."Gishiki".DS."algorithms".DS."string_functions.php");

//include the logging engine
include(ROOT."Gishiki".DS."logs".DS."Priority.php");
include(ROOT."Gishiki".DS."logs".DS."Log.php");

//include the base class exception
include(ROOT."Gishiki".DS."core".DS."Exception.php");

//include the JSON manager
include(ROOT."Gishiki".DS."JSON".DS."JSONException.php");
include(ROOT."Gishiki".DS."JSON".DS."JSON.php");

//include the caching engine
include(ROOT."Gishiki".DS."cache".DS."Cache.php");
include(ROOT."Gishiki".DS."cache".DS."CacheConnectionString.php");

//include the cookie management system
include(ROOT."Gishiki".DS."cookie".DS."CookieException.php");
include(ROOT."Gishiki".DS."cookie".DS."Cookie.php");
include(ROOT."Gishiki".DS."cookie".DS."CookieProvider.php");

//include the RSA and AES helper library
include(ROOT."Gishiki".DS."security".DS."CipherException.php");
include(ROOT."Gishiki".DS."security".DS."SymmetricCipher.php");
include(ROOT."Gishiki".DS."security".DS."AsymmetricCipherAlgorithms.php");
include(ROOT."Gishiki".DS."security".DS."AsymmetricPrivateKeyCipher.php");
include(ROOT."Gishiki".DS."security".DS."AsymmetricPublicKeyCipher.php");
include(ROOT."Gishiki".DS."security".DS."AsymmetricCipher.php");

//require base controller classes
include(ROOT."Gishiki".DS."core".DS."MVC".DS."WebController.php");
include(ROOT."Gishiki".DS."core".DS."MVC".DS."Service.php");

//include the routing engine
include(ROOT."Gishiki".DS."core".DS."Routing.php");
include(ROOT."Gishiki".DS."core".DS."Route.php");

//include the environment manager
include(ROOT."Gishiki".DS."core".DS."Environment.php");

//include the application manager
include(ROOT."Gishiki".DS."core".DS."Application.php");

//include the runtime (JIT) component of the ORM
include(ROOT."Gishiki".DS."ORM".DS."Runtime".DS."ActiveRecord.php");

//include database structure descriptiors entities
include(ROOT."Gishiki".DS."ORM".DS."Common".DS."DataType.php");
include(ROOT."Gishiki".DS."ORM".DS."Common".DS."Field.php");
include(ROOT."Gishiki".DS."ORM".DS."Common".DS."Table.php");
include(ROOT."Gishiki".DS."ORM".DS."Common".DS."Database.php");

//include models code generator (AOT)
include(ROOT."Gishiki".DS."ORM".DS."ModelBuilding".DS."ModelBuildingException.php");
include(ROOT."Gishiki".DS."ORM".DS."ModelBuilding".DS."ModelBuilder.php");
include(ROOT."Gishiki".DS."ORM".DS."ModelBuilding".DS."StaticAnalyzerInterface.php");
include(ROOT."Gishiki".DS."ORM".DS."ModelBuilding".DS."StaticAnalyzer.php");
include(ROOT."Gishiki".DS."ORM".DS."ModelBuilding".DS."Adapters".DS."XML_StaticAnalyzer.php");
include(ROOT."Gishiki".DS."ORM".DS."ModelBuilding".DS."Adapters".DS."JSON_StaticAnalyzer.php");

//this is the environment used to fulfill the incoming request
$executionEnvironment = NULL;


/**
 * The Gishiki action starter and framework entry point
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class Gishiki {
    
    /**
     * Initialize the Gishiki engine and prepare for
     * the execution of a framework instance
     */
    static function Initialize()
    {
        global $executionEnvironment;
        
        //the name of the directory that contains model, view and controller (must be placed in the root)
        if (!defined('APPLICATION_DIR')) {
            define('APPLICATION_DIR', ROOT."application".DS);
        }
        
        //each Gishiki instance is binded with a new created Environment
        $executionEnvironment = new \Gishiki\Core\Environment(TRUE);
    }
    
    /**
     * Execute the requested operation.
     * That operation can be the execution of a service,
     * a controller or the fetch of static content.
     */
    static function Run()
    {   
        //fulfill the client request
        global $executionEnvironment;
        $executionEnvironment->FulfillRequest();
    }
}
