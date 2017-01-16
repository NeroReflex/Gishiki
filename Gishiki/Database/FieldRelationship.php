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

namespace Gishiki\Database;

/**
 * A collection of relationship that can be applied to a field.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class FieldRelationship
{
    const EQUAL = '=';
    const NOT_EQUAL = '!=';
    const LESS_THAN = '<';
    const LESS_OR_EQUAL_THAN = '<=';
    const GREATER_THAN = '>';
    const GREATER_OR_EQUAL_THAN = '>=';
    const IN_RANGE = 'IN';
    const NOT_IN_RANGE = 'NOT IN';
    const LIKE = 'LIKE';
    const NOT_LIKE = 'NOT LIKE';
}
