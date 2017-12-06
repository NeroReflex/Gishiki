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

/**
 * Provides a working implementation of table schema extractor.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
trait ActiveRecordSerializationTrait
{
    /**
     * @var array the matrix of transformation of model_entry_key => db_row_name
     */
    private $transformations = [];

    /**
     * @var \Closure[] the collection of function to be executed to cast each model_entry_key in a correct value
     */
    private $filters = [];

    /**
     * @var string the name of the table/collection that will hold current model data
     */
    private $collection = "";

    /**
     * Get the nae of the table or collection that will hold data.
     *
     * @return string the collection name
     */
    private function getCollectionName() : string
    {
        return $this->collection;
    }

    /**
     * Load filtering functions from static::$structure array.
     *
     * @throws ActiveRecordException workflow problem
     */
    private function initTransitionSchema()
    {
        //table must have been already correctly parsed, so I know data is well-formed
        if (!ActiveRecordTables::isRegistered(static::class)) {
            throw new ActiveRecordException("Table definition for the current ActiveRecord object is missing.", 300);
        }

        foreach (static::$structure['fields'] as $fieldName => &$fieldDefinition) {
            $this->transformations[$fieldDefinition] = $fieldDefinition["name"];
        }

        $table = ActiveRecordTables::retrieve(static::class);

        //update the table name
        $this->collection = $table->getName();
        foreach ($table->getColumns() as &$column) {
            $dataType = $column->getType();

            /*switch ($dataType) {
                case 1:
                    $this->filters[0][] = function ($value) { return intval($value); };
                    break;
            }*/
        }
    }

    /**
     * Generate data to be written on the database.
     *
     * Generated data is compatible with the table structure in use
     * and can be used with Gishiki database adapters.
     *
     * @param array $data the data contained inside the current model
     * @return array the data to be presented to the database manager
     */
    private function executeSerialization(array &$data) : array
    {
        $result = [];

        foreach ($this->transformations as $keyOnModel => $keyOnDatabase) {
            if (array_key_exists($keyOnModel, $data)) {
                $result[$keyOnDatabase] = $this->executeFilter($keyOnModel, $data[$keyOnModel]);
            }
        }

        return $result;
    }

    /**
     * Generate data to be used on a model.
     *
     * Generated comes from a database adapter and is converted
     * to be loaded in the current model.
     *
     * @param array $data the data contained inside the database
     * @return array the data to be loaded on the current model
     */
    private function executeDeserialization(array &$data) : array
    {
        $result = [];

        foreach ($this->transformations as $keyOnModel => $keyOnDatabase) {
            if (array_key_exists($keyOnDatabase, $data)) {
                $result[$keyOnModel] = $this->executeFilter($keyOnModel, $data[$keyOnModel]);
            }
        }

        return $result;
    }

    /**
     * Perform every necessary filtering value on a given model portion.
     *
     * @param  string $keyOnModel the key associated with the value
     * @param  mixed  $value      the value to be filtered
     * @return mixed the filtered value
     */
    private function executeFilter($keyOnModel, $value)
    {
        $filtered = $this->filters[$keyOnModel]($value);

        return $filtered;
    }
}