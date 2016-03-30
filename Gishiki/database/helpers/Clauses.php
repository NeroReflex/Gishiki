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

/**
 * Organize criteria to fetch data from database
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class Clauses {
    //correspondence between the filed and its value
    private $fieldANDValue;
    
    //the replationship between the field and its value
    private $fieldANDValueRalationship;
    
    /**
     * Setup an everything-allowed criteria
     */
    public function __construct() {
        //setup two arrays
        $this->fieldANDValue = array();
        $this->fieldANDValueRalationship = array();
    }
    
    /**
     * Add a criteria to exclude one or more objects to be retrived from the database
     * 
     * @param string $fieldName the name of the class property
     * @param integer $relationship the relationhip between the field and its data, look at Criteria abstract class
     * @param anytype $fieldValue the field data (cannot be a resource nor an object)
     */
    public function AddClause($fieldName, $relationship, $fieldValue = NULL) {
        //check for the type of $fieldName
        if (gettype($fieldName) != "string") {
            throw new DatabaseException("The name of a field must be given as a string", -30);
        }
        
        //check for the type of $relationship
        if (gettype($relationship) != "integer") {
            throw new DatabaseException("The relationship between a field and its value must be given as an integer, look at the Criteria class", -31);
        }
        
        //check for the type of $fieldValue
        $fieldValueType = gettype($fieldValue);
        if (($fieldValueType == "resource") || ($fieldValueType == "unknown")) {
            throw new DatabaseException("The value of a field cannot be a resource nor an unknown value", -30);
        }
        
        //add it to the allowed results
        $this->fieldANDValue[$fieldName] = $fieldValue;
        $this->fieldANDValueRalationship[$fieldName] = $relationship;
    }
    
    /**
     * 
     * @return array an array containing 
     */
    private function GetClauses() {
        $toReturn = array(
            0 => $this->fieldANDValue,
            1 => $this->fieldANDValueRalationship,
        );
        
        return $toReturn;
    }
}