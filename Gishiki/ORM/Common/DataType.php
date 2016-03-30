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

namespace Gishiki\ORM\Common {
    
    /**
     * Abstract representation of the type of the data that can be stored in a field
     *
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    abstract class DataType {
        /** 
         * integer-number data type 
         */
        const INTEGER = 0;
        
        /** 
         * string data type
         */
        const STRING = 1;
        
        /** 
         * floating-point-number data type 
         */
        const FLOAT = 2;
        
        /** 
         * boolean data type 
         */
        const BOOLEAN = 3;
        
        /**
         * date data type
         */
        const DATETIME = 4;
    }
}