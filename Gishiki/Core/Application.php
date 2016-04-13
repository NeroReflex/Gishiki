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
    abstract class Application
    {
        
        /**
         * Read the application configuration (settings.ini) and return the 
         * parsing result
         * 
         * @return array the application configuration
         */
        public static function GetSettings()
        {
            //parse the settings file
            $appConfiguration = \Gishiki\JSON\JSON::DeSerialize(file_get_contents(APPLICATION_DIR."settings.json"));
            
            //return the application configuration
            return $appConfiguration;
        }
        
        /**
         * Start the Object-relational mapping bundled with Gishiki:
         *      -   Execute the AOT component to generate the PHP code (if needed)
         *      -   Include the generated php code
         *      -   Perform any additional setup operations
         */
        public static function StartORM()
        {
            //load every database connection
            \Gishiki\ActiveRecord\ConnectionsProvider::RegisterGroup(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty("DATA_CONNECTIONS"));
            
            //load every model in the models directory
            foreach (glob(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty("MODEL_DIR")."/*.php") as $filename) {
                include($filename);
            }
        }
        
        /**
         * Chack if the application to be executed exists, is valid and has the
         * configuration file
         * 
         * @return bool the application existence
         */
        public static function Exists()
        {
            //return the existence of an application directory and a configuratio file
            return ((file_exists(APPLICATION_DIR)) && (file_exists(APPLICATION_DIR."settings.json")));
        }

        /**
         * Setup a new empty application structure/skeleton
         * 
         * @return int the number of errors occurred
         */
        public static function CreateNew()
        {
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
                    file_put_contents(APPLICATION_DIR.".htaccess", "Deny from all", LOCK_EX);
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
                } elseif (file_put_contents(APPLICATION_DIR."Models".DS."Book.php", file_get_contents(ROOT."Gishiki".DS."Core".DS."example_app".DS."book_model.php"), LOCK_EX) === false) {
                    $errors++;
                }
            } else {
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
            
            if (file_put_contents(APPLICATION_DIR."routes.php", file_get_contents(ROOT."Gishiki".DS."Core".DS."example_app".DS."routes.php"), LOCK_EX) === false) {
                $errors++;
            }
            
            if (in_array("sqlite", \PDO::getAvailableDrivers())) {
                try {
                    //create a new example db
                    $example_db = new \PDO("sqlite:default_db.sqlite");
                    
                    //this is the query for the creation of the example table
                    $example_db->exec("CREATE TABLE IF NOT EXISTS 'books' ('id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,'isbn' TEXT, 'title' TEXT, 'author' TEXT, 'price' REAL, 'publication_date' DATETIME)");
                } catch (\PDOException $ex) {
                    new \Gishiki\Logging\Log("Error in the default db", "The following error was encountered while creating the default database: ".$ex->getMessage(), \Gishiki\Logging\Priority::WARNING);
                }
            }
            
            if ((!file_exists(Environment::GetCurrentEnvironment()->GetConfigurationProperty('APPLICATION_DIR')."settings.json")) && ($errors == 0)) {
                $configuration = file_get_contents(ROOT."Gishiki".DS."Core".DS."example_app".DS."settings.json");
                $configuration = str_replace("\"SECURITY\":\"SETTINGS_HERE!\"",
                                 "\"security\": {".PHP_EOL
                                ."        \"serverPassword\": \"".$new_password."\",".PHP_EOL
                                ."        \"serverKey\": \"ServerKey\"".PHP_EOL
                                ."    },".PHP_EOL
                        .PHP_EOL."    \"cookies\": {".PHP_EOL
                                ."        \"cookiesPrefix\": \"GishikiCookie_\",".PHP_EOL
                                ."        \"cookiesEncryptedPrefix\": \",".base64_encode(openssl_random_pseudo_bytes(16))."\",".PHP_EOL
                                ."        \"cookiesKey\": \"".base64_encode(openssl_random_pseudo_bytes(256))."\",".PHP_EOL
                                ."        \"cookiesExpiration\": 5184000,".PHP_EOL
                                ."        \"cookiesPath\": \"/\"".PHP_EOL
                                ."    }".PHP_EOL, $configuration) ;
                        
                if (file_put_contents(APPLICATION_DIR."settings.json", $configuration, LOCK_EX) === false) {
                    $errors++;
                }
            }
            
            if ($errors == 0) {
                //force the settings refresh
                $environment = Environment::GetCurrentEnvironment();
                $configurationReload = new \ReflectionMethod($environment, "LoadConfiguration");
                $configurationReload->setAccessible(true);
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
