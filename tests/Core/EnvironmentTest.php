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

namespace Gishiki\tests\Core\Router;

use Gishiki\Core\Environment;
use PHPUnit\Framework\TestCase;

/**
 * The tester for the Environment class.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class EnvironmentTest extends TestCase
{
    public function testNoVar()
    {
        $this->assertFalse(Environment::has('this_not_exists'));
    }

    public function testSet()
    {
        Environment::set('test_env_key', __FUNCTION__);

        $this->assertTrue(Environment::has('test_env_key'));

        $this->assertEquals(__FUNCTION__, Environment::get('test_env_key'));
    }

    public function testGet()
    {
        $this->assertFalse(Environment::has('testget_env_key'));

        $this->assertNull(Environment::get('testget_env_key'));

        Environment::set('testget_env_key', __FUNCTION__);

        $this->assertTrue(Environment::has('testget_env_key'));

        $this->assertEquals(__FUNCTION__, Environment::get('testget_env_key'));
    }

    public function testUnset()
    {
        Environment::set('test_env_key_unset', __FUNCTION__);

        $this->assertTrue(Environment::has('test_env_key_unset'));

        $this->assertEquals(__FUNCTION__, Environment::get('test_env_key_unset'));

        Environment::remove('test_env_key_unset');

        $this->assertFalse(Environment::has('test_env_key_unset'));
    }

    public function testInvalidSet()
    {
        $this->expectException(\InvalidArgumentException::class);

        Environment::set(4, "value");
    }

    public function testInvalidGet()
    {
        $this->expectException(\InvalidArgumentException::class);

        Environment::get(null);
    }

    public function testInvalidRemove()
    {
        $this->expectException(\InvalidArgumentException::class);

        Environment::remove(null);
    }

    public function testInvalidHas()
    {
        $this->expectException(\InvalidArgumentException::class);

        Environment::has(90.4);
    }
}