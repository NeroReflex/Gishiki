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

namespace Gishiki\tests\JSON;

use Gishiki\JSON\JSON;

class JSONTest extends \PHPUnit_Framework_TestCase
{
    public function testDecode()
    {
        $jsonMsg = '{
    "glossary": {
        "title": "example glossary",
		"GlossDiv": {
            "title": "S",
			"GlossList": {
                "GlossEntry": {
                    "ID": "SGML",
					"SortAs": "SGML",
					"GlossTerm": "Standard Generalized Markup Language",
					"Acronym": "SGML",
					"Abbrev": "ISO 8879:1986",
					"GlossDef": {
                        "para": "A meta-markup language, used to create markup languages such as DocBook.",
						"GlossSeeAlso": ["GML", "XML"]
                    },
					"GlossSee": "markup"
                }
            }
        }
    }
}';

        $jsonObj = array(
  'glossary' => array(
    'title' => 'example glossary',
    'GlossDiv' => array(
      'title' => 'S',
      'GlossList' => array(
        'GlossEntry' => array(
          'ID' => 'SGML',
          'SortAs' => 'SGML',
          'GlossTerm' => 'Standard Generalized Markup Language',
          'Acronym' => 'SGML',
          'Abbrev' => 'ISO 8879:1986',
          'GlossDef' => array(
            'para' => 'A meta-markup language, used to create markup languages such as DocBook.',
            'GlossSeeAlso' => array(
              0 => 'GML',
              1 => 'XML',
            ),
          ),
          'GlossSee' => 'markup',
        ),
      ),
    ),
  ),
);

        //test the deserialization
        $this->assertEquals($jsonObj, JSON::DeSerialize($jsonMsg));
    }

    /**
     * @expectedException Gishiki\JSON\JSONException
     */
    public function testDecodeMalformed()
    {
        $jsonMsg = '{
    "glossary": {
        "title": "example glossary",
		"GlossDiv": {
            "title": "S",
			"GlossList": {
                "GlossEntry": {
                    "ID": "SGML",
					"SortAs": "SGML",
					"GlossTerm": "Standard Generalized Markup Language",
					"Acronym": "SGML",
					"Abbrev": "ISO 8879:1986",
					"GlossDef
                                        : {
                        "para": "A meta-markup language, used to create markup languages such as DocBook.",
						"GlossSeeAlso": ["GML", "XML"]
                    },
					"GlossSee": "markup"
                }
            }
        }
    }
';

        //test the deserialization
        JSON::DeSerialize($jsonMsg);
    }

    public function testEncode()
    {
        $jsonMsg = '{
    "glossary": {
        "title": "example glossary",
		"GlossDiv": {
            "title": "S",
			"GlossList": {
                "GlossEntry": {
                    "ID": "SGML",
					"SortAs": "SGML",
					"GlossTerm": "Standard Generalized Markup Language",
					"Acronym": "SGML",
					"Abbrev": "ISO 8879:1986",
					"GlossDef": {
                        "para": "A meta-markup language, used to create markup languages such as DocBook.",
						"GlossSeeAlso": ["GML", "XML"]
                    },
					"GlossSee": "markup"
                }
            }
        }
    }
}';

        $jsonObj = array(
  'glossary' => array(
    'title' => 'example glossary',
    'GlossDiv' => array(
      'title' => 'S',
      'GlossList' => array(
        'GlossEntry' => array(
          'ID' => 'SGML',
          'SortAs' => 'SGML',
          'GlossTerm' => 'Standard Generalized Markup Language',
          'Acronym' => 'SGML',
          'Abbrev' => 'ISO 8879:1986',
          'GlossDef' => array(
            'para' => 'A meta-markup language, used to create markup languages such as DocBook.',
            'GlossSeeAlso' => array(
              0 => 'GML',
              1 => 'XML',
            ),
          ),
          'GlossSee' => 'markup',
        ),
      ),
    ),
  ),
);

        //test the deserialization
        $this->assertEquals($jsonObj, JSON::DeSerialize(JSON::Serialize($jsonObj)));
    }
}
