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

use Gishiki\Core\MVC\Model\ActiveRecord;

class TModelNoTableName extends ActiveRecord
{
    protected static $structure = [];
}

class TModelNoFields extends ActiveRecord
{
    protected static $structure = [
        "name" => 'my_table'
    ];
}

class TModelNoFieldName extends ActiveRecord
{
    protected static $structure = [
        "name" => 'my_table',
        "fields" => [
            [
                "type" => "int"
            ]
        ]
    ];
}

class TModelNoFieldType extends ActiveRecord
{
    protected static $structure = [
        "name" => 'my_table',
        "fields" => [
            [
                "name" => "id"
            ]
        ]
    ];
}

class TModelBadFieldType extends ActiveRecord
{
    protected static $structure = [
        "name" => 'my_table',
        "fields" => [
            [
                "name" => "id",
                "type" => "invalid. ha!"
            ]
        ]
    ];
}

class TModelCorrectNoRelations extends ActiveRecord
{
    protected static $structure = [
        "name" => 'id_only',
        "fields" => [
            [
                "name" => "id",
                "type" => "integer",
                "not_null" => true,
                "auto_increment" => true,
                "primary_key" => true,
            ]
        ]
    ];
}

class TModelBook extends ActiveRecord
{
    protected static $structure = [
        "name" => 'book',
        "fields" => [
            [
                "name" => "id",
                "type" => "integer",
                "not_null" => true,
                "auto_increment" => true,
                "primary_key" => true,
            ],
            [
                "name" => "title",
                "type" => "string",
                "not_null" => true,
            ],
            [
                "name" => "author_id",
                "type" => "integer",
                "not_null" => true,
                "relation" => [
                    TModelBookAuthor::class,
                    "id"
                ]
            ]
        ]
    ];
}

class TModelBookAuthor extends ActiveRecord
{
    protected static $structure = [
        "name" => 'author',
        "fields" => [
            [
                "name" => "id",
                "type" => "integer",
                "not_null" => true,
                "auto_increment" => true,
                "primary_key" => true,
            ]
        ]
    ];
}
