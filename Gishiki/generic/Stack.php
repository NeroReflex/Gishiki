<?php
/**************************************************************************
Copyright 2015 Benato Denis

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

/**
 * A stack emulator written in PHP
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class Stack {
    //the stack
    private $stack;
    
    //the stack limit number
    private $maxElements;
    
    /**
     * Create an empty stack
     * 
     * @param integer $levelsLimit the number of different stack levels
     */
    public function __construct($levelsLimit = NULL) {
        //check for levelsLimit correctness
        $levelsLimitType = gettype($levelsLimit);
        if (($levelsLimitType != "integer") && ($levelsLimitType != "NULL")) {
            throw new StackException("Invalid stack levels limit number", 1);
        }
        
        //initialize an empty array
        $this->stack = array();
        
        //save the stack limit level
        $this->maxElements = $levelsLimit;
        
        //start from a zero-length stack
        $this->Clear();
    }
    
    /**
     * Push data to the stack (add the given value at the top of the stack)
     * 
     * @param anytype $data the data to be stored inside the stack
     */
    public function PUSH($data) {
        if (($this->GetLength() == $this->maxElements) && (gettype($this->maxElements) == "integer")) {
            throw new StackException("Stack overflow, levels limit reached", 2);
        }
        //update the stack
        array_unshift($this->stack, $data);
    }
    
    /**
     * Retrive the value at the top of the stack and delete it from the stack
     * 
     * @return anytype the value at the top of the stack
     */
    public function POP() {
        if (!$this->IsEmpty()) {
            //return the retrived data
            return array_shift($this->stack);
        } else {
            //throw an exception if the stack is empty
            throw new StackException("Stack is empty", 3);
        }
    }
    
    /**
     * Return the value at the top of the stack without removing it
     * 
     * @return anytype the value at the top of the stack (or NULL if empty)
     */
    public function TOP() {
        if (!$this->IsEmpty()) {
            return current($this->stack);
        } else {
            //throw an exception if the stack is empty
            throw new StackException("Stack is empty", 3);
        }
    }
    
    /**
     * Delete every element from the stack
     */
    public function Clear() {
        //update the stack length
        $this->stack = array();
    }
    
    /**
     * Get the number of stored elements
     * 
     * @return integer the stack length/number of stored elements
     */
    public function GetLength()
    {
        //return the stack length
        return count($this->stack);
    }
    
    /**
     * Return true if the stack is empty, false otherwise
     * 
     * @return boolean the emptiness of the stack
     */
    public function IsEmpty() {
        //return true only if the stack is empty
        return empty($this->stack);
    }
    
    /**
     * Revert the order of the stack elements
     */
    public function Revert() {
        //revert the array used to hold the stack in memory
        $this->stack = array_reverse($this->stack);
    }
}
