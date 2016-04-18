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

namespace Gishiki\Tests\Algorithms;

use Gishiki\Algorithms\Manipulation;

class ManipulationTest extends \PHPUnit_Framework_TestCase {
    
    
    public function testInterpolation() {
        $correct_message = "Carnevale vecchio e pazzo
s’è venduto il materasso
per comprare pane, vino,
tarallucci e cotechino.
E mangiando a crepapelle
la montagna di frittelle
gli è cresciuto un gran pancione
che somiglia ad un pallone.
Beve, beve all’improvviso
gli diventa rosso il viso
poi gli scoppia anche la pancia
mentre ancora mangia, mangia.
Così muore il Carnevale
e gli fanno il funerale:
dalla polvere era nato
e di polvere è tornato.";
        
        $message_without_data = "Carnevale {{age}} e {{how}}
s’è venduto il {{what sold}}
per comprare pane, vino,
tarallucci e {{to_buy}}.
E mangiando a crepapelle
la montagna di frittelle
gli è cresciuto un gran pancione
che somiglia ad un pallone.
Beve, beve all’improvviso
gli diventa rosso il viso
poi gli scoppia anche la pancia
mentre ancora mangia, mangia.
Così muore il Carnevale
e gli fanno il funerale:
dalla {{is}} era nato
e di {{is}} è tornato.";
        $data = [
            'age' => 'vecchio', 
            'how' => 'pazzo',
            'what sold' => 'materasso',
            'to_buy' => 'cotechino',
            'is' => 'polvere'];
        $message_with_data = Manipulation::str_interpolate($message_without_data, $data);
        
        
        //test if the interpolation works
        $this->assertEquals($message_with_data, $correct_message);
    }
}