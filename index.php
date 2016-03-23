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

//turn off all error reporting
error_reporting(0);

//turn on all error reporting
ini_set("display_errors", 1);
error_reporting(E_ALL);

//change the character encoding
mb_internal_encoding("UTF-8");

/* The first operations to execute is figuring out directory separator character and the root path (the path Gishiki is installed) */

//get directory separator
if (!defined('DS')) {
    if (defined('DIRECTORY_SEPARATOR'))
    {   define('DS',DIRECTORY_SEPARATOR);   }
    else
    {   define('DS', "/");  }
}

//get the root path
if ((!defined('ROOT')) || (ROOT == "") || (ROOT == NULL))
{    define('ROOT', realpath(__DIR__).DS);   }

//include the base application and perform basic operations
include(ROOT."Gishiki".DS."Gishiki.inc");

//what action was required?
$action = "";

//get the requested resource
if ((isset($_GET["rewritten"])) && ($_GET["rewritten"] == "true")) {
    //read the requested resource if the mod_rewrite (or any rewrite module) has been used 
    $CurrentScript = $_SERVER['PHP_SELF'];
    $URL = urldecode($_SERVER["REQUEST_URI"]);
    
    for ($i = (strlen($CurrentScript) - 1); $i >= 0; $i--) {
        if ($CurrentScript[$i] == '/') {
            $CurrentScriptPath = substr($CurrentScript, 0, ($i + 1));
            if (($CurrentScriptPath != '') && ($CurrentScriptPath != '/')) {
                $position = strpos($URL, $CurrentScriptPath);
                if ($position !== FALSE) {
                    $action = substr($URL, $position + strlen($CurrentScriptPath));
                } else {
                    exit("unexpected PHP behaviour!");
                }
            } else {
                $action = $URL;
            }
            break;
        }
    }
} else {
    //read the requested resource if the mod_rewrite was not used
    $action = $_GET["action"];
}
    
//start the framework
Gishiki::Initialize();

//if the framework needs to be installed.....
if (!\Gishiki\Core\Application::Exists())
{
    //setup the new application
    if (\Gishiki\Core\Application::CreateNew()) {
        
    } else {
        exit("<div><br /><b>Check for the environment, delete the created application directory and retry the installation</b></div>");
    }
} else {
    //run an instance of the application
    Gishiki::Run($action);
}