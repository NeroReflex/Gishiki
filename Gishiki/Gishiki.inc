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


//set the gishiki version
include(ROOT."Gishiki".DS."Version.inc");

//include the routing engine
include(ROOT."Gishiki".DS."core".DS."Routing.inc");

//include the logging engine
include(ROOT."Gishiki".DS."logs".DS."Graylog2".DS."GELFMessage.inc");
include(ROOT."Gishiki".DS."logs".DS."Graylog2".DS."GELFMessagePublisher.inc");
include(ROOT."Gishiki".DS."logs".DS."Priority.php");
include(ROOT."Gishiki".DS."logs".DS."Log.php");
include(ROOT."Gishiki".DS."logs".DS."LogManagement.php");

//include the base class exception
include(ROOT."Gishiki".DS."core".DS."Exception.php");

//include the JSON manager
include(ROOT."Gishiki".DS."JSON".DS."JSONException.php");
include(ROOT."Gishiki".DS."JSON".DS."JSON.php");

//include generic algorithms
include(ROOT."Gishiki".DS."generic".DS."StackException.php");
include(ROOT."Gishiki".DS."generic".DS."Stack.php");

//include the serialization library
include(ROOT."Gishiki".DS."serialization".DS."SerializationException.php");
include(ROOT."Gishiki".DS."serialization".DS."DirectSerialization.php");

//include the caching engine
include(ROOT."Gishiki".DS."cache".DS."Cache.php");
include(ROOT."Gishiki".DS."cache".DS."CacheConnectionString.php");
include(ROOT."Gishiki".DS."cache".DS."CachedMinification.php");

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

//include the list of http status codes
include(ROOT."Gishiki".DS."HTTPResponseCodes.inc");

//include the list of mime types
include(ROOT."Gishiki".DS."MIMETypes.inc");

//include the CSS and JS minifier
include(ROOT."Gishiki".DS."minify".DS."Converter.inc");
include(ROOT."Gishiki".DS."minify".DS."Minify.inc");
include(ROOT."Gishiki".DS."minify".DS."CSS.inc");
include(ROOT."Gishiki".DS."minify".DS."JS.inc");
include(ROOT."Gishiki".DS."minify".DS."Exception.inc");

//include the less and scss compiler integration
include(ROOT."Gishiki".DS."less".DS."lessIntegration.inc");
include(ROOT."Gishiki".DS."scss".DS."scssIntegration.inc");

//require base model and controller classes
include(ROOT."Gishiki".DS."core".DS."MVC".DS."ModelException.php");
include(ROOT."Gishiki".DS."core".DS."MVC".DS."ModelsCollection.php");
include(ROOT."Gishiki".DS."core".DS."MVC".DS."Model.php");
include(ROOT."Gishiki".DS."core".DS."MVC".DS."Controller.php");
include(ROOT."Gishiki".DS."core".DS."MVC".DS."WebController.php");
include(ROOT."Gishiki".DS."core".DS."MVC".DS."InterfaceController.php");

//include the environment manager
include(ROOT."Gishiki".DS."core".DS."Environment.php");

//include the application manager
include(ROOT."Gishiki".DS."core".DS."Application.php");

//include the schema manager
include(ROOT."Gishiki".DS."database".DS."medoo".DS."medoo.php");
include(ROOT."Gishiki".DS."database".DS."DataTypes.php");
include(ROOT."Gishiki".DS."database".DS."SchemaException.php");
include(ROOT."Gishiki".DS."database".DS."DatabaseException.php");
include(ROOT."Gishiki".DS."database".DS."Schema.php");
include(ROOT."Gishiki".DS."database".DS."DatabaseType.php");
include(ROOT."Gishiki".DS."database".DS."Database.php");
include(ROOT."Gishiki".DS."database".DS."ConnectionString.inc");

//this is the environment used to fulfill the incoming request
$executionEnvironment = NULL;


/**
 * The Gishiki action starter and framework entry point
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class Gishiki {   
    /**
     * Setup a framework instance
     */
    public function __construct() {
        
    }

    /**
     * Initialize the Gishiki engine and prepare for
     *  the execution of the framework
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
     * Execute the requested control-model-view operations
     * 
     * @param string $request the requested resource
     */
    public function Run($request)
    {
        if (strlen($request) >= 1) {
            //remove the final / as it is not needed and may become a problem
            if ($request[strlen($request) - 1] == '/') {
                $request = substr($request, 0, strlen($request) - 1);
            }
        }
        
        if (strlen($request) >= 1) {
            //remove the starting / as it is not needed and may become a problem
            if ($request[0] == '/') {
                $request = substr($request, 1, strlen($request));
            }
        }
        
        //fulfill the client request
        global $executionEnvironment;
        $executionEnvironment->FulfillRequest($request);
    }
    
    /**
     * Get the current framework version
     * 
     * @return integer a number that identify the framework version
     */
    static function GetCurrentVersion()
    {
        $versionNumber = (GISHIKI_MAJOR * 100) + ((GISHIKI_MINOR % 10) * 10) + (GISHIKI_REVISION % 10);
        
        //return the retrived version as a single number
        return $versionNumber;
    }
    
    /**
     * Install the framework
     */
    static function Install()
    {
        
    }
}
