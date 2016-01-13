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
********************************************************************************/

namespace Gishiki\Logging {
    
    /**
    * A collection of all possible log priorities
    *
    * Benato Denis <benato.denis96@gmail.com>
    */
    abstract class Priority {
        const EMERGENCY = 0;
        const ALERT = 1;
        const CRITICAL = 2;
        const ERROR = 3;
        const WARNING = 4;
        const NOTICE = 5;
        const INFO = 6;
        const DEBUG = 7;
    }
}