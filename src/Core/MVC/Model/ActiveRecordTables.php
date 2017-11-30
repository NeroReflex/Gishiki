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

namespace Gishiki\Core\MVC\Model;

use Gishiki\Database\Schema\Table;

/**
 * This container is responsible of holding in-memory representation of
 * database tables corresponding with any ActiveRecord instances.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class ActiveRecordTables
{
    private static $tables = [];

    public static function register($className, Table $table)
    {
        if ((!is_string($className)) || (strlen($className) < 3)) {
            throw new \InvalidArgumentException('Invalid name for an ActiveRecord instance');
        }

        if (self::isRegistered($className)) {
            throw new ActiveRecordException("An ActiveRecord with the same name ($className) has already been registered.", 200);
        }

        self::$tables[$className] = $table;
    }

    public static function &retrieve($className) : Table
    {
        if ((!is_string($className)) || (strlen($className) < 3)) {
            throw new \InvalidArgumentException('Invalid name for an ActiveRecord instance.');
        }

        if (!self::isRegistered($className)) {
            throw new ActiveRecordException("An ActiveRecord with the given name ($className) does not exists.", 200);
        }

        return self::$tables[$className];
    }

    public static function isRegistered($className) : bool
    {
        if ((!is_string($className)) || (strlen($className) < 3)) {
            throw new \InvalidArgumentException('Invalid name for an ActiveRecord instance');
        }

        return array_key_exists($className, self::$tables);
    }

    public static function getRegistered() : array
    {
        return array_keys(self::$tables);
    }
}
