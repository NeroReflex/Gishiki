<?php
/**************************************************************************
Copyright 2016 Benato Denis

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

use Gishiki\Pipeline\PipelineRuntime;
use Gishiki\Pipeline\Pipeline;
use Gishiki\Algorithms\Collections\SerializableCollection;
use Gishiki\Database\DatabaseManager;

/**
 * The tester for the PipelineRuntime class.
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class PipelineRuntimeTest extends \PHPUnit_Framework_TestCase
{
    private static function GetConnection()
    {
        try {
            return DatabaseManager::Retrieve('pipeline_testing_db');
        } catch (\Gishiki\Database\DatabaseException $ex) {
            return DatabaseManager::Connect('pipeline_testing_db', \Gishiki\tests\Database\MongoDatabaseTest::GetConnectionQuery());
        }
    }

    public function testFullPipeline()
    {
        $value = 0x5F;

        self::GetConnection();
        \Gishiki\Pipeline\PipelineSupport::Initialize('pipeline_testing_db', 'testing.pipeline');

        $pipeline = new Pipeline('first_fulltest!');
        $pipeline->bindStage('firstStage', function (SerializableCollection &$collection) use ($value) {
            $collection->set('value', $value);
        });

        //creaate the pipeline runtime
        $pipelineExecutor = new PipelineRuntime($pipeline);
        $pipelineExecutor(2);

        $this->assertEquals($value, $pipelineExecutor->getDataCollection()->get('value'));
        $this->assertEquals(\Gishiki\Pipeline\RuntimeStatus::COMPLETED, $pipelineExecutor->getStatus());
    }

    public function testAbortedPipeline()
    {
        $reason = 'I must be doing something wrong....';

        self::GetConnection();
        \Gishiki\Pipeline\PipelineSupport::Initialize('pipeline_testing_db', 'testing.pipeline');

        $pipeline = new Pipeline('first_aborttest!');
        $pipeline->bindStage('firstStage', function (SerializableCollection &$collection) use ($reason) {
            \Gishiki\Pipeline\PipelineSupport::Abort($reason);
        });

        //creaate the pipeline runtime
        $pipelineRetrieved = \Gishiki\Pipeline\PipelineCollector::getPipelineByName('first_aborttest!');
        $pipelineExecutor = new PipelineRuntime($pipelineRetrieved);
        $pipelineExecutor(2);

        $this->assertEquals($reason, $pipelineExecutor->getAbortMessage());
        $this->assertEquals(\Gishiki\Pipeline\RuntimeStatus::ABORTED, $pipelineExecutor->getStatus());
    }

    public function testFullMultistagePipeline()
    {
        self::GetConnection();
        \Gishiki\Pipeline\PipelineSupport::Initialize('pipeline_testing_db', 'testing.pipeline');

        $pipeline = new Pipeline('second_fulltest!');
        $pipeline->bindStage('firstStage', function (SerializableCollection &$collection) {
            $collection->set('value', 5);
        });
        $pipeline->bindStage('secondStage', function (SerializableCollection &$collection) {
            $collection->set('value', $collection->get('value') + 1);
        });
        $pipeline->bindStage('thirdStage', function (SerializableCollection &$collection) {
            $collection->set('value', $collection->get('value') * 3);
        });

        //create the pipeline runtime
        $pipelineExecutor = new PipelineRuntime($pipeline);
        $pipelineExecutor(-1);

        $this->assertEquals(3, $pipelineExecutor->getCompletedStagesCount());
        $this->assertEquals(18, $pipelineExecutor->getDataCollection()->get('value'));
        $this->assertEquals(\Gishiki\Pipeline\RuntimeStatus::COMPLETED, $pipelineExecutor->getStatus());
    }

    public function testFullMultistagePipelineWithReturnValues()
    {
        self::GetConnection();
        \Gishiki\Pipeline\PipelineSupport::Initialize('pipeline_testing_db', 'testing.pipeline');

        $pipeline = new Pipeline('third_fulltest!');
        $pipeline->bindStage('firstStage', function (SerializableCollection &$collection) {
            $collection->set('value', 5);

            return 'stringa';
        });
        $pipeline->bindStage('secondStage', function (SerializableCollection &$collection) {
            $collection->set('value', $collection->get('value') + 1);

            return 0x5A;
        });
        $pipeline->bindStage('thirdStage', function (SerializableCollection &$collection) {
            $collection->set('value', $collection->get('value') * 3);

            return 7.43;
        });

        //create the pipeline runtime
        $pipelineExecutor = new PipelineRuntime($pipeline, \Gishiki\Pipeline\RuntimeType::SYNCHRONOUS);
        $pipelineExecutor(-1);

        $report = $pipelineExecutor->getExecutionReport();
        $i = 0;
        $this->assertEquals('stringa', $report[$i++]['result']);
        $this->assertEquals(0x5A, $report[$i++]['result']);
        $this->assertEquals(7.43, $report[$i++]['result']);
        $this->assertEquals(\Gishiki\Pipeline\RuntimeType::SYNCHRONOUS, $pipelineExecutor->getType());
    }

    public function testSplitMultistagePipelineWithReturnValues()
    {
        self::GetConnection();
        \Gishiki\Pipeline\PipelineSupport::Initialize('pipeline_testing_db', 'testing.pipeline');

        $pipeline = new Pipeline('first_splittest!');
        $pipeline->bindStage('firstStage', function (SerializableCollection &$collection) {
            $collection->set('value', 5);

            return 'stringa';
        });
        $pipeline->bindStage('secondStage', function (SerializableCollection &$collection) {
            $collection->set('value', $collection->get('value') + 1);

            return 0x5A;
        });
        $pipeline->bindStage('thirdStage', function (SerializableCollection &$collection) {
            $collection->set('value', $collection->get('value') * 3);

            return 7.43;
        });
        $pipeline->bindStage('fourthStage', function (SerializableCollection &$collection) {
            $collection->set('value', $collection->get('value') + 2);

            return;
        });

        //create the pipeline runtime
        $pipelineExecutor = new PipelineRuntime($pipeline, \Gishiki\Pipeline\RuntimeType::SYNCHRONOUS);
        $pipelineExecutor(1);

        $this->assertEquals(5, $pipelineExecutor->getDataCollection()->get('value'));
        $this->assertEquals(1, $pipelineExecutor->getCompletedStagesCount());
        $this->assertEquals(\Gishiki\Pipeline\RuntimeStatus::STOPPED, $pipelineExecutor->getStatus());

        $pipelineExecutor(1);

        $this->assertEquals(6, $pipelineExecutor->getDataCollection()->get('value'));
        $this->assertEquals(2, $pipelineExecutor->getCompletedStagesCount());
        $this->assertEquals(\Gishiki\Pipeline\RuntimeStatus::STOPPED, $pipelineExecutor->getStatus());

        $pipelineExecutor(1);

        $this->assertEquals(18, $pipelineExecutor->getDataCollection()->get('value'));
        $this->assertEquals(3, $pipelineExecutor->getCompletedStagesCount());
        $this->assertEquals(\Gishiki\Pipeline\RuntimeStatus::STOPPED, $pipelineExecutor->getStatus());

        $pipelineExecutor(1);

        $this->assertEquals(20, $pipelineExecutor->getDataCollection()->get('value'));
        $this->assertEquals(4, $pipelineExecutor->getCompletedStagesCount());
        $this->assertEquals(\Gishiki\Pipeline\RuntimeStatus::COMPLETED, $pipelineExecutor->getStatus());

        $report = $pipelineExecutor->getExecutionReport();
        $i = 0;
        $this->assertEquals('stringa', $report[$i++]['result']);
        $this->assertEquals(0x5A, $report[$i++]['result']);
        $this->assertEquals(7.43, $report[$i++]['result']);
        $this->assertEquals(null, $report[$i++]['result']);
        $this->assertEquals(\Gishiki\Pipeline\RuntimeType::SYNCHRONOUS, $pipelineExecutor->getType());
    }

    /**
     * @expectedException \Gishiki\Pipeline\PipelineException
     */
    public function testNonexistentId()
    {
        PipelineRuntime::Restore('bad_id :D:D');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidId()
    {
        PipelineRuntime::Restore(7);
    }

    public function testInterruptedPipeline()
    {
        self::GetConnection();
        \Gishiki\Pipeline\PipelineSupport::Initialize('pipeline_testing_db', 'testing.pipeline');

        $pipeline = new Pipeline('first_intertest!');
        $pipeline->bindStage('firstStage', function (SerializableCollection &$collection) {
            $collection->set('value', 3);
        });
        $pipeline->bindStage('secondStage', function (SerializableCollection &$collection) {
            $collection->set('value', $collection->get('value') + 2);
        });
        $pipeline->bindStage('thirdStage', function (SerializableCollection &$collection) {
            $collection->set('value', $collection->get('value') * 3);
        });

        //create the pipeline runtime
        $pipelineExecutor = new PipelineRuntime($pipeline);
        $pipelineExecutor(2);

        $this->assertEquals(2, $pipelineExecutor->getCompletedStagesCount());
        $this->assertEquals(5, $pipelineExecutor->getDataCollection()->get('value'));

        $uniqueID = $pipelineExecutor->getUniqueID();
        $pipelineExecutor = null;

        $samePipeline = PipelineRuntime::Restore($uniqueID);
        $samePipeline(-1);

        $this->assertEquals(3, $samePipeline->getCompletedStagesCount());
        $this->assertEquals(15, $samePipeline->getDataCollection()->get('value'));
        $this->assertEquals(\Gishiki\Pipeline\RuntimeStatus::COMPLETED, $samePipeline->getStatus());
    }

    public function testAsyncPipelines()
    {
        self::GetConnection();
        \Gishiki\Pipeline\PipelineSupport::Initialize('pipeline_testing_db', 'testing.pipeline');

        $pipeline = new Pipeline('asyncTester');
        $pipeline->bindStage('loneStage', function (SerializableCollection &$collection) {

        });

        $highestPriority = new PipelineRuntime($pipeline, \Gishiki\Pipeline\RuntimeType::ASYNCHRONOUS, \Gishiki\Pipeline\RuntimePriority::LOWEST);

        $this->assertEquals($highestPriority->getUniqueID(), \Gishiki\Pipeline\PipelineSupport::getNextAsyncByPriority()->getUniqueID());

        new PipelineRuntime($pipeline, \Gishiki\Pipeline\RuntimeType::ASYNCHRONOUS, \Gishiki\Pipeline\RuntimePriority::LOWEST);
        $highestPriority = new PipelineRuntime($pipeline, \Gishiki\Pipeline\RuntimeType::ASYNCHRONOUS, \Gishiki\Pipeline\RuntimePriority::LOW);

        $this->assertEquals($highestPriority->getUniqueID(), \Gishiki\Pipeline\PipelineSupport::getNextAsyncByPriority()->getUniqueID());

        new PipelineRuntime($pipeline, \Gishiki\Pipeline\RuntimeType::ASYNCHRONOUS, \Gishiki\Pipeline\RuntimePriority::LOWEST);
        new PipelineRuntime($pipeline, \Gishiki\Pipeline\RuntimeType::ASYNCHRONOUS, \Gishiki\Pipeline\RuntimePriority::LOW);
        new PipelineRuntime($pipeline, \Gishiki\Pipeline\RuntimeType::ASYNCHRONOUS, \Gishiki\Pipeline\RuntimePriority::LOW);
        $highestPriority = new PipelineRuntime($pipeline, \Gishiki\Pipeline\RuntimeType::ASYNCHRONOUS, \Gishiki\Pipeline\RuntimePriority::HIGH);

        $this->assertEquals($highestPriority->getUniqueID(), \Gishiki\Pipeline\PipelineSupport::getNextAsyncByPriority()->getUniqueID());

        new PipelineRuntime($pipeline, \Gishiki\Pipeline\RuntimeType::ASYNCHRONOUS, \Gishiki\Pipeline\RuntimePriority::MEDIUM);
        $this->assertEquals($highestPriority->getUniqueID(), \Gishiki\Pipeline\PipelineSupport::getNextAsyncByPriority()->getUniqueID());

        while ($currentPipeline = \Gishiki\Pipeline\PipelineSupport::getNextAsyncByPriority()) {
            $currentPipeline(-1);
        }
    }

    public function testTypeChangeFromAsyncToSync()
    {
        self::GetConnection();
        \Gishiki\Pipeline\PipelineSupport::Initialize('pipeline_testing_db', 'testing.pipeline');

        $pipeline = new Pipeline('changeTypeFromAsyncToSync');
        $pipeline->bindStage('firstStage', function (SerializableCollection &$collection) {
            \Gishiki\Pipeline\PipelineSupport::ChangeType(\Gishiki\Pipeline\RuntimeType::SYNCHRONOUS);
        });
        $pipeline->bindStage('secondStage', function (SerializableCollection &$collection) {
            return false;
        });

        $runtime = new PipelineRuntime($pipeline, \Gishiki\Pipeline\RuntimeType::ASYNCHRONOUS);
        $this->assertEquals(\Gishiki\Pipeline\RuntimeType::ASYNCHRONOUS, $runtime->getType());
        $runtime(-1);

        $this->assertEquals(\Gishiki\Pipeline\RuntimeType::SYNCHRONOUS, $runtime->getType());
        $this->assertEquals(1, $runtime->getCompletedStagesCount());

        $runtime(-1);
        $this->assertEquals(\Gishiki\Pipeline\RuntimeType::SYNCHRONOUS, $runtime->getType());
        $this->assertEquals(2, $runtime->getCompletedStagesCount());
    }

    public function testTypeChangeFromSyncToAsync()
    {
        self::GetConnection();
        \Gishiki\Pipeline\PipelineSupport::Initialize('pipeline_testing_db', 'testing.pipeline');

        $pipeline = new Pipeline('changeTypeFromSyncToAsync');
        $pipeline->bindStage('firstStage', function (SerializableCollection &$collection) {
            \Gishiki\Pipeline\PipelineSupport::ChangeType(\Gishiki\Pipeline\RuntimeType::ASYNCHRONOUS);
        });
        $pipeline->bindStage('secondStage', function (SerializableCollection &$collection) {
            return false;
        });

        $runtime = new PipelineRuntime($pipeline, \Gishiki\Pipeline\RuntimeType::SYNCHRONOUS);
        $this->assertEquals(\Gishiki\Pipeline\RuntimeType::SYNCHRONOUS, $runtime->getType());
        $runtime(-1);

        $this->assertEquals(\Gishiki\Pipeline\RuntimeType::ASYNCHRONOUS, $runtime->getType());
        $this->assertEquals(2, $runtime->getCompletedStagesCount());
    }

    public function testCollectionInit()
    {
        self::GetConnection();
        \Gishiki\Pipeline\PipelineSupport::Initialize('pipeline_testing_db', 'testing.pipeline');

        $pipeline = new Pipeline('testCollectionInit');
        $pipeline->bindStage('stageTest', function (SerializableCollection &$collection) {
            $newval = (($collection->has('value')) && ($collection->value == 5)) ? 0xFF : 0x00;
            $collection->set('result', $newval);
        });

        $runtime = new PipelineRuntime($pipeline, \Gishiki\Pipeline\RuntimeType::SYNCHRONOUS, \Gishiki\Pipeline\RuntimePriority::URGENT, [
            'value' => 5,
        ]);
        $runtime(-1);

        $this->assertEquals(0xFF, $runtime->getDataCollection()->get('result'));
    }
    
    /**
     * @expectedException \Gishiki\Pipeline\PipelineException
     */
    public function testRandomGetUniqueIDCall()
    {
        \Gishiki\Pipeline\PipelineSupport::GetUniqueID();
    }
    
    public function testGetUniqueID()
    {
        self::GetConnection();
        \Gishiki\Pipeline\PipelineSupport::Initialize('pipeline_testing_db', 'testing.pipeline');

        $pipeline = new Pipeline(__FUNCTION__);
        $pipeline->bindStage('stageTest', function (SerializableCollection &$collection) {
            $collection->set('id', \Gishiki\Pipeline\PipelineSupport::GetUniqueID());
        });

        $runtime = new PipelineRuntime($pipeline);
        $runtime();

        $this->assertEquals($runtime->getUniqueID(), $runtime->getDataCollection()->get('id'));
    }
}
