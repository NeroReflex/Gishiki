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

namespace Gishiki\tests\Application;

use Gishiki\HttpKernel\Response;
use Gishiki\HttpKernel\Request;
use Gishiki\Algorithms\Collections\GenericCollection;

/**
 * A controller that is not recognised as a test by PHPUnit.
 *
 * used to test some features of the framework
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class FakeController extends \Gishiki\Core\MVC\Controller
{
    public function myAction()
    {
        $this->Response->write('My email is: '.$this->Arguments->mail);
    }

    public static function quickAction(Request &$request, Response &$response, GenericCollection &$collection)
    {
        $response->write('should I send an email to '.$collection->mail.'?');
    }
}
