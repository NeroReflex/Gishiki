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

namespace Gishiki\JSON {

    /**
     * This is a list of all possibles types for a JSONValue
     *
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    abstract class JSONValueType {
        const NULL_VALUE = 0;
        const OBJECT_VALUE = 1;
        //const ARRAY_VALUE = 2;
        const BOOL_VALUE = 3;
        const STRING_VALUE = 4;
        const INTEGER_VALUE = 5;
        const FLOAT_VALUE = 6;
    }
}
