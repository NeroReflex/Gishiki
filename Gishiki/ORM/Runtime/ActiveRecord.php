<?php
define('PHP_ACTIVERECORD_VERSION_ID','1.0');

if (!defined('PHP_ACTIVERECORD_AUTOLOAD_PREPEND'))
	define('PHP_ACTIVERECORD_AUTOLOAD_PREPEND',true);

include(ROOT."Gishiki".DS."ORM".DS."Runtime".DS."lib".DS."Singleton.php");
include(ROOT."Gishiki".DS."ORM".DS."Runtime".DS."lib".DS."Config.php");
include(ROOT."Gishiki".DS."ORM".DS."Runtime".DS."lib".DS."Utils.php");
include(ROOT."Gishiki".DS."ORM".DS."Runtime".DS."lib".DS."DateTime.php");
include(ROOT."Gishiki".DS."ORM".DS."Runtime".DS."lib".DS."Model.php");
include(ROOT."Gishiki".DS."ORM".DS."Runtime".DS."lib".DS."Table.php");
include(ROOT."Gishiki".DS."ORM".DS."Runtime".DS."lib".DS."ConnectionManager.php");
include(ROOT."Gishiki".DS."ORM".DS."Runtime".DS."lib".DS."Connection.php");
include(ROOT."Gishiki".DS."ORM".DS."Runtime".DS."lib".DS."SQLBuilder.php");
include(ROOT."Gishiki".DS."ORM".DS."Runtime".DS."lib".DS."Reflections.php");
include(ROOT."Gishiki".DS."ORM".DS."Runtime".DS."lib".DS."Inflector.php");
include(ROOT."Gishiki".DS."ORM".DS."Runtime".DS."lib".DS."CallBack.php");
include(ROOT."Gishiki".DS."ORM".DS."Runtime".DS."lib".DS."Exceptions.php");
include(ROOT."Gishiki".DS."ORM".DS."Runtime".DS."lib".DS."Cache.php");

if (!defined('PHP_ACTIVERECORD_AUTOLOAD_DISABLE'))
	spl_autoload_register('activerecord_autoload',false,PHP_ACTIVERECORD_AUTOLOAD_PREPEND);

function activerecord_autoload($class_name)
{
	$path = ActiveRecord\Config::instance()->get_model_directory();
	$root = realpath(isset($path) ? $path : '.');

	if (($namespaces = ActiveRecord\get_namespaces($class_name)))
	{
		$class_name = array_pop($namespaces);
		$directories = array();

		foreach ($namespaces as $directory)
			$directories[] = $directory;

		$root .= DIRECTORY_SEPARATOR . implode($directories, DIRECTORY_SEPARATOR);
	}

	$file = "$root/$class_name.php";

	if (file_exists($file))
		require $file;
}
