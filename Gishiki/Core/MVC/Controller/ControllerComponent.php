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

namespace Gishiki\Core\MVC\Controller;

use Gishiki\Algorithms\Collections\SerializableCollection;

/**
 * This class represents a component
 * that will be used by many controllers to generate
 * a complete response.
 *
 * The task of a single component is to generate a piece of
 * the response in a serialized format.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class ControllerComponent
{
    /**
     * @var SerializableCollection the data to be filled
     */
    protected $data = null;

    /**
     * Setup a controller component.
     */
    public function __construct()
    {
        $this->data = new SerializableCollection();
    }

    /**
     * Get the reference to the data that will be (or already was)
     * managed by the component object.
     *
     * @return SerializableCollection the data within the current component
     */
    public function &getData() : SerializableCollection
    {
        return $this->data;
    }
}