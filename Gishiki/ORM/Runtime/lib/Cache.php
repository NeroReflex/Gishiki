<?php
namespace ActiveRecord;
use Closure;

/**
 * This is a wrapper that now connects ActiveRecord's memcache to the 
 * more advanced Gishiki caching
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class Cache
{
	public static function flush()
	{
            \Gishiki\Caching\Cache::Flush();
	}

	public static function get($key, $closure)
	{
            if (!\Gishiki\Caching\Cache::Connected())
            {   return $closure();  }
            else {
                if (!\Gishiki\Caching\Cache::Exists($key))
                {   
                    $value = $closure();
                    \Gishiki\Caching\Cache::Store($key, $value); 
                    return $value;
                }
                else {
                    return \Gishiki\Caching\Cache::Fetch($key);
                }
            }
	}
}
