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

/* =====================================================
 *              Gishiki Version
 * =====================================================
 */
if (!defined('GISHIKI_MAJOR')) {
    define('GISHIKI_MAJOR', 0);
}

if (!defined('GISHIKI_MINOR')) {
    define('GISHIKI_MINOR', 0);
}

if (!defined('GISHIKI_REVISION')) {
    define('GISHIKI_REVISION', 1);
}

if (!defined('GISHIKI_STATUS')) {
    define('GISHIKI_STATUS', "Alpha");
}

//include the configuration file
require_once(ROOT."Gishiki".DS."config.php");

//include the routing engine
require_once(ROOT."Gishiki".DS."core".DS."Routing.php");

//include generic algorithms
require_once(ROOT."Gishiki".DS."generic".DS."StackException.php");
require_once(ROOT."Gishiki".DS."generic".DS."Stack.php");
require_once(ROOT."Gishiki".DS."JSON".DS."JSON.php");
require_once(ROOT."Gishiki".DS."JSON".DS."JSONException.php");

//include the serialization engine
require_once(ROOT."Gishiki".DS."serialization".DS."SerializationException.php");
require_once(ROOT."Gishiki".DS."serialization".DS."DirectSerialization.php");
require_once(ROOT."Gishiki".DS."serialization".DS."Serialization.php");

//include the caching engine
require_once(ROOT."Gishiki".DS."cache".DS."CacheManager.php");

//include the cookie management system
require_once(ROOT."Gishiki".DS."cookie".DS."CookieException.php");
require_once(ROOT."Gishiki".DS."cookie".DS."Cookie.php");
require_once(ROOT."Gishiki".DS."cookie".DS."CookieProvider.php");

//include the database helper library
require_once(ROOT."Gishiki".DS."database".DS."helpers".DS."ColumnType.php");
require_once(ROOT."Gishiki".DS."database".DS."helpers".DS."Criteria.php");
require_once(ROOT."Gishiki".DS."database".DS."helpers".DS."Clauses.php");
require_once(ROOT."Gishiki".DS."database".DS."helpers".DS."QueryCreator.php");
require_once(ROOT."Gishiki".DS."database".DS."DatabaseType.php");
require_once(ROOT."Gishiki".DS."database".DS."ConnectionString.php");
require_once(ROOT."Gishiki".DS."database".DS."DatabaseConnector.php");
require_once(ROOT."Gishiki".DS."database".DS."XMLConnection.php");
require_once(ROOT."Gishiki".DS."database".DS."Database.php");
require_once(ROOT."Gishiki".DS."database".DS."DatabaseException.php");

//include the RSA and AES helper library
require_once(ROOT."Gishiki".DS."security".DS."CipherException.php");
require_once(ROOT."Gishiki".DS."security".DS."SymmetricCipher.php");
require_once(ROOT."Gishiki".DS."security".DS."AsymmetricCipher.php");

//include the list of http status codes
require_once(ROOT."Gishiki".DS."HTTPResponseCodes.php");

//include the list of mime types
include(ROOT."Gishiki".DS."MIMETypes.php");

//include the CSS and JS minifier
require_once(ROOT."Gishiki".DS."minify".DS."Converter.php");
require_once(ROOT."Gishiki".DS."minify".DS."Minify.php");
require_once(ROOT."Gishiki".DS."minify".DS."CSS.php");
require_once(ROOT."Gishiki".DS."minify".DS."JS.php");
require_once(ROOT."Gishiki".DS."minify".DS."Exception.php");

//include the minifier
require_once(ROOT."Gishiki".DS."minify".DS."CachedMinification.php");

//include the less and scss compiler integration
require_once(ROOT."Gishiki".DS."less".DS."lessIntegration.php");
require_once(ROOT."Gishiki".DS."scss".DS."scssIntegration.php");

//require base model and controller classes
require_once(ROOT."Gishiki".DS."core".DS."MVC".DS."BaseModel.php");
require_once(ROOT."Gishiki".DS."core".DS."MVC".DS."BaseController.php");

//require the model manager class
require_once(ROOT."Gishiki".DS."core".DS."MVC".DS."ModelException.php");
require_once(ROOT."Gishiki".DS."core".DS."MVC".DS."ModelManager.php");

//include the environment manager
require_once(ROOT."Gishiki".DS."core".DS."Environment.php");

//this is the environment used to fulfill the incoming request
$executionEnvironment = NULL;

