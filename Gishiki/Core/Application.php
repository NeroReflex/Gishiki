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
            //get the json encoded application settings
            $settings_configuration = file_get_contents(APPLICATION_DIR."settings.json");
            
            //upload every environment constant
            if (file_exists(APPLICATION_DIR."environment.ini")) {
                $environmentConfiguration = parse_ini_file(APPLICATION_DIR."environment.ini");
                foreach ($environmentConfiguration as $env_const_name => $env_const_value)
                {
                    define($env_const_name, $env_const_value);
                    $settings_configuration = str_replace('{{@'.$env_const_name.'}}', $env_const_value, $settings_configuration);
                }
            }
            
            //update every environment placeholder
            while (true)
            {
                $to_be_replaced = get_string_between($settings_configuration, '{{@', '}}');
                if ($to_be_replaced == '') {
                    break;
                } else {
                    $settings_configuration = str_replace('{{@'.$to_be_replaced.'}}', getenv($to_be_replaced), $settings_configuration);
                }
            }
            
            //parse the settings file
            $appConfiguration = \Gishiki\JSON\JSON::DeSerialize($settings_configuration);
            
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
         * Check if the application to be executed exists, is valid and has the
         * configuration file
         * 
         * @return bool the application existence
         */
        public static function Exists()
        {
            //return the existence of an application directory and a configuratio file
            return ((file_exists(APPLICATION_DIR)) && (file_exists(APPLICATION_DIR."settings.json")));
        }
    }
}
