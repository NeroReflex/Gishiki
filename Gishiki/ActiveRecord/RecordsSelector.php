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

namespace Gishiki\ActiveRecord;

/**
 * This is the records selector: a component shared between the developer 
 * and the database adapter.
 * 
 * A record selector is used to select records to be affected by a database transaction
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class RecordsSelector {
    
    /**
     * This is the maximum number of records that can get affected by the operation
     * 
     * @var integer the maximum value of record results
     */
    protected $limit = 0;
    
    /**
     * This is the starting offset of records to be affected by the operation 
     * 
     * @var integer the offset of record results
     */
    protected $offset = 0;
    
    /**
     * This is the list of records selectors a record, to be affected must be 
     * selected using these criteria
     * 
     * @var integer list of records selectors 
     */
    protected $selectors = array();
    
    /**
     * Set the maximum number of affected records
     * 
     * @param integer $limit the maximum value of affected records
     */
    protected function set_limit($limit) {
        $this->limit = intval($limit);
    }
    
    /**
     * Set the offset of affected rows: avoid affecting the specified 
     * number of records
     * 
     * @param integer $offset the starting offset of rows to be affected
     */
    protected function set_offset($offset) {
        $this->offset = intval($offset);
    }
    
    /**
     * Add a selector for a database transaction affecting two or more records
     * 
     * @param string $fieldname the name of the field
     * @param string $fieldcondition the condition relationship between the field name and field value
     * @param mixed $fieldvalue the value to be placed in relationship between the field name
     * @throws DatabaseException the error while adding the record selector
     */
    protected function add_selector($fieldname, $fieldcondition, $fieldvalue) {
        $relationship = "";
            switch (strtolower($fieldcondition)) {
                case "greater":
                case "greaterthan":
                case "gt":
                    $relationship = ">";
                    break;
                
                case "greaterorequal":
                case "greaterorequalthan":
                case "goet":
                    $relationship = ">=";
                    break;
                
                case "lower":
                case "lowerthan":
                case "lt":
                    $relationship = "<";
                    break;
                
                case "lowerorequal":
                case "lowerorequalthan":
                case "loet":
                    $relationship = "<=";
                    break;
                
                case "notequal":
                case "notequalthan":
                case "isnot":
                case "not":
                    $relationship = "!=";
                    break;
                
                case "equal":
                case "is":
                    $relationship = "=";
                    break;
                
                default:
                    throw new DatabaseException("Unknown selector modifier " . $fieldcondition, 7);
            }
            $this->selectors[$fieldname . '[' . $relationship . ']'] = $fieldvalue;
    } 
    
    /**
     * Add a filter to the current selector.
     * 
     * You call this function by calling a function like:
     * <code>
     * $records_selection = RecordsSelector::filters()
     *                          ->where_title_equal('Example Book')
     *                          ->where_price_greatherthan(9.50)
     *                          ->limit(20)
     *                          ->offset(7);
     * </code>
     * 
     * @param string $name the function called name
     * @param array $arguments arguments passed to the called function
     * @return \Gishiki\ActiveRecord\RecordsSelector the selector with the new filter
     */
    public function __call($name, $arguments) {
        if (strpos($name, "where_") === 0) {
            $fieldname = str_replace_once("where_", "", $name);
            $fieldcondition = "";
            
            //get the value relationship
            $current_last_char = "";
            while (($current_last_char = $fieldname[strlen($fieldname) - 1]) != '_') {
                $fieldcondition = $current_last_char . $fieldcondition;
                $fieldname = substr($fieldname, 0, strlen($fieldname) - 1);
            } $fieldname = substr($fieldname, 0, strlen($fieldname) - 1);
            
            //add the current selector
            $this->add_selector($fieldname, $fieldcondition, $arguments[0]);
        } else if (strtolower($name) == "offset") {
            $this->set_offset($arguments[0]);
        } else if (strtolower($name) == "limit") {
            $this->set_limit($arguments[0]);
        } else {
            throw new DatabaseException("Unknown record selection criteria: " . $name, 8);
        }
        
        return $this;
    }
    
    /**
     * Create a new record selector and add filter.
     * 
     * consider:
     * <code>
     * $records_selection = RecordsSelector::filters()
     *                          ->where_title_equal('Example Book')
     *                          ->where_price_greatherthan(9.50)
     *                          ->limit(20)
     *                          ->offset(7);
     * </code>
     * is the same as:
     * <code>
     * $records_selection = RecordsSelector::filters(['where_title_equal' => 'Example Book', 'where_price_greatherthan' => 9.50, 'limit' => 20, 'offset' => 7]);
     * </code>
     * just pick the better syntax.
     * 
     * @param array $filters filters to be applied
     * @return \Gishiki\ActiveRecord\RecordsSelector the newly created records selector
     */
    static function filters($filters = array()) {
        //create a new filter
        $newfilter = new RecordsSelector();
        
        foreach ($filters as $filtername => &$filter) {
            $newfilter->{$filtername}($filter);
        }
        
        //return the newly created filter
        return $newfilter;
    }
    
}