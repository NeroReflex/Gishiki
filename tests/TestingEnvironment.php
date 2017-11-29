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

abstract class TestingEnvironment
{
    private static $sqlite = "tests/db_test.sqlite";

    private static $mysql = "host=127.0.0.1;port=3306;dbname=travis;user=root;password=";

    private static $pgsql = "host=localhost;port=5432;dbname=travis;user=vagrant;password=vagrant";

    public static function init()
    {
        self::$pgsql = (getenv("CI")) ? getenv("PG_CONN") : self::$pgsql;
        self::$mysql = (getenv("CI")) ? getenv("MYSQL_CONN") : self::$mysql;
        self::$sqlite = (getenv("CI")) ? getenv("SQLITE_CONN") : self::$sqlite;

        //setup the database
        file_put_contents(static::$sqlite, "");
    }

    public static function getPostgreSQLConnectionQuery() : string
    {
        return self::$pgsql;
    }

    public static function getMySQLConnectionQuery() : string
    {
        return self::$mysql;
    }

    public static function getSQLiteConnectionQuery() : string
    {
        return self::$sqlite;
    }

    public static function getMemcachedServer() : array
    {
        return ["localhost", 11211, 100];
    }
}
