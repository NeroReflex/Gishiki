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

namespace Gishiki\Security\Encryption\Symmetric;

/**
 * This class represents an algorithm collection for the asymmetric
 * encryption engine.
 *
 * Note: This class uses OpenSSL for strong encryption
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class Cryptography
{
    /******************************************************
     *              List of known algorithms              *
     ******************************************************/
    const AES_CBC_128           =  'aes-128-cbc';
    const AES_CBC_192           =  'aes-192-cbc';
    const AES_CBC_256           =  'aes-256-cbc';
    
    public static function encrypt(SecretKey &$key, $message, $algorithm = self::AES_CBC_128) {
        //check the plain message type
        if ((!is_string($message)) || (strlen($message) <= 0)) {
            throw new \InvalidArgumentException('The plain message to be encrypted must be given as a non-empty string');
        }
        
        //get the managed kersion of the key
        $managedKey = $key();
        
        //check for the key length
        if (($algorithm == self::AES_CBC_128) && ($managedKey["byteLength"] != 16) ) {
            throw new SymmetricException("You must be using a key with the correct length for the choosen algorithm", 0); 
        }
        
        
    }
}
