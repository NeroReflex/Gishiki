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
        return Environment::GetCurrentEnvironment()->GetConfigurationProperty('ROUTING_ENABLED');
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
        $RoutingRules = Environment::GetCurrentEnvironment()->GetConfigurationProperty('ROUTING_CONSTANT_CONFIGURATION');
        
        //import the regex routing rules set
        $RoutingRegex = Environment::GetCurrentEnvironment()->GetConfigurationProperty('ROUTING_ACTIVE_CONFIGURATION');
        
        //try a mathing with the standard rules
        if ((isset($RoutingRules[$currentRoute])) && ($RoutingRules[$currentRoute] != '') && ($RoutingRules[$currentRoute] != NULL)) {
            //return the routing result
            return $RoutingRules[$currentRoute];
        }
        
        /*          check for regexp matching             */
        
        //start cycling for each regex
        reset($RoutingRegex);
            
        while ($current = current($RoutingRegex)) {
            //get the regex that must be match to complete routing
            $regexToBeMatched = key($RoutingRegex);
                
            $matches = array();
            
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
            
            //jump to the next regexp routing
            next($RoutingRegex);
        }
        
        //if nothing has matched return the given route
        return $currentRoute;
    }
}
