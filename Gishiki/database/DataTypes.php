<?php
/****************************************************************************
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

namespace Gishiki\Database {
    
    /**
     * A collection of data types supported on every database system
     *
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    abstract class DataTypes {
        
        /* the string data type is supperted even by my toothbrush if used as
         * a database management system.
         */
        const STRING        = 0;
        
        /* a boolean value is stored as a boolean value only when supported,
         * if it is not supported by that database management system it will be
         * threat as an integer.
         */
        const BOOLEAN       = 1;
        
        /* on a database management system that doesn't support the data 
         * type 'timestamp', integer will be used (to store the date as an 
         * unix timestamp)
         */
        const TIME          = 2;
        
        /* an integer of medium size, consider that this integer might be 16 or 
         * 32 bits signed. It depends on the database management system used
         */
        const INTEGER       = 3;
        
        /* an integer of the smallest size possible, but at least 8 bit long.
         * This integer will be signed, so don't try to store values greater
         * than 127.
         */
        const SMALL_INTEGER = 4;
        
        /* an integer of the larger size possible. Use this data type if you 
         * want to store gigantic numbers
         */
        const BIG_INTEGER   = 5;
        
        /* a floating point data type, the bigger one is the one that is
         * going to be used
         */
        const REAL          = 6;
        
        /* a data type used to hold in-memory something that is not binary-safe
         * nor can be categorized. If a database management system doesn't 
         * support this data type string will be used
         */
        const BLOB          = 7;
    }
}