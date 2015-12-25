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

namespace Gishiki\Core\MVC {

    /**
     * A generic collection of models.
     *
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    class ModelsCollection
    {
        /**
         * The collection of models (this is an array)
         */
        private $mdlsFetched = [];

        /**
         * A number that is resolved as an integer for the previous array
         */
        private $current = 0;

        /**
         * Creates the models collection from a list of models
         *
         * @param $models the array of models
         */
        public function __construct($models){
            $this->mdlsFetched = $models;
        }

        /**
         * Get the index of the currently active model
         *
         * @return integer the index of the current model
         */
        private function getIndexOfCurrent() {
            return (abs($this->current)) % $this->NumberOfResults();
        }

        /**
         * This function is useful when it is used as a while condition,
         * because it helps in cycling each element of the collection
         *
         * @return boolean return a value that flags when the last element is reached for the second time
         */
        public function LastResultPassed() {
            return (((abs($this->current)) % ($this->NumberOfResults() + 1)) == $this->NumberOfResults()) || ($this->NumberOfResults() == 0);
        }

        /**
         * Get the number of models inside the collection
         *
         * @return integer the number of models
         */
        public function NumberOfResults() {
            return count($this->mdlsFetched);
        }

        /**
         * Get the model currently pointed by the internal pointer
         *
         * @return Gishiki_Model the currently selected model
         */
        public function GetCurrentResult()/* : Gishiki_Model*/ {
            return $this->mdlsFetched[$this->getIndexOfCurrent()];
        }

        /**
         * Get the model currently pointed by the internal pointer and
         * move the pointer forward to the next model
         *
         * @return Gishiki_Model the currently selected model
         */
        public function GetCurrentResultAndStep()/* : Gishiki_Model*/ {
            $data = $this->mdlsFetched[$this->getIndexOfCurrent()];
            $this->current++;
            return $data;
        }

        /**
         * Get the model following the currently pointed by the internal pointer and
         * move the pointer forward to the next model
         *
         * @return Gishiki_Model the currently selected model
         */
        public function GetNextResult()/* : Gishiki_Model*/ {
            $this->current++;
            return $this->mdlsFetched[$this->getIndexOfCurrent()];
        }

        /**
         * Get the model preceding the currently pointed by the internal pointer and
         * move the pointer backward to the previous model
         *
         * @return Gishiki_Model the currently selected model
         */
        public function GetPreviousResult()/* : Gishiki_Model*/ {
            $this->current--;
            return $this->mdlsFetched[$this->getIndexOfCurrent()];
        }
    }
}