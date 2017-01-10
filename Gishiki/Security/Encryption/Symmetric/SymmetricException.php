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

namespace Gishiki\Security\Encryption\Symmetric;

/**
 * The symmetric encryption exception thrown by Gishiki
 * when a cryptographic operation results is bad.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class SymmetricException extends \Gishiki\Core\Exception
{
    /**
     * Create the symmetric-related exception.
     *
     * @param string $message   the error message
     * @param int    $errorCode the symmetric encryption error code
     */
    public function __construct($message, $errorCode)
    {
        parent::__construct($message, $errorCode);
    }
}
