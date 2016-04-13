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

if (!function_exists("str_replace_once")) {
    
    /**
     * Convenient function that behave exactly like str_replace for the first occurrence only
     * 
     * @param  string $str_pattern     the pattern to be replaced
     * @param  string $str_replacement the string to replace the first matched pattern
     * @param  string $string          the string to search the pattern into
     * @return string the new string with the first matched pattern replaced
     */
    function str_replace_once($str_pattern, $str_replacement, $string)
    {
        if (strpos($string, $str_pattern) !== false) {
            $occurrence = strpos($string, $str_pattern);
            return substr_replace($string, $str_replacement, strpos($string, $str_pattern), strlen($str_pattern));
        }

        return $string;
    }
}

if (!function_exists("str_replace_list")) {
    
    /**
     * Convenient function that behave exactly like str_replace for the first occurrence only
     * 
     * @param  array  $str_patterns    the list of pattern to be replaced
     * @param  string $str_replacement the string to replace the first matched pattern
     * @param  string $string          the string to search the pattern into
     * @return string the new string with the first matched pattern replaced
     */
    function str_replace_list($str_patterns, $str_replacement, $string)
    {
        foreach ($str_patterns as $pattern) {
            $string = str_replace($pattern, $str_replacement, $string);
        }
        
        return $string;
    }
}

if (!function_exists("get_string_between")) {
    
    /**
     * Get the string between two substrings
     * 
     * @param  string $string the string to be analyzed
     * @param  string $start  the fisrt substring
     * @param  string $end    the second substring
     * @return string the string between the two substrings
     */
    function get_string_between($string, $start, $end)
    {
        $string = ' '.$string;
        $ini = strpos($string, $start);
        if ($ini == 0) {
            return '';
        }
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }
}