/**
 * The Gishiki action starter and framework entry point
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class Gishiki {   
    /**
     * Setup a framework instance
     */
    public function __construct() {
        
    }

    /**
     * Initialize the Gishiki engine and prepare for
     *  the execution of the framework
     */
    static function Initialize()
    {
        global $executionEnvironment;
        
        //each Gishiki instance is binded with a new created Environment
        $executionEnvironment = new Environment(TRUE);
        
        //the name of the directory that contains model, view and controller (must be placed in the root)
        if (!defined('APPLICATION_DIR')) {
            define('APPLICATION_DIR', Environment::GetCurrentEnvironment()->GetConfigurationProperty('APPLICATION_DIR'));
        }
        
        //the name of the directory that contains database files (sqlite3)
        if (!defined('DATABASE_DIR')) {
            define('DATABASE_DIR', Environment::GetCurrentEnvironment()->GetConfigurationProperty('DATABASE_DIR'));
        }

        //the name of the directory that contains database connection files (xml format)
        if (!defined('DATABASE_CONNECTION_DIR')) {
            define('DATABASE_CONNECTION_DIR', Environment::GetCurrentEnvironment()->GetConfigurationProperty('DATABASE_CONNECTION_DIR'));
        }
        
        //the name of the directory that contains the keys for the asymmetric chiper (must be placed in the root)
        if (!defined('KEYS_DIR')) {
            define('KEYS_DIR', Environment::GetCurrentEnvironment()->GetConfigurationProperty('KEYS_DIR'));
        }

        //the name of the directory that contains controllers
        if (!defined('CONTROLLER_DIR')) {
            define('CONTROLLER_DIR', Environment::GetCurrentEnvironment()->GetConfigurationProperty('CONTROLLER_DIR'));
        }

        //the name of the directory that contains models
        if (!defined('MODEL_DIR')) {
            define('MODEL_DIR', Environment::GetCurrentEnvironment()->GetConfigurationProperty('MODEL_DIR'));
        }

        //the name of the directory that contains views
        if (!defined('VIEW_DIR')) {
            define('VIEW_DIR', Environment::GetCurrentEnvironment()->GetConfigurationProperty('VIEW_DIR'));
        }

        //the name of the directory that contains classes
        if (!defined('CLASS_DIR')) {
            define('CLASS_DIR', Environment::GetCurrentEnvironment()->GetConfigurationProperty('CLASS_DIR'));
        }
        
        //the name of the directory that contains generic resources
        if (!defined('RESOURCE_DIR')) {
            define('RESOURCE_DIR', Environment::GetCurrentEnvironment()->GetConfigurationProperty('RESOURCE_DIR'));
        }
        
        //the name of the directory that contains cache files
        if (!defined('CACHE_DIR')) {
            define('CACHE_DIR', Environment::GetCurrentEnvironment()->GetConfigurationProperty('CACHE_DIR'));
        }
    }
    
    /**
     * Execute the requested control-model-view operations
     * 
     * @param string $request the requested resource
     */
    public function Run($request)
    {
        if (strlen($request) >= 1) {
            //remove the final / as it is not needed and may become a problem
            if ($request[strlen($request) - 1] == '/') {
                $request = substr($request, 0, strlen($request) - 1);
            }
        }
        
        if (strlen($request) >= 1) {
            //remove the starting / as it is not needed and may become a problem
            if ($request[0] == '/') {
                $request = substr($request, 1, strlen($request));
            }
        }
        
        //fulfill the client request
        global $executionEnvironment;
        $executionEnvironment->FulfillRequest($request);
    }
    
    /**
     * Get the current framework version
     * 
     * @return integer a number that identify the framework version
     */
    static function GetCurrentVersion()
    {
        $versionNumber = (GISHIKI_MAJOR * 100) + ((GISHIKI_MINOR % 10) * 10) + (GISHIKI_REVISION % 10);
        
        //return the retrived version as a single number
        return $versionNumber;
    }
    
    /**
     * Get the installed framework version
     * 
     * @return integer the number that identify the installed version framework
     */
    static function GetInstalledVersion()
    {
        //get the file name of the install log file
        $currentlyInstalledVersionFile = ROOT."Gishiki.info";
        
        //the installed version
        $version = NULL;
        
        if (file_exists($currentlyInstalledVersionFile))
        {
            $fileVersionContent = file_get_contents($currentlyInstalledVersionFile);
            
            $exploded = explode(".", $fileVersionContent);
            
            $majorStr = $exploded[0];
            $minorStr = $exploded[1];
            $reexploded = explode(" ", $exploded[2]);
            $revisionStr = $reexploded[0];
            
            $major = intval($majorStr);
            $minor = intval($minorStr);
            $revision = intval($revisionStr);
            
            $versionNumber = ($major * 100) + ($minor * 10) + $revision;
            
            return $versionNumber;
        } else {
            $version = 0;
        }
        
        //return the retrived version as a single number
        return $version;
    }
    
    /**
     * Update from an old version of installed framework
     */
    static function Update()
    {
        
    }
    
    /**
     * Install the framework
     */
    static function Install()
    {
        $errors = 0;
        if (file_exists(ROOT."README.md")) {
            @unlink(ROOT."README.md");
        }
        
        ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gishiki Installation</title>
  <meta name="description" content="Gishiki Installation">
  <meta name="author" content="Benato Denis">

  <style>
      /*!
 * Bootstrap v3.3.5 (http://getbootstrap.com)
 * Copyright 2011-2015 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 *//*! normalize.css v3.0.3 | MIT License | github.com/necolas/normalize.css */
html{font-family:sans-serif;-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%}body{margin:0}article,aside,details,figcaption,figure,footer,header,hgroup,main,menu,nav,section,summary{display:block}audio,canvas,progress,video{display:inline-block;vertical-align:baseline}audio:not([controls]){display:none;height:0}[hidden],template{display:none}a{background-color:transparent}a:active,a:hover{outline:0}abbr[title]{border-bottom:1px dotted}b,strong{font-weight:bold}dfn{font-style:italic}h1{font-size:2em;margin:0.67em 0}mark{background:#ff0;color:#000}small{font-size:80%}sub,sup{font-size:75%;line-height:0;position:relative;vertical-align:baseline}sup{top:-0.5em}sub{bottom:-0.25em}img{border:0}svg:not(:root){overflow:hidden}figure{margin:1em 40px}hr{-webkit-box-sizing:content-box;-moz-box-sizing:content-box;box-sizing:content-box;height:0}pre{overflow:auto}code,kbd,pre,samp{font-family:monospace, monospace;font-size:1em}button,input,optgroup,select,textarea{color:inherit;font:inherit;margin:0}button{overflow:visible}button,select{text-transform:none}button,html input[type="button"],input[type="reset"],input[type="submit"]{-webkit-appearance:button;cursor:pointer}button[disabled],html input[disabled]{cursor:default}button::-moz-focus-inner,input::-moz-focus-inner{border:0;padding:0}input{line-height:normal}input[type="checkbox"],input[type="radio"]{-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;padding:0}input[type="number"]::-webkit-inner-spin-button,input[type="number"]::-webkit-outer-spin-button{height:auto}input[type="search"]{-webkit-appearance:textfield;-webkit-box-sizing:content-box;-moz-box-sizing:content-box;box-sizing:content-box}input[type="search"]::-webkit-search-cancel-button,input[type="search"]::-webkit-search-decoration{-webkit-appearance:none}fieldset{border:1px solid #c0c0c0;margin:0 2px;padding:0.35em 0.625em 0.75em}legend{border:0;padding:0}textarea{overflow:auto}optgroup{font-weight:bold}table{border-collapse:collapse;border-spacing:0}td,th{padding:0}/*! Source: https://github.com/h5bp/html5-boilerplate/blob/master/src/css/main.css */@media print{*,*:before,*:after{background:transparent !important;color:#000 !important;-webkit-box-shadow:none !important;box-shadow:none !important;text-shadow:none !important}a,a:visited{text-decoration:underline}a[href]:after{content:" (" attr(href) ")"}abbr[title]:after{content:" (" attr(title) ")"}a[href^="#"]:after,a[href^="javascript:"]:after{content:""}pre,blockquote{border:1px solid #999;page-break-inside:avoid}thead{display:table-header-group}tr,img{page-break-inside:avoid}img{max-width:100% !important}p,h2,h3{orphans:3;widows:3}h2,h3{page-break-after:avoid}.navbar{display:none}.btn>.caret,.dropup>.btn>.caret{border-top-color:#000 !important}.label{border:1px solid #000}.table{border-collapse:collapse !important}.table td,.table th{background-color:#fff !important}.table-bordered th,.table-bordered td{border:1px solid #ddd !important}}*{-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box}*:before,*:after{-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box}html{font-size:10px;-webkit-tap-highlight-color:rgba(0,0,0,0)}body{font-family:"Helvetica Neue",Helvetica,Arial,sans-serif;font-size:14px;line-height:1.42857143;color:#333;background-color:#fff}input,button,select,textarea{font-family:inherit;font-size:inherit;line-height:inherit}a{color:#337ab7;text-decoration:none}a:hover,a:focus{color:#23527c;text-decoration:underline}a:focus{outline:thin dotted;outline:5px auto -webkit-focus-ring-color;outline-offset:-2px}figure{margin:0}img{vertical-align:middle}.img-responsive,.thumbnail>img,.thumbnail a>img{display:block;max-width:100%;height:auto}.img-rounded{border-radius:6px}.img-thumbnail{padding:4px;line-height:1.42857143;background-color:#fff;border:1px solid #ddd;border-radius:4px;-webkit-transition:all .2s ease-in-out;-o-transition:all .2s ease-in-out;transition:all .2s ease-in-out;display:inline-block;max-width:100%;height:auto}.img-circle{border-radius:50%}hr{margin-top:20px;margin-bottom:20px;border:0;border-top:1px solid #eee}.sr-only{position:absolute;width:1px;height:1px;margin:-1px;padding:0;overflow:hidden;clip:rect(0, 0, 0, 0);border:0}.sr-only-focusable:active,.sr-only-focusable:focus{position:static;width:auto;height:auto;margin:0;overflow:visible;clip:auto}[role="button"]{cursor:pointer}h1,h2,h3,h4,h5,h6,.h1,.h2,.h3,.h4,.h5,.h6{font-family:inherit;font-weight:500;line-height:1.1;color:inherit}h1 small,h2 small,h3 small,h4 small,h5 small,h6 small,.h1 small,.h2 small,.h3 small,.h4 small,.h5 small,.h6 small,h1 .small,h2 .small,h3 .small,h4 .small,h5 .small,h6 .small,.h1 .small,.h2 .small,.h3 .small,.h4 .small,.h5 .small,.h6 .small{font-weight:normal;line-height:1;color:#777}h1,.h1,h2,.h2,h3,.h3{margin-top:20px;margin-bottom:10px}h1 small,.h1 small,h2 small,.h2 small,h3 small,.h3 small,h1 .small,.h1 .small,h2 .small,.h2 .small,h3 .small,.h3 .small{font-size:65%}h4,.h4,h5,.h5,h6,.h6{margin-top:10px;margin-bottom:10px}h4 small,.h4 small,h5 small,.h5 small,h6 small,.h6 small,h4 .small,.h4 .small,h5 .small,.h5 .small,h6 .small,.h6 .small{font-size:75%}h1,.h1{font-size:36px}h2,.h2{font-size:30px}h3,.h3{font-size:24px}h4,.h4{font-size:18px}h5,.h5{font-size:14px}h6,.h6{font-size:12px}p{margin:0 0 10px}.lead{margin-bottom:20px;font-size:16px;font-weight:300;line-height:1.4}@media (min-width:768px){.lead{font-size:21px}}small,.small{font-size:85%}mark,.mark{background-color:#fcf8e3;padding:.2em}.text-left{text-align:left}.text-right{text-align:right}.text-center{text-align:center}.text-justify{text-align:justify}.text-nowrap{white-space:nowrap}.text-lowercase{text-transform:lowercase}.text-uppercase{text-transform:uppercase}.text-capitalize{text-transform:capitalize}.text-muted{color:#777}.text-primary{color:#337ab7}a.text-primary:hover,a.text-primary:focus{color:#286090}.text-success{color:#3c763d}a.text-success:hover,a.text-success:focus{color:#2b542c}.text-info{color:#31708f}a.text-info:hover,a.text-info:focus{color:#245269}.text-warning{color:#8a6d3b}a.text-warning:hover,a.text-warning:focus{color:#66512c}.text-danger{color:#a94442}a.text-danger:hover,a.text-danger:focus{color:#843534}.bg-primary{color:#fff;background-color:#337ab7}a.bg-primary:hover,a.bg-primary:focus{background-color:#286090}.bg-success{background-color:#dff0d8}a.bg-success:hover,a.bg-success:focus{background-color:#c1e2b3}.bg-info{background-color:#d9edf7}a.bg-info:hover,a.bg-info:focus{background-color:#afd9ee}.bg-warning{background-color:#fcf8e3}a.bg-warning:hover,a.bg-warning:focus{background-color:#f7ecb5}.bg-danger{background-color:#f2dede}a.bg-danger:hover,a.bg-danger:focus{background-color:#e4b9b9}.page-header{padding-bottom:9px;margin:40px 0 20px;border-bottom:1px solid #eee}ul,ol{margin-top:0;margin-bottom:10px}ul ul,ol ul,ul ol,ol ol{margin-bottom:0}.list-unstyled{padding-left:0;list-style:none}.list-inline{padding-left:0;list-style:none;margin-left:-5px}.list-inline>li{display:inline-block;padding-left:5px;padding-right:5px}dl{margin-top:0;margin-bottom:20px}dt,dd{line-height:1.42857143}dt{font-weight:bold}dd{margin-left:0}@media (min-width:768px){.dl-horizontal dt{float:left;width:160px;clear:left;text-align:right;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.dl-horizontal dd{margin-left:180px}}abbr[title],abbr[data-original-title]{cursor:help;border-bottom:1px dotted #777}.initialism{font-size:90%;text-transform:uppercase}blockquote{padding:10px 20px;margin:0 0 20px;font-size:17.5px;border-left:5px solid #eee}blockquote p:last-child,blockquote ul:last-child,blockquote ol:last-child{margin-bottom:0}blockquote footer,blockquote small,blockquote .small{display:block;font-size:80%;line-height:1.42857143;color:#777}blockquote footer:before,blockquote small:before,blockquote .small:before{content:'\2014 \00A0'}.blockquote-reverse,blockquote.pull-right{padding-right:15px;padding-left:0;border-right:5px solid #eee;border-left:0;text-align:right}.blockquote-reverse footer:before,blockquote.pull-right footer:before,.blockquote-reverse small:before,blockquote.pull-right small:before,.blockquote-reverse .small:before,blockquote.pull-right .small:before{content:''}.blockquote-reverse footer:after,blockquote.pull-right footer:after,.blockquote-reverse small:after,blockquote.pull-right small:after,.blockquote-reverse .small:after,blockquote.pull-right .small:after{content:'\00A0 \2014'}address{margin-bottom:20px;font-style:normal;line-height:1.42857143}code,kbd,pre,samp{font-family:Menlo,Monaco,Consolas,"Courier New",monospace}code{padding:2px 4px;font-size:90%;color:#c7254e;background-color:#f9f2f4;border-radius:4px}kbd{padding:2px 4px;font-size:90%;color:#fff;background-color:#333;border-radius:3px;-webkit-box-shadow:inset 0 -1px 0 rgba(0,0,0,0.25);box-shadow:inset 0 -1px 0 rgba(0,0,0,0.25)}kbd kbd{padding:0;font-size:100%;font-weight:bold;-webkit-box-shadow:none;box-shadow:none}pre{display:block;padding:9.5px;margin:0 0 10px;font-size:13px;line-height:1.42857143;word-break:break-all;word-wrap:break-word;color:#333;background-color:#f5f5f5;border:1px solid #ccc;border-radius:4px}pre code{padding:0;font-size:inherit;color:inherit;white-space:pre-wrap;background-color:transparent;border-radius:0}.pre-scrollable{max-height:340px;overflow-y:scroll}.btn{display:inline-block;margin-bottom:0;font-weight:normal;text-align:center;vertical-align:middle;-ms-touch-action:manipulation;touch-action:manipulation;cursor:pointer;background-image:none;border:1px solid transparent;white-space:nowrap;padding:6px 12px;font-size:14px;line-height:1.42857143;border-radius:4px;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none}.btn:focus,.btn:active:focus,.btn.active:focus,.btn.focus,.btn:active.focus,.btn.active.focus{outline:thin dotted;outline:5px auto -webkit-focus-ring-color;outline-offset:-2px}.btn:hover,.btn:focus,.btn.focus{color:#333;text-decoration:none}.btn:active,.btn.active{outline:0;background-image:none;-webkit-box-shadow:inset 0 3px 5px rgba(0,0,0,0.125);box-shadow:inset 0 3px 5px rgba(0,0,0,0.125)}.btn.disabled,.btn[disabled],fieldset[disabled] .btn{cursor:not-allowed;opacity:.65;filter:alpha(opacity=65);-webkit-box-shadow:none;box-shadow:none}a.btn.disabled,fieldset[disabled] a.btn{pointer-events:none}.btn-default{color:#333;background-color:#fff;border-color:#ccc}.btn-default:focus,.btn-default.focus{color:#333;background-color:#e6e6e6;border-color:#8c8c8c}.btn-default:hover{color:#333;background-color:#e6e6e6;border-color:#adadad}.btn-default:active,.btn-default.active,.open>.dropdown-toggle.btn-default{color:#333;background-color:#e6e6e6;border-color:#adadad}.btn-default:active:hover,.btn-default.active:hover,.open>.dropdown-toggle.btn-default:hover,.btn-default:active:focus,.btn-default.active:focus,.open>.dropdown-toggle.btn-default:focus,.btn-default:active.focus,.btn-default.active.focus,.open>.dropdown-toggle.btn-default.focus{color:#333;background-color:#d4d4d4;border-color:#8c8c8c}.btn-default:active,.btn-default.active,.open>.dropdown-toggle.btn-default{background-image:none}.btn-default.disabled,.btn-default[disabled],fieldset[disabled] .btn-default,.btn-default.disabled:hover,.btn-default[disabled]:hover,fieldset[disabled] .btn-default:hover,.btn-default.disabled:focus,.btn-default[disabled]:focus,fieldset[disabled] .btn-default:focus,.btn-default.disabled.focus,.btn-default[disabled].focus,fieldset[disabled] .btn-default.focus,.btn-default.disabled:active,.btn-default[disabled]:active,fieldset[disabled] .btn-default:active,.btn-default.disabled.active,.btn-default[disabled].active,fieldset[disabled] .btn-default.active{background-color:#fff;border-color:#ccc}.btn-default .badge{color:#fff;background-color:#333}.btn-primary{color:#fff;background-color:#337ab7;border-color:#2e6da4}.btn-primary:focus,.btn-primary.focus{color:#fff;background-color:#286090;border-color:#122b40}.btn-primary:hover{color:#fff;background-color:#286090;border-color:#204d74}.btn-primary:active,.btn-primary.active,.open>.dropdown-toggle.btn-primary{color:#fff;background-color:#286090;border-color:#204d74}.btn-primary:active:hover,.btn-primary.active:hover,.open>.dropdown-toggle.btn-primary:hover,.btn-primary:active:focus,.btn-primary.active:focus,.open>.dropdown-toggle.btn-primary:focus,.btn-primary:active.focus,.btn-primary.active.focus,.open>.dropdown-toggle.btn-primary.focus{color:#fff;background-color:#204d74;border-color:#122b40}.btn-primary:active,.btn-primary.active,.open>.dropdown-toggle.btn-primary{background-image:none}.btn-primary.disabled,.btn-primary[disabled],fieldset[disabled] .btn-primary,.btn-primary.disabled:hover,.btn-primary[disabled]:hover,fieldset[disabled] .btn-primary:hover,.btn-primary.disabled:focus,.btn-primary[disabled]:focus,fieldset[disabled] .btn-primary:focus,.btn-primary.disabled.focus,.btn-primary[disabled].focus,fieldset[disabled] .btn-primary.focus,.btn-primary.disabled:active,.btn-primary[disabled]:active,fieldset[disabled] .btn-primary:active,.btn-primary.disabled.active,.btn-primary[disabled].active,fieldset[disabled] .btn-primary.active{background-color:#337ab7;border-color:#2e6da4}.btn-primary .badge{color:#337ab7;background-color:#fff}.btn-success{color:#fff;background-color:#5cb85c;border-color:#4cae4c}.btn-success:focus,.btn-success.focus{color:#fff;background-color:#449d44;border-color:#255625}.btn-success:hover{color:#fff;background-color:#449d44;border-color:#398439}.btn-success:active,.btn-success.active,.open>.dropdown-toggle.btn-success{color:#fff;background-color:#449d44;border-color:#398439}.btn-success:active:hover,.btn-success.active:hover,.open>.dropdown-toggle.btn-success:hover,.btn-success:active:focus,.btn-success.active:focus,.open>.dropdown-toggle.btn-success:focus,.btn-success:active.focus,.btn-success.active.focus,.open>.dropdown-toggle.btn-success.focus{color:#fff;background-color:#398439;border-color:#255625}.btn-success:active,.btn-success.active,.open>.dropdown-toggle.btn-success{background-image:none}.btn-success.disabled,.btn-success[disabled],fieldset[disabled] .btn-success,.btn-success.disabled:hover,.btn-success[disabled]:hover,fieldset[disabled] .btn-success:hover,.btn-success.disabled:focus,.btn-success[disabled]:focus,fieldset[disabled] .btn-success:focus,.btn-success.disabled.focus,.btn-success[disabled].focus,fieldset[disabled] .btn-success.focus,.btn-success.disabled:active,.btn-success[disabled]:active,fieldset[disabled] .btn-success:active,.btn-success.disabled.active,.btn-success[disabled].active,fieldset[disabled] .btn-success.active{background-color:#5cb85c;border-color:#4cae4c}.btn-success .badge{color:#5cb85c;background-color:#fff}.btn-info{color:#fff;background-color:#5bc0de;border-color:#46b8da}.btn-info:focus,.btn-info.focus{color:#fff;background-color:#31b0d5;border-color:#1b6d85}.btn-info:hover{color:#fff;background-color:#31b0d5;border-color:#269abc}.btn-info:active,.btn-info.active,.open>.dropdown-toggle.btn-info{color:#fff;background-color:#31b0d5;border-color:#269abc}.btn-info:active:hover,.btn-info.active:hover,.open>.dropdown-toggle.btn-info:hover,.btn-info:active:focus,.btn-info.active:focus,.open>.dropdown-toggle.btn-info:focus,.btn-info:active.focus,.btn-info.active.focus,.open>.dropdown-toggle.btn-info.focus{color:#fff;background-color:#269abc;border-color:#1b6d85}.btn-info:active,.btn-info.active,.open>.dropdown-toggle.btn-info{background-image:none}.btn-info.disabled,.btn-info[disabled],fieldset[disabled] .btn-info,.btn-info.disabled:hover,.btn-info[disabled]:hover,fieldset[disabled] .btn-info:hover,.btn-info.disabled:focus,.btn-info[disabled]:focus,fieldset[disabled] .btn-info:focus,.btn-info.disabled.focus,.btn-info[disabled].focus,fieldset[disabled] .btn-info.focus,.btn-info.disabled:active,.btn-info[disabled]:active,fieldset[disabled] .btn-info:active,.btn-info.disabled.active,.btn-info[disabled].active,fieldset[disabled] .btn-info.active{background-color:#5bc0de;border-color:#46b8da}.btn-info .badge{color:#5bc0de;background-color:#fff}.btn-warning{color:#fff;background-color:#f0ad4e;border-color:#eea236}.btn-warning:focus,.btn-warning.focus{color:#fff;background-color:#ec971f;border-color:#985f0d}.btn-warning:hover{color:#fff;background-color:#ec971f;border-color:#d58512}.btn-warning:active,.btn-warning.active,.open>.dropdown-toggle.btn-warning{color:#fff;background-color:#ec971f;border-color:#d58512}.btn-warning:active:hover,.btn-warning.active:hover,.open>.dropdown-toggle.btn-warning:hover,.btn-warning:active:focus,.btn-warning.active:focus,.open>.dropdown-toggle.btn-warning:focus,.btn-warning:active.focus,.btn-warning.active.focus,.open>.dropdown-toggle.btn-warning.focus{color:#fff;background-color:#d58512;border-color:#985f0d}.btn-warning:active,.btn-warning.active,.open>.dropdown-toggle.btn-warning{background-image:none}.btn-warning.disabled,.btn-warning[disabled],fieldset[disabled] .btn-warning,.btn-warning.disabled:hover,.btn-warning[disabled]:hover,fieldset[disabled] .btn-warning:hover,.btn-warning.disabled:focus,.btn-warning[disabled]:focus,fieldset[disabled] .btn-warning:focus,.btn-warning.disabled.focus,.btn-warning[disabled].focus,fieldset[disabled] .btn-warning.focus,.btn-warning.disabled:active,.btn-warning[disabled]:active,fieldset[disabled] .btn-warning:active,.btn-warning.disabled.active,.btn-warning[disabled].active,fieldset[disabled] .btn-warning.active{background-color:#f0ad4e;border-color:#eea236}.btn-warning .badge{color:#f0ad4e;background-color:#fff}.btn-danger{color:#fff;background-color:#d9534f;border-color:#d43f3a}.btn-danger:focus,.btn-danger.focus{color:#fff;background-color:#c9302c;border-color:#761c19}.btn-danger:hover{color:#fff;background-color:#c9302c;border-color:#ac2925}.btn-danger:active,.btn-danger.active,.open>.dropdown-toggle.btn-danger{color:#fff;background-color:#c9302c;border-color:#ac2925}.btn-danger:active:hover,.btn-danger.active:hover,.open>.dropdown-toggle.btn-danger:hover,.btn-danger:active:focus,.btn-danger.active:focus,.open>.dropdown-toggle.btn-danger:focus,.btn-danger:active.focus,.btn-danger.active.focus,.open>.dropdown-toggle.btn-danger.focus{color:#fff;background-color:#ac2925;border-color:#761c19}.btn-danger:active,.btn-danger.active,.open>.dropdown-toggle.btn-danger{background-image:none}.btn-danger.disabled,.btn-danger[disabled],fieldset[disabled] .btn-danger,.btn-danger.disabled:hover,.btn-danger[disabled]:hover,fieldset[disabled] .btn-danger:hover,.btn-danger.disabled:focus,.btn-danger[disabled]:focus,fieldset[disabled] .btn-danger:focus,.btn-danger.disabled.focus,.btn-danger[disabled].focus,fieldset[disabled] .btn-danger.focus,.btn-danger.disabled:active,.btn-danger[disabled]:active,fieldset[disabled] .btn-danger:active,.btn-danger.disabled.active,.btn-danger[disabled].active,fieldset[disabled] .btn-danger.active{background-color:#d9534f;border-color:#d43f3a}.btn-danger .badge{color:#d9534f;background-color:#fff}.btn-link{color:#337ab7;font-weight:normal;border-radius:0}.btn-link,.btn-link:active,.btn-link.active,.btn-link[disabled],fieldset[disabled] .btn-link{background-color:transparent;-webkit-box-shadow:none;box-shadow:none}.btn-link,.btn-link:hover,.btn-link:focus,.btn-link:active{border-color:transparent}.btn-link:hover,.btn-link:focus{color:#23527c;text-decoration:underline;background-color:transparent}.btn-link[disabled]:hover,fieldset[disabled] .btn-link:hover,.btn-link[disabled]:focus,fieldset[disabled] .btn-link:focus{color:#777;text-decoration:none}.btn-lg{padding:10px 16px;font-size:18px;line-height:1.3333333;border-radius:6px}.btn-sm{padding:5px 10px;font-size:12px;line-height:1.5;border-radius:3px}.btn-xs{padding:1px 5px;font-size:12px;line-height:1.5;border-radius:3px}.btn-block{display:block;width:100%}.btn-block+.btn-block{margin-top:5px}input[type="submit"].btn-block,input[type="reset"].btn-block,input[type="button"].btn-block{width:100%}.breadcrumb{padding:8px 15px;margin-bottom:20px;list-style:none;background-color:#f5f5f5;border-radius:4px}.breadcrumb>li{display:inline-block}.breadcrumb>li+li:before{content:"/\00a0";padding:0 5px;color:#ccc}.breadcrumb>.active{color:#777}.pagination{display:inline-block;padding-left:0;margin:20px 0;border-radius:4px}.pagination>li{display:inline}.pagination>li>a,.pagination>li>span{position:relative;float:left;padding:6px 12px;line-height:1.42857143;text-decoration:none;color:#337ab7;background-color:#fff;border:1px solid #ddd;margin-left:-1px}.pagination>li:first-child>a,.pagination>li:first-child>span{margin-left:0;border-bottom-left-radius:4px;border-top-left-radius:4px}.pagination>li:last-child>a,.pagination>li:last-child>span{border-bottom-right-radius:4px;border-top-right-radius:4px}.pagination>li>a:hover,.pagination>li>span:hover,.pagination>li>a:focus,.pagination>li>span:focus{z-index:3;color:#23527c;background-color:#eee;border-color:#ddd}.pagination>.active>a,.pagination>.active>span,.pagination>.active>a:hover,.pagination>.active>span:hover,.pagination>.active>a:focus,.pagination>.active>span:focus{z-index:2;color:#fff;background-color:#337ab7;border-color:#337ab7;cursor:default}.pagination>.disabled>span,.pagination>.disabled>span:hover,.pagination>.disabled>span:focus,.pagination>.disabled>a,.pagination>.disabled>a:hover,.pagination>.disabled>a:focus{color:#777;background-color:#fff;border-color:#ddd;cursor:not-allowed}.pagination-lg>li>a,.pagination-lg>li>span{padding:10px 16px;font-size:18px;line-height:1.3333333}.pagination-lg>li:first-child>a,.pagination-lg>li:first-child>span{border-bottom-left-radius:6px;border-top-left-radius:6px}.pagination-lg>li:last-child>a,.pagination-lg>li:last-child>span{border-bottom-right-radius:6px;border-top-right-radius:6px}.pagination-sm>li>a,.pagination-sm>li>span{padding:5px 10px;font-size:12px;line-height:1.5}.pagination-sm>li:first-child>a,.pagination-sm>li:first-child>span{border-bottom-left-radius:3px;border-top-left-radius:3px}.pagination-sm>li:last-child>a,.pagination-sm>li:last-child>span{border-bottom-right-radius:3px;border-top-right-radius:3px}.pager{padding-left:0;margin:20px 0;list-style:none;text-align:center}.pager li{display:inline}.pager li>a,.pager li>span{display:inline-block;padding:5px 14px;background-color:#fff;border:1px solid #ddd;border-radius:15px}.pager li>a:hover,.pager li>a:focus{text-decoration:none;background-color:#eee}.pager .next>a,.pager .next>span{float:right}.pager .previous>a,.pager .previous>span{float:left}.pager .disabled>a,.pager .disabled>a:hover,.pager .disabled>a:focus,.pager .disabled>span{color:#777;background-color:#fff;cursor:not-allowed}.label{display:inline;padding:.2em .6em .3em;font-size:75%;font-weight:bold;line-height:1;color:#fff;text-align:center;white-space:nowrap;vertical-align:baseline;border-radius:.25em}a.label:hover,a.label:focus{color:#fff;text-decoration:none;cursor:pointer}.label:empty{display:none}.btn .label{position:relative;top:-1px}.label-default{background-color:#777}.label-default[href]:hover,.label-default[href]:focus{background-color:#5e5e5e}.label-primary{background-color:#337ab7}.label-primary[href]:hover,.label-primary[href]:focus{background-color:#286090}.label-success{background-color:#5cb85c}.label-success[href]:hover,.label-success[href]:focus{background-color:#449d44}.label-info{background-color:#5bc0de}.label-info[href]:hover,.label-info[href]:focus{background-color:#31b0d5}.label-warning{background-color:#f0ad4e}.label-warning[href]:hover,.label-warning[href]:focus{background-color:#ec971f}.label-danger{background-color:#d9534f}.label-danger[href]:hover,.label-danger[href]:focus{background-color:#c9302c}.badge{display:inline-block;min-width:10px;padding:3px 7px;font-size:12px;font-weight:bold;color:#fff;line-height:1;vertical-align:middle;white-space:nowrap;text-align:center;background-color:#777;border-radius:10px}.badge:empty{display:none}.btn .badge{position:relative;top:-1px}.btn-xs .badge,.btn-group-xs>.btn .badge{top:0;padding:1px 5px}a.badge:hover,a.badge:focus{color:#fff;text-decoration:none;cursor:pointer}.list-group-item.active>.badge,.nav-pills>.active>a>.badge{color:#337ab7;background-color:#fff}.list-group-item>.badge{float:right}.list-group-item>.badge+.badge{margin-right:5px}.nav-pills>li>a>.badge{margin-left:3px}.jumbotron{padding-top:30px;padding-bottom:30px;margin-bottom:30px;color:inherit;background-color:#eee}.jumbotron h1,.jumbotron .h1{color:inherit}.jumbotron p{margin-bottom:15px;font-size:21px;font-weight:200}.jumbotron>hr{border-top-color:#d5d5d5}.container .jumbotron,.container-fluid .jumbotron{border-radius:6px}.jumbotron .container{max-width:100%}@media screen and (min-width:768px){.jumbotron{padding-top:48px;padding-bottom:48px}.container .jumbotron,.container-fluid .jumbotron{padding-left:60px;padding-right:60px}.jumbotron h1,.jumbotron .h1{font-size:63px}}.thumbnail{display:block;padding:4px;margin-bottom:20px;line-height:1.42857143;background-color:#fff;border:1px solid #ddd;border-radius:4px;-webkit-transition:border .2s ease-in-out;-o-transition:border .2s ease-in-out;transition:border .2s ease-in-out}.thumbnail>img,.thumbnail a>img{margin-left:auto;margin-right:auto}a.thumbnail:hover,a.thumbnail:focus,a.thumbnail.active{border-color:#337ab7}.thumbnail .caption{padding:9px;color:#333}.alert{padding:15px;margin-bottom:20px;border:1px solid transparent;border-radius:4px}.alert h4{margin-top:0;color:inherit}.alert .alert-link{font-weight:bold}.alert>p,.alert>ul{margin-bottom:0}.alert>p+p{margin-top:5px}.alert-dismissable,.alert-dismissible{padding-right:35px}.alert-dismissable .close,.alert-dismissible .close{position:relative;top:-2px;right:-21px;color:inherit}.alert-success{background-color:#dff0d8;border-color:#d6e9c6;color:#3c763d}.alert-success hr{border-top-color:#c9e2b3}.alert-success .alert-link{color:#2b542c}.alert-info{background-color:#d9edf7;border-color:#bce8f1;color:#31708f}.alert-info hr{border-top-color:#a6e1ec}.alert-info .alert-link{color:#245269}.alert-warning{background-color:#fcf8e3;border-color:#faebcc;color:#8a6d3b}.alert-warning hr{border-top-color:#f7e1b5}.alert-warning .alert-link{color:#66512c}.alert-danger{background-color:#f2dede;border-color:#ebccd1;color:#a94442}.alert-danger hr{border-top-color:#e4b9c0}.alert-danger .alert-link{color:#843534}@-webkit-keyframes progress-bar-stripes{from{background-position:40px 0}to{background-position:0 0}}@-o-keyframes progress-bar-stripes{from{background-position:40px 0}to{background-position:0 0}}@keyframes progress-bar-stripes{from{background-position:40px 0}to{background-position:0 0}}.progress{overflow:hidden;height:20px;margin-bottom:20px;background-color:#f5f5f5;border-radius:4px;-webkit-box-shadow:inset 0 1px 2px rgba(0,0,0,0.1);box-shadow:inset 0 1px 2px rgba(0,0,0,0.1)}.progress-bar{float:left;width:0%;height:100%;font-size:12px;line-height:20px;color:#fff;text-align:center;background-color:#337ab7;-webkit-box-shadow:inset 0 -1px 0 rgba(0,0,0,0.15);box-shadow:inset 0 -1px 0 rgba(0,0,0,0.15);-webkit-transition:width .6s ease;-o-transition:width .6s ease;transition:width .6s ease}.progress-striped .progress-bar,.progress-bar-striped{background-image:-webkit-linear-gradient(45deg, rgba(255,255,255,0.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.15) 50%, rgba(255,255,255,0.15) 75%, transparent 75%, transparent);background-image:-o-linear-gradient(45deg, rgba(255,255,255,0.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.15) 50%, rgba(255,255,255,0.15) 75%, transparent 75%, transparent);background-image:linear-gradient(45deg, rgba(255,255,255,0.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.15) 50%, rgba(255,255,255,0.15) 75%, transparent 75%, transparent);-webkit-background-size:40px 40px;background-size:40px 40px}.progress.active .progress-bar,.progress-bar.active{-webkit-animation:progress-bar-stripes 2s linear infinite;-o-animation:progress-bar-stripes 2s linear infinite;animation:progress-bar-stripes 2s linear infinite}.progress-bar-success{background-color:#5cb85c}.progress-striped .progress-bar-success{background-image:-webkit-linear-gradient(45deg, rgba(255,255,255,0.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.15) 50%, rgba(255,255,255,0.15) 75%, transparent 75%, transparent);background-image:-o-linear-gradient(45deg, rgba(255,255,255,0.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.15) 50%, rgba(255,255,255,0.15) 75%, transparent 75%, transparent);background-image:linear-gradient(45deg, rgba(255,255,255,0.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.15) 50%, rgba(255,255,255,0.15) 75%, transparent 75%, transparent)}.progress-bar-info{background-color:#5bc0de}.progress-striped .progress-bar-info{background-image:-webkit-linear-gradient(45deg, rgba(255,255,255,0.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.15) 50%, rgba(255,255,255,0.15) 75%, transparent 75%, transparent);background-image:-o-linear-gradient(45deg, rgba(255,255,255,0.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.15) 50%, rgba(255,255,255,0.15) 75%, transparent 75%, transparent);background-image:linear-gradient(45deg, rgba(255,255,255,0.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.15) 50%, rgba(255,255,255,0.15) 75%, transparent 75%, transparent)}.progress-bar-warning{background-color:#f0ad4e}.progress-striped .progress-bar-warning{background-image:-webkit-linear-gradient(45deg, rgba(255,255,255,0.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.15) 50%, rgba(255,255,255,0.15) 75%, transparent 75%, transparent);background-image:-o-linear-gradient(45deg, rgba(255,255,255,0.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.15) 50%, rgba(255,255,255,0.15) 75%, transparent 75%, transparent);background-image:linear-gradient(45deg, rgba(255,255,255,0.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.15) 50%, rgba(255,255,255,0.15) 75%, transparent 75%, transparent)}.progress-bar-danger{background-color:#d9534f}.progress-striped .progress-bar-danger{background-image:-webkit-linear-gradient(45deg, rgba(255,255,255,0.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.15) 50%, rgba(255,255,255,0.15) 75%, transparent 75%, transparent);background-image:-o-linear-gradient(45deg, rgba(255,255,255,0.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.15) 50%, rgba(255,255,255,0.15) 75%, transparent 75%, transparent);background-image:linear-gradient(45deg, rgba(255,255,255,0.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.15) 50%, rgba(255,255,255,0.15) 75%, transparent 75%, transparent)}.media{margin-top:15px}.media:first-child{margin-top:0}.media,.media-body{zoom:1;overflow:hidden}.media-body{width:10000px}.media-object{display:block}.media-object.img-thumbnail{max-width:none}.media-right,.media>.pull-right{padding-left:10px}.media-left,.media>.pull-left{padding-right:10px}.media-left,.media-right,.media-body{display:table-cell;vertical-align:top}.media-middle{vertical-align:middle}.media-bottom{vertical-align:bottom}.media-heading{margin-top:0;margin-bottom:5px}.media-list{padding-left:0;list-style:none}.list-group{margin-bottom:20px;padding-left:0}.list-group-item{position:relative;display:block;padding:10px 15px;margin-bottom:-1px;background-color:#fff;border:1px solid #ddd}.list-group-item:first-child{border-top-right-radius:4px;border-top-left-radius:4px}.list-group-item:last-child{margin-bottom:0;border-bottom-right-radius:4px;border-bottom-left-radius:4px}a.list-group-item,button.list-group-item{color:#555}a.list-group-item .list-group-item-heading,button.list-group-item .list-group-item-heading{color:#333}a.list-group-item:hover,button.list-group-item:hover,a.list-group-item:focus,button.list-group-item:focus{text-decoration:none;color:#555;background-color:#f5f5f5}button.list-group-item{width:100%;text-align:left}.list-group-item.disabled,.list-group-item.disabled:hover,.list-group-item.disabled:focus{background-color:#eee;color:#777;cursor:not-allowed}.list-group-item.disabled .list-group-item-heading,.list-group-item.disabled:hover .list-group-item-heading,.list-group-item.disabled:focus .list-group-item-heading{color:inherit}.list-group-item.disabled .list-group-item-text,.list-group-item.disabled:hover .list-group-item-text,.list-group-item.disabled:focus .list-group-item-text{color:#777}.list-group-item.active,.list-group-item.active:hover,.list-group-item.active:focus{z-index:2;color:#fff;background-color:#337ab7;border-color:#337ab7}.list-group-item.active .list-group-item-heading,.list-group-item.active:hover .list-group-item-heading,.list-group-item.active:focus .list-group-item-heading,.list-group-item.active .list-group-item-heading>small,.list-group-item.active:hover .list-group-item-heading>small,.list-group-item.active:focus .list-group-item-heading>small,.list-group-item.active .list-group-item-heading>.small,.list-group-item.active:hover .list-group-item-heading>.small,.list-group-item.active:focus .list-group-item-heading>.small{color:inherit}.list-group-item.active .list-group-item-text,.list-group-item.active:hover .list-group-item-text,.list-group-item.active:focus .list-group-item-text{color:#c7ddef}.list-group-item-success{color:#3c763d;background-color:#dff0d8}a.list-group-item-success,button.list-group-item-success{color:#3c763d}a.list-group-item-success .list-group-item-heading,button.list-group-item-success .list-group-item-heading{color:inherit}a.list-group-item-success:hover,button.list-group-item-success:hover,a.list-group-item-success:focus,button.list-group-item-success:focus{color:#3c763d;background-color:#d0e9c6}a.list-group-item-success.active,button.list-group-item-success.active,a.list-group-item-success.active:hover,button.list-group-item-success.active:hover,a.list-group-item-success.active:focus,button.list-group-item-success.active:focus{color:#fff;background-color:#3c763d;border-color:#3c763d}.list-group-item-info{color:#31708f;background-color:#d9edf7}a.list-group-item-info,button.list-group-item-info{color:#31708f}a.list-group-item-info .list-group-item-heading,button.list-group-item-info .list-group-item-heading{color:inherit}a.list-group-item-info:hover,button.list-group-item-info:hover,a.list-group-item-info:focus,button.list-group-item-info:focus{color:#31708f;background-color:#c4e3f3}a.list-group-item-info.active,button.list-group-item-info.active,a.list-group-item-info.active:hover,button.list-group-item-info.active:hover,a.list-group-item-info.active:focus,button.list-group-item-info.active:focus{color:#fff;background-color:#31708f;border-color:#31708f}.list-group-item-warning{color:#8a6d3b;background-color:#fcf8e3}a.list-group-item-warning,button.list-group-item-warning{color:#8a6d3b}a.list-group-item-warning .list-group-item-heading,button.list-group-item-warning .list-group-item-heading{color:inherit}a.list-group-item-warning:hover,button.list-group-item-warning:hover,a.list-group-item-warning:focus,button.list-group-item-warning:focus{color:#8a6d3b;background-color:#faf2cc}a.list-group-item-warning.active,button.list-group-item-warning.active,a.list-group-item-warning.active:hover,button.list-group-item-warning.active:hover,a.list-group-item-warning.active:focus,button.list-group-item-warning.active:focus{color:#fff;background-color:#8a6d3b;border-color:#8a6d3b}.list-group-item-danger{color:#a94442;background-color:#f2dede}a.list-group-item-danger,button.list-group-item-danger{color:#a94442}a.list-group-item-danger .list-group-item-heading,button.list-group-item-danger .list-group-item-heading{color:inherit}a.list-group-item-danger:hover,button.list-group-item-danger:hover,a.list-group-item-danger:focus,button.list-group-item-danger:focus{color:#a94442;background-color:#ebcccc}a.list-group-item-danger.active,button.list-group-item-danger.active,a.list-group-item-danger.active:hover,button.list-group-item-danger.active:hover,a.list-group-item-danger.active:focus,button.list-group-item-danger.active:focus{color:#fff;background-color:#a94442;border-color:#a94442}.list-group-item-heading{margin-top:0;margin-bottom:5px}.list-group-item-text{margin-bottom:0;line-height:1.3}.panel{margin-bottom:20px;background-color:#fff;border:1px solid transparent;border-radius:4px;-webkit-box-shadow:0 1px 1px rgba(0,0,0,0.05);box-shadow:0 1px 1px rgba(0,0,0,0.05)}.panel-body{padding:15px}.panel-heading{padding:10px 15px;border-bottom:1px solid transparent;border-top-right-radius:3px;border-top-left-radius:3px}.panel-heading>.dropdown .dropdown-toggle{color:inherit}.panel-title{margin-top:0;margin-bottom:0;font-size:16px;color:inherit}.panel-title>a,.panel-title>small,.panel-title>.small,.panel-title>small>a,.panel-title>.small>a{color:inherit}.panel-footer{padding:10px 15px;background-color:#f5f5f5;border-top:1px solid #ddd;border-bottom-right-radius:3px;border-bottom-left-radius:3px}.panel>.list-group,.panel>.panel-collapse>.list-group{margin-bottom:0}.panel>.list-group .list-group-item,.panel>.panel-collapse>.list-group .list-group-item{border-width:1px 0;border-radius:0}.panel>.list-group:first-child .list-group-item:first-child,.panel>.panel-collapse>.list-group:first-child .list-group-item:first-child{border-top:0;border-top-right-radius:3px;border-top-left-radius:3px}.panel>.list-group:last-child .list-group-item:last-child,.panel>.panel-collapse>.list-group:last-child .list-group-item:last-child{border-bottom:0;border-bottom-right-radius:3px;border-bottom-left-radius:3px}.panel>.panel-heading+.panel-collapse>.list-group .list-group-item:first-child{border-top-right-radius:0;border-top-left-radius:0}.panel-heading+.list-group .list-group-item:first-child{border-top-width:0}.list-group+.panel-footer{border-top-width:0}.panel>.table,.panel>.table-responsive>.table,.panel>.panel-collapse>.table{margin-bottom:0}.panel>.table caption,.panel>.table-responsive>.table caption,.panel>.panel-collapse>.table caption{padding-left:15px;padding-right:15px}.panel>.table:first-child,.panel>.table-responsive:first-child>.table:first-child{border-top-right-radius:3px;border-top-left-radius:3px}.panel>.table:first-child>thead:first-child>tr:first-child,.panel>.table-responsive:first-child>.table:first-child>thead:first-child>tr:first-child,.panel>.table:first-child>tbody:first-child>tr:first-child,.panel>.table-responsive:first-child>.table:first-child>tbody:first-child>tr:first-child{border-top-left-radius:3px;border-top-right-radius:3px}.panel>.table:first-child>thead:first-child>tr:first-child td:first-child,.panel>.table-responsive:first-child>.table:first-child>thead:first-child>tr:first-child td:first-child,.panel>.table:first-child>tbody:first-child>tr:first-child td:first-child,.panel>.table-responsive:first-child>.table:first-child>tbody:first-child>tr:first-child td:first-child,.panel>.table:first-child>thead:first-child>tr:first-child th:first-child,.panel>.table-responsive:first-child>.table:first-child>thead:first-child>tr:first-child th:first-child,.panel>.table:first-child>tbody:first-child>tr:first-child th:first-child,.panel>.table-responsive:first-child>.table:first-child>tbody:first-child>tr:first-child th:first-child{border-top-left-radius:3px}.panel>.table:first-child>thead:first-child>tr:first-child td:last-child,.panel>.table-responsive:first-child>.table:first-child>thead:first-child>tr:first-child td:last-child,.panel>.table:first-child>tbody:first-child>tr:first-child td:last-child,.panel>.table-responsive:first-child>.table:first-child>tbody:first-child>tr:first-child td:last-child,.panel>.table:first-child>thead:first-child>tr:first-child th:last-child,.panel>.table-responsive:first-child>.table:first-child>thead:first-child>tr:first-child th:last-child,.panel>.table:first-child>tbody:first-child>tr:first-child th:last-child,.panel>.table-responsive:first-child>.table:first-child>tbody:first-child>tr:first-child th:last-child{border-top-right-radius:3px}.panel>.table:last-child,.panel>.table-responsive:last-child>.table:last-child{border-bottom-right-radius:3px;border-bottom-left-radius:3px}.panel>.table:last-child>tbody:last-child>tr:last-child,.panel>.table-responsive:last-child>.table:last-child>tbody:last-child>tr:last-child,.panel>.table:last-child>tfoot:last-child>tr:last-child,.panel>.table-responsive:last-child>.table:last-child>tfoot:last-child>tr:last-child{border-bottom-left-radius:3px;border-bottom-right-radius:3px}.panel>.table:last-child>tbody:last-child>tr:last-child td:first-child,.panel>.table-responsive:last-child>.table:last-child>tbody:last-child>tr:last-child td:first-child,.panel>.table:last-child>tfoot:last-child>tr:last-child td:first-child,.panel>.table-responsive:last-child>.table:last-child>tfoot:last-child>tr:last-child td:first-child,.panel>.table:last-child>tbody:last-child>tr:last-child th:first-child,.panel>.table-responsive:last-child>.table:last-child>tbody:last-child>tr:last-child th:first-child,.panel>.table:last-child>tfoot:last-child>tr:last-child th:first-child,.panel>.table-responsive:last-child>.table:last-child>tfoot:last-child>tr:last-child th:first-child{border-bottom-left-radius:3px}.panel>.table:last-child>tbody:last-child>tr:last-child td:last-child,.panel>.table-responsive:last-child>.table:last-child>tbody:last-child>tr:last-child td:last-child,.panel>.table:last-child>tfoot:last-child>tr:last-child td:last-child,.panel>.table-responsive:last-child>.table:last-child>tfoot:last-child>tr:last-child td:last-child,.panel>.table:last-child>tbody:last-child>tr:last-child th:last-child,.panel>.table-responsive:last-child>.table:last-child>tbody:last-child>tr:last-child th:last-child,.panel>.table:last-child>tfoot:last-child>tr:last-child th:last-child,.panel>.table-responsive:last-child>.table:last-child>tfoot:last-child>tr:last-child th:last-child{border-bottom-right-radius:3px}.panel>.panel-body+.table,.panel>.panel-body+.table-responsive,.panel>.table+.panel-body,.panel>.table-responsive+.panel-body{border-top:1px solid #ddd}.panel>.table>tbody:first-child>tr:first-child th,.panel>.table>tbody:first-child>tr:first-child td{border-top:0}.panel>.table-bordered,.panel>.table-responsive>.table-bordered{border:0}.panel>.table-bordered>thead>tr>th:first-child,.panel>.table-responsive>.table-bordered>thead>tr>th:first-child,.panel>.table-bordered>tbody>tr>th:first-child,.panel>.table-responsive>.table-bordered>tbody>tr>th:first-child,.panel>.table-bordered>tfoot>tr>th:first-child,.panel>.table-responsive>.table-bordered>tfoot>tr>th:first-child,.panel>.table-bordered>thead>tr>td:first-child,.panel>.table-responsive>.table-bordered>thead>tr>td:first-child,.panel>.table-bordered>tbody>tr>td:first-child,.panel>.table-responsive>.table-bordered>tbody>tr>td:first-child,.panel>.table-bordered>tfoot>tr>td:first-child,.panel>.table-responsive>.table-bordered>tfoot>tr>td:first-child{border-left:0}.panel>.table-bordered>thead>tr>th:last-child,.panel>.table-responsive>.table-bordered>thead>tr>th:last-child,.panel>.table-bordered>tbody>tr>th:last-child,.panel>.table-responsive>.table-bordered>tbody>tr>th:last-child,.panel>.table-bordered>tfoot>tr>th:last-child,.panel>.table-responsive>.table-bordered>tfoot>tr>th:last-child,.panel>.table-bordered>thead>tr>td:last-child,.panel>.table-responsive>.table-bordered>thead>tr>td:last-child,.panel>.table-bordered>tbody>tr>td:last-child,.panel>.table-responsive>.table-bordered>tbody>tr>td:last-child,.panel>.table-bordered>tfoot>tr>td:last-child,.panel>.table-responsive>.table-bordered>tfoot>tr>td:last-child{border-right:0}.panel>.table-bordered>thead>tr:first-child>td,.panel>.table-responsive>.table-bordered>thead>tr:first-child>td,.panel>.table-bordered>tbody>tr:first-child>td,.panel>.table-responsive>.table-bordered>tbody>tr:first-child>td,.panel>.table-bordered>thead>tr:first-child>th,.panel>.table-responsive>.table-bordered>thead>tr:first-child>th,.panel>.table-bordered>tbody>tr:first-child>th,.panel>.table-responsive>.table-bordered>tbody>tr:first-child>th{border-bottom:0}.panel>.table-bordered>tbody>tr:last-child>td,.panel>.table-responsive>.table-bordered>tbody>tr:last-child>td,.panel>.table-bordered>tfoot>tr:last-child>td,.panel>.table-responsive>.table-bordered>tfoot>tr:last-child>td,.panel>.table-bordered>tbody>tr:last-child>th,.panel>.table-responsive>.table-bordered>tbody>tr:last-child>th,.panel>.table-bordered>tfoot>tr:last-child>th,.panel>.table-responsive>.table-bordered>tfoot>tr:last-child>th{border-bottom:0}.panel>.table-responsive{border:0;margin-bottom:0}.panel-group{margin-bottom:20px}.panel-group .panel{margin-bottom:0;border-radius:4px}.panel-group .panel+.panel{margin-top:5px}.panel-group .panel-heading{border-bottom:0}.panel-group .panel-heading+.panel-collapse>.panel-body,.panel-group .panel-heading+.panel-collapse>.list-group{border-top:1px solid #ddd}.panel-group .panel-footer{border-top:0}.panel-group .panel-footer+.panel-collapse .panel-body{border-bottom:1px solid #ddd}.panel-default{border-color:#ddd}.panel-default>.panel-heading{color:#333;background-color:#f5f5f5;border-color:#ddd}.panel-default>.panel-heading+.panel-collapse>.panel-body{border-top-color:#ddd}.panel-default>.panel-heading .badge{color:#f5f5f5;background-color:#333}.panel-default>.panel-footer+.panel-collapse>.panel-body{border-bottom-color:#ddd}.panel-primary{border-color:#337ab7}.panel-primary>.panel-heading{color:#fff;background-color:#337ab7;border-color:#337ab7}.panel-primary>.panel-heading+.panel-collapse>.panel-body{border-top-color:#337ab7}.panel-primary>.panel-heading .badge{color:#337ab7;background-color:#fff}.panel-primary>.panel-footer+.panel-collapse>.panel-body{border-bottom-color:#337ab7}.panel-success{border-color:#d6e9c6}.panel-success>.panel-heading{color:#3c763d;background-color:#dff0d8;border-color:#d6e9c6}.panel-success>.panel-heading+.panel-collapse>.panel-body{border-top-color:#d6e9c6}.panel-success>.panel-heading .badge{color:#dff0d8;background-color:#3c763d}.panel-success>.panel-footer+.panel-collapse>.panel-body{border-bottom-color:#d6e9c6}.panel-info{border-color:#bce8f1}.panel-info>.panel-heading{color:#31708f;background-color:#d9edf7;border-color:#bce8f1}.panel-info>.panel-heading+.panel-collapse>.panel-body{border-top-color:#bce8f1}.panel-info>.panel-heading .badge{color:#d9edf7;background-color:#31708f}.panel-info>.panel-footer+.panel-collapse>.panel-body{border-bottom-color:#bce8f1}.panel-warning{border-color:#faebcc}.panel-warning>.panel-heading{color:#8a6d3b;background-color:#fcf8e3;border-color:#faebcc}.panel-warning>.panel-heading+.panel-collapse>.panel-body{border-top-color:#faebcc}.panel-warning>.panel-heading .badge{color:#fcf8e3;background-color:#8a6d3b}.panel-warning>.panel-footer+.panel-collapse>.panel-body{border-bottom-color:#faebcc}.panel-danger{border-color:#ebccd1}.panel-danger>.panel-heading{color:#a94442;background-color:#f2dede;border-color:#ebccd1}.panel-danger>.panel-heading+.panel-collapse>.panel-body{border-top-color:#ebccd1}.panel-danger>.panel-heading .badge{color:#f2dede;background-color:#a94442}.panel-danger>.panel-footer+.panel-collapse>.panel-body{border-bottom-color:#ebccd1}.embed-responsive{position:relative;display:block;height:0;padding:0;overflow:hidden}.embed-responsive .embed-responsive-item,.embed-responsive iframe,.embed-responsive embed,.embed-responsive object,.embed-responsive video{position:absolute;top:0;left:0;bottom:0;height:100%;width:100%;border:0}.embed-responsive-16by9{padding-bottom:56.25%}.embed-responsive-4by3{padding-bottom:75%}.well{min-height:20px;padding:19px;margin-bottom:20px;background-color:#f5f5f5;border:1px solid #e3e3e3;border-radius:4px;-webkit-box-shadow:inset 0 1px 1px rgba(0,0,0,0.05);box-shadow:inset 0 1px 1px rgba(0,0,0,0.05)}.well blockquote{border-color:#ddd;border-color:rgba(0,0,0,0.15)}.well-lg{padding:24px;border-radius:6px}.well-sm{padding:9px;border-radius:3px}.close{float:right;font-size:21px;font-weight:bold;line-height:1;color:#000;text-shadow:0 1px 0 #fff;opacity:.2;filter:alpha(opacity=20)}.close:hover,.close:focus{color:#000;text-decoration:none;cursor:pointer;opacity:.5;filter:alpha(opacity=50)}button.close{padding:0;cursor:pointer;background:transparent;border:0;-webkit-appearance:none}.clearfix:before,.clearfix:after,.dl-horizontal dd:before,.dl-horizontal dd:after,.pager:before,.pager:after,.panel-body:before,.panel-body:after{content:" ";display:table}.clearfix:after,.dl-horizontal dd:after,.pager:after,.panel-body:after{clear:both}.center-block{display:block;margin-left:auto;margin-right:auto}.pull-right{float:right !important}.pull-left{float:left !important}.hide{display:none !important}.show{display:block !important}.invisible{visibility:hidden}.text-hide{font:0/0 a;color:transparent;text-shadow:none;background-color:transparent;border:0}.hidden{display:none !important}.affix{position:fixed}
.btn-default,.btn-primary,.btn-success,.btn-info,.btn-warning,.btn-danger{text-shadow:0 -1px 0 rgba(0,0,0,0.2);-webkit-box-shadow:inset 0 1px 0 rgba(255,255,255,0.15),0 1px 1px rgba(0,0,0,0.075);box-shadow:inset 0 1px 0 rgba(255,255,255,0.15),0 1px 1px rgba(0,0,0,0.075)}.btn-default:active,.btn-primary:active,.btn-success:active,.btn-info:active,.btn-warning:active,.btn-danger:active,.btn-default.active,.btn-primary.active,.btn-success.active,.btn-info.active,.btn-warning.active,.btn-danger.active{-webkit-box-shadow:inset 0 3px 5px rgba(0,0,0,0.125);box-shadow:inset 0 3px 5px rgba(0,0,0,0.125)}.btn-default.disabled,.btn-primary.disabled,.btn-success.disabled,.btn-info.disabled,.btn-warning.disabled,.btn-danger.disabled,.btn-default[disabled],.btn-primary[disabled],.btn-success[disabled],.btn-info[disabled],.btn-warning[disabled],.btn-danger[disabled],fieldset[disabled] .btn-default,fieldset[disabled] .btn-primary,fieldset[disabled] .btn-success,fieldset[disabled] .btn-info,fieldset[disabled] .btn-warning,fieldset[disabled] .btn-danger{-webkit-box-shadow:none;box-shadow:none}.btn-default .badge,.btn-primary .badge,.btn-success .badge,.btn-info .badge,.btn-warning .badge,.btn-danger .badge{text-shadow:none}.btn:active,.btn.active{background-image:none}.btn-default{background-image:-webkit-linear-gradient(top, #fff 0, #e0e0e0 100%);background-image:-o-linear-gradient(top, #fff 0, #e0e0e0 100%);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #fff), to(#e0e0e0));background-image:linear-gradient(to bottom, #fff 0, #e0e0e0 100%);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffffff', endColorstr='#ffe0e0e0', GradientType=0);filter:progid:DXImageTransform.Microsoft.gradient(enabled = false);background-repeat:repeat-x;border-color:#dbdbdb;text-shadow:0 1px 0 #fff;border-color:#ccc}.btn-default:hover,.btn-default:focus{background-color:#e0e0e0;background-position:0 -15px}.btn-default:active,.btn-default.active{background-color:#e0e0e0;border-color:#dbdbdb}.btn-default.disabled,.btn-default[disabled],fieldset[disabled] .btn-default,.btn-default.disabled:hover,.btn-default[disabled]:hover,fieldset[disabled] .btn-default:hover,.btn-default.disabled:focus,.btn-default[disabled]:focus,fieldset[disabled] .btn-default:focus,.btn-default.disabled.focus,.btn-default[disabled].focus,fieldset[disabled] .btn-default.focus,.btn-default.disabled:active,.btn-default[disabled]:active,fieldset[disabled] .btn-default:active,.btn-default.disabled.active,.btn-default[disabled].active,fieldset[disabled] .btn-default.active{background-color:#e0e0e0;background-image:none}.btn-primary{background-image:-webkit-linear-gradient(top, #337ab7 0, #265a88 100%);background-image:-o-linear-gradient(top, #337ab7 0, #265a88 100%);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #337ab7), to(#265a88));background-image:linear-gradient(to bottom, #337ab7 0, #265a88 100%);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff337ab7', endColorstr='#ff265a88', GradientType=0);filter:progid:DXImageTransform.Microsoft.gradient(enabled = false);background-repeat:repeat-x;border-color:#245580}.btn-primary:hover,.btn-primary:focus{background-color:#265a88;background-position:0 -15px}.btn-primary:active,.btn-primary.active{background-color:#265a88;border-color:#245580}.btn-primary.disabled,.btn-primary[disabled],fieldset[disabled] .btn-primary,.btn-primary.disabled:hover,.btn-primary[disabled]:hover,fieldset[disabled] .btn-primary:hover,.btn-primary.disabled:focus,.btn-primary[disabled]:focus,fieldset[disabled] .btn-primary:focus,.btn-primary.disabled.focus,.btn-primary[disabled].focus,fieldset[disabled] .btn-primary.focus,.btn-primary.disabled:active,.btn-primary[disabled]:active,fieldset[disabled] .btn-primary:active,.btn-primary.disabled.active,.btn-primary[disabled].active,fieldset[disabled] .btn-primary.active{background-color:#265a88;background-image:none}.btn-success{background-image:-webkit-linear-gradient(top, #5cb85c 0, #419641 100%);background-image:-o-linear-gradient(top, #5cb85c 0, #419641 100%);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #5cb85c), to(#419641));background-image:linear-gradient(to bottom, #5cb85c 0, #419641 100%);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff5cb85c', endColorstr='#ff419641', GradientType=0);filter:progid:DXImageTransform.Microsoft.gradient(enabled = false);background-repeat:repeat-x;border-color:#3e8f3e}.btn-success:hover,.btn-success:focus{background-color:#419641;background-position:0 -15px}.btn-success:active,.btn-success.active{background-color:#419641;border-color:#3e8f3e}.btn-success.disabled,.btn-success[disabled],fieldset[disabled] .btn-success,.btn-success.disabled:hover,.btn-success[disabled]:hover,fieldset[disabled] .btn-success:hover,.btn-success.disabled:focus,.btn-success[disabled]:focus,fieldset[disabled] .btn-success:focus,.btn-success.disabled.focus,.btn-success[disabled].focus,fieldset[disabled] .btn-success.focus,.btn-success.disabled:active,.btn-success[disabled]:active,fieldset[disabled] .btn-success:active,.btn-success.disabled.active,.btn-success[disabled].active,fieldset[disabled] .btn-success.active{background-color:#419641;background-image:none}.btn-info{background-image:-webkit-linear-gradient(top, #5bc0de 0, #2aabd2 100%);background-image:-o-linear-gradient(top, #5bc0de 0, #2aabd2 100%);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #5bc0de), to(#2aabd2));background-image:linear-gradient(to bottom, #5bc0de 0, #2aabd2 100%);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff5bc0de', endColorstr='#ff2aabd2', GradientType=0);filter:progid:DXImageTransform.Microsoft.gradient(enabled = false);background-repeat:repeat-x;border-color:#28a4c9}.btn-info:hover,.btn-info:focus{background-color:#2aabd2;background-position:0 -15px}.btn-info:active,.btn-info.active{background-color:#2aabd2;border-color:#28a4c9}.btn-info.disabled,.btn-info[disabled],fieldset[disabled] .btn-info,.btn-info.disabled:hover,.btn-info[disabled]:hover,fieldset[disabled] .btn-info:hover,.btn-info.disabled:focus,.btn-info[disabled]:focus,fieldset[disabled] .btn-info:focus,.btn-info.disabled.focus,.btn-info[disabled].focus,fieldset[disabled] .btn-info.focus,.btn-info.disabled:active,.btn-info[disabled]:active,fieldset[disabled] .btn-info:active,.btn-info.disabled.active,.btn-info[disabled].active,fieldset[disabled] .btn-info.active{background-color:#2aabd2;background-image:none}.btn-warning{background-image:-webkit-linear-gradient(top, #f0ad4e 0, #eb9316 100%);background-image:-o-linear-gradient(top, #f0ad4e 0, #eb9316 100%);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #f0ad4e), to(#eb9316));background-image:linear-gradient(to bottom, #f0ad4e 0, #eb9316 100%);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#fff0ad4e', endColorstr='#ffeb9316', GradientType=0);filter:progid:DXImageTransform.Microsoft.gradient(enabled = false);background-repeat:repeat-x;border-color:#e38d13}.btn-warning:hover,.btn-warning:focus{background-color:#eb9316;background-position:0 -15px}.btn-warning:active,.btn-warning.active{background-color:#eb9316;border-color:#e38d13}.btn-warning.disabled,.btn-warning[disabled],fieldset[disabled] .btn-warning,.btn-warning.disabled:hover,.btn-warning[disabled]:hover,fieldset[disabled] .btn-warning:hover,.btn-warning.disabled:focus,.btn-warning[disabled]:focus,fieldset[disabled] .btn-warning:focus,.btn-warning.disabled.focus,.btn-warning[disabled].focus,fieldset[disabled] .btn-warning.focus,.btn-warning.disabled:active,.btn-warning[disabled]:active,fieldset[disabled] .btn-warning:active,.btn-warning.disabled.active,.btn-warning[disabled].active,fieldset[disabled] .btn-warning.active{background-color:#eb9316;background-image:none}.btn-danger{background-image:-webkit-linear-gradient(top, #d9534f 0, #c12e2a 100%);background-image:-o-linear-gradient(top, #d9534f 0, #c12e2a 100%);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #d9534f), to(#c12e2a));background-image:linear-gradient(to bottom, #d9534f 0, #c12e2a 100%);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffd9534f', endColorstr='#ffc12e2a', GradientType=0);filter:progid:DXImageTransform.Microsoft.gradient(enabled = false);background-repeat:repeat-x;border-color:#b92c28}.btn-danger:hover,.btn-danger:focus{background-color:#c12e2a;background-position:0 -15px}.btn-danger:active,.btn-danger.active{background-color:#c12e2a;border-color:#b92c28}.btn-danger.disabled,.btn-danger[disabled],fieldset[disabled] .btn-danger,.btn-danger.disabled:hover,.btn-danger[disabled]:hover,fieldset[disabled] .btn-danger:hover,.btn-danger.disabled:focus,.btn-danger[disabled]:focus,fieldset[disabled] .btn-danger:focus,.btn-danger.disabled.focus,.btn-danger[disabled].focus,fieldset[disabled] .btn-danger.focus,.btn-danger.disabled:active,.btn-danger[disabled]:active,fieldset[disabled] .btn-danger:active,.btn-danger.disabled.active,.btn-danger[disabled].active,fieldset[disabled] .btn-danger.active{background-color:#c12e2a;background-image:none}.thumbnail,.img-thumbnail{-webkit-box-shadow:0 1px 2px rgba(0,0,0,0.075);box-shadow:0 1px 2px rgba(0,0,0,0.075)}.dropdown-menu>li>a:hover,.dropdown-menu>li>a:focus{background-image:-webkit-linear-gradient(top, #f5f5f5 0, #e8e8e8 100%);background-image:-o-linear-gradient(top, #f5f5f5 0, #e8e8e8 100%);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #f5f5f5), to(#e8e8e8));background-image:linear-gradient(to bottom, #f5f5f5 0, #e8e8e8 100%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#fff5f5f5', endColorstr='#ffe8e8e8', GradientType=0);background-color:#e8e8e8}.dropdown-menu>.active>a,.dropdown-menu>.active>a:hover,.dropdown-menu>.active>a:focus{background-image:-webkit-linear-gradient(top, #337ab7 0, #2e6da4 100%);background-image:-o-linear-gradient(top, #337ab7 0, #2e6da4 100%);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #337ab7), to(#2e6da4));background-image:linear-gradient(to bottom, #337ab7 0, #2e6da4 100%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff337ab7', endColorstr='#ff2e6da4', GradientType=0);background-color:#2e6da4}.navbar-default{background-image:-webkit-linear-gradient(top, #fff 0, #f8f8f8 100%);background-image:-o-linear-gradient(top, #fff 0, #f8f8f8 100%);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #fff), to(#f8f8f8));background-image:linear-gradient(to bottom, #fff 0, #f8f8f8 100%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffffff', endColorstr='#fff8f8f8', GradientType=0);filter:progid:DXImageTransform.Microsoft.gradient(enabled = false);border-radius:4px;-webkit-box-shadow:inset 0 1px 0 rgba(255,255,255,0.15),0 1px 5px rgba(0,0,0,0.075);box-shadow:inset 0 1px 0 rgba(255,255,255,0.15),0 1px 5px rgba(0,0,0,0.075)}.navbar-default .navbar-nav>.open>a,.navbar-default .navbar-nav>.active>a{background-image:-webkit-linear-gradient(top, #dbdbdb 0, #e2e2e2 100%);background-image:-o-linear-gradient(top, #dbdbdb 0, #e2e2e2 100%);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #dbdbdb), to(#e2e2e2));background-image:linear-gradient(to bottom, #dbdbdb 0, #e2e2e2 100%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffdbdbdb', endColorstr='#ffe2e2e2', GradientType=0);-webkit-box-shadow:inset 0 3px 9px rgba(0,0,0,0.075);box-shadow:inset 0 3px 9px rgba(0,0,0,0.075)}.navbar-brand,.navbar-nav>li>a{text-shadow:0 1px 0 rgba(255,255,255,0.25)}.navbar-inverse{background-image:-webkit-linear-gradient(top, #3c3c3c 0, #222 100%);background-image:-o-linear-gradient(top, #3c3c3c 0, #222 100%);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #3c3c3c), to(#222));background-image:linear-gradient(to bottom, #3c3c3c 0, #222 100%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff3c3c3c', endColorstr='#ff222222', GradientType=0);filter:progid:DXImageTransform.Microsoft.gradient(enabled = false);border-radius:4px}.navbar-inverse .navbar-nav>.open>a,.navbar-inverse .navbar-nav>.active>a{background-image:-webkit-linear-gradient(top, #080808 0, #0f0f0f 100%);background-image:-o-linear-gradient(top, #080808 0, #0f0f0f 100%);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #080808), to(#0f0f0f));background-image:linear-gradient(to bottom, #080808 0, #0f0f0f 100%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff080808', endColorstr='#ff0f0f0f', GradientType=0);-webkit-box-shadow:inset 0 3px 9px rgba(0,0,0,0.25);box-shadow:inset 0 3px 9px rgba(0,0,0,0.25)}.navbar-inverse .navbar-brand,.navbar-inverse .navbar-nav>li>a{text-shadow:0 -1px 0 rgba(0,0,0,0.25)}.navbar-static-top,.navbar-fixed-top,.navbar-fixed-bottom{border-radius:0}@media (max-width:767px){.navbar .navbar-nav .open .dropdown-menu>.active>a,.navbar .navbar-nav .open .dropdown-menu>.active>a:hover,.navbar .navbar-nav .open .dropdown-menu>.active>a:focus{color:#fff;background-image:-webkit-linear-gradient(top, #337ab7 0, #2e6da4 100%);background-image:-o-linear-gradient(top, #337ab7 0, #2e6da4 100%);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #337ab7), to(#2e6da4));background-image:linear-gradient(to bottom, #337ab7 0, #2e6da4 100%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff337ab7', endColorstr='#ff2e6da4', GradientType=0)}}.alert{text-shadow:0 1px 0 rgba(255,255,255,0.2);-webkit-box-shadow:inset 0 1px 0 rgba(255,255,255,0.25),0 1px 2px rgba(0,0,0,0.05);box-shadow:inset 0 1px 0 rgba(255,255,255,0.25),0 1px 2px rgba(0,0,0,0.05)}.alert-success{background-image:-webkit-linear-gradient(top, #dff0d8 0, #c8e5bc 100%);background-image:-o-linear-gradient(top, #dff0d8 0, #c8e5bc 100%);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #dff0d8), to(#c8e5bc));background-image:linear-gradient(to bottom, #dff0d8 0, #c8e5bc 100%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffdff0d8', endColorstr='#ffc8e5bc', GradientType=0);border-color:#b2dba1}.alert-info{background-image:-webkit-linear-gradient(top, #d9edf7 0, #b9def0 100%);background-image:-o-linear-gradient(top, #d9edf7 0, #b9def0 100%);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #d9edf7), to(#b9def0));background-image:linear-gradient(to bottom, #d9edf7 0, #b9def0 100%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffd9edf7', endColorstr='#ffb9def0', GradientType=0);border-color:#9acfea}.alert-warning{background-image:-webkit-linear-gradient(top, #fcf8e3 0, #f8efc0 100%);background-image:-o-linear-gradient(top, #fcf8e3 0, #f8efc0 100%);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #fcf8e3), to(#f8efc0));background-image:linear-gradient(to bottom, #fcf8e3 0, #f8efc0 100%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#fffcf8e3', endColorstr='#fff8efc0', GradientType=0);border-color:#f5e79e}.alert-danger{background-image:-webkit-linear-gradient(top, #f2dede 0, #e7c3c3 100%);background-image:-o-linear-gradient(top, #f2dede 0, #e7c3c3 100%);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #f2dede), to(#e7c3c3));background-image:linear-gradient(to bottom, #f2dede 0, #e7c3c3 100%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#fff2dede', endColorstr='#ffe7c3c3', GradientType=0);border-color:#dca7a7}.progress{background-image:-webkit-linear-gradient(top, #ebebeb 0, #f5f5f5 100%);background-image:-o-linear-gradient(top, #ebebeb 0, #f5f5f5 100%);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #ebebeb), to(#f5f5f5));background-image:linear-gradient(to bottom, #ebebeb 0, #f5f5f5 100%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffebebeb', endColorstr='#fff5f5f5', GradientType=0)}.progress-bar{background-image:-webkit-linear-gradient(top, #337ab7 0, #286090 100%);background-image:-o-linear-gradient(top, #337ab7 0, #286090 100%);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #337ab7), to(#286090));background-image:linear-gradient(to bottom, #337ab7 0, #286090 100%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff337ab7', endColorstr='#ff286090', GradientType=0)}.progress-bar-success{background-image:-webkit-linear-gradient(top, #5cb85c 0, #449d44 100%);background-image:-o-linear-gradient(top, #5cb85c 0, #449d44 100%);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #5cb85c), to(#449d44));background-image:linear-gradient(to bottom, #5cb85c 0, #449d44 100%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff5cb85c', endColorstr='#ff449d44', GradientType=0)}.progress-bar-info{background-image:-webkit-linear-gradient(top, #5bc0de 0, #31b0d5 100%);background-image:-o-linear-gradient(top, #5bc0de 0, #31b0d5 100%);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #5bc0de), to(#31b0d5));background-image:linear-gradient(to bottom, #5bc0de 0, #31b0d5 100%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff5bc0de', endColorstr='#ff31b0d5', GradientType=0)}.progress-bar-warning{background-image:-webkit-linear-gradient(top, #f0ad4e 0, #ec971f 100%);background-image:-o-linear-gradient(top, #f0ad4e 0, #ec971f 100%);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #f0ad4e), to(#ec971f));background-image:linear-gradient(to bottom, #f0ad4e 0, #ec971f 100%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#fff0ad4e', endColorstr='#ffec971f', GradientType=0)}.progress-bar-danger{background-image:-webkit-linear-gradient(top, #d9534f 0, #c9302c 100%);background-image:-o-linear-gradient(top, #d9534f 0, #c9302c 100%);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #d9534f), to(#c9302c));background-image:linear-gradient(to bottom, #d9534f 0, #c9302c 100%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffd9534f', endColorstr='#ffc9302c', GradientType=0)}.progress-bar-striped{background-image:-webkit-linear-gradient(45deg, rgba(255,255,255,0.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.15) 50%, rgba(255,255,255,0.15) 75%, transparent 75%, transparent);background-image:-o-linear-gradient(45deg, rgba(255,255,255,0.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.15) 50%, rgba(255,255,255,0.15) 75%, transparent 75%, transparent);background-image:linear-gradient(45deg, rgba(255,255,255,0.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.15) 50%, rgba(255,255,255,0.15) 75%, transparent 75%, transparent)}.list-group{border-radius:4px;-webkit-box-shadow:0 1px 2px rgba(0,0,0,0.075);box-shadow:0 1px 2px rgba(0,0,0,0.075)}.list-group-item.active,.list-group-item.active:hover,.list-group-item.active:focus{text-shadow:0 -1px 0 #286090;background-image:-webkit-linear-gradient(top, #337ab7 0, #2b669a 100%);background-image:-o-linear-gradient(top, #337ab7 0, #2b669a 100%);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #337ab7), to(#2b669a));background-image:linear-gradient(to bottom, #337ab7 0, #2b669a 100%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff337ab7', endColorstr='#ff2b669a', GradientType=0);border-color:#2b669a}.list-group-item.active .badge,.list-group-item.active:hover .badge,.list-group-item.active:focus .badge{text-shadow:none}.panel{-webkit-box-shadow:0 1px 2px rgba(0,0,0,0.05);box-shadow:0 1px 2px rgba(0,0,0,0.05)}.panel-default>.panel-heading{background-image:-webkit-linear-gradient(top, #f5f5f5 0, #e8e8e8 100%);background-image:-o-linear-gradient(top, #f5f5f5 0, #e8e8e8 100%);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #f5f5f5), to(#e8e8e8));background-image:linear-gradient(to bottom, #f5f5f5 0, #e8e8e8 100%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#fff5f5f5', endColorstr='#ffe8e8e8', GradientType=0)}.panel-primary>.panel-heading{background-image:-webkit-linear-gradient(top, #337ab7 0, #2e6da4 100%);background-image:-o-linear-gradient(top, #337ab7 0, #2e6da4 100%);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #337ab7), to(#2e6da4));background-image:linear-gradient(to bottom, #337ab7 0, #2e6da4 100%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff337ab7', endColorstr='#ff2e6da4', GradientType=0)}.panel-success>.panel-heading{background-image:-webkit-linear-gradient(top, #dff0d8 0, #d0e9c6 100%);background-image:-o-linear-gradient(top, #dff0d8 0, #d0e9c6 100%);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #dff0d8), to(#d0e9c6));background-image:linear-gradient(to bottom, #dff0d8 0, #d0e9c6 100%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffdff0d8', endColorstr='#ffd0e9c6', GradientType=0)}.panel-info>.panel-heading{background-image:-webkit-linear-gradient(top, #d9edf7 0, #c4e3f3 100%);background-image:-o-linear-gradient(top, #d9edf7 0, #c4e3f3 100%);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #d9edf7), to(#c4e3f3));background-image:linear-gradient(to bottom, #d9edf7 0, #c4e3f3 100%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffd9edf7', endColorstr='#ffc4e3f3', GradientType=0)}.panel-warning>.panel-heading{background-image:-webkit-linear-gradient(top, #fcf8e3 0, #faf2cc 100%);background-image:-o-linear-gradient(top, #fcf8e3 0, #faf2cc 100%);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #fcf8e3), to(#faf2cc));background-image:linear-gradient(to bottom, #fcf8e3 0, #faf2cc 100%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#fffcf8e3', endColorstr='#fffaf2cc', GradientType=0)}.panel-danger>.panel-heading{background-image:-webkit-linear-gradient(top, #f2dede 0, #ebcccc 100%);background-image:-o-linear-gradient(top, #f2dede 0, #ebcccc 100%);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #f2dede), to(#ebcccc));background-image:linear-gradient(to bottom, #f2dede 0, #ebcccc 100%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#fff2dede', endColorstr='#ffebcccc', GradientType=0)}.well{background-image:-webkit-linear-gradient(top, #e8e8e8 0, #f5f5f5 100%);background-image:-o-linear-gradient(top, #e8e8e8 0, #f5f5f5 100%);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #e8e8e8), to(#f5f5f5));background-image:linear-gradient(to bottom, #e8e8e8 0, #f5f5f5 100%);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffe8e8e8', endColorstr='#fff5f5f5', GradientType=0);border-color:#dcdcdc;-webkit-box-shadow:inset 0 1px 3px rgba(0,0,0,0.05),0 1px 0 rgba(255,255,255,0.1);box-shadow:inset 0 1px 3px rgba(0,0,0,0.05),0 1px 0 rgba(255,255,255,0.1)}
  </style>
  
  <!--[if lt IE 9]>
  <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
  <![endif]-->
</head>
<body>
    <div class="jumbotron">
      <div class="container">
        <h1>Gishiki Installation</h1>
        <p>This is the installation of Gishiki framework.<br />When you will have correctly installed Gishiki you will need to read the documentation in order
        to develop your websites, applications and services.</p>
        <p><a class="btn btn-primary btn-lg" href="Documentation/index.html" role="button">Documentation &raquo;</a></p>
      </div>
    </div>
</div>
        <?php
        if (!Environment::GetCurrentEnvironment()->ExtensionSupport("zlib")) {
            //update the number of errors
            $errors++;
            
            //show the error to the user
            ?>
<div class="alert alert-danger" role="alert"><b>zlib:</b> In order to install and run Gishiki you need (at least) PHP 5.3 compiled with zlib enabled.
<br />Please, install a supported PHP version and retry.</div>
            <?php
        }
        
        if (!Environment::GetCurrentEnvironment()->ExtensionSupport("SimpleXML")) {
            //update the number of errors
            $errors++;
            
            //show the error to the user
            ?>
<div class="alert alert-danger" role="alert"><b>SimpleXML:</b> In order to install and run Gishiki you need (at least) PHP 5.3 compiled with SimpleXML enabled.
<br />Please, install a supported PHP version and retry.</div>
            <?php
        }
        
        if (!Environment::GetCurrentEnvironment()->ExtensionSupport('APC')) {
            //show the warning to the user
            ?>
<div class="alert alert-warning" role="alert"><b>APC:</b> Alternative PHP Caching extension is not installed, beside it is not necessary, it would increase performances and reduce disk usage.
<br />Please, install the APC extension for PHP. You can install it whenever you want.</div>
            <?php
        }
        
        if (!Environment::GetCurrentEnvironment()->ExtensionSupport("openssl")) {
            //update the number of errors
            $errors++;
            
            //show the error to the user
            ?>
<div class="alert alert-danger" role="alert"><b>OpenSSL:</b> OpenSSL PHP extension not installed, but it is required to install and run Gishiki.
<br />Please, install the OpenSSL extension for PHP and retry.</div>
            <?php
        }
        $errors = 0;
        if (!file_exists(Environment::GetCurrentEnvironment()->GetConfigurationProperty('APPLICATION_DIR'))) {
            if (!@mkdir(Environment::GetCurrentEnvironment()->GetConfigurationProperty('APPLICATION_DIR')))
            {
                //update the number of errors
                $errors++;
            } else {
                @file_put_contents(Environment::GetCurrentEnvironment()->GetConfigurationProperty('APPLICATION_DIR').".htaccess", "Deny from all ");
            }
        }
        
        if (!file_exists(Environment::GetCurrentEnvironment()->GetConfigurationProperty('DATABASE_DIR'))) {
            if (!@mkdir(Environment::GetCurrentEnvironment()->GetConfigurationProperty('DATABASE_DIR')))
            {
                //update the number of errors
                $errors++;
            } else {
                @file_put_contents(Environment::GetCurrentEnvironment()->GetConfigurationProperty('DATABASE_DIR').".htaccess", "Deny from all ");
            }
        }
        
        if (!file_exists(Environment::GetCurrentEnvironment()->GetConfigurationProperty('DATABASE_CONNECTION_DIR'))) {
            if (!@mkdir(Environment::GetCurrentEnvironment()->GetConfigurationProperty('DATABASE_CONNECTION_DIR')))
            {
                //update the number of errors
                $errors++;
            } else {
                @file_put_contents(Environment::GetCurrentEnvironment()->GetConfigurationProperty('DATABASE_CONNECTION_DIR').".htaccess", "Deny from all ");
            }
        }
        
        if (!file_exists(Environment::GetCurrentEnvironment()->GetConfigurationProperty('KEYS_DIR'))) {
            if (!@mkdir(Environment::GetCurrentEnvironment()->GetConfigurationProperty('KEYS_DIR')))
            {
                //update the number of errors
                $errors++;
            } else {
                @file_put_contents(Environment::GetCurrentEnvironment()->GetConfigurationProperty('KEYS_DIR').".htaccess", "Deny from all ");
            }
        }
        
        if (!file_exists(CONTROLLER_DIR)) {
            if (!@mkdir(CONTROLLER_DIR))
            {
                //update the number of errors
                $errors++;
            }
        }
        
        if (!file_exists(MODEL_DIR)) {
            if (!@mkdir(MODEL_DIR))
            {
                //update the number of errors
                $errors++;
            }
        }
        
        if (!file_exists(VIEW_DIR)) {
            if (!@mkdir(VIEW_DIR))
            {
                //update the number of errors
                $errors++;
            }
        }
        
        if (!file_exists(RESOURCE_DIR)) {
            if (!@mkdir(RESOURCE_DIR))
            {
                //update the number of errors
                $errors++;
            }
        }
        
        if (!file_exists(CLASS_DIR)) {
            if (!@mkdir(CLASS_DIR))
            {
                //update the number of errors
                $errors++;
            }
        }
        
        if ($errors > 0)
        {
            ?>
<div class="alert alert-danger" role="alert"><b>Directories:</b> the framework directory structure was not build because something prevent this script to modify the disk content.<br />
Please, check for permissions and retry.</div>
            <?php
        } else {
            /*      finalize the robots.txt file        */
            global $applicationDir;
            global $databaseDir;
            global $connections;
            global $keys;
            touch(ROOT."robots.txt");
            @file_put_contents(ROOT."robots.txt", "Disallow: /".$applicationDir."/".PHP_EOL, FILE_APPEND);
            @file_put_contents(ROOT."robots.txt", "Disallow: /".$databaseDir."/".PHP_EOL, FILE_APPEND);
            @file_put_contents(ROOT."robots.txt", "Disallow: /".$connections."/".PHP_EOL, FILE_APPEND);
            @file_put_contents(ROOT."robots.txt", "Disallow: /".$keys."/".PHP_EOL, FILE_APPEND);
            ?>
<div class="alert alert-info" role="alert"><b>Directories:</b> the framework directory structure has been built successfully.</div>
            <?php
        }
        
        //check for supported RDBMS
        $databaseDrivers = Environment::GetCurrentEnvironment()->GetDatabaseDrivers();
        if (in_array("sqlite3", $databaseDrivers, TRUE))
        {
            //create the default database
            $defaultDB = new Database();
            $defaultDB->CreateConnection("sqlite3://Default:".base64_encode(openssl_random_pseudo_bytes(64)));
            $defaultDB->SaveConnection("Default");
            $defaultDB->CloseConnection();
        }
        
        //setup the application unique RSA encryption key
        global $RSAServerUniqueKey;
        $keyGenerator = new AsymmetricCipher();
        $keyGenerator->GenerateNewKey($RSAServerUniqueKey, AsymmetricCipher::RSA4096);
        ?><div class="alert alert-info" role="alert"><b>RSA Key:</b> the default RSA key was created with the name: " <?php echo $RSAServerUniqueKey; ?> ".
            <br />Feel free to use or delete it.</div>

            <?php
            //get the file name of the install log file
            $currentlyInstalledVersionFile = ROOT."Gishiki.info";
            
            //build the version string
            $versionStr = GISHIKI_MAJOR.".".GISHIKI_MINOR.".".GISHIKI_REVISION." ".GISHIKI_STATUS;
            
            if (@file_put_contents($currentlyInstalledVersionFile, $versionStr) != FALSE)
            {
                //print success
                ?>
<div class="alert alert-success" role="alert"><b>Success!</b> Gishiki framework installation was completed successfully.<br />
You can now use Gishiki without any limitations!</div>
                <?php
            } else {
                //print an error
                ?>
<div class="alert alert-danger" role="alert"><b>Write error:</b> the 
installer was unable to complete the installation.<br />
Please, check for disk permissions and retry.</div>
                <?php
            }
        ?>
</body>
</html>
        <?php
    }
}
