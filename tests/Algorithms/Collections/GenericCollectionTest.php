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

namespace Gishiki\tests\Algorithms\Collections;

use Gishiki\Algorithms\Collections\GenericCollection;

class GenericCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testGenericCollectionIteration()
    {
        //this is the native collection
        $native_collection = array(
            'test1' => 7,
            'test2' => 'my string',
            0 => 'first',
            1 => 'third',
            'test3' => json_encode(
                    array(
                        'author' => 'Benato Denis',
                        'title' => 'Example Book',
                        'tags' => array(
                            'development', 'PHP', 'framework', 'Gishiki',
                        ),
                )), );

        //build a managed collection from the native one
        $collection_to_test = new GenericCollection($native_collection);

        //try rebuilding the collection iterating over each element
        $rebuilt_collection = array();
        foreach ($collection_to_test->getIterator() as $key => $value) {
            $rebuilt_collection[$key] = $value;
        }

        //test if the collection rebuild process has given the right result
        $this->assertEquals($rebuilt_collection, $native_collection);
    }

    public function testGenericCollectionNativeAsClasscall()
    {
        //this is the native collection
        $native_collection = array(
            'test1' => 7,
            'test2' => 'my string',
            'test3' => json_encode(
                    array(
                        'author' => 'Benato Denis',
                        'title' => 'Example Book',
                        'tags' => array(
                            'development', 'PHP', 'framework', 'Gishiki',
                        ),
                )),
            20 => 'testkey',
            );

        //build a managed collection from the native one
        $collection_to_test = new GenericCollection($native_collection);

        //try rebuilding the collection iterating over each element
        $rebuilt_collection = $collection_to_test();

        //test if the collection rebuild process has given the right result
        $this->assertEquals($rebuilt_collection, $native_collection);
    }
}
