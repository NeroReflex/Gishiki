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

namespace Gishiki\Algorithms\Collections;

/**
 * A data collection of ordered values managed as a stack (FIFO structure).
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class StackCollection extends GenericCollection
{
    /**
     * @var int the number of maximum elements, or a negative number for no limits
     */
    protected $limit;

    /**
     * StackCollection constructor.
     *
     * @param int   $limit the number of maximum numbers (or a negative number for no limits)
     * @param array $items collection of values to initialize the stack with
     * @throws \InvalidArgumentException an invalid collection was given
     */
    public function __construct($items = [], $limit = -1)
    {
        //check if the given items list is a valid items list
        if (!is_array($items)) {
            throw new \InvalidArgumentException('The collection of properties and nested data must be expressed as an array');
        }

        if (!is_integer($limit)) {
            throw new \InvalidArgumentException('The maximum amount of items must be expressed with an integer number');
        }

        foreach ($items as $value) {
            $this->push($value);
        }

        //stack can only contain this many items
        $this->limit = $limit;
    }

    /**
     * Insert an element on the top of the FIFO structure.
     *
     * @param  mixed $item the element to be inserted
     * @throws StackException the stack is on its limit
     */
    public function push($item)
    {
        //trap for stack overflow
        if (($this->limit > 0) && (count($this->data) >= $this->limit)) {
            throw new StackException('The stack collection is full', 1);
        }

        //prepend item to the start of the array
        array_unshift($this->data, $item);
    }

    /**
     * Remove the element at the top of the stack and return it.
     *
     * @return mixed the element at the top of the stack
     * @throws StackException the stack is empty
     */
    public function pop()
    {
        if ($this->empty()) {
            //trap for stack underflow
            throw new StackException('The stack collection is empty', 2);
        }

        //pop item from the start of the array
        return array_shift($this->data);
    }

    /**
     * Reverse the order of the whole structure.
     */
    public function reverse()
    {
        $this->data = array_reverse($this->data);
    }

    /**
     * Get the element at the top of the stack.
     *
     * @return mixed the last pushed element
     */
    public function top()
    {
        return current($this->data);
    }

    /**
     * @throws StackException Invalid function in a stack
     */
    public function set($key, $value)
    {
        throw new StackException('The stack collection cannot be modified calling the set function', 3);
    }

    /**
     * @throws StackException Invalid function in a stack
     */
    public function get($key, $default = null)
    {
        throw new StackException('The FIFO stack collection order cannot be violated calling the get function', 3);
    }
}