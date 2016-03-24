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
     * The Gishiki base web controller. Every web controller (controllers used to 
     * generate an application for the prowser) inherit from this class
     * 
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    class WebController extends Controller {
        /** this is the HTML that will be sent to the client */
        private $rawContent;
        
        /** this is a flag used to know if a template was loaded */
        private $templateLoaded;
        
        /**
         * Initialize the we controller. Each web controller MUST call this constructor
         */
        public function __construct() {
            //call the controller constructor
            parent::__construct();
            
            //load an empty response buffer
            $this->rawContent = "";
            
            //no template loaded (yet)
            $this->templateLoaded = FALSE;
        }
        
        /**
         * Load a template inside the HTML response buffer
         * 
         * @param string $templateName the template name WITHOUT '.template'
         * @throws \Exception an exception is thrown if the template cannot be found
         */
        protected function LoadTemplate($templateName) {
            //check for the partial view existence
            if (file_exists(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('VIEW_DIR').$viewName.".template")) {
                
                //get the raw partial view
                $content = "";
                if (file_exists(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('VIEW_DIR').$viewName.".template")) {
                    $content = file_get_contents(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('VIEW_DIR').$viewName.".template");
                }

                //compile & include every less file included in the current view
                \lessIntegration::IncludeAnyLESS($content);

                //compile & include every scss file included in the current view
                \scssIntegration::IncludeAnySCSS($content);

                //for each data subset query update the partial view
                if (gettype($dataSubset) == "array")
                {
                    //perform swaps/replacements
                    $substitutions = count($dataSubset);
                    for ($i = 0; $i < $substitutions; $i++) {
                        $currentData = current($dataSubset);
                        $currentDataIndex = key($dataSubset);
                        $content = str_replace("{{".$currentDataIndex."}}", $currentData, $content);
                        next($dataSubset);
                    }
                }

                //include the template
                $this->rawContent = $content;
                
                //a template has been loaded
                $this->templateLoaded = TRUE;
            } else {
                throw new \Exception("The partial view \'".$viewName."\' cannot be found");
            }
        }
        
        /**
         * Process a partial view and store the result to the output buffer 
         * (that will be given to the client at the end of the controller lifetime)
         * 
         * @param string $viewName the name of the partial view WITHOUT '.html'
         * @param array $dataSubset an array of sobstitution strings
         * @param string $viewPlaceHolder this is used to complete the template previously loaded
         * @throws \Exception an exception is thrown if the partial view cannot be found
         */
        protected function LoadView($viewName, $dataSubset = NULL, $viewPlaceHolder = "") {
            //check for the partial view existence
            if (file_exists(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('VIEW_DIR').$viewName.".html")) {
                
                //get the raw partial view
                $content = "";
                if (file_exists(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('VIEW_DIR').$viewName.".html")) {
                    $content = file_get_contents(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('VIEW_DIR').$viewName.".html");
                }

                //compile & include every less file included in the current view
                \lessIntegration::IncludeAnyLESS($content);

                //compile & include every scss file included in the current view
                \scssIntegration::IncludeAnySCSS($content);

                //for each data subset query update the partial view
                if (gettype($dataSubset) == "array")
                {
                    //perform swaps/replacements
                    $substitutions = count($dataSubset);
                    for ($i = 0; $i < $substitutions; $i++) {
                        $currentData = current($dataSubset);
                        $currentDataIndex = key($dataSubset);
                        $content = str_replace("{{".$currentDataIndex."}}", htmlentities($currentData, ENT_HTML5), $content);
                        next($dataSubset);
                    }
                }

                //store the partial view or complete the template
                if (($viewPlaceHolder == "") || (!$this->templateLoaded)) {    
                    $this->rawContent .= $content;
                } else {
                    $this->rawContent = str_replace("{{{".$viewPlaceHolder."}}}", $content."{{{".$viewPlaceHolder."}}}", $this->rawContent);
                }
            } else {
                throw new \Exception("The partial view \'".$viewName."\' cannot be found");
            }
        }
        
        /**
         * Compress the web page (according to the request) 
         * and send it to the client (probably a browser)
         */
        public function __destruct() {
            //call the controller standard destructor
            parent::__destruct();

            //delete every content placeholder
            $matches = [];
            preg_match('/{{{(.*)\?}}}/', $this->rawContent, $matches);
            while (count($matches) > 0) {
                //remove the placeholder from the page content and from the array
                str_replace(array_pop($matches), "", $this->rawContent);
            }
        }
        
    }
}