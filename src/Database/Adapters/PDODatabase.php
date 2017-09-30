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

namespace Gishiki\Database\Adapters;

use Gishiki\Algorithms\Collections\CollectionInterface;
use Gishiki\Database\DatabaseException;
use Gishiki\Database\Runtime\SelectionCriteria;
use Gishiki\Database\Runtime\ResultModifier;
use Gishiki\Database\RelationalDatabaseInterface;
use Gishiki\Database\Schema\Table;
use Gishiki\Database\Adapters\Utils\QueryBuilder\SQLQueryBuilder;

/**
 * Represent an generic database.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class PDODatabase implements RelationalDatabaseInterface
{
    /**
     * Return the name of the PDO driver to be used for this database type.
     *
     * @return string the PDO driver name
     */
    protected function getPDODriverName()
    {
        return '';
    }

    /**
     * Generate a PDO connection string that will be used to connect a database.
     *
     * @param  mixed  $details           information used to open a database connection with PDO
     * @throws \InvalidArgumentException invalid connection details
     * @return string                    the string to be passed to the PDO driver
     */
    protected function generateConnectionQuery($details)
    {
        return [
            $this->getPDODriverName().':'.$details,
            null,
            null,
            null
        ];
    }

    /**
     * Get the query builder for the current RDBMS.
     *
     * @return SQLQueryBuilder the query builder for the used pdo adapter
     */
    protected function getQueryBuilder()
    {
        return new SQLQueryBuilder();
    }

    /**
     * @var bool TRUE only if the connection is alive
     */
    protected $connected;

    /**
     * @var \PDO the native pdo connection
     */
    protected $connection;

    /**
     * Create a new database connection using the given connection string.
     *
     * The connect function is automatically called.
     *
     * @param string $details the connection string
     */
    public function __construct($details)
    {
        $this->connection = [];
        $this->connected = false;

        //connect to the database
        $this->connect($details);
    }

    /**
     * {@inheritdoc}
     */
    public function connect($details)
    {
        //check for argument type
        if ((!is_string($details)) || (strlen($details) <= 0)) {
            throw new \InvalidArgumentException('The connection query must be given as a non-empty string');
        }

        //check for the pdo driver
        if ((strlen($this->getPDODriverName()) > 0) && (!in_array($this->getPDODriverName(), \PDO::getAvailableDrivers()))) {
            throw new DatabaseException('No '.$this->getPDODriverName().' PDO driver', 0);
        }

        //open the connection
        try {
            $connectionInfo = $this->generateConnectionQuery($details);

            $this->connection = new \PDO($connectionInfo[0], $connectionInfo[1], $connectionInfo[2], $connectionInfo[3]);
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            //the connection is opened
            $this->connected = true;
        } catch (\PDOException $ex) {
            throw new DatabaseException('Error while opening the database connection:'.$ex->getMessage(), 1);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        $this->connection = [];
        $this->connected = false;
    }

    /**
     * {@inheritdoc}
     */
    public function createTable(Table $table)
    {
        //check for closed database connection
        if (!$this->connected()) {
            throw new DatabaseException('The database connection must be opened before executing any operation', 2);
        }

        //build the sql query
        $queryBuilder = $this->getQueryBuilder()->createTableQuery($table);

        //open a new statement and execute it
        try {
            //prepare a statement with that safe sql string
            $stmt = $this->connection->prepare($queryBuilder->exportQuery());

            //execute the statement resolving placeholders
            $stmt->execute($queryBuilder->exportParams());
        } catch (\PDOException $ex) {
            throw new DatabaseException('Error while performing the table creation operation: '.$ex->getMessage(), 7);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function connected()
    {
        return $this->connected;
    }

    /**
     * {@inheritdoc}
     */
    public function create($collection, $data)
    {
        //check for invalid database name
        if ((!is_string($collection)) || (strlen($collection) <= 0)) {
            throw new \InvalidArgumentException('The name of the table must be given as a non-empty string');
        }

        //check for invalid data collection
        if ((!is_array($data)) && (!($data instanceof CollectionInterface))) {
            throw new \InvalidArgumentException('The data to be written on the database must be given as a collection');
        }

        //check for closed database connection
        if (!$this->connected()) {
            throw new DatabaseException('The database connection must be opened before executing any operation', 2);
        }

        //get an associative array of the input data
        $adaptedData = ($data instanceof CollectionInterface) ? $data->all() : $data;

        //build the sql query
        $queryBuilder = $this->getQueryBuilder()->insertQuery($collection, $adaptedData);

        //open a new statement and execute it
        try {
            //prepare a statement with that safe sql string
            $stmt = $this->connection->prepare($queryBuilder->exportQuery());

            //execute the statement resolving placeholders
            $stmt->execute($queryBuilder->exportParams());

            //as per documentation return the id of the last inserted row
            return $this->connection->lastInsertId();
        } catch (\PDOException $ex) {
            throw new DatabaseException('Error while performing the creation operation: '.$ex->getMessage(), 3);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update($collection, $data, SelectionCriteria $where)
    {
        //check for invalid database name
        if ((!is_string($collection)) || (strlen($collection) <= 0)) {
            throw new \InvalidArgumentException('The name of the table must be given as a non-empty string');
        }

        //check for invalid data collection
        if ((!is_array($data)) && (!($data instanceof CollectionInterface))) {
            throw new \InvalidArgumentException('The data to be written on the database must be given as a collection');
        }

        //check for closed database connection
        if (!$this->connected()) {
            throw new DatabaseException('The database connection must be opened before executing any operation', 2);
        }

        //get an associative array of the input data
        $adaptedData = ($data instanceof CollectionInterface) ? $data->all() : $data;

        //build the sql query
        $queryBuilder = $this->getQueryBuilder()->updateQuery($collection, $adaptedData, $where);

        //open a new statement and execute it
        try {
            //prepare a statement with that safe sql string
            $sql = $queryBuilder->exportQuery();
            $stmt = $this->connection->prepare($sql);

            //execute the statement resolving placeholders
            $stmt->execute($queryBuilder->exportParams());

            //return the number of affected rows
            return $stmt->rowCount();
        } catch (\PDOException $ex) {
            throw new DatabaseException('Error while performing the update operation: '.$ex->getMessage(), 4);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($collection, SelectionCriteria $where)
    {
        //check for invalid database name
        if ((!is_string($collection)) || (strlen($collection) <= 0)) {
            throw new \InvalidArgumentException('The name of the table must be given as a non-empty string');
        }

        //check for closed database connection
        if (!$this->connected()) {
            throw new DatabaseException('The database connection must be opened before executing any operation', 2);
        }

        //build the sql query
        $queryBuilder = $this->getQueryBuilder()->deleteQuery($collection, $where);

        //open a new statement and execute it
        try {
            //prepare a statement with that safe sql string
            $stmt = $this->connection->prepare($queryBuilder->exportQuery());

            //execute the statement resolving placeholders
            $stmt->execute($queryBuilder->exportParams());

            //return the number of affected rows
            return $stmt->rowCount();
        } catch (\PDOException $ex) {
            throw new DatabaseException('Error while performing the delete operation: '.$ex->getMessage(), 5);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAll($collection)
    {
        //check for invalid database name
        if ((!is_string($collection)) || (strlen($collection) <= 0)) {
            throw new \InvalidArgumentException('The name of the table must be given as a non-empty string');
        }

        //check for closed database connection
        if (!$this->connected()) {
            throw new DatabaseException('The database connection must be opened before executing any operation', 2);
        }

        //build the sql query
        $queryBuilder = $this->getQueryBuilder()->deleteAllQuery($collection);

        //open a new statement and execute it
        try {
            //prepare a statement with that safe sql string
            $stmt = $this->connection->prepare($queryBuilder->exportQuery());

            //execute the statement resolving placeholders
            $stmt->execute($queryBuilder->exportParams());

            //return the number of affected rows
            return $stmt->rowCount();
        } catch (\PDOException $ex) {
            throw new DatabaseException('Error while performing the delete operation: '.$ex->getMessage(), 5);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function read($collection, SelectionCriteria $where, ResultModifier $mod)
    {
        //check for invalid database name
        if ((!is_string($collection)) || (strlen($collection) <= 0)) {
            throw new \InvalidArgumentException('The name of the table must be given as a non-empty string');
        }

        //check for closed database connection
        if (!$this->connected()) {
            throw new DatabaseException('The database connection must be opened before executing any operation', 2);
        }

        //build the sql query
        $queryBuilder = $this->getQueryBuilder()->readQuery($collection, $where, $mod);

        //open a new statement and execute it
        try {
            //prepare a statement with that safe sql string
            $stmt = $this->connection->prepare($queryBuilder->exportQuery());

            //execute the statement resolving placeholders
            $stmt->execute($queryBuilder->exportParams());

            //return an associative array of data
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $ex) {
            throw new DatabaseException('Error while performing the read operation: '.$ex->getMessage(), 6);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function readSelective($collection, $fields, SelectionCriteria $where, ResultModifier $mod)
    {
        //check for invalid database name
        if ((!is_string($collection)) || (strlen($collection) <= 0)) {
            throw new \InvalidArgumentException('The name of the table must be given as a non-empty string');
        }

        //check for closed database connection
        if (!$this->connected()) {
            throw new DatabaseException('The database connection must be opened before executing any operation', 2);
        }

        //build the sql query
        $queryBuilder = $this->getQueryBuilder()->selectiveReadQuery($collection, $fields, $where, $mod);

        //open a new statement and execute it
        try {
            //prepare a statement with that safe sql string
            $stmt = $this->connection->prepare($queryBuilder->exportQuery());

            //execute the statement resolving placeholders
            $stmt->execute($queryBuilder->exportParams());

            //return the fetch result
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $ex) {
            throw new DatabaseException('Error while performing the read operation: '.$ex->getMessage(), 6);
        }
    }
}