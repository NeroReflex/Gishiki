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
            //sarse the settings file
            $appConfiguration = parse_ini_file(APPLICATION_DIR."settings.ini", TRUE, INI_SCANNER_TYPED);
            
            //return the application configuration
            return $appConfiguration;
        }

        /**
         * Chack if the application to be executed exists, is valid and has the
         * configuration file
         * 
         * @return boolean the application existence
         */
        static function CheckExistence() {
            //return the existence of an application directory and a configuratio file
            return ((file_exists(APPLICATION_DIR)) && (file_exists(APPLICATION_DIR."settings.ini")));
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

            
            /*if (!Environment::GetCurrentEnvironment()->ExtensionSupport("SimpleXML")) {
                //update the number of errors
                $errors++;

                //show the error to the user
                echo "<div><b>SimpleXML:</b> In order to install and run Gishiki you need (at least) PHP 5.3 compiled with SimpleXML enabled.
                <br />Please, install a supported PHP version and retry.</div>";
            }
        
            if (!Environment::GetCurrentEnvironment()->ExtensionSupport("openssl")) {
                //update the number of errors
                $errors++;

                //show the error to the user
                echo "<div><b>OpenSSL:</b> OpenSSL PHP extension not installed, but it is required to install and run Gishiki.
                <br />Please, install the OpenSSL extension for PHP and retry.</div>";
            }*/
            
            if ((!file_exists(APPLICATION_DIR."interfaceControllers".DS)) && ($errors == 0)) {
                if (!@mkdir(APPLICATION_DIR."interfaceControllers".DS)) {
                    $errors++;
                }
            }

            if ((!file_exists(APPLICATION_DIR."webControllers".DS)) && ($errors == 0)) {
                if (!@mkdir(APPLICATION_DIR."webControllers".DS)) {
                    $errors++;
                }
            }
           
            if ((!file_exists(APPLICATION_DIR."Models".DS)) && ($errors == 0)) {
                if (!@mkdir(APPLICATION_DIR."Models".DS)) {
                    $errors++;
                }
            }
            
            if ((!file_exists(APPLICATION_DIR."Views".DS)) && ($errors == 0)) {
                if (!@mkdir(APPLICATION_DIR."Views".DS)) {
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
            
            if ((!file_exists(APPLICATION_DIR."Schemas".DS)) && ($errors == 0)) {
                if (!@mkdir(APPLICATION_DIR."Schemas".DS)) {
                    $errors++;
                }
            }
            
            //try to create the log file
            touch(APPLICATION_DIR."server_log.xml");
            
            $passiveRoutingSetup = 
                    '"" > "Default/Index"'.PHP_EOL.
                    '"index.php" > "Default/Index" ';
                
            $activeRoutingSetup =
                    '"-(.*)/Default-" > "{1}/Index"'.PHP_EOL.
                    '"-(.*).php-" > "Default/Index/{1}.php" ';
                
            $passiveRoutingConfigWrite = file_put_contents(APPLICATION_DIR."passive_rounting.cfg", $passiveRoutingSetup, LOCK_EX);
            $activeRoutingConfigWrite = file_put_contents(APPLICATION_DIR."active_routing.cfg", $activeRoutingSetup, LOCK_EX);
            if (($passiveRoutingConfigWrite === FALSE) || ($activeRoutingConfigWrite === FALSE)) {
                $errors++;
            }
            
            if ((!file_exists(Environment::GetCurrentEnvironment()->GetConfigurationProperty('APPLICATION_DIR')."settings.ini")) && ($errors == 0)) {
                $configuration = "[general]".PHP_EOL
                                ."development = on".PHP_EOL
                                ."compression = off".PHP_EOL
                        .PHP_EOL."; filesystem related settings".PHP_EOL
                                ."[filesystem]".PHP_EOL
                                ."interfaceControllersDirectory = \"interfaceControllers\"".PHP_EOL
                                ."webControllersDirectory = \"webControllers\"".PHP_EOL
                                ."modelsDirectory = \"Models\"".PHP_EOL
                                ."viewsDirectory = \"Views\"".PHP_EOL
                                ."schemataDirectory = \"Schemas\"".PHP_EOL
                                ."resourcesDirectory = \"Resources\"".PHP_EOL
                                ."keysDirectory = \"Keyring\"".PHP_EOL
                                ."logFile = \"server_log.xml\"".PHP_EOL
                        .PHP_EOL."; do not change the serverPassword as it is the key of the serverKey".PHP_EOL
                                ."[security]".PHP_EOL
                                ."serverPassword = \"".$new_password."\"".PHP_EOL
                                ."serverKey = \"ServerKey\"".PHP_EOL
                        .PHP_EOL."; cookies related settings".PHP_EOL
                                ."[cookies]".PHP_EOL
                                ."cookiesPrefix = \"GishikiCookie_\"".PHP_EOL
                                ."cookiesEncryptedPrefix = \"".base64_encode(openssl_random_pseudo_bytes(16))."\"".PHP_EOL
                                ."cookiesKey = \"".base64_encode(openssl_random_pseudo_bytes(256))."\"".PHP_EOL
                                ."cookiesExpiration = 5184000".PHP_EOL
                                ."cookiesPath = \"/\"".PHP_EOL
                        .PHP_EOL."; routing related settings".PHP_EOL
                                ."[routing]".PHP_EOL
                                ."routing = on".PHP_EOL
                                ."passiveRules = \"passive_rounting.cfg\"".PHP_EOL
                                ."activeRules = \"active_routing.cfg\"".PHP_EOL
                        .PHP_EOL."; database connection settings".PHP_EOL
                                ."[database]".PHP_EOL
                                ."connection = \"\""
                        .PHP_EOL."; caching related settings".PHP_EOL
                                ."[cache]".PHP_EOL
                                ."enabled = false".PHP_EOL
                                ."server = \"memcached://localhost:11211\"".PHP_EOL;
                if (file_put_contents(APPLICATION_DIR."settings.ini", $configuration, LOCK_EX) === FALSE) {
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