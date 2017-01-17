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

use Gishiki\Database\ResultModifier;
use Gishiki\Database\FieldOrdering;

/**
 * The tester for the ResultModifier class.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class ResultModifierTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBadLimit()
    {
        $resMod = ResultModifier::Initialize([
            'limit' => 5,
            'skip' => 8
        ])->limit("bad")->skip(5);
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBadOffset()
    {
        $resMod = ResultModifier::Initialize([
            'limit' => 5,
            'skip' => 8
        ])->limit(10)->skip("bad");
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBadNameOrdering()
    {
        $resMod = ResultModifier::Initialize([
            'limit' => 5,
            'skip' => 8
        ])->order(null, FieldOrdering::ASC);
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBadOrderOrdering()
    {
        $resMod = ResultModifier::Initialize([
            'limit' => 5,
            'skip' => 8
        ])->order("name", null);
    }
    
    public function testLimitAndOffset()
    {
        $exportResult = [
            'limit' => 8,
            'skip' => 5,
            'order' => [ ]
        ];
        
        $resMod = ResultModifier::Initialize([
            'limit' => 5,
            'skip' => 8
        ])->limit(8)->skip(5);
        
        $exportMethod = new \ReflectionMethod($resMod, 'export');
        $exportMethod->setAccessible(true);

        $this->assertEquals($exportResult, $exportMethod->invoke($resMod));
    }
    
    public function testOrdering()
    {
        $exportResult = [
            'limit' => 0,
            'skip' => 0,
            'order' => [
                "name" => FieldOrdering::ASC,
                "surname" => FieldOrdering::ASC,
                "year" =>  FieldOrdering::DESC,
            ]
        ];
        
        $resMod = ResultModifier::Initialize([ ])
                ->order("name", FieldOrdering::ASC)
                ->order("surname", FieldOrdering::ASC)
                ->order("year", FieldOrdering::DESC);
        
        $exportMethod = new \ReflectionMethod($resMod, 'export');
        $exportMethod->setAccessible(true);

        $this->assertEquals($exportResult, $exportMethod->invoke($resMod));
    }
}
