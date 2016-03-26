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
        /** integer-number data type 
         * (automatically chosen from the available ones for a given RDBMS) 
         */
        const INTEGER = 0;
        
        /** string data type 
         * (every RDBMS should be able to handle string type) 
         */
        const STRING = 1;
        
        /** floating-point-number data type 
         * (every RDBMS should be able to handle a floating-point type, 
         * but the actual implementation may vary)
         */
        const FLOAT = 2;
        
        /** boolean data type 
         * (if an RDBMS doesn't support bool data it is automatically translated
         * into an integer that resembles a boolean)
         */
        const BOOLEAN = 3;
    }
}