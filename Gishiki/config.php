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



/* =============================================================================
 *                                                                              *
 *                          PHP General Configuration                           *
 *                                                                              *
 * ============================================================================ */

//$Development value sets the error reporting:
//TRUE  --> Development	--> ALL PHP errors are reported
//FALSE --> Production	--> No PHP errors are reported
$Development = TRUE; //BOOLEAN

//Should the gzip compression be used when possible?
$Compression = FALSE; //BOOLEAN

//The name of the directory for the caching engine (won't be used if using APC)
$ChachingDir = "cache"; //STRING


/* =============================================================================
 *                                                                              *
 *                          Security Settings                                   *
 *                                                                              *
 * ============================================================================ */

//the default password to crypt and decrypt content (at least 32 characters)
$DefaultPassword    = "*gV8iVW5oQSIncIZOcqYjVlgxARwt]6J"; //STRING

//the name of the directory that contains the keys for the asymmetric chiper
$keyDir             = "keys"; //STRING

//the name of the file, stored inside the keys directory that contains
//the application RSA key
$RSAServerUniqueKey = "ServerKey"; //STRING


/* =============================================================================
 *                                                                              *
 *                          Database Settings                                   *
 *                                                                              *
 * ============================================================================ */

//the name of the directory that contains sqlite databases
$databaseDir            = "databases"; //STRING

//the name of the directory that contains sqlite databases
$databaseConnectionsDir = "connections"; //STRING


/* =============================================================================
 *                                                                              *
 *                          MVC Structure                                       *
 *                                                                              *
 * ============================================================================ */

//the name of the directory that contains model, view and controller (must be placed in the root)
$applicationDir         = "application"; //STRING

//the name of the directory that contains controllers
$controllerDir          = "Controller"; //STRING

//the name of the directory that contains models
$modelDir               = "Model"; //STRING

//the name of the directory that contains views
$viewDir                = "View"; //STRING

//the name of the directory that contains classes
$classDir               = "Classes"; //STRING

//the name of the directory that contains files to be served to the client
$resourcesDir           = "Resources"; //STRING

/* =============================================================================
 *                                                                              *
 *                          Routing Configuration                               *
 *                                                                              *
 * ============================================================================ */

//turn ON and OFF the routing
$RoutingEnabled = TRUE; //BOOLEAN

//simple re-routing sobstitutions
$RoutingRules = array(
    "" => "Default/Index",
    "index.php" => "Default/Index",
); //ARRAY of sobstitution: index becomes value

//regexp re-routing sobstitutions
$RoutingRegex = array(
    "-(.*)/Default-" => "{1}/Index",
    "-(.*).php-" => "Default/Index/{1}.php",
); //ARRAY matched regexp are sobstituted with the regexp number, if the number is between '{' and '}'


/* =============================================================================
 *                                                                              *
 *                          Cookies Configuration                               *
 *                                                                              *
 * ============================================================================ */

//the prefix for each cookie manageable through the framework
$CookiesPrefix = "GishikiCookie_"; //STRING

//the prefix for each cookie manageable through the framework
//this prefix is added to the cookie to distinguish normal cookies from secure cookies
$CookiesEncryptedPrefix = "84RioJRQ3IkFimmeNA"; //STRING

//the key used to encrypt cookies (at least 32 characters)
$CookiesKey = "yu2RMp7okcdM9tFWnkRWPD3laK@HPP#F"; //STRING

//the default time (in seconds) that must pass to invalidate the cookie,
//if set to 0 the cookie will be a session cookie
$DefaultCookiesExpiration = 60*60*24*30; //INTEGER

//The path on the server in which the cookie will be available on,
//if set to "/", the cookie will be available within the entire domain.
//you should edit this value to match the framework path on your server.
$CookiesPath = "/"; //STRING