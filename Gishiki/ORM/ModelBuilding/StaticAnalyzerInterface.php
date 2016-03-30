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
    interface StaticAnalyzerInterface {
        
        /**
         * Analyze the file/resource passed as an argument to the 
         * static analyzer class constructor
         * 
         * The resulting structure is no validated and almost no 
         * errors check is performed
         * 
         * @throws \Gishiki\ORM\ModelBuilding\ModelBuildingException the exception prevenenting the correct analysis
         */
        public function Analyze();
        
        /**
         * Check weather the Analyze function has been called and no errors
         * were encountered
         * 
         * @return boolean TRUE if the analysis was successful
         */
        public function Analyzed();
        
        /**
         * Can be called only if Analyzed() returns TRUE: if an invalid call is 
         * performed than either NULL or a malformed database structure 
         * will be returned.
         * 
         * Get the result of the analysis of the given resource
         * 
         * @return \Gishiki\ORM\Common\Database the database structure deducted from the performed analysis
         */
        public function Result();
        
    }
}