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

namespace Gishiki\Core {
    
    /**
     * The Application abstraction
     * 
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    abstract class Application {
        
        /**
         * Read the application configuration (settings.ini) and return the 
         * parsing result
         * 
         * @return array the application configuration
         */
        static function GetSettings() {
            //parse the settings file
            $appConfiguration = \Gishiki\JSON\JSON::DeSerialize(file_get_contents(APPLICATION_DIR."settings.json"));
            
            //return the application configuration
            return $appConfiguration;
        }

        /**
         * Analyze a resource and build a model out of that resource
         * 
         * @param string $resource the resource to be analyzed
         */
        static function GenerateORMData($resource, $on_the_fly = FALSE) {
            //get the file path of the model
            $model_filepath = APPLICATION_DIR."Models".DS.pathinfo($resource, PATHINFO_FILENAME).".php";
            
            if (($on_the_fly) || (!file_exists($model_filepath))) {
                try {
                    //set the file containing the database structure
                    $analyzer = new \Gishiki\ORM\ModelBuilding\StaticAnalyzer($resource);

                    //analyze that file
                    $analyzer->Analyze();

                    //was that file correctly analyzed?
                    if ($analyzer->Analyzed()) {

                        //get the analysis result
                        $database_structure = $analyzer->Result();

                        //initialize the code generator
                        $code_generator = new \Gishiki\ORM\ModelBuilding\ModelBuilder($database_structure);

                        //check for errors
                        $code_generator->ErrorsCheck();

                        //perform the code generation
                        $compilation_result = $code_generator->Compile();

                        //include the compilation result
                        eval($compilation_result);

                        if (!\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty("DATA_AUTOCACHE"))
                        {   file_put_contents($model_filepath, "<?php".PHP_EOL.$compilation_result, LOCK_EX);  }
                    } else {
                        die("in resource '".$resource."': unknown error!");
                    }

                } catch (\Gishiki\ORM\ModelBuilding\ModelBuildingException $error) {
                    die("Error number (".$error->getCode()."): ".$error->getMessage());
                }
            } else if (!$on_the_fly) {
                include($model_filepath);
            }
        }
        
        /**
         * Start the Object-relational mapping bundled with Gishiki:
         *      -   Execute the AOT component to generate the PHP code (if needed)
         *      -   Include the generated php code
         *      -   Perform any additional setup operations
         */
        static function StartORM($resources) {
            //iterate over each database descriptor
            foreach ($resources as &$resource) //compile the current database descriptor 
            {   Application::GenerateORMData(APPLICATION_DIR.$resource, \Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty("DATA_AUTOCACHE"));    }
            
            //start up PHP ActiveRecord
            \ActiveRecord\Config::initialize(function($cfg)
            {
                $cfg->set_model_directory(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty("MODEL_DIR"));
                $cfg->set_connections(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty("DATA_CONNECTIONS"));
            });
        }
        
        /**
         * Chack if the application to be executed exists, is valid and has the
         * configuration file
         * 
         * @return boolean the application existence
         */
        static function Exists() {
            //return the existence of an application directory and a configuratio file
            return ((file_exists(APPLICATION_DIR)) && (file_exists(APPLICATION_DIR."settings.json")));
        }

