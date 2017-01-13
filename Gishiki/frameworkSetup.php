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

use Gishiki\Gishiki;
use Gishiki\Core\Route;
use Gishiki\HttpKernel\Request;
use Gishiki\HttpKernel\Response;
use Gishiki\Algorithms\Collections\SerializableCollection;

if (!defined('TESTING')) {
    Gishiki::Initialize();

    //if the xdebug plugin is enabled this is a development environment
    if (function_exists('xdebug_get_code_coverage')) {
        Route::get('/phpinfo', function (Request $request, Response &$response) {
            phpinfo();
        });
    } else {
        $response->withStatus(501);
        $response->write("Not implemented on the current machine");
    }
    
    Route::get('/info', function (Request $request, Response &$response) {
        $info = new SerializableCollection(
                [
                    'Framework' => 'Gishiki',
                    'Operating System' => php_uname(),
                    'PHP' => phpversion(),
                    'sapi' => php_sapi_name(),
                    'zend' => zend_version(),
                ]);

        //write the response
        $response->setSerializedBody($info);
    });

    Route::get('/serverkey', function (Request $request, Response &$response) {
        //get the serialized public key
        $serializedPubKey = (new \Gishiki\Security\Encryption\Asymmetric\PrivateKey())->exportPublicKey();

        //write the response
        $response->withHeader('Content-Type', 'application/x-pem-file');
        $response->withStatus(200);
        $response->write($serializedPubKey);
    });

    //run an instance of the application
    Gishiki::Run();
}
