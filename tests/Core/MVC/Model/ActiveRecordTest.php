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

namespace Gishiki\tests\Core\MVC\Model;

use Gishiki\Core\MVC\Model\ActiveRecordException;

use PHPUnit\Framework\TestCase;

/**
 * The tester for the ActiveRecord class.
 *
 * Used to test every feature of the ActiveRecord class
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class ActiveRecordTest extends TestCase
{
    public function testSchemaWithNoName()
    {
        $this->expectException(ActiveRecordException::class);
        $this->expectExceptionCode(100);

        \TModelNoTableName::getTableDefinition();
    }

    public function testSchemaWithNoFields()
    {
        $this->expectException(ActiveRecordException::class);
        $this->expectExceptionCode(104);

        \TModelNoFields::getTableDefinition();
    }

    public function testSchemaWithNoFieldName()
    {
        $this->expectException(ActiveRecordException::class);
        $this->expectExceptionCode(101);

        \TModelNoFieldName::getTableDefinition();
    }

    public function testSchemaWithNoFieldType()
    {
        $this->expectException(ActiveRecordException::class);
        $this->expectExceptionCode(102);

        \TModelNoFieldType::getTableDefinition();
    }

    public function testSchemaWithBadFieldType()
    {
        $this->expectException(ActiveRecordException::class);
        $this->expectExceptionCode(102);

        \TModelBadFieldType::getTableDefinition();
    }
}