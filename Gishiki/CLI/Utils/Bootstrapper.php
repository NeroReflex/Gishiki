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

namespace Gishiki\CLI\Utils;

use Gishiki\Algorithms\Collections\SerializableCollection;
use Gishiki\Security\Encryption\Asymmetric\PrivateKey;
use Gishiki\Security\Encryption\Symmetric\SecretKey;

final class Bootstrapper
{
    public function controller($controllerName)
    {
        if (!file_exists('Controllers')) {
            throw new \Exception('The Controllers directory doesn\'t exists');
        }
        
        if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $controllerName)) {
            throw new \Exception('The controller name is not valid');
        }
        
        if (file_exists('Controllers'.DS.$controllerName.'.php')) {
            throw new \Exception('A controller with the same name already exists');
        }
        
        $controllerText =
                '<?php'.PHP_EOL.PHP_EOL.
                'use Gishiki\Core\MVC\Controller'.PHP_EOL.PHP_EOL.
                'final class '.$controllerName.' extends Controller'.PHP_EOL.
                '{'.PHP_EOL.
                '    function index()'.PHP_EOL.
                '    {'.PHP_EOL.
                '    }'.PHP_EOL.
                '}';
        
        if (file_put_contents('Controllers'.DS.$controllerName.'.php', $controllerText) === false) {
            throw new \Exception('The new controller file cannot be written');
        }
    }
    
    public function application()
    {
        if ((!mkdir('Controllers'))) {
            throw new \Exception('The Controllers directory cannot be created');
        }
        
        if ((!mkdir('Models'))) {
            throw new \Exception('The Models directory cannot be created');
        }
        
        if (file_exists('composer.json')) {
            //composer have to autoload Controllers and Models
            $deserComposer = SerializableCollection::deserialize(file_get_contents('composer.json'), SerializableCollection::JSON);
            if (!$deserComposer->has('autoload')) {
                $deserComposer->set('autoload', ['classmap' => [ 'Controllers', 'Models']]);
                file_put_contents('composer.json', $deserComposer->serialize(SerializableCollection::JSON));
            }
        }

        //generate a new private key
        try {
            if (file_put_contents('private_key.pem', PrivateKey::Generate(PrivateKey::RSA4096)) === false) {
                throw new \Exception('The application private key cannot be written');
            }
        } catch (\Gishiki\Exception $ex) {
            throw new \Exception('The private key cannot be generated');
        }

        //generate a new configuration file
        try {
            $settings = new SerializableCollection([
                'general' => [
                    'development' => true,
                    'autolog' => 'stream://error',
                ],
                'security' => [
                    'serverKey' => 'file://private_key.pem',
                    'serverPassword' => SecretKey::Generate(openssl_random_pseudo_bytes(32), 32),
                ],
                'connections' => [
                    [
                        'name' => 'default',
                        'query' => 'sqlite://default.sqlite',
                    ],
                ],
            ]);

            if (file_put_contents('settings.json', $settings->serialize(SerializableCollection::JSON)) === false) {
                throw new \Exception('The application configuration cannot be written');
            }
        } catch (\Gishiki\Exception $ex) {
            throw new \Exception('The application configuration cannot be generated');
        }

        $router_file =
        '<?php'.PHP_EOL.PHP_EOL.
        "require __DIR__.'/vendor/autoload.php';".PHP_EOL.PHP_EOL.
        "use Gishiki\Core\Route;".PHP_EOL.
        "use Gishiki\HttpKernel\Request;".PHP_EOL.
        "use Gishiki\HttpKernel\Response;".PHP_EOL.
        "use Gishiki\Algorithms\Collections\SerializableCollection;".PHP_EOL.
        "use Gishiki\Gishiki;".PHP_EOL.
        PHP_EOL.PHP_EOL.
        'Route::get("/", function (Request &$request, Response &$response) {'.PHP_EOL.
        '    $result = new SerializableCollection(['.PHP_EOL.
        '        "timestamp" => time()'.PHP_EOL.
        '    ]);'.PHP_EOL.
        PHP_EOL.
        '    //send the response to the client'.PHP_EOL.
        '    $response->setSerializedBody($result);'.PHP_EOL.
        '});'.PHP_EOL.
        PHP_EOL.PHP_EOL.
        'Route::any(Route::NOT_FOUND, function (Request &$request, Response &$response) {'.PHP_EOL.
        '    $result = new SerializableCollection(['.PHP_EOL.
        '        "error" => "Not Found",'.PHP_EOL.
        '        "timestamp" => time()'.PHP_EOL.
        '    ]);'.PHP_EOL.
        PHP_EOL.
        '    //send the response to the client'.PHP_EOL.
        '    $response->setSerializedBody($result);'.PHP_EOL.
        '});'.PHP_EOL.PHP_EOL.
        '//this triggers the framework execution'.PHP_EOL.
        'Gishiki::Run();'.PHP_EOL;

        if (file_put_contents('index.php', $router_file) === false) {
            throw new \Exception('The application router file cannot be written');
        }
    }
}
