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
     * The Gishiki Routing helper
     *
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    abstract class Routing {

        /**
         * Check for the routing status
         * 
         * @return boolean TRUE if routing is enabled, false otherwise
         */
        static function IsEnabled() {
            //return the routing feature status
            return \Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('ROUTING_ENABLED');
        }

        /**
         * Load the given routing configuration file and return its content on an 
         * array that can be used by the Routing::Route() function
         * 
         * @param string $routingFile the real path of the routing configuration file
         * @return array the set o routing rules
         */
        static function GetConfiguration($routingFile) {
            //make sure the routing configuration file does exists
            if ((gettype($routingFile) != "string") || (!file_exists($routingFile))) {
                exit("Unable to obtain the routing configuration");
            }
            
            //setup the routing rules structure
            $routingRules = [];
            
            //try to open the routing rules file
            $routingRulesFile = fopen($routingFile, "r");
            if (!$routingRulesFile) {
                exit("Invalid routing file");
            }
            
            /*  what is the 'lexer' reading? 
             * 0 => nothing (clean)
             * 1 => the first string
             * 2 => waiting for a '>' after the closing '"'
             * 3 => waiting for a '"' after the '>' char
             * 4 => the second string
             */
            $reading = 0;
            
            $firstString = "";
            $secondString = "";
            
            //read the file for routing rules
            while (!feof($routingRulesFile)) {
                $currentChar = fgetc($routingRulesFile);
                
                switch ($reading) {
                    case 0:
                        if ($currentChar == '"') {
                            $reading++;
                        }
                        break;
                    
                    case 1:
                        if ($currentChar == '"') {
                            $reading++;
                        } else {
                            $firstString = $firstString.$currentChar;
                        }
                        break;
                        
                    case 2:
                        if ($currentChar == '>') {
                            $reading++;
                        }
                        break;
                        
                    case 3:
                        if ($currentChar == '"') {
                            $reading++;
                        }
                        break;
                    case 4:
                        if ($currentChar == '"') {
                            $reading = 0;
                            
                            //save the read routing configuration
                            $routingRules[$firstString] = $secondString;
                            
                            //reset both strings
                            $secondString = "";
                            $firstString = "";
                        } else {
                            $secondString = $secondString.$currentChar;
                        }
                        break;
                        
                    default:
                        exit("bad error. Stop.");
                        break;
                }
            }
            
            //close the file
            fclose($routingRulesFile);
            
            //return the set of rules
            return $routingRules;
        }
        
        /**
         * Scan a requested resource and re-route using a easy mathing OR a regexp
         * routing system.
         * 
         * @param type $currentRoute the routing input
         * @return string the routing result (may be the routing input)
         */
        static function Route($currentRoute) {
            //import the standard routing rules set
            $RoutingRules = \Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('ROUTING_CONSTANT_CONFIGURATION');

            //import the regex routing rules set
            $RoutingRegex = \Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('ROUTING_ACTIVE_CONFIGURATION');

            //try a mathing with the standard rules
            if ((isset($RoutingRules[$currentRoute])) && ($RoutingRules[$currentRoute] != '') && ($RoutingRules[$currentRoute] != NULL)) {
                //return the routing result
                return $RoutingRules[$currentRoute];
            }

            /*          check for regexp matching             */

            //start cycling for each regex
            reset($RoutingRegex);
            $i = 0;
            while ($i < count($RoutingRegex)) {
                $current = current($RoutingRegex);
                
                //get the regex that must be match to complete routing
                $regexToBeMatched = key($RoutingRegex);

                $matches = [];

                $regexMathingResult = preg_match($regexToBeMatched, $currentRoute, $matches);

                //check for the regex mathing
                if ($regexMathingResult == 1) {
                    $response = $current;

                    //and update the regexp request
                    for ($j = 1; $j < (count($matches)); $j++) {
                        $response = str_replace("{". $j ."}", $matches[$j], $response);
                    }

                    return $response;
                } else if ($regexMathingResult === FALSE) {

                }

                //update the counter
                $i++;
                
                //jump to the next regexp routing
                next($RoutingRegex);
            }

            //if nothing has matched return the given route
            return $currentRoute;
        }
    }
}