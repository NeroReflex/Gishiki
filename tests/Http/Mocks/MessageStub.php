<?php
/**
 * Slim Framework (http://slimframework.com).
 *
 * @link      https://github.com/slimphp/Slim
 *
 * @copyright Copyright (c) 2011-2015 Josh Lockhart
 * @license   https://github.com/slimphp/Slim/blob/master/LICENSE.md (MIT License)
 */
namespace Gishiki\tests\Http\Mocks;

use Gishiki\HttpKernel\Message;

/**
 * Mock object for Gishiki\HttpKernel\MessageTest.
 */
class MessageStub extends Message
{
    /**
     * Protocol version.
     *
     * @var string
     */
    public $protocolVersion;

    /**
     * Headers.
     *
     * @var \Gishiki\HttpKernel\HeadersInterface
     */
    public $headers;

    /**
     * Body object.
     *
     * @var \Psr\Http\Message\StreamInterface
     */
    public $body;
}
