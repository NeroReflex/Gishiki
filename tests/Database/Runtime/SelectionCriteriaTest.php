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

namespace Gishiki\tests\Database\Runtime;

use PHPUnit\Framework\TestCase;

use Gishiki\Database\Runtime\SelectionCriteria;
use Gishiki\Database\Runtime\FieldRelation;

/**
 * The tester for the SelectionCriteria class.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class SelectionCriteriaTest extends TestCase
{
    
    public function testBadNameAnd()
    {
        $this->expectException(\InvalidArgumentException::class);
        SelectionCriteria::select(['a' => [3, 5, 6]])->AndWhere(3, FieldRelation::EQUAL, '');
    }

    public function testBadRelationAnd()
    {
        $this->expectException(\InvalidArgumentException::class);
        SelectionCriteria::select(['a' => [3, 5, 6]])->AndWhere('a', 'IDK', '');
    }

    public function testBadValueAnd()
    {
        $this->expectException(\InvalidArgumentException::class);
        SelectionCriteria::select(['a' => [3, 5, 6]])->AndWhere('a', FieldRelation::EQUAL, new SelectionCriteria());
    }

    public function testBadValueOr()
    {
        $this->expectException(\InvalidArgumentException::class);
        SelectionCriteria::select(['a' => [3, 5, 6]])->OrWhere('a', FieldRelation::EQUAL, new SelectionCriteria());
    }

    public function testBadNameOr()
    {
        $this->expectException(\InvalidArgumentException::class);
        SelectionCriteria::select(['a' => [3, 5, 6]])->OrWhere(3, FieldRelation::EQUAL, '');
    }

    public function testBadRelationOr()
    {
        $this->expectException(\InvalidArgumentException::class);
        SelectionCriteria::select(['a' => [3, 5, 6]])->OrWhere('a', 'IDK', '');
    }

    public function testInitializerOnly()
    {
        $sc = SelectionCriteria::select(['a' => [3, 5, 6], 'b' => 96]);

        $exportMethod = new \ReflectionMethod($sc, 'export');
        $exportMethod->setAccessible(true);
        $resultModifierExported = $exportMethod->invoke($sc);

        $this->assertEquals([
            'historic' => [128, 129],
            'criteria' => [
                'and' => [
                    [
                        0 => 'a',
                        1 => FieldRelation::IN_RANGE,
                        2 => [3, 5, 6],
                    ],
                    [
                        0 => 'b',
                        1 => FieldRelation::EQUAL,
                        2 => 96,
                    ],
                ],
                'or' => [],
            ],
        ], $resultModifierExported);
    }

    public function testOrAfterInitializer()
    {
        $sc = SelectionCriteria::select(['a' => [3, 5, 6], 'b' => 96])->OrWhere('c', FieldRelation::LIKE, '%test%');

        $exportMethod = new \ReflectionMethod($sc, 'export');
        $exportMethod->setAccessible(true);
        $resultModifierExported = $exportMethod->invoke($sc);

        $this->assertEquals([
            'historic' => [128, 129, 0],
            'criteria' => [
                'and' => [
                    [
                        0 => 'a',
                        1 => FieldRelation::IN_RANGE,
                        2 => [3, 5, 6],
                    ],
                    [
                        0 => 'b',
                        1 => FieldRelation::EQUAL,
                        2 => 96,
                    ],
                ],
                'or' => [
                    [
                        0 => 'c',
                        1 => FieldRelation::LIKE,
                        2 => '%test%',
                    ],
                ],
            ],
        ], $resultModifierExported);
    }
}
