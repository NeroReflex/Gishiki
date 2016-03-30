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

//include the less compiler (3rd party)
require_once(ROOT."Gishiki".DS."less".DS."lessc".DS."lessc.inc.php");

/**
 * Integrate into the framework the less compiler found at https://github.com/leafo/lessphp
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class lessIntegration {
    
    /**
     * Compile a less file inside the views directory and cache the content
     * to gain huge speedups
     * 
     * @param string $filePath the name of the less file to be compiled
     * @return string the result of the less compilation
     */
    static function FileCompilation($filePath) {
        if (file_exists((VIEW_DIR.$filePath))) {
            //get the last modified time of the less file and its hash to avoid
            $lessCompiledHash = md5(filemtime(VIEW_DIR.$filePath)).".lesscache";
            //compiling it two time if it is not needed

            if (!CacheManager::Exists($lessCompiledHash)) {
                //get the content of the less file to be compiled
                $lessContent = file_get_contents(VIEW_DIR.$filePath);

                //setup the less compiler
                $less = new lessc();

                //compile the content of the less file using the less compiler
                $compilationResult = $less->compile($lessContent);

                //minify the compilation result
                $minifier = new Minify\CSS();
                $minifier->add($compilationResult);
                $compilationMinifiedResult = $minifier->minify();

                //store the compilation result into the cache
                CacheManager::Store($lessCompiledHash, $compilationMinifiedResult);
                //to have a huge speedup the next time a visitor will 
                //request the same less file to be compiled

                //return the compilation result
                return $compilationMinifiedResult;
            } else {
                //return the content that was previously cached
                return CacheManager::Fetch($lessCompiledHash);
            }
        } else {
            throw new Exception("The given less file cannot be compiled because it does't exists");
        }
    }
    
    /**
     * Compile a list of less files
     * 
     * @param array $lessFileList an array with a list of less files
     * @param integer $numberOfCompiledFiles this will hold the number of compiled files
     * @return array an array of compiled less file: each index is a file name, its value is the compilation result
     */
    static function MultipleFileCompilation(&$lessFileList, &$numberOfCompiledFiles) {
        //prepare the structure to hold compilation results
        $compilationResults = array();
        
        //prepare the compilation counter
        $numberOfCompiledFiles = 0;
        
        //cycle each less file
        reset($lessFileList);
        $numberOfFilesToCompile = count($lessFileList);
        while($numberOfCompiledFiles < $numberOfFilesToCompile) {
            $currentLESSFile = current($lessFileList);
            
            //compile the current less file
            $compilationResults[$currentLESSFile] = lessIntegration::FileCompilation($currentLESSFile);
            
            //increase the number of compiled files
            $numberOfCompiledFiles++;
            
            //jump to the next less file to compile
            next($lessFileList);
        }
        
        //return the result of the file group compilation
        return $compilationResults;
    }
    
    /**
     * Include in a view each less file that was linked like this:
     * {{{less 'lessfilename.less' this is an optional comment....}}}
     * 
     * @param string $content the non-preprocessed HTML
     * @throws Exception the error occurred while recognizing a less inclusion or compilation
     */
    static function IncludeAnyLESS(&$content) {
        $openLESSInclusion = "{{{less ";
        $closeLESSInclusion = "}}}";
        
            //this is the array containing names of all less files
            $lessFiles = array();
        
            //this is the key array that holds everything important to perform
            $openingANDClosingLESSTags = array();
            //a less inclusion
            
            //compile any LESS script included
            $lastLESSInclusionPosition = TRUE;
            $lastLESSExclusionPosition = TRUE;
            $searchStartingFrom = 0;
            while (($lastLESSInclusionPosition != FALSE) && ($lastLESSExclusionPosition != FALSE)) {
                $lastLESSInclusionPosition = strpos($content, $openLESSInclusion, $searchStartingFrom);
                $lastLESSExclusionPosition = strpos($content, $closeLESSInclusion, $searchStartingFrom);
                
                //was the LESS inclusion found?
                if (($lastLESSInclusionPosition != FALSE) && ($lastLESSExclusionPosition != FALSE)) {
                    $searchStartingFrom = $lastLESSExclusionPosition + strlen($closeLESSInclusion);
                    
                    $fileName = "";
                    $ended = FALSE;
                    $replacement = "";
                
                    //read the file name
                    $readingTheName = FALSE;
                    for ($j = $lastLESSInclusionPosition; $j < ($lastLESSExclusionPosition + strlen($closeLESSInclusion)); $j++) {
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

                    //check for the less file to be recognized
                    if ((($ended) && ($fileName == "")) || (!$ended)) {
                        throw new Exception("Syntax error in less inclusion usage");
                    }

                    //store the file name
                    $lessFiles[] = $fileName;
                    
                    //store the opening and closing of the found less inclusion tag
                    $openingANDClosingLESSTags[] = array(0 => $lastLESSInclusionPosition, 1 => ($lastLESSExclusionPosition + strlen($closeLESSInclusion)), 2 => $fileName, 3 => $replacement);
                }
            }
            
            //this is the number of compiled files
            $numberOfCompiledFiles = 0;
            
            //this is the array of compiled less files
            $arrayOfSobstitutions = lessIntegration::MultipleFileCompilation($lessFiles, $numberOfCompiledFiles);
            //and is organized like this: "file.less" => "compiled-and-minified CSS content"
            
            //cycle each less inclusion
            for ($i = 0; $i < $numberOfCompiledFiles; $i++) {
                //get everything necessary to replace a less inclusion with the compilation result
                $lessFile = $openingANDClosingLESSTags[$i][2];
                $srtTOReplace = $openingANDClosingLESSTags[$i][3];
                
                //perform the css inclusion
                $times = 0;
                $content = str_replace($srtTOReplace, '<style type="text/css">'.$arrayOfSobstitutions[$lessFile].'</style>', $content, $times);
                
                //perform the error check
                if ($times <= 0) {
                    throw new Exception("An error occurred while processing a less inclusion");
                }
            }
    }
}