        /**
         * Setup a new empty application structure/skeleton
         * 
         * @return integer the number of errors occurred
         */
        static function CreateNew() {
            //remove the php execution time limit
            set_time_limit(0);

            //get a new password
            $new_password = base64_encode(openssl_random_pseudo_bytes(256));
            
            //setup the error counter
            $errors = 0;
            
            if ((!file_exists(APPLICATION_DIR)) && ($errors == 0)) {
                if (!@mkdir(APPLICATION_DIR)) {
                    $errors++;
                } else {
                    file_put_contents(APPLICATION_DIR . ".htaccess", "Deny from all", LOCK_EX);
                }
            }
            
            if ((!file_exists(APPLICATION_DIR."Services".DS)) && ($errors == 0)) {
                if (!@mkdir(APPLICATION_DIR."Services".DS)) {
                    $errors++;
                }
            }
            
            if ((!file_exists(APPLICATION_DIR."Models".DS)) && ($errors == 0)) {
                if (!@mkdir(APPLICATION_DIR."Models".DS)) {
                    $errors++;
                }
            }
            
            if ((!file_exists(APPLICATION_DIR."Keyring".DS)) && ($errors == 0)) {
                if (!@mkdir(APPLICATION_DIR."Keyring".DS)) {
                    $errors++;
                } else {
                    file_put_contents(APPLICATION_DIR."Keyring".DS.".htaccess", "Deny from all", LOCK_EX);
                }
            }
            
            if ((!file_exists(APPLICATION_DIR."Resources".DS)) && ($errors == 0)) {
                if (!@mkdir(APPLICATION_DIR."Resources".DS)) {
                    $errors++;
                }
            }
            
            $routing_example = 
                    "//import the namespace for Routing".PHP_EOL.
                    "use \\Gishiki\\Core\\Routing;".PHP_EOL.PHP_EOL.
                    "Routing::setRoute(Routing::GET, \"/\", function() {".PHP_EOL.
                    "   //this is the homepage, just render a small list of books...".PHP_EOL.
                    "});".PHP_EOL.PHP_EOL.
                    "Routing::setRoute(Routing::GET, \"/book/{id}\", function(\$params) {".PHP_EOL.
                    "   //parameter \"id\" contains the id of the searched user, you just search the user in your database and return it".PHP_EOL.
                    "   echo 'You have requested to see the book with ID '.\$params->get(\"id\");".PHP_EOL.
                    "});".PHP_EOL.
                    "Routing::setRoute(Routing::GET, \"/book/new/{name}/{author}/{cost}/{date}\", function(\$params) {".PHP_EOL.
                    "   //look at bookstore.xml to review the used data model".PHP_EOL.
                    "   \$book = new book();".PHP_EOL.
                    "   \$book->setAuthor(\$params->get(\"author\"));".PHP_EOL.
                    "   \$book->setTitle(\$params->get(\"name\"));".PHP_EOL.
                    "   \$book->setPrice(\$params->get(\"cost\"));".PHP_EOL.
                    "   \$book->setPublication_date(new ActiveRecord\\DateTime(\$params->get(\"date\")));".PHP_EOL.
                    "   //the model is automatically saved into the database. Enjoy!".PHP_EOL.
                    "   echo \"Book stored into the default database!\";".PHP_EOL.
                    "});".PHP_EOL.
                    "Routing::setErrorCallback(Routing::NotFoudCallback, function() {".PHP_EOL.
                    "   //this is what is executed if the router is unable to find a suitable route for a request".PHP_EOL.
                    "   die(\"Sorry man.... 404 Page Not Found!\");".PHP_EOL.
                    "});".PHP_EOL;
            if (file_put_contents(APPLICATION_DIR."router.php", "<?php ".PHP_EOL.$routing_example."?>", LOCK_EX) === FALSE) {
                $errors++;
            }
            
            $bookstore_example = <<<XML
<?xml version='1.0' standalone='yes'?>
<!-- the connection named "default" is added by the application initializer -->
<database name="bookstore" connection="default">
    <table name="books"><!-- table names always ends with a trailing 's' -->
        <column type="integer" name="id" primaryKey="true"></column>
        <column type="string" name="title"></column>
        <column type="float" name="price"></column>
        <column type="string" name="author"></column>
        <column type="datetime" name="publication_date"></column>
    </table>
</database>
XML;
            $bookstore_example_xml = new \SimpleXMLElement($bookstore_example);
            $bookstore_example_xml->asXML(APPLICATION_DIR."bookstore.xml");
            
            if (in_array("sqlite", \PDO::getAvailableDrivers())) {
                try {
                    //create a new example db
                    $example_db = new \PDO("sqlite:default_db.sqlite");
                    
                    //this is the query for the creation of the example table
                    $example_db->exec("CREATE TABLE 'books' ('id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 'title' TEXT, 'author' TEXT, 'price' REAL, 'publication_date' DATETIME)");
                } catch (\PDOException $ex) {
                    new \Gishiki\Logging\Log("Error in the default db", "The following error was encountered while creating the default database: ".$ex->getMessage(), \Gishiki\Logging\Priority::WARNING);
                }
            }
            
            if ((!file_exists(Environment::GetCurrentEnvironment()->GetConfigurationProperty('APPLICATION_DIR')."settings.json")) && ($errors == 0)) {
                $configuration = "{".PHP_EOL 
                                ."  \"general\": {".PHP_EOL
                                ."      \"development\": true".PHP_EOL
                                ."  },".PHP_EOL
                        .PHP_EOL."  \"database\": {".PHP_EOL
                                ."      \"on-the-fly\": false,".PHP_EOL
                                ."      \"mappers\": [".PHP_EOL
                                ."          \"bookstore.xml\"".PHP_EOL
                                ."      ],".PHP_EOL
                                ."      \"connections\": {".PHP_EOL
                                ."          \"default\":  \"sqlite://default_db.sqlite\", ".PHP_EOL
                                ."          \"MySQL\":  \"mysql://username:password@localhost/development?charset=utf8\", ".PHP_EOL
                                ."          \"PostgreSQL\":  \"pgsql://username:password@localhost/development\", ".PHP_EOL
                                ."          \"SQLite\":  \"sqlite://development_database.db\", ".PHP_EOL
                                ."          \"SQLite_file\":  \"sqlite://unix(/var/www/html/database.sqlite)\", ".PHP_EOL
                                ."          \"oci\":  \"oci://username:passsword@localhost/xe\" ".PHP_EOL
                                ."      }".PHP_EOL
                                ."  },".PHP_EOL
                        .PHP_EOL."  \"security\": {".PHP_EOL
                                ."      \"serverPassword\": \"".$new_password."\",".PHP_EOL
                                ."      \"serverKey\": \"ServerKey\"".PHP_EOL
                                ."  },".PHP_EOL
                        .PHP_EOL."  \"cookies\": {".PHP_EOL
                                ."      \"cookiesPrefix\": \"GishikiCookie_\",".PHP_EOL
                                ."      \"cookiesEncryptedPrefix\": \",".base64_encode(openssl_random_pseudo_bytes(16))."\",".PHP_EOL
                                ."      \"cookiesKey\": \"".base64_encode(openssl_random_pseudo_bytes(256))."\",".PHP_EOL
                                ."      \"cookiesExpiration\": 5184000,".PHP_EOL
                                ."      \"cookiesPath\": \"/\"".PHP_EOL
                                ."  },".PHP_EOL
                        .PHP_EOL."  \"cache\": {".PHP_EOL
                                ."      \"enabled\": false,".PHP_EOL
                                ."      \"server\": \"memcached://localhost:11211\"".PHP_EOL
                                ."  }".PHP_EOL
                                ."}";
                if (file_put_contents(APPLICATION_DIR."settings.json", $configuration, LOCK_EX) === FALSE) {
                    $errors++;
                }
            }
            
            if ($errors == 0) {
                //force the settings refresh
                $environment = Environment::GetCurrentEnvironment();
                $configurationReload = new \ReflectionMethod($environment, "LoadConfiguration");
                $configurationReload->setAccessible(TRUE);
                $configurationReload->invoke($environment);
                
                //setup the application unique RSA encryption key
                $privateMasterKey = new \Gishiki\Security\AsymmetricPrivateKeyCipher();
                $privateMasterKey->ImportPrivateKey(\Gishiki\Security\AsymmetricCipher::GenerateNewKey(\Gishiki\Security\AsymmetricCipherAlgorithms::RSA4096));
                \Gishiki\Security\AsymmetricCipher::StorePrivateKey($privateMasterKey, "ServerKey", $new_password);
                \Gishiki\Security\AsymmetricCipher::StorePublicKey($privateMasterKey, "ServerKey");
                
                echo "<div><b>Installation</b> The base directory for a Gishiki application have been created successfully.
                <br />You can now create your special application.</div>";
            } else {
                echo "<div><b>Installation</b> The base directory for a Gishiki application cannot be created.
                <br />Please, fix directory permissions.</div>";
            }
            return ($errors == 0);
        }
    }
}