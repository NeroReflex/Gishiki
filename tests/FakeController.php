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

use Gishiki\Core\MVC\Controller\Controller;
use Gishiki\Algorithms\Collections\GenericCollection;

/**
 * A controller that is not recognised as a test by PHPUnit.
 *
 * used to test some features of the framework
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class FakeController extends Controller
{
    public static function generateTestingData()
    {
        srand(null);

        $data = [
            "int_test" => rand(0, 150),
            "str_test" => base64_encode(openssl_random_pseudo_bytes(32)),
            "float_test" => rand(0, 3200) + (rand(0, 9) / 10),
            "array_test" => [
                base64_encode(openssl_random_pseudo_bytes(32)),
                base64_encode(openssl_random_pseudo_bytes(32)),
                base64_encode(openssl_random_pseudo_bytes(32)),
                base64_encode(openssl_random_pseudo_bytes(32))
            ],
        ];

        return $data;
    }

    public function none()
    {

    }

    public function do()
    {
        $this->response->getBody()->write('Th1s 1s 4 t3st');
    }

    public function myAction()
    {
        $this->response->getBody()->write('My email is: '.$this->arguments->get('mail'));
    }

    public function quickAction()
    {
        if (!($this->arguments instanceof GenericCollection)) {
            throw new \RuntimeException("something went wrong");
        }

        $this->response->getBody()->write('should I send an email to '.$this->arguments->get('mail').'?');
    }

    public function exceptionTest()
    {
        throw new \Gishiki\Core\Exception("testing exception", 0);
    }

    public function completeTest()
    {
        $this->getResponse()->getBody()->write("bye bye ".$this->arguments->get('name'));
    }

    public function customNotFound()
    {
        $this->getResponse()->getBody()->write("404 - Not Found (Custom :))");
    }
}
