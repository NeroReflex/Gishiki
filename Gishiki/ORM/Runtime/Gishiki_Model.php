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
 * @author denis
 */
class Gishiki_Model extends \Gishiki\Algorithms\CyclableCollection {
    
    /**
     * has the model been modified? (and thus need to be inserted/updated on the
     * database or data source)
     * 
     * @var boolean TRUE if the model should be written to the database
     */
    protected $modified = false;
    
    /**
     * Is the current model going to be saved before being deleted from the memory?
     * 
     * @var bool TRUE if the model is going to be automatically inserted/updated 
     */
    protected $auto_save = true;
    
    /**
     * Provide basic setup operation of a model
     */
    public function __construct() {
        //automatically save the model
        $this->auto_save = TRUE;
        
        //the model is new, and therefore, needs to be inserted to the database
        $this->modified = TRUE;
    }
    
    
}
