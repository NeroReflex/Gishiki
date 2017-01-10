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

namespace Gishiki\tests\Pipeline;

use Gishiki\Pipeline\Pipeline;

/**
 * The tester for the Pipeline class.
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class PipelineTest extends \PHPUnit_Framework_TestCase
{
    public function testPipelineStageBinding()
    {
        //setup a simple example pipeline
        $pipeline = new Pipeline('testingPLLN');
        $pipeline->bindStage('setup', function ($input) {
            return $input++;
        });
        $pipeline->bindStage('calculate', function ($input) {
            return ($input % 2) == 0;
        });

        //and additional info
        $this->assertEquals(2, $pipeline->countStages());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBadPipelineName()
    {
        $pipeline = new Pipeline(3);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBadFunctionName()
    {
        //setup a simple example pipeline
        $pipeline = new Pipeline('testingPLLNBadFuncName');
        $pipeline->bindStage('', function ($input) {
            return $input++;
        });
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBadFunction()
    {
        //setup a simple example pipeline
        $pipeline = new Pipeline('testingPLLNBadFunc');
        $pipeline->bindStage('correct_name', 90);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDoubleFunctionName()
    {
        //setup a simple example pipeline
        $pipeline = new Pipeline('testingPLLNDoubleFuncName');
        $pipeline->bindStage('same_name', function ($input) {
            return $input++;
        });
        $pipeline->bindStage('same_name', function ($input) {
            return $input--;
        });
    }

    public function testNameAndIndexConversion()
    {
        $name_one = 'not_a_good_name';
        $name_two = 'special_name';

        //setup a simple example pipeline
        $pipeline = new Pipeline('testingPLLNNameAndIndexConversion');
        $pipeline->bindStage($name_one, function ($input) {
            return $input++;
        });
        $pipeline->bindStage($name_two, function ($input) {
            return $input--;
        });

        $this->assertEquals($name_two, $pipeline->getFunctionNameByIndex(1));
        $this->assertEquals(0, $pipeline->getFunctionIndexByName($name_one));
    }

    /**
     * @expectedException \Gishiki\Pipeline\PipelineException
     */
    public function testBadNameAndIndexConversion()
    {
        $name_one = 'bad_not_a_good_name';
        $name_two = 'bad_special_name';

        //setup a simple example pipeline
        $pipeline = new Pipeline('testingPLLNBadNameAndIndexConversion');
        $pipeline->bindStage($name_one, function ($input) {
            return $input++;
        });
        $pipeline->bindStage($name_two, function ($input) {
            return $input--;
        });

        $pipeline->getFunctionIndexByName('invalid_'.$name_one);
    }

    /**
     * @expectedException \Gishiki\Pipeline\PipelineException
     */
    public function testNameAndBadIndexConversion()
    {
        $name_one = 'bad_not_a_good_name';
        $name_two = 'bad_special_name';

        //setup a simple example pipeline
        $pipeline = new Pipeline('testingPLLNNameAndBadIndexConversion');
        $pipeline->bindStage($name_one, function ($input) {
            return $input++;
        });
        $pipeline->bindStage($name_two, function ($input) {
            return $input--;
        });

        $pipeline->getFunctionNameByIndex(2);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNameAndInvalidIndexConversion()
    {
        $name_one = 'bad_not_a_good_name';
        $name_two = 'bad_special_name';

        //setup a simple example pipeline
        $pipeline = new Pipeline('testingPLLNNameAndInvalidIndexConversion');
        $pipeline->bindStage($name_one, function ($input) {
            return $input++;
        });
        $pipeline->bindStage($name_two, function ($input) {
            return $input--;
        });

        $pipeline->getFunctionNameByIndex('this is a string...');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidNameAndIndexConversion()
    {
        $name_one = 'bad_not_a_good_name';
        $name_two = 'bad_special_name';

        //setup a simple example pipeline
        $pipeline = new Pipeline('testingPLLNInvalidNameAndIndexConversion');
        $pipeline->bindStage($name_one, function ($input) {
            return $input++;
        });
        $pipeline->bindStage($name_two, function ($input) {
            return $input--;
        });

        $pipeline->getFunctionIndexByName(25);
    }

    public function testPipelineFunctionName()
    {
        $name = 'unique98364PipelineName';

        $functionNames = ['func_zero', 'func-one', 'funk@two'];

        $pipeline = new Pipeline($name);
        $i = 0;
        $pipeline->bindStage($functionNames[$i++], function ($params) {

        });
        $pipeline->bindStage($functionNames[$i++], function ($params) {

        });
        $pipeline->bindStage($functionNames[$i++], function ($params) {

        });

        $this->assertEquals(3, $pipeline->countStages());
        $this->assertEquals(2, $pipeline->getFunctionIndexByName($functionNames[2]));
        $this->assertEquals($functionNames[0], $pipeline->getFunctionNameByIndex(0));
    }
}
