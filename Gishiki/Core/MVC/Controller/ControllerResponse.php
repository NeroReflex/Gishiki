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
use Psr\Http\Message\ResponseInterface;

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
final class ControllerResponse extends ControllerComponent
{
    /**
     * Use a component function to complete the controller response.
     *
     * @param  string $componentName     the name of the component to be used
     * @param  string $componentFunction the name of the action to be performed
     * @param  array  $args              the list of parameters to be passed to the action
     * @return mixed  the value returned by the component action
     * @throws ControllerException the error preventing the body to be written
     */
    public function import($componentName, $componentFunction, array $args = [])
    {
        //check if the component exists
        if (!class_exists($componentName)) {
            throw new ControllerException("The given component name (".$componentName.") doesn't name a class", 1);
        }

        //reflect the component
        $reflectedComponent = null;
        try {
            $reflectedComponent = new \ReflectionClass($componentName);
        } catch (\ReflectionException $ex) {
            throw new ControllerException("The given controller component cannot be used", 2);
        }

        //use the reflected component to instantiate a component
        $component = $reflectedComponent->newInstance();

        //check whether the component implements the component interface
        if (!($component instanceof ControllerComponent)) {
            throw new ControllerException("The given class is not a valid controller component", 3);
        }

        //use the component object to reflect the action
        $reflectedAction = null;
        try {
            $reflectedAction = new \ReflectionMethod($component, $componentFunction);
        } catch (\ReflectionException $ex) {
            throw new ControllerException("The given action doesn't exists withing the current controller component", 4);
        }

        //call the component action
        $reflectedAction->setAccessible(true);
        $result = $reflectedAction->invokeArgs($component, $args);

        //import the component result
        foreach ($component->getData()->getIterator() as $key => $value) {
            $this->getData()->set($key, $value);
        }

        return $result;
    }

    /**
     * Compile the given Response instance using passed ControllerComponents.
     *
     * @param  ResponseInterface $response     the response to be compiled
     * @param  string|null       $fromTemplate the name of the file containing the template
     * @throws ControllerException the error preventing the body to be written
     */
    public function compile(ResponseInterface &$response, $fromTemplate = null)
    {
        //check if the response is writable
        if (!$response->getBody()->isWritable()) {
            throw new ControllerException("The request body is not writable", 0);
        }

        if (is_null($fromTemplate)) {
            $format = SerializableCollection::JSON;
            $formatValue = 'application/json;';


            //append the result of serialization to the given request
            $response->getBody()->write(
                $this->getData()->serialize($format)
            );

            $response = $response->withAddedHeader('Content-Type', $formatValue);

            return;
        }

        $response = $response->withAddedHeader('Content-Type', 'text/html;');
    }
}