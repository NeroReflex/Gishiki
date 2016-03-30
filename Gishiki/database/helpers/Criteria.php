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
 * Relationship between a field and its value
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class Criteria {
    const EQUAL = 0;
    const NOT_EQUAL = 1;
    const GREATER_THAN = 2;
    const LESS_THAN = 3;
    const GREATER_EQUAL = 4;
    const LESS_EQUAL = 5;
    const LIKE = 6;
    const NOT_LIKE = 7;
    const IS_NULL = 8;
    const IS_NOT_NULL = 9;
    /*const REGEXP = 10;     */
}