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

use MatthiasMullie\Minify;

//include the scss compiler (3rd party)
include(ROOT."Gishiki".DS."scss".DS."scssc".DS."scssc.inc");

/**
 * Integrate into the framework the scss compiler found at https://github.com/leafo/scssphp
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class scssIntegration {
    
    /**
     * Compile a scss file inside the views directory and cache the content
     * to gain huge speedups
     * 
     * @param string $filePath the name of the scss file to be compiled
     * @return string the result of the scss compilation
     */
    static function FileCompilation($filePath) {
        if (file_exists((\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('VIEW_DIR').$filePath))) {
            //get the last modified time of the scss file and its hash to avoid
            $scssCompiledHash = md5(filemtime(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('VIEW_DIR').$filePath)).".scsscache";
            //compiling it two time if it is not needed

            if (!CacheManager::Exists($scssCompiledHash)) {
                //get the content of the scss file to be compiled
                $scssContent = file_get_contents(\Gishiki\Core\Environment::GetCurrentEnvironment()->GetConfigurationProperty('VIEW_DIR').$filePath);

                //setup the scss compiler
                $scss = new scssc();

                //compile the content of the scss file using the scss compiler
                $compilationResult = $scss->compile($scssContent);

                //minify the compilation result
                $minifier = new Minify\CSS();
                $minifier->add($compilationResult);            
                $compilationMinifiedResult = $minifier->minify();

                //store the compilation result into the cache
                CacheManager::Store($scssCompiledHash, $compilationMinifiedResult);
                //to have a huge speedup the next time a visitor will 
                //request the same scss file to be compiled

                //return the compilation result
                return $compilationMinifiedResult;
            } else {
                //return the content that was previously cached
                return CacheManager::Fetch($scssCompiledHash);
            }
        } else {
            throw new Exception("The given scss file cannot be compiled because it does't exists");
        }
    }
    
    /**
     * Compile a list of scss files
     * 
     * @param array $scssFileList an array with a list of scss files
     * @param integer $numberOfCompiledFiles this will hold the number of compiled files
     * @return array an array of compiled scss file: each index is a file name, its value is the compilation result
     */
    static function MultipleFileCompilation(&$scssFileList, &$numberOfCompiledFiles) {
        //prepare the structure to hold compilation results
        $compilationResults = [];
        
        //prepare the compilation counter
        $numberOfCompiledFiles = 0;
        
        //cycle each scss file
        reset($scssFileList);
        $numberOfFilesToCompile = count($scssFileList);
        while($numberOfCompiledFiles < $numberOfFilesToCompile) {
            $currentSCSSFile = current($scssFileList);
            
            //compile the current scss file
            $compilationResults[$currentSCSSFile] = scssIntegration::FileCompilation($currentSCSSFile);
            
            //increase the number of compiled files
            $numberOfCompiledFiles++;
            
            //jump to the next scss file to compile
            next($scssFileList);
        }
        
        //return the result of the file group compilation
        return $compilationResults;
    }
    
    /**
     * Include in a view each scss file that was linked like this:
     * {{{scss 'scssfilename.scss' this is an optional comment....}}}
     * 
     * @param string $content the non-preprocessed HTML
     * @throws Exception the error occurred while recognizing a scss inclusion or compilation
     */
    static function IncludeAnySCSS(&$content) {
        $openSCSSInclusion = "{{{scss ";
        $closeSCSSInclusion = "}}}";
        
            //this is the array containing names of all scss files
            $scssFiles = [];
        
            $openingANDClosingSCSSTags = [];
            
            //compile any SCSS script included
            $lastSCSSInclusionPosition = TRUE;
            $searchStartingFrom = 0;
            while ($lastSCSSInclusionPosition != FALSE) {
                $lastSCSSInclusionPosition = strpos($content, $openSCSSInclusion, $searchStartingFrom);
                $lastSCSSExclusionPosition = strpos($content, $closeSCSSInclusion, $searchStartingFrom);
                
                //was the SCSS inclusion found?
                if (($lastSCSSInclusionPosition != FALSE) && ($lastSCSSExclusionPosition != FALSE)) {
                    $searchStartingFrom = $lastSCSSExclusionPosition + strlen($closeSCSSInclusion);
                    
                    $fileName = "";
                    $ended = FALSE;
                    $replacement = "";
                
                    //read the file name
                    $readingTheName = FALSE;
                    for ($j = $lastSCSSInclusionPosition; $j < ($lastSCSSExclusionPosition + strlen($closeSCSSInclusion)); $j++) {
                        $replacement = $replacement.$content[$j];
                        if (!$ended) {
                            if ((!$readingTheName) && ($content[$j] == "'")) {
                                $readingTheName = TRUE;
                            } else if (($readingTheName) && ($content[$j] != "'")) {
                                //store the character
                                $fileName = $fileName.$content[$j];
                            } else if (($readingTheName) && ($content[$j] == "'")) {
                                $readingTheName = FALSE;
                                $ended = TRUE;
                            }
                        }
                    }

                    //check for the scss file to be recognized
                    if ((($ended) && ($fileName == "")) || (!$ended)) {
                        throw new Exception("Syntax error in scss inclusion usage");
                    }

                    //store the file name
                    $scssFiles[] = $fileName;
                    
                    //store the opening and closing of the found scss inclusion tag
                    $openingANDClosingSCSSTags[] = [0 => $lastSCSSInclusionPosition, 1 => ($lastSCSSExclusionPosition + strlen($closeSCSSInclusion)), 2 => $fileName, 3 => $replacement];
                }
            }
            
            //this is the number of compiled files
            $numberOfCompiledFiles = 0;
            
            //this is the array of compiled scss files
            $arrayOfSobstitutions = scssIntegration::MultipleFileCompilation($scssFiles, $numberOfCompiledFiles);
            //and is organized like this: "file.scss" => "compiled-and-minified CSS content"
            
            //cycle each scss inclusion
            for ($i = 0; $i < $numberOfCompiledFiles; $i++) {
                //get everything necessary to replace a scss inclusion with the compilation result
                $scssFile = $openingANDClosingSCSSTags[$i][2];
                $srtTOReplace = $openingANDClosingSCSSTags[$i][3];
                
                //perform the css inclusion
                $times = 0;
                $content = str_replace($srtTOReplace, '<style type="text/css">'.$arrayOfSobstitutions[$scssFile].'</style>', $content, $times);
                
                //perform the error check
                if ($times <= 0) {
                    throw new Exception("An error occurred while processing a scss inclusion");
                }
            }
    }
}
