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

/* The first operations to execute is figuring out directory separator character
 * and the root path (the path Gishiki is installed) */

//get directory separator
if (!defined('DS')) {
    if (defined('DIRECTORY_SEPARATOR')) {
        define('DS', DIRECTORY_SEPARATOR);
    } else {
        define('DS', "/");
    }
}

//get the root path
if ((!defined('ROOT')) || (ROOT == "") || (ROOT == null)) {
    define('ROOT', realpath(__DIR__).DS);
}

$composer_autoloader = __DIR__.'/vendor/autoload.php';

//include the base application and perform basic operations
if (file_exists($composer_autoloader)) {
    require __DIR__.'/vendor/autoload.php';
} else {
    die('Gishiki cannot run until is is installed using composer!');
}

//start the framework
\Gishiki\Gishiki::Initialize();

//run an instance of the application
\Gishiki\Gishiki::Run();
