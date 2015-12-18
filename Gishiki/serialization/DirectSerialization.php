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

namespace Gishiki\Serialization {

	/**
	 * An helper class for general data serialization
	 *
	 * Benato Denis <benato.denis96@gmail.com>
	 */
	abstract class DirectSerialization {
		/**
		 * Prepare a value to be serialized in a string
		 * 
		 * @param mixed $value the value to be serialized. Can be string, integer, double, boolean, array and object
		 */
		static function SerializeValue($value) {
			//get the type of the value that have to be serialized
			$valuetype = gettype($value);
			
			//the serialized value
			$serializedValue = NULL;
			
			switch ($valuetype) {
				case "integer":
					$serializedValue = "integer@".(string)$value;
					break;
				case "double":
					$serializedValue = "floating-point@".(string)$value;
					break;
				case "string":
					$serializedValue = "string@".$value;
					break;
				case "NULL":
					$serializedValue = "null@"."NULL";
					break;
				case "object":
					$serializedValue = "object@".base64_encode(serialize($value));
					break;
				case "array":
					$serializedValue = "array@".json_encode($value);
					break;
				default:
					throw new SerializationException("Unserializabe value", 0);
			}
			
			//return the serialized value
			return $serializedValue;
		}
		
		/**
		 * Unserialize a value serialized by DirectSerialization::Serialize()
		 * 
		 * @param string $serializedValue the value serialized by DirectSerialization::Serialize()
		 */
		static function DeserializeValue($serializedValue) {
			//the unserialized value
			$unserialized = NULL;
			
			//split the serialized value into an array of 2 elements:
			$valueSplit = explode("@", $serializedValue, 2);
			//the first element is the value type,
			//the second element is the value string-encoded
			
			switch ($valueSplit[0]) {
				case "integer":
					$unserialized = intval($valueSplit[1]);
					break;
				case "floating-point":
					$unserialized = floatval($valueSplit[1]);
					break;
				case "string":
					$unserialized = $valueSplit[1];
					break;
				case "null":
					$unserialized = NULL;
					break;
				case "object":
					$unserialized = unserialize(base64_decode($valueSplit[0]));
					break;
				case "array":
					$serializedValue = "array:".json_decode($value);
					break;
				default:
					throw new SerializationException("Invalid serialized value", 0);
			}
			
			//return the de-serialized value
			return $unserialized;
		}
	}
}