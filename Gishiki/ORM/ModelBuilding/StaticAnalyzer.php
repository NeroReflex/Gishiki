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

namespace Gishiki\ORM\ModelBuilding {
    
    /**
     * Every static analysis tools used to build the AOT component of the ORM
     * must implements this interface
     *
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    class StaticAnalyzer implements StaticAnalyzerInterface {
        //this is the analyzer (that implements StaticAnalyzerInterface)
        //used for the analysis of the given resource
        private $analyzer = NULL;
        
        /**
         * Analyze the given file using the proper analyzer for the given file
         * format type.
         * 
         * @param string $resource the full path to the schemata file
         * @throws \Gishiki\ORM\ModelBuilding\ModelBuildingException the error occurred while detecting the proper analyzer
         */
        public function __construct($resource) {
            //get the extension of the given resource
            $extension = pathinfo($resource, PATHINFO_EXTENSION);
            
            switch (strtolower($extension)) {
                case "xml":
                    $this->analyzer = new \Gishiki\ORM\ModelBuilding\Adapters\XML_StaticAnalyzer($resource);
                    break;
                
                case "json":
                    $this->analyzer = new \Gishiki\ORM\ModelBuilding\Adapters\JSON_StaticAnalyzer($resource);
                    break;
                
                default:
                    throw new \Gishiki\ORM\ModelBuilding\ModelBuildingException("in resource '".$resource."': unable to detect the analyzer to be used", 0);
            }
        }
        
        public function Analyze() {
            return $this->analyzer->Analyze();
        }
        
        public function Analyzed() {
            return $this->analyzer->Analyzed();
        }
        
        public function Result() {
            return $this->analyzer->Result();
        }
    }
}