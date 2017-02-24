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

namespace Gishiki\tests\Algorithms\Collections;

use PHPUnit\Framework\TestCase;

use Gishiki\Algorithms\Collections\DeserializationException;
use Gishiki\Algorithms\Collections\SerializationException;
use Gishiki\Algorithms\Collections\SerializableCollection;
use Gishiki\Algorithms\Collections\GenericCollection;

class SerializationCollectionTest extends TestCase
{
    public function testCollectionDeserialization()
    {
        $collection = new SerializableCollection([
            'a' => 1,
            'b' => 5.50,
            'c' => 'srf',
            'e' => true,
            'f' => [1, 2, 3, 4],
        ]);

        $this->assertEquals($collection, SerializableCollection::deserialize($collection));
    }

    public function testCollectionDefault()
    {
        $expected = [
            'a' => 1,
            'b' => 5.50,
            'c' => 'srf',
            'e' => true,
            'f' => [1, 2, 3, 4],
        ];

        $collection = new SerializableCollection($expected);

        $this->assertEquals($expected, SerializableCollection::deserialize((string) $collection)->all());
    }

    public function testCollection()
    {
        $collection = new SerializableCollection();
        $collection->set('a', 1);
        $collection->set('b', 5.50);
        $collection->set('c', 'srf');
        $collection->set('e', true);
        $collection->set('f', [1, 2, 3, 4]);

        $this->assertEquals([
            'a' => 1,
            'b' => 5.50,
            'c' => 'srf',
            'e' => true,
            'f' => [1, 2, 3, 4],
        ], $collection->all());
    }

    public function testJsonSerialization()
    {
        $collection = new SerializableCollection([
            'a' => 1,
            'b' => 5.50,
            'c' => 'srf',
            'e' => true,
            'f' => [1, 2, 3, 4],
        ]);

        $serializationResult = /*json_encode*/($collection->serialize());
        $serialization = json_decode($serializationResult, true);

        $this->assertEquals([
            'a' => 1,
            'b' => 5.50,
            'c' => 'srf',
            'e' => true,
            'f' => [1, 2, 3, 4],
        ], $serialization);
    }

    public function testXmlSerialization()
    {
        $collection = new SerializableCollection([
            'a' => 1,
            'b' => 5.50,
            'c' => 'srf',
            'e' => true,
            'f' => [1, 2, 3, 4],
        ]);

        $serializationResult = $collection->serialize(SerializableCollection::XML);
        $serialization = SerializableCollection::deserialize($serializationResult, SerializableCollection::XML);

        $this->assertEquals([
            'a' => 1,
            'b' => 5.50,
            'c' => 'srf',
            'e' => true,
            'f' => [1, 2, 3, 4],
        ], $serialization->all());
    }

    public function testYamlSerialization()
    {
        $collection = new SerializableCollection([
            'a' => 1,
            'b' => 5.50,
            'c' => 'srf',
            'e' => true,
            'f' => [1, 2, 3, 4],
        ]);

        $serializationResult = $collection->serialize(SerializableCollection::YAML);
        $serialization = \Symfony\Component\Yaml\Yaml::parse($serializationResult);

        $this->assertEquals([
            'a' => 1,
            'b' => 5.50,
            'c' => 'srf',
            'e' => true,
            'f' => [1, 2, 3, 4],
        ], $serialization);
    }

    public function testJsonDeserialization()
    {
        $this->assertEquals(new SerializableCollection([
            'a' => 1,
            'b' => 5.50,
            'c' => 'srf',
            'e' => true,
            'f' => [1, 2, 3, 4],
        ]), SerializableCollection::deserialize('{"a":1,"b":5.5,"c":"srf","e":true,"f":[1,2,3,4]}'));
    }

