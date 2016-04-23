<?php
/**
 * Slim Framework (http://slimframework.com)
 *
 * @link      https://github.com/slimphp/Slim
 * @copyright Copyright (c) 2011-2015 Josh Lockhart
 * @license   https://github.com/slimphp/Slim/blob/3.x/LICENSE.md (MIT License)
 */
namespace Gishiki\tests\Http;

use ReflectionProperty;
use Gishiki\HttpKernel\Cookies;

class CookiesTest extends \PHPUnit_Framework_TestCase
{
    
    public function testSetDefaults()
    {
        $defaults = [
            'value' => 'toast',
            'domain' => null,
            'path' => null,
            'expires' => null,
            'secure' => true,
            'httponly' => true
        ];

        $cookies = new Cookies;

        $prop = new ReflectionProperty($cookies, 'defaults');
        $prop->setAccessible(true);

        $origDefaults = $prop->getValue($cookies);

        $cookies->setDefaults($defaults);

        $this->assertEquals($defaults, $prop->getValue($cookies));
        $this->assertNotEquals($origDefaults, $prop->getValue($cookies));
    }
}
