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

namespace Gishiki\tests\Algorithms;

use Gishiki\Algorithms\Manipulation;

class ManipulationTest extends \PHPUnit_Framework_TestCase
{
    public function testInterpolation()
    {
        $correct_message = 'Carnevale vecchio e pazzo
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
e di polvere è tornato.';

        $message_without_data = 'Carnevale {{age}} e {{how}}
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
e di {{is}} è tornato.';
        $data = [
            'age' => 'vecchio',
            'how' => 'pazzo',
            'what sold' => 'materasso',
            'to_buy' => 'cotechino',
            'is' => 'polvere', ];
        $message_with_data = Manipulation::interpolate($message_without_data, $data);

        //test if the interpolation works
        $this->assertEquals($message_with_data, $correct_message);
    }

    public function testBetween()
    {
        $string = "@ >this is a test # T0 ch3ck 1h3 ''getBetween'# function";

        //test if the getBetween works using strings as delimiters works
        $this->assertEquals('getBetween', Manipulation::getBetween($string, "''", "'#"));

        //test if the getBetween works using characters as delimiters works
        $this->assertEquals('this is a test ', Manipulation::getBetween($string, '>', '#'));

        //test for strange failures
        $this->assertEquals(false, Manipulation::getBetween($string, '@', '##'));
    }

    public function testReplaceOnce()
    {
        $string = '* * * * * - *';
        $replace_with = '-';

        //perform & test the first sobstitution
        $this->assertEquals('- * * * * - *', Manipulation::replaceOnce('*', $replace_with, $string));
    }

    public function testReplaceOnceNoMatches()
    {
        $string = '* * * * * - *';
        $replace_with = '-';

        //perform & test the null sobstitution
        $this->assertEquals('* * * * * - *', Manipulation::replaceOnce('/', $replace_with, $string));
    }

    public function testReplaceList()
    {
        $string = '* * * @ * - *';
        $replace_with = '-';

        //perform & test the first sobstitution
        $this->assertEquals('- - - - - - -', Manipulation::replaceList(['*', '@'], $replace_with, $string));
    }

    public function testReplaceListNoMatches()
    {
        $string = '- - - - - - -';
        $replace_with = '+';

        //perform & test the first sobstitution
        $this->assertEquals('- - - - - - -', Manipulation::replaceList(['*', '@'], $replace_with, $string));
    }
}
