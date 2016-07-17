<?php
/**
 * Slim Framework (http://slimframework.com).
 *
 * @link      https://github.com/slimphp/Slim
 *
 * @copyright Copyright (c) 2011-2015 Josh Lockhart
 * @license   https://github.com/slimphp/Slim/blob/3.x/LICENSE.md (MIT License)
 */
namespace Gishiki\HttpKernel;

use Gishiki\Algorithms\Collections\CollectionInterface;

/**
 * Headers Interface.
 *
 * @since   3.0.0
 */
interface HeadersInterface extends CollectionInterface
{
    public function add($key, $value);

    public function normalizeKey($key);
}
