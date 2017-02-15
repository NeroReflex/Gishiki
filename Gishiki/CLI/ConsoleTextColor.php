<?php
/**************************************************************************
Copyright 2017 Benato Denis

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

namespace Gishiki\CLI;

/**
 * The collection of console text colors.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class ConsoleTextColor {
    const off        = 0;
    const bold       = 1;
    const italic     = 3;
    const underline  = 4;
    const blink      = 5;
    const inverse    = 7;
    const hidden     = 8;
    const black      = 30;
    const red        = 31;
    const green      = 32;
    const yellow     = 33;
    const blue       = 34;
    const magenta    = 35;
    const cyan       = 36;
    const white      = 37;
}
