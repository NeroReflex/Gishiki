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

namespace Gishiki\Database;

/**
 * An helper class used to abstract how records/documents are selected from the 
 * database table/collection.
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class SelectionCriteria
{
    private $criteria = array();
    private $id = null;

    public function WhereID(ObjectIDInterface $objectID)
    {
        $this->id = clone $objectID;

        return $this;
    }

    public function InRange($field, $values)
    {
        if (!array_key_exists($field, $this->criteria)) {
            $this->criteria[$field] = array();
        }
        $this->criteria[$field] = array_merge($this->criteria[$field], [
            '$in' => $values,
        ]);

        return $this;
    }

    public function NotInRange($field, $values)
    {
        if (!array_key_exists($field, $this->criteria)) {
            $this->criteria[$field] = array();
        }
        $this->criteria[$field] = array_merge($this->criteria[$field], [
            '$nin' => $values,
        ]);

        return $this;
    }

    public function EqualThan($field, $value)
    {
        if (!array_key_exists($field, $this->criteria)) {
            $this->criteria[$field] = array();
        }
        $this->criteria[$field] = array_merge($this->criteria[$field], [
            '$eq' => $value,
        ]);

        return $this;
    }

    public function NotEqualThan($field, $value)
    {
        if (!array_key_exists($field, $this->criteria)) {
            $this->criteria[$field] = array();
        }
        $this->criteria[$field] = array_merge($this->criteria[$field], [
            '$ne' => $value,
        ]);

        return $this;
    }

    public function GreaterThan($field, $value)
    {
        if (!array_key_exists($field, $this->criteria)) {
            $this->criteria[$field] = array();
        }
        $this->criteria[$field] = array_merge($this->criteria[$field], [
            '$gt' => $value,
        ]);

        return $this;
    }

    public function GreaterOrEqualThan($field, $value)
    {
        if (!array_key_exists($field, $this->criteria)) {
            $this->criteria[$field] = array();
        }
        $this->criteria[$field] = array_merge($this->criteria[$field], [
            '$gte' => $value,
        ]);

        return $this;
    }

    public function LessThan($field, $value)
    {
        if (!array_key_exists($field, $this->criteria)) {
            $this->criteria[$field] = array();
        }
        $this->criteria[$field] = array_merge($this->criteria[$field], [
            '$lt' => $value,
        ]);

        return $this;
    }

    public function LessOrEqualThan($field, $value)
    {
        if (!array_key_exists($field, $this->criteria)) {
            $this->criteria[$field] = array();
        }
        $this->criteria[$field] = array_merge($this->criteria[$field], [
            '$lte' => $value,
        ]);

        return $this;
    }
}
