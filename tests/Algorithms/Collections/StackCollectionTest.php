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

use Gishiki\Algorithms\Collections\StackCollection;
use Gishiki\Algorithms\Collections\StackException;

class StackCollectionTest extends TestCase
{
    public function testPush()
    {
        $collection = new StackCollection();

        $collection->push(null);
        $collection->push(5);
        $collection->push('Hello, World!');
        $this->assertEquals('Hello, World!', $collection->top());
        $this->assertEquals('Hello, World!', $collection->pop());
    }

    public function testUnderflow()
    {
        $this->expectException(StackException::class);

        $collection = new StackCollection();
        $collection->pop();
    }

    public function testOverflow()
    {
        $this->expectException(StackException::class);

        $collection = new StackCollection([1], 1);
        $collection->push(2);
    }

    public function testInvalidSet()
    {
        $this->expectException(StackException::class);

        $collection = new StackCollection();
        $collection->set(0, 'not working');
    }

    public function testInvalidGet()
    {
        $this->expectException(StackException::class);

        $collection = new StackCollection(['not working']);
        $collection->get(0);
    }

    public function testReverse()
    {
        $collection = new StackCollection([1, 3, 5]);
        $this->assertEquals(5, $collection->top());

        $collection->push(null);

        $collection->reverse();

        $this->assertEquals(1, $collection->pop());
        $this->assertEquals(3, $collection->pop());
        $this->assertEquals(5, $collection->pop());

    }

    public function testBadConstructorParam()
    {
        $this->expectException(\InvalidArgumentException::class);

        $collection = new StackCollection(null);
    }

    public function testBadConstructorLimit()
    {
        $this->expectException(\InvalidArgumentException::class);

        $collection = new StackCollection([], null);
    }
}