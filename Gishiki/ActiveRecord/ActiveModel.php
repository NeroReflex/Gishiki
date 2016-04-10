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
 * This is the representation of a model for the ActiveRecord engine
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class ActiveModel extends \Gishiki\Algorithms\CyclableCollection {
    
    /**
     * Keep track of modified attributes.
     * Do not touch! Internal use ONLY!
     * 
     * @var array what attributes were changed?
     */
    protected $__dirty = array();
    
    /**
     * Do not touch! Internal use ONLY!
     * 
     * @var boolean can this model be saved to the database?
     */
    protected $__ghost = false;
    
    /**
     * This is the name of the table/collection for all models
     * of the same type
     * 
     * @var string the name of the primary key
     */
    static $table_name = "";
    
    /**
     * This is the name of the primary key for all models
     * of the same type
     * 
     * @var string the name of the primary key
     */
    static $primary_key = "id";
            
    /**
     * This is the name of the connection that all models
     * of the same type are sharing
     * 
     * @var string the name of the primary key
     */
    static $connection = null;
    
    /**
     * Creates an empty model from a data collection.
     * 
     * The data collection is expressed as an associative array:
     * <code>
     * class Book extends ActiveModel { }
     * 
     * $mybook = new Book(['author' => 'Example Author', 'title' => 'Example Book', 'price' => 5.50]);
     * 
     * //nah, kidding :')
     * $mybook->price += 10.50;
     * </code>
     * 
     * @param array $setup_values the collection used to build the current model
     */
    public function __construct($setup_values = []) {
        parent::__construct($setup_values);
        
        //you have blown all properties!
        $this->__dirty = array_keys($setup_values);
        
        $this->array[static::$primary_key] = null;
    }
    
    /**
     * Automatically save your model if needed
     */
    public function __destruct() {
        if (count($this->__dirty) > 0)
        {   $this->save();  }
    }
    
    public function __set($key, $value) {
        if (!in_array($key, $this->__dirty))
        {   $this->__dirty[] = $key;    }
        
        //use a set filter
        $filter_setter_name = "__filter_set_" . $key;
        
        //use a filter if it exists
        if (method_exists($this, $filter_setter_name))
        {   $value = $this->$filter_setter_name($value);     }
        
        parent::__set($key, $value);
    }
    
    public function &__get($key) {
        //use a set filter
        $filter_getter_name = "__filter_get_" . $key;
        
        //use a filter if it exists
        if (method_exists($this, $filter_getter_name))
        {   return $this->$filter_getter_name(parent::__get($key));     }
        else {  return parent::__get($key); }
    }
    
    /**
     * Create a new model with the given attributes and immediatly save it into
     * the storing engine
     * 
     * <code>
     * class Book extends ActiveModel {  };
     * 
     * $my_new_book = Book::Create(['title' => 'My book', 'author' => 'me',  ... ]);
     * </code>
     * 
     * @param array $attributes the array of attributes
     * @return mixed a new instance of your model
     */
    static function Create($attributes = []) {
        //create a new instance of the model
        $model_reflecter = new \ReflectionClass(get_called_class());
        $new_model_instance = $model_reflecter->newInstance($attributes);
        
        //immediat model store
        $new_model_instance->save();
        
        //return the new model
        return $new_model_instance;
    }
    
    /**
     * Destroy all models that are selected by the given record selector
     * 
     * <code>
     * class Book extends ActiveModel {  };
     * 
     * $my_new_book = Book::Destroy(RecordsSelector::filters()
     *                              ->where_title_is('Example Book'));
     * </code>
     * 
     * @param RecordsSelector $selector the array of attributes
     * @return integer the number of removed records
     */
    static function Destroy(RecordsSelector $selector) {
        //get the database connection
        $db_connection = ConnectionsProvider::FetchConnection(static::$connection);
        
        //delete records from the current table using the given selector
        return $db_connection->Delete(self::getTableName(), $selector);
    }
    
    /**
     * Retrive, from the currently used database, all the records matching the given criteria
     * 
     * @param \Gishiki\ActiveRecord\RecordsSelector $selector the filter to be applied to select records
     * @return \Gishiki\ActiveRecord\ActiveResult the collection of fetched records
     */
    static function Dispense(RecordsSelector $selector) {
        //get the database connection
        $db_connection = ConnectionsProvider::FetchConnection(static::$connection);
        
        //fetch records from the current table using the given selector
        $records = $db_connection->Read(self::getTableName(), $selector);
        
        //foreach record build a model an insert into the models array
        $models = array(); $i = 0;
        foreach($records as &$record) {
            //create the new model instance
            $model_reflected = new \ReflectionClass(get_called_class());
            $models[$i] = $model_reflected->newInstance();
            
            //and fill it
            $model_data = new \ReflectionProperty($models[$i], 'array');
            $model_data->setAccessible(TRUE);
            $model_data->setValue($models[$i], $record);
            
            $model_dirty_data = new \ReflectionProperty($models[$i], '__dirty');
            $model_dirty_data->setAccessible(TRUE);
            $model_dirty_data->setValue($models[$i], array());
            
            $i++;
        }
        
        //return the new models array
        return new ActiveResult($models);
    }
    
    /**
     * Lock or unlock the model in ghost mode.
     + A ghost model cannot be modified or saved into the database.
     * 
     * @param boolean $readonly if TRUE the model will become a ghost
     */
    public function Ghost($readonly = true) {
        $this->__ghost = ($readonly == true);
    }
    
    /**
     * Save the current model into the database
     */
    public function save() {
        if (!$this->__ghost) {
            //get the database connection
            $db_connection = ConnectionsProvider::FetchConnection(static::$connection);
            
            if (count($this->__dirty) > 0) {
                if (($this->array[static::$primary_key] === null) || (0 === count(static::Dispense(RecordsSelector::filters()->{'where_' . static::$primary_key . '_is'}($this->array[static::$primary_key])->limit(1))))) {
                    //build the insertion array (the model without id)
                    $insertion_array = $this->array;
                    unset($insertion_array[static::$primary_key]);
                    
                    //store the id of the newly saved model
                    $this->array[static::$primary_key] = $db_connection->Create(self::getTableName(), $insertion_array, static::$primary_key);
                } else {
                    //build the update array
                    $new_value = array();
                    foreach ($this->__dirty as &$dirty_key)
                    {   $new_value[$dirty_key] = $this->array[$dirty_key];    }
                        
                    //update the model
                    $db_connection->Update(self::getTableName(), $new_value, RecordsSelector::filters(["where_" . static::$primary_key . "_equal" => $this->array[static::$primary_key], ]));
                }
            }
            
            //no dirty attributes now!
            $this->__dirty = array();
        }
    }
    
    /**
     * Delete the current model from the database
     * and lock it into readonly mode to avoid the autosave
     */
    public function remove() {
        //delete the model if it is not null
        if ($this->array[static::$primary_key] !== null) {
            //get the database connection
            $db_connection = ConnectionsProvider::FetchConnection(static::$connection);
            
            //update the model
            $db_connection->Delete(self::getTableName(), RecordsSelector::filters(["where_" . static::$primary_key . "_equal" => $this->array[static::$primary_key], ]));
        }
        
        //this model is not mapped on the database
        $this->array[static::$primary_key] = null;
        
        //set it in readonly mode
        $this->Ghost(true);
    }
    
    /**
     * Return the name of the reflected database table or collection
     * 
     * @return string the real name of the tabse
     */
    protected static function getTableName() {
        //get the name of the table
        if (!static::$table_name) {
            static::$table_name = strtolower(get_called_class()) . "s";
            
            static::$table_name = str_replace("/", "_", static::$table_name);
            static::$table_name = str_replace("\\", "_", static::$table_name);
        }
        
        return static::$table_name;
    }
}