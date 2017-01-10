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
class PipelineCollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Gishiki\Pipeline\PipelineException
     */
    public function testDoublePipeline()
    {
        $pipeline_one = new Pipeline('pipelineWithSameName');
        $pipeline_two = new Pipeline('pipelineWithSameName');
    }

    public function testPipelineName()
    {
        $name = 'unique3244PipelineName';

        $pipeline = new Pipeline($name);
        $this->assertEquals($name, $pipeline->getName());

        $this->assertEquals(true, \Gishiki\Pipeline\PipelineCollector::checkPipelineByName($name));
        $this->assertEquals($pipeline->getName(), \Gishiki\Pipeline\PipelineCollector::getPipelineByName($name)->getName());
    }

    public function testPipelineNonexistentName()
    {
        $name = 'unique53463PipelineName';

        $pipeline = new Pipeline($name);
        $this->assertEquals($name, $pipeline->getName());

        $this->assertEquals(false, \Gishiki\Pipeline\PipelineCollector::checkPipelineByName($name.'_nonexistent'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBadNameinGettingPipeline()
    {
        \Gishiki\Pipeline\PipelineCollector::getPipelineByName(4);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBadNameinCheckingPipeline()
    {
        \Gishiki\Pipeline\PipelineCollector::checkPipelineByName(null);
    }
}
