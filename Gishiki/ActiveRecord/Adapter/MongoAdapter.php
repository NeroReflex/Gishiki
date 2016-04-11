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

namespace Gishiki\ActiveRecord\Adapter;

/**
 * This is the MongoDB database adapter
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class MongoAdapter implements \Gishiki\ActiveRecord\DatabaseAdapter {
    private $conection = null;
    
    public function __construct($connection_query, $ssl_key = null, $ssl_certificate = null, $ssl_ca = null) {
        //extract connection info from the connection query
        $db_conn = explode('@', $connection_query, 2);
        $user_and_password = explode(':', $db_conn[0], 2);
        $host_and_port = explode(':', explode('/', $db_conn[1], 2)[0], 2);
        $db_name = explode('/', $db_conn[1], 2)[1];
        
        $this->connection = new MongoDB\Driver\Manager("mongodb://" . $host_and_port[0] . ":" . $host_and_port[1]);
        
    }

}