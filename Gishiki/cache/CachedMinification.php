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


/**
 * The model loader and and manager
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class CachedMinification {
    
    /**
     * Minify a JavaScript file and store it in the cache to avoid compressing 
     * a second time if the file haven't changed from the last time it 
     * was minified
     * 
     * @param string $resourcePath the server file path of the js file to be minified
     * @return string the minified JS
     */
    static function MinifyJavaScript($resourcePath) {
        $minifiedHash = md5(filemtime($resourcePath).$resourcePath);
        
        //prepare the minified resource
        $minifiedResource = NULL;
        
        if (\Gishiki\Caching\Cache::Exists($minifiedHash)) {
            //get the minified cached file
            $minifiedResource = \Gishiki\Caching\Cache::Fetch($minifiedHash);
        } else {
            //minify the js
            $minifier = new \MatthiasMullie\Minify\JS();
            $minifier->add(file_get_contents($resourcePath));
            $minifiedResource = $minifier->minify();
                    
            //and cache it
            \Gishiki\Caching\Cache::Store($minifiedHash, $minifiedResource);
        }
        
        //return the generated or fetched resource
        return $minifiedResource;
    }
    
    /**
     * Minify a CSS file and store it in the cache to avoid compressing 
     * a second time if the file haven't changed from the last time it 
     * was minified
     * 
     * @param string $resourcePath the server file path of the css file to be minified
     * @return string the minified JS
     */
    static function MinifyCascadingSheetStyle($resourcePath) {
        $minifiedHash = md5(filemtime($resourcePath).$resourcePath);
        
        //prepare the minified resource
        $minifiedResource = NULL;
        
        if (\Gishiki\Caching\Cache::Exists($minifiedHash)) {
            //get the minified cached file
            $minifiedResource = \Gishiki\Caching\Cache::Fetch($minifiedHash);
        } else {
            //minify the js
            $minifier = new \MatthiasMullie\Minify\CSS();
            $minifier->add(file_get_contents($resourcePath));
            $minifiedResource = $minifier->minify();
                    
            //and cache it
            \Gishiki\Caching\Cache::Store($minifiedHash, $minifiedResource);
        }
        
        //return the generated or fetched resource
        return $minifiedResource;
    }
}
