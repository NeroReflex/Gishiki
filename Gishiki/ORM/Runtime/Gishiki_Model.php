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

namespace Gishiki\ORM\Runtime;

/**
 * Description of Gishiki_Model
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class Gishiki_Model extends \Gishiki\Algorithms\CyclableCollection {

    /**
     * has the model been modified? (and thus need to be inserted/updated on the
     * database or data source)
     * 
     * @var boolean TRUE if the model should be written to the database
     */
    protected $modified = FALSE;
    
    /**
     * Is the current model going to be saved before being deleted from the memory?
     * 
     * @var bool TRUE if the model is going to be automatically inserted/updated 
     */
    protected $auto_save = TRUE;
    
    /**
     * Provide basic setup operation of a model
     */
    public function __construct() {
        //automatically save the model
        $this->auto_save = TRUE;
        
        //the model is new, and therefore, needs to be inserted to the database
        $this->modified = TRUE;
    }
    
    /**
     * Set a property of the current model
     * 
     * @param string $key the index must be the name of a field
     * @param mixed $value the value that is going to be saved into the database
     * @return mixed the given value
     */
    public function set($key, $value) {
        //the model is going to be edited
        $this->modified = TRUE;
        
        //set the property
        return parent::set($key, $value);
    }
    
    /**
     * Prevent the model to be automatically saved when removed from the memory
     */
    public function illegalAutoUpdate() {
        $this->auto_save = FALSE;
    }
    
    /**
     * Perform finalization operations ans save the current model to the database
     * if that operation is not marked as "manual only"
     */
    public function __destruct() {
        //make sure the model is automatically updated on the database if it should be
        if (($this->auto_save) && ($this->modified))
        {   $this->save();  }
    }
    
    /**
     * Force the current model to be saved to the database, but you can choose 
     * the timing:
     *     - when the model is removed from memory
     *     - on function call
     * 
     * @param boolean $when_removed if set (TRUE) will undo an illegalAutoUpdate() function call
     */
    public function save($when_removed = FALSE) {
        if ($when_removed) //restore the auto-update
        {   $this->auto_save = TRUE;    }
        else {
            //get the name of the primary key used to choose update or insert
            $primary_key_name = static::PrimaryKey_Name();
            
            //create a copy of data to be saved (inserted)
            $data = $this->array;
            unset($data[$primary_key_name]);
            
            //get the database connection to be used
            $connHandler = \Gishiki\Core\Environment::GetCurrentEnvironment()->GetConnection(static::Connection_Name());

            //check if the data should be updated or inserted
            if (!$this->exists_key($primary_key_name))
            { //this model needs to be inserted
                $connHandler->insert(get_called_class(), $data);
            } else {  //this model needs to be updated
                $connHandler->update(get_called_class(), $data, [static::PrimaryKey_Name() => $this->array->get(static::PrimaryKey_Name())] );
            }
        }
    }
    
    /**
     * get the name of the connection to the proper database:
     * this is a stub value for the base model 
     * class, but every configuration should include a connection named default
     * 
     * @return string the name of the connection to be used
     */
    protected static function Connection_Name() {
        return "default";
    }
    
    /**
     * get the name of the primary key (stub value for the base model class)
     * 
     * @return string the name of the primary key field
     */
    protected static function PrimaryKey_Name() {
        return "id";
    }
}