    public function testYamlDeserialization()
    {
        $yaml = <<<'EOD'
--- !clarkevans.com/^invoice
invoice: 34843
date: "2001-01-23"
bill-to: &id001
  given: Chris
  family: Dumars
  address:
    lines: |-
      458 Walkman Dr. Suite #292
    city: Royal Oak
    state: MI
    postal: 48046
ship-to: *id001
product:
- sku: BL394D
  quantity: 4
  description: Basketball
  price: 450
- sku: BL4438H
  quantity: 1
  description: Super Hoop
  price: 2392
tax: 251.420000
total: 4443.520000
comments: Late afternoon is best. Backup contact is Nancy Billsmer @ 338-4338.
...
EOD;

        $this->assertEquals([
  'invoice' => 34843,
  'date' => '2001-01-23',
  'bill-to' => [
    'given' => 'Chris',
    'family' => 'Dumars',
    'address' => [
      'lines' => '458 Walkman Dr. Suite #292',
      'city' => 'Royal Oak',
      'state' => 'MI',
      'postal' => 48046,
    ],
  ],
  'ship-to' => [
    'given' => 'Chris',
    'family' => 'Dumars',
    'address' => [
      'lines' => '458 Walkman Dr. Suite #292',
      'city' => 'Royal Oak',
      'state' => 'MI',
      'postal' => 48046,
    ],
  ],
  'product' => [
    0 => [
      'sku' => 'BL394D',
      'quantity' => 4,
      'description' => 'Basketball',
      'price' => 450,
    ],
    1 => [
            'sku' => 'BL4438H',
            'quantity' => 1,
            'description' => 'Super Hoop',
            'price' => 2392,
    ],
  ],
  'tax' => 251.42,
  'total' => 4443.52,
  'comments' => 'Late afternoon is best. Backup contact is Nancy Billsmer @ 338-4338.',
], SerializableCollection::deserialize($yaml, SerializableCollection::YAML)->all());
    }

    public function testLoadFromAnotherGenericCollection()
    {
        $collection = new SerializableCollection([
            'a' => 1,
            'b' => 5.50,
            'c' => 'srf',
            'e' => true,
            'f' => [1, 2, 3, 4],
        ]);

        $this->assertEquals($collection, new SerializableCollection($collection));
    }

    public function testLoadFromAnotherSerializableCollection()
    {
        $collection = new GenericCollection([
            'a' => 1,
            'b' => 5.50,
            'c' => 'srf',
            'e' => true,
            'f' => [1, 2, 3, 4],
        ]);

        $this->assertEquals($collection->all(), (new SerializableCollection($collection))->all());
    }

    public function testNotStringJsonDeserialization()
    {
        $this->expectException(DeserializationException::class);
        SerializableCollection::deserialize(9.70, SerializableCollection::JSON);
    }

    public function testNotStringXmlDeserialization()
    {
        $this->expectException(DeserializationException::class);
        SerializableCollection::deserialize(new \stdClass(), SerializableCollection::XML);
    }

    public function testNotStringYamlDeserialization()
    {
        $this->expectException(DeserializationException::class);
        SerializableCollection::deserialize(false, SerializableCollection::YAML);
    }

    public function testBadDeserializator()
    {
        $this->expectException(DeserializationException::class);
        SerializableCollection::deserialize('{---', 'this cannot be a valid deserializator!');
    }

    public function testBadYamlDeserialization()
    {
        $this->expectException(DeserializationException::class);
        $badYaml =
'x
language:';

        SerializableCollection::deserialize($badYaml, SerializableCollection::YAML);
    }

    public function testBadXmlDeserialization()
    {
        $this->expectException(DeserializationException::class);
        $badXml = <<<'XML'
<root>probl<em>
                </root>
                
XML;

        SerializableCollection::deserialize($badXml, SerializableCollection::XML);
    }

    public function testBadJsonDeserialization()
    {
        $this->expectException(DeserializationException::class);
        SerializableCollection::deserialize('bad json', SerializableCollection::JSON);
    }

    public function testUnknownSerializationException()
    {
        $this->expectException(SerializationException::class);
        (new SerializableCollection([
            'a' => 1,
            'b' => 5.50,
            'c' => 'srf',
            'e' => true,
            'f' => [1, 2, 3, 4],
        ]))->serialize(255);
    }

    public function testInvalidSerializationException()
    {
        $this->expectException(\InvalidArgumentException::class);
        (new SerializableCollection([
            'a' => 1,
            'b' => 5.50,
            'c' => 'srf',
            'e' => true,
            'f' => [1, 2, 3, 4],
        ]))->serialize('WTF?!?');
    }
    
    public function testBadSerializationException()
    {
        $this->expectException(SerializationException::class);
        $obj = (new SerializableCollection([
            'a' => 1,
            'b' => 5.50,
            'c' => 'srf',
            'e' => true,
            'f' => [1, 2, 3, 4],
        ]));

        $obj->set('bad_chars', "\xB1\x31");

        $obj->serialize();
    }
}
