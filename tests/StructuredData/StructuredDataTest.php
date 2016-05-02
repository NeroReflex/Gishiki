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

namespace Gishiki\tests\StructuredData;

use Gishiki\StructuredData\StructuredData;

/**
 * A collection of tests for the structured data manager
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class StructuredDataTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $arrayWithoutNestedArray = [
            "efe" => 1,
            "ethe" => "vvbaq",
            "1lop" => true,
            "ldaeo_" => 34.50
            ];
        
        //create the structured data
        $serializationResult = new StructuredData($arrayWithoutNestedArray);
        
        //check the result
        $this->assertEquals($arrayWithoutNestedArray, $serializationResult->all());
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBadConstructor()
    {
        new StructuredData(true);
    }
    
    public function testNested()
    {
        $arrayWithNestedArray = [
            "efe" => 1,
            "ethe" => "vvbaq",
            "pofw" => [
                "ffej" => "fef",
                "ekf" => 8.90,
                ],
            "1lop" => true,
            "ldaeo_" => 34.50
        ];
        
        $structuredData = new StructuredData($arrayWithNestedArray);
        
        $refactoredArray = [
            "efe" => 1,
            "ethe" => "vvbaq",
            "pofw" => new StructuredData([
                "ffej" => "fef",
                "ekf" => 8.90,
                ]),
            "1lop" => true,
            "ldaeo_" => 34.50
        ];
        
        $this->assertEquals($refactoredArray, $structuredData->all());
    }
}