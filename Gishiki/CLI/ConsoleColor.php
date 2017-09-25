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
abstract class ConsoleColor
{
    const OFF = 0;

    const TEXT_BOLD = 1;
    const TEXT_ITALIC = 3;
    const TEXT_UNDERLINE = 4;
    const TEXT_BLINK = 5;
    const TEXT_INVERSE = 7;
    const TEXT_HIDDEN = 8;

    const TEXT_BLACK = 30;
    const TEXT_RED = 31;
    const TEXT_GREEN = 32;
    const TEXT_YELLOW = 33;
    const TEXT_BLUE = 34;
    const TEXT_MAGENTA = 35;
    const TEXT_CYAN = 36;
    const TEXT_WHITE = 37;

    const BACKGROUND_BLACK = 40;
    const BACKGROUND_RED = 41;
    const BACKGROUND_GREEN = 42;
    const BACKGROUND_YELLOW = 43;
    const BACKGROUND_BLUE = 44;
    const BACKGROUND_MAGENTA = 45;
    const BACKGROUND_CYAN = 46;
    const BACKGROUND_WHITE = 47;
}
