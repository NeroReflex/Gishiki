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

use PHPUnit\Framework\TestCase;

use Gishiki\Algorithms\Collections\GenericCollection;

class GenericCollectionTest extends TestCase
{
    public function testInvalidCreation() {
        $this->expectException(\InvalidArgumentException::class);

        new GenericCollection(null);
    }

    public function testIteration()
    {
        //this is the native collection
        $native_collection = [
            'test1' => 7,
            'test2' => 'my string',
            0 => 'first',
            1 => 'third',
            'test3' => json_encode(
                    [
                        'author' => 'Benato Denis',
                        'title' => 'Example Book',
                        'tags' => [
                            'development', 'PHP', 'framework', 'Gishiki',
                        ],
                ]), ];

        //build a managed collection from the native one
        $collection_to_test = new GenericCollection($native_collection);

        //try rebuilding the collection iterating over each element
        $rebuilt_collection = [];
        foreach ($collection_to_test->getIterator() as $key => $value) {
            $rebuilt_collection[$key] = $value;
        }

        //test if the collection rebuild process has given the right result
        $this->assertEquals($rebuilt_collection, $native_collection);
    }

    public function testObjectNotation()
    {
        $collection = new GenericCollection([
            'tags' => 'tag1'
        ]);

        $this->assertEquals('tag1', $collection->tags);

        $collection->tags = ['tag1', 'tag2'];

        $this->assertEquals(['tag1', 'tag2'], $collection->tags);
    }

    public function testClear()
    {
        $arr = [
            "foo" => "bar",
            42    => 24,
            "multi" => [
                "dimensional" => [
                    "array" => "foo"
                ]
            ]];

        $collection = new GenericCollection($arr);

        $this->assertEquals($arr, $collection->all());

        $collection->clear();

        $this->assertEquals([], $collection->all());
    }

    public function testKeys()
    {
        $collection = new GenericCollection([
            "key1" => "val1",
            "Key2" => "val2",
            "Key3" => [
                "Val3",
                null
            ]
        ]);

        $this->assertEquals(["key1", "Key2", "Key3"], $collection->keys());
    }

    public function testReplace()
    {
        $arr = [
            "v1" => 10,
            "v2" => 3,
            "v3" => 4.5,
            "v4" => 9,
            "v5" => 430,
            "v7" => 0.3
        ];

        $collection = new GenericCollection($arr);

        foreach ($arr as &$current) {
            $current += 1.25;
        }

        $collection->replace($arr);

        $this->assertEquals($arr, $collection->all());

        $this->assertEquals(6, $collection->count());
    }

    public function testNativeAsClassCall()
    {
        //this is the native collection
        $native_collection = [
            'test1' => 7,
            'test2' => 'my string',
            'test3' => json_encode(
                    [
                        'author' => 'Benato Denis',
                        'title' => 'Example Book',
                        'tags' => [
                            'development', 'PHP', 'framework', 'Gishiki',
                        ],
                ]),
            20 => 'testkey',
            ];

        //build a managed collection from the native one
        $collection_to_test = new GenericCollection($native_collection);

        //try rebuilding the collection iterating over each element
        $rebuilt_collection = $collection_to_test();

        //test if the collection rebuild process has given the right result
        $this->assertEquals($rebuilt_collection, $native_collection);
    }
}
