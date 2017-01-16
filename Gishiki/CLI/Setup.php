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

use \Gishiki\Algorithms\Collections\SerializableCollection;
use \Gishiki\Security\Encryption\Asymmetric\PrivateKey;
use \Gishiki\Security\Encryption\Symmetric\SecretKey;

function Setup()
{
    if (file_exists("application")) {
        printf("An application already exists!\n");
        exit();
    }
    
    if (!mkdir("application")) {
        printf("The application directory cannot be created\n");
        exit();
    }
    
    if (!mkdir("application/Controllers")) {
        printf("The Controllers directory cannot be created\n");
        exit();
    }

    //generate a new private key
    try {
        if (file_put_contents("application/private_key.pem", PrivateKey::Generate(PrivateKey::RSA4096)) === false) {
            printf("The application private key cannot be written\n");
            exit();
        }
    } catch (\Gishiki\Exception $ex) {
        printf("The private key cannot be generated\n");
        exit();
    }

    //generate a new configuration file
    try {
        $settings = new SerializableCollection([
            "general" => [
                "development" => true,
                "autolog" => "stream://error"
            ],
            "security" => [
                "serverKey" => "file://application/private_key.pem",
                "serverPassword" => SecretKey::Generate(openssl_random_pseudo_bytes(32), 32),
            ],
            "connections" => [
                [
                    "name" => "default",
                    "query" => "sqlite://application/default.sqlite"
                ]
            ]
        ]);

        if (file_put_contents("application/settings.json", $settings->serialize(SerializableCollection::JSON)) === false) {
            printf("The application configuration cannot be written\n");
            exit();
        }
    } catch (\Gishiki\Exception $ex) {
        printf("The application configuration cannot be generated\n");
        exit();
    }

    $router_file = "<?php".PHP_EOL.
    "use Gishiki\Core\Route;".PHP_EOL.
    "use Gishiki\HttpKernel\Request;".PHP_EOL.
    "use Gishiki\HttpKernel\Response;".PHP_EOL.
    "use Gishiki\Algorithms\Collections\SerializableCollection;".PHP_EOL.
    PHP_EOL.PHP_EOL.
    "Route::get(\"/\", function (Request &\$request, Response &\$response) {".PHP_EOL.
    "    \$result = new SerializableCollection([".PHP_EOL.
    "        \"timestamp\" => time()".PHP_EOL.
    "    ]);".PHP_EOL.
    PHP_EOL.
    "    //send the response to the client".PHP_EOL.
    "    \$response->setSerializedBody(\$result);".PHP_EOL.
    "});".PHP_EOL.
    PHP_EOL.PHP_EOL.
    "Route::any(Route::NOT_FOUND, function (Request &\$request, Response &\$response) {".PHP_EOL.
    "    \$result = new SerializableCollection([".PHP_EOL.
    "        \"error\" => \"Not Found\",".PHP_EOL.
    "        \"timestamp\" => time()".PHP_EOL.
    "    ]);".PHP_EOL.
    PHP_EOL.
    "    //send the response to the client".PHP_EOL.
    "    \$response->setSerializedBody(\$result);".PHP_EOL.
    "});".PHP_EOL;

    if (file_put_contents("application/routes.php", $router_file) === false) {
        printf("The application router file cannot be written\n");
        exit();
    }


    //congrats! The project has been initialized
    printf("An empty project has been created\n");
}
