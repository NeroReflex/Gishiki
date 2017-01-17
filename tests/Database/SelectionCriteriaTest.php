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

namespace Gishiki\tests\Database;

use Gishiki\Database\SelectionCriteria;
use Gishiki\Database\FieldRelationship;

/**
 * The tester for the SelectionCriteria class.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class SelectionCriteriaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    function testBadNameAnd() {
        SelectionCriteria::Select([ 'a' => [3, 5, 6]])->and_where(3, FieldRelationship::EQUAL, "");
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    function testBadRelationshipAnd() {
        SelectionCriteria::Select([ 'a' => [3, 5, 6]])->and_where('a', 'IDK', "");
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    function testBadValueAnd() {
        SelectionCriteria::Select([ 'a' => [3, 5, 6]])->and_where('a', FieldRelationship::EQUAL, new SelectionCriteria());
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    function testBadValueOr() {
        SelectionCriteria::Select([ 'a' => [3, 5, 6]])->or_where('a', FieldRelationship::EQUAL, new SelectionCriteria());
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    function testBadNameOr() {
        SelectionCriteria::Select([ 'a' => [3, 5, 6]])->or_where(3, FieldRelationship::EQUAL, "");
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    function testBadRelationshipOr() {
        SelectionCriteria::Select([ 'a' => [3, 5, 6]])->or_where('a', 'IDK', "");
    }
    
    function testInitializerOnly() {
        $sc = SelectionCriteria::Select([ 'a' => [3, 5, 6], 'b' => 96]);
        
        $exportMethod = new \ReflectionMethod($sc, 'export');
        $exportMethod->setAccessible(true);
        $resultModifierExported = $exportMethod->invoke($sc);
        
        $this->assertEquals([
            'historic' => [ 128, 129 ],
            'criteria' => [
                'and' => [
                    [
                        0 => 'a',
                        1 => FieldRelationship::IN_RANGE,
                        2 => [3, 5, 6]
                    ],
                    [
                        0 => 'b',
                        1 => FieldRelationship::EQUAL,
                        2 => 96
                    ]
                ],
                'or' => []
            ]
        ], $resultModifierExported);
    }
    
    function testOrAfterInitializer() {
        $sc = SelectionCriteria::Select([ 'a' => [3, 5, 6], 'b' => 96])->or_where('c', FieldRelationship::LIKE, '%test%');
        
        $exportMethod = new \ReflectionMethod($sc, 'export');
        $exportMethod->setAccessible(true);
        $resultModifierExported = $exportMethod->invoke($sc);
        
        $this->assertEquals([
            'historic' => [ 128, 129, 0 ],
            'criteria' => [
                'and' => [
                    [
                        0 => 'a',
                        1 => FieldRelationship::IN_RANGE,
                        2 => [3, 5, 6]
                    ],
                    [
                        0 => 'b',
                        1 => FieldRelationship::EQUAL,
                        2 => 96
                    ]
                ],
                'or' => [
                    [
                        0 => 'c',
                        1 => FieldRelationship::LIKE,
                        2 => '%test%'
                    ]
                ]
            ]
        ], $resultModifierExported);
    }
}
