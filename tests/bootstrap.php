<?php
/**************************************************************************
Copyright 2017 Benato Denis

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

// Set timezone
date_default_timezone_set('America/New_York');
// Prevent session cookies
ini_set('session.use_cookies', 0);

define('TESTING', 'YES');

// Enable Composer autoloader
/** @var \Composer\Autoload\ClassLoader $autoloader */
$autoloader = require dirname(__DIR__).'/vendor/autoload.php';

// Register test classes
$autoloader->addPsr4('Gishiki\\Tests\\', __DIR__);

include __DIR__.'/Application/FakeController.php';

\Gishiki\Gishiki::Initialize();
