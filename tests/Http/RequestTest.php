<?php
/**
 * Slim Framework (http://slimframework.com).
 *
 * @link      https://github.com/slimphp/Slim
 *
 * @copyright Copyright (c) 2011-2015 Josh Lockhart
 * @license   https://github.com/slimphp/Slim/blob/master/LICENSE.md (MIT License)
 */
namespace Gishiki\tests\Http;

use ReflectionProperty;
use Gishiki\Algorithms\Collections\GenericCollection;
use Gishiki\Core\Environment;
use Gishiki\HttpKernel\Headers;
use Gishiki\HttpKernel\Request;
use Gishiki\HttpKernel\RequestBody;
use Gishiki\HttpKernel\UploadedFile;
use Gishiki\HttpKernel\Uri;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function requestFactory()
    {
        $env = Environment::mock();

        $uri = Uri::createFromString('https://example.com:443/foo/bar?abc=123');
        $headers = Headers::createFromEnvironment($env);
        $cookies = [
            'user' => 'john',
            'id' => '123',
        ];
        $serverParams = $env->all();
        $body = new RequestBody();
        $uploadedFiles = UploadedFile::createFromEnvironment($env);
        $request = new Request('GET', $uri, $headers, $cookies, $serverParams, $body, $uploadedFiles);

        return $request;
    }

    public function testDisableSetter()
    {
        $request = $this->requestFactory();
        $request->foo = 'bar';

        $this->assertFalse(property_exists($request, 'foo'));
    }

    public function testAddsHostHeaderFromUri()
    {
        $request = $this->requestFactory();
        $this->assertEquals('example.com', $request->getHeaderLine('Host'));
    }

    /*******************************************************************************
     * Method
     ******************************************************************************/

    public function testGetMethod()
    {
        $this->assertEquals('GET', $this->requestFactory()->getMethod());
    }

    public function testGetOriginalMethod()
    {
        $this->assertEquals('GET', $this->requestFactory()->getOriginalMethod());
    }

    public function testWithMethod()
    {
        $request = $this->requestFactory()->withMethod('PUT');

        //$this->assertAttributeEquals(null, 'method', $request);
        $this->assertAttributeEquals('PUT', 'originalMethod', $request);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWithMethodInvalid()
    {
        $this->requestFactory()->withMethod('FOO');
    }

    public function testWithMethodNull()
    {
        $request = $this->requestFactory()->withMethod(null);

        $this->assertAttributeEquals(null, 'originalMethod', $request);
    }

    /**
     * @covers Gishiki\HttpKernel\Request::createFromEnvironment
     */
    public function testCreateFromEnvironment()
    {
        $env = Environment::mock([
            'SCRIPT_NAME' => '/index.php',
            'REQUEST_URI' => '/foo',
            'REQUEST_METHOD' => 'POST',
        ]);

        $request = Request::createFromEnvironment($env);
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals($env->all(), $request->getServerParams());
    }

    /**
     * @covers Gishiki\HttpKernel\Request::createFromEnvironment
     */
    public function testCreateFromEnvironmentWithMultipart()
    {
        $_POST['foo'] = 'bar';

        $env = Environment::mock([
            'SCRIPT_NAME' => '/index.php',
            'REQUEST_URI' => '/foo',
            'REQUEST_METHOD' => 'POST',
            'HTTP_CONTENT_TYPE' => 'multipart/form-data; boundary=---foo',
        ]);

        $request = Request::createFromEnvironment($env);
        unset($_POST);

        $this->assertEquals(['foo' => 'bar'], $request->getParsedBody());
    }

    /**
     * @covers Gishiki\HttpKernel\Request::createFromEnvironment
     */
    public function testCreateFromEnvironmentWithMultipartMethodOverride()
    {
        $_POST['_METHOD'] = 'PUT';

        $env = Environment::mock([
            'SCRIPT_NAME' => '/index.php',
            'REQUEST_URI' => '/foo',
            'REQUEST_METHOD' => 'POST',
            'HTTP_CONTENT_TYPE' => 'multipart/form-data; boundary=---foo',
        ]);

        $request = Request::createFromEnvironment($env);
        unset($_POST);

        $this->assertEquals('POST', $request->getOriginalMethod());
        $this->assertEquals('PUT', $request->getMethod());
    }

    public function testGetMethodWithOverrideHeader()
    {
        $uri = Uri::createFromString('https://example.com:443/foo/bar?abc=123');
        $headers = new Headers([
            'HTTP_X_HTTP_METHOD_OVERRIDE' => 'PUT',
        ]);
        $cookies = [];
        $serverParams = [];
        $body = new RequestBody();
        $request = new Request('POST', $uri, $headers, $cookies, $serverParams, $body);

        $this->assertEquals('PUT', $request->getMethod());
        $this->assertEquals('POST', $request->getOriginalMethod());
    }

    public function testGetMethodWithOverrideParameterFromBodyObject()
    {
        $uri = Uri::createFromString('https://example.com:443/foo/bar?abc=123');
        $headers = new Headers([
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]);
        $cookies = [];
        $serverParams = [];
        $body = new RequestBody();
        $body->write('_METHOD=PUT');
        $body->rewind();
        $request = new Request('POST', $uri, $headers, $cookies, $serverParams, $body);

        $this->assertEquals('PUT', $request->getMethod());
        $this->assertEquals('POST', $request->getOriginalMethod());
    }

    public function testGetMethodOverrideParameterFromBodyArray()
    {
        $uri = Uri::createFromString('https://example.com:443/foo/bar?abc=123');
        $headers = new Headers([
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]);
        $cookies = [];
        $serverParams = [];
        $body = new RequestBody();
        $body->write('_METHOD=PUT');
        $body->rewind();
        $request = new Request('POST', $uri, $headers, $cookies, $serverParams, $body);
        $request->registerMediaTypeParser('application/x-www-form-urlencoded', function ($input) {
            parse_str($input, $body);

            return $body; // <-- Array
        });

        $this->assertEquals('PUT', $request->getMethod());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateRequestWithInvalidMethodString()
    {
        $uri = Uri::createFromString('https://example.com:443/foo/bar?abc=123');
        $headers = new Headers();
        $cookies = [];
        $serverParams = [];
        $body = new RequestBody();
        $request = new Request('FOO', $uri, $headers, $cookies, $serverParams, $body);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateRequestWithInvalidMethodOther()
    {
        $uri = Uri::createFromString('https://example.com:443/foo/bar?abc=123');
        $headers = new Headers();
        $cookies = [];
        $serverParams = [];
        $body = new RequestBody();
        $request = new Request(10, $uri, $headers, $cookies, $serverParams, $body);
    }

    public function testIsGet()
    {
        $request = $this->requestFactory();
        $prop = new ReflectionProperty($request, 'originalMethod');
        $prop->setAccessible(true);
        $prop->setValue($request, 'GET');

        $this->assertTrue($request->isGet());
    }

    public function testIsPost()
    {
        $request = $this->requestFactory();
        $prop = new ReflectionProperty($request, 'originalMethod');
        $prop->setAccessible(true);
        $prop->setValue($request, 'POST');

        $this->assertTrue($request->isPost());
    }

    public function testIsPut()
    {
        $request = $this->requestFactory();
        $prop = new ReflectionProperty($request, 'originalMethod');
        $prop->setAccessible(true);
        $prop->setValue($request, 'PUT');

        $this->assertTrue($request->isPut());
    }

    public function testIsPatch()
    {
        $request = $this->requestFactory();
        $prop = new ReflectionProperty($request, 'originalMethod');
        $prop->setAccessible(true);
        $prop->setValue($request, 'PATCH');

        $this->assertTrue($request->isPatch());
    }

    public function testIsDelete()
    {
        $request = $this->requestFactory();
        $prop = new ReflectionProperty($request, 'originalMethod');
        $prop->setAccessible(true);
        $prop->setValue($request, 'DELETE');

        $this->assertTrue($request->isDelete());
    }

    public function testIsHead()
    {
        $request = $this->requestFactory();
        $prop = new ReflectionProperty($request, 'originalMethod');
        $prop->setAccessible(true);
        $prop->setValue($request, 'HEAD');

        $this->assertTrue($request->isHead());
    }

    public function testIsOptions()
    {
        $request = $this->requestFactory();
        $prop = new ReflectionProperty($request, 'originalMethod');
        $prop->setAccessible(true);
        $prop->setValue($request, 'OPTIONS');

        $this->assertTrue($request->isOptions());
    }

    public function testIsXhr()
    {
        $uri = Uri::createFromString('https://example.com:443/foo/bar?abc=123');
        $headers = new Headers([
            'Content-Type' => 'application/x-www-form-urlencoded',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);
        $cookies = [];
        $serverParams = [];
        $body = new RequestBody();
        $request = new Request('GET', $uri, $headers, $cookies, $serverParams, $body);

        $this->assertTrue($request->isXhr());
    }

    /*******************************************************************************
     * URI
     ******************************************************************************/

    public function testGetRequestTarget()
    {
        $this->assertEquals('/foo/bar?abc=123', $this->requestFactory()->getRequestTarget());
    }

    public function testGetRequestTargetAlreadySet()
    {
        $request = $this->requestFactory();
        $prop = new ReflectionProperty($request, 'requestTarget');
        $prop->setAccessible(true);
        $prop->setValue($request, '/foo/bar?abc=123');

        $this->assertEquals('/foo/bar?abc=123', $request->getRequestTarget());
    }

    public function testGetRequestTargetIfNoUri()
    {
        $request = $this->requestFactory();
        $prop = new ReflectionProperty($request, 'uri');
        $prop->setAccessible(true);
        $prop->setValue($request, null);

        $this->assertEquals('/', $request->getRequestTarget());
    }

    public function testWithRequestTarget()
    {
        $clone = $this->requestFactory()->withRequestTarget('/test?user=1');

        $this->assertAttributeEquals('/test?user=1', 'requestTarget', $clone);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWithRequestTargetThatHasSpaces()
    {
        $this->requestFactory()->withRequestTarget('/test/m ore/stuff?user=1');
    }

    public function testGetUri()
    {
        $uri = Uri::createFromString('https://example.com:443/foo/bar?abc=123');
        $headers = new Headers();
        $cookies = [];
        $serverParams = [];
        $body = new RequestBody();
        $request = new Request('GET', $uri, $headers, $cookies, $serverParams, $body);

        $this->assertSame($uri, $request->getUri());
    }

    public function testWithUri()
    {
        // Uris
        $uri1 = Uri::createFromString('https://example.com:443/foo/bar?abc=123');
        $uri2 = Uri::createFromString('https://example2.com:443/test?xyz=123');

        // Request
        $headers = new Headers();
        $cookies = [];
        $serverParams = [];
        $body = new RequestBody();
        $request = new Request('GET', $uri1, $headers, $cookies, $serverParams, $body);
        $clone = $request->withUri($uri2);

        $this->assertAttributeSame($uri2, 'uri', $clone);
    }

    public function testGetContentType()
    {
        $headers = new Headers([
            'Content-Type' => ['application/json;charset=utf8'],
        ]);
        $request = $this->requestFactory();
        $headersProp = new ReflectionProperty($request, 'headers');
        $headersProp->setAccessible(true);
        $headersProp->setValue($request, $headers);

        $this->assertEquals('application/json;charset=utf8', $request->getContentType());
    }

    public function testGetContentTypeEmpty()
    {
        $request = $this->requestFactory();

        $this->assertNull($request->getContentType());
    }

    public function testGetMediaType()
    {
        $headers = new Headers([
            'Content-Type' => ['application/json;charset=utf8'],
        ]);
        $request = $this->requestFactory();
        $headersProp = new ReflectionProperty($request, 'headers');
        $headersProp->setAccessible(true);
        $headersProp->setValue($request, $headers);

        $this->assertEquals('application/json', $request->getMediaType());
    }

    public function testGetMediaTypeEmpty()
    {
        $request = $this->requestFactory();

        $this->assertNull($request->getMediaType());
    }

    public function testGetMediaTypeParams()
    {
        $headers = new Headers([
            'Content-Type' => ['application/json;charset=utf8;foo=bar'],
        ]);
        $request = $this->requestFactory();
        $headersProp = new ReflectionProperty($request, 'headers');
        $headersProp->setAccessible(true);
        $headersProp->setValue($request, $headers);

        $this->assertEquals(['charset' => 'utf8', 'foo' => 'bar'], $request->getMediaTypeParams());
    }

    public function testGetMediaTypeParamsEmpty()
    {
        $headers = new Headers([
            'Content-Type' => ['application/json'],
        ]);
        $request = $this->requestFactory();
        $headersProp = new ReflectionProperty($request, 'headers');
        $headersProp->setAccessible(true);
        $headersProp->setValue($request, $headers);

        $this->assertEquals([], $request->getMediaTypeParams());
    }

    public function testGetMediaTypeParamsWithoutHeader()
    {
        $request = $this->requestFactory();

        $this->assertEquals([], $request->getMediaTypeParams());
    }

    public function testGetContentCharset()
    {
        $headers = new Headers([
            'Content-Type' => ['application/json;charset=utf8'],
        ]);
        $request = $this->requestFactory();
        $headersProp = new ReflectionProperty($request, 'headers');
        $headersProp->setAccessible(true);
        $headersProp->setValue($request, $headers);

        $this->assertEquals('utf8', $request->getContentCharset());
    }

    public function testGetContentCharsetEmpty()
    {
        $headers = new Headers([
            'Content-Type' => ['application/json'],
        ]);
        $request = $this->requestFactory();
        $headersProp = new ReflectionProperty($request, 'headers');
        $headersProp->setAccessible(true);
        $headersProp->setValue($request, $headers);

        $this->assertNull($request->getContentCharset());
    }

    public function testGetContentCharsetWithoutHeader()
    {
        $request = $this->requestFactory();

        $this->assertNull($request->getContentCharset());
    }

    public function testGetContentLength()
    {
        $headers = new Headers([
            'Content-Length' => '150', // <-- Note we define as a string
        ]);
        $request = $this->requestFactory();
        $headersProp = new ReflectionProperty($request, 'headers');
        $headersProp->setAccessible(true);
        $headersProp->setValue($request, $headers);

        $this->assertEquals(150, $request->getContentLength());
    }

    public function testGetContentLengthWithoutHeader()
    {
        $request = $this->requestFactory();

        $this->assertNull($request->getContentLength());
    }

    /*******************************************************************************
     * Cookies
     ******************************************************************************/

    public function testGetCookieParams()
    {
        $shouldBe = [
            'user' => 'john',
            'id' => '123',
        ];

        $this->assertEquals($shouldBe, $this->requestFactory()->getCookieParams());
    }

    public function testWithCookieParams()
    {
        $request = $this->requestFactory();
        $clone = $request->withCookieParams(['type' => 'framework']);

        $this->assertEquals(['type' => 'framework'], $clone->getCookieParams());
    }

    /*******************************************************************************
     * Query Params
     ******************************************************************************/

    public function testGetQueryParams()
    {
        $this->assertEquals(['abc' => '123'], $this->requestFactory()->getQueryParams());
    }

    public function testGetQueryParamsAlreadySet()
    {
        $request = $this->requestFactory();
        $prop = new ReflectionProperty($request, 'queryParams');
        $prop->setAccessible(true);
        $prop->setValue($request, ['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $request->getQueryParams());
    }

    public function testWithQueryParams()
    {
        $request = $this->requestFactory();
        $clone = $request->withQueryParams(['foo' => 'bar']);
        $cloneUri = $clone->getUri();

        $this->assertEquals('abc=123', $cloneUri->getQuery()); // <-- Unchanged
        $this->assertAttributeEquals(['foo' => 'bar'], 'queryParams', $clone); // <-- Changed
    }

    public function testGetQueryParamsWithoutUri()
    {
        $request = $this->requestFactory();
        $prop = new ReflectionProperty($request, 'uri');
        $prop->setAccessible(true);
        $prop->setValue($request, null);

        $this->assertEquals([], $request->getQueryParams());
    }

    /*******************************************************************************
     * Uploaded files
     ******************************************************************************/

    /**
     * @covers Gishiki\HttpKernel\Request::withUploadedFiles
     * @covers Gishiki\HttpKernel\Request::getUploadedFiles
     */
    public function testWithUploadedFiles()
    {
        $files = [new UploadedFile('foo.txt'), new UploadedFile('bar.txt')];

        $request = $this->requestFactory();
        $clone = $request->withUploadedFiles($files);

        $this->assertEquals([], $request->getUploadedFiles());
        $this->assertEquals($files, $clone->getUploadedFiles());
    }

    /*******************************************************************************
     * Server Params
     ******************************************************************************/

    public function testGetServerParams()
    {
        $mockEnv = Environment::mock();
        $request = $this->requestFactory();

        $serverParams = $request->getServerParams();
        foreach ($serverParams as $key => $value) {
            if ($key == 'REQUEST_TIME' || $key == 'REQUEST_TIME_FLOAT') {
                $this->assertGreaterThanOrEqual(
                    $mockEnv[$key],
                    $value,
                    sprintf('%s value of %s was less than expected value of %s', $key, $value, $mockEnv[$key])
                );
            } else {
                $this->assertEquals(
                    $mockEnv[$key],
                    $value,
                    sprintf('%s value of %s did not equal expected value of %s', $key, $value, $mockEnv[$key])
                );
            }
        }
    }

    /*******************************************************************************
     * File Params
     ******************************************************************************/

    /*******************************************************************************
     * Attributes
     ******************************************************************************/

    public function testGetAttributes()
    {
        $request = $this->requestFactory();
        $attrProp = new ReflectionProperty($request, 'attributes');
        $attrProp->setAccessible(true);
        $attrProp->setValue($request, new GenericCollection(['foo' => 'bar']));

        $this->assertEquals(['foo' => 'bar'], $request->getAttributes());
    }

    public function testGetAttribute()
    {
        $request = $this->requestFactory();
        $attrProp = new ReflectionProperty($request, 'attributes');
        $attrProp->setAccessible(true);
        $attrProp->setValue($request, new GenericCollection(['foo' => 'bar']));

        $this->assertEquals('bar', $request->getAttribute('foo'));
        $this->assertNull($request->getAttribute('bar'));
        $this->assertEquals(2, $request->getAttribute('bar', 2));
    }

    public function testWithAttribute()
    {
        $request = $this->requestFactory();
        $attrProp = new ReflectionProperty($request, 'attributes');
        $attrProp->setAccessible(true);
        $attrProp->setValue($request, new GenericCollection(['foo' => 'bar']));
        $clone = $request->withAttribute('test', '123');

        $this->assertEquals('123', $clone->getAttribute('test'));
    }

    public function testWithAttributes()
    {
        $request = $this->requestFactory();
        $attrProp = new ReflectionProperty($request, 'attributes');
        $attrProp->setAccessible(true);
        $attrProp->setValue($request, new GenericCollection(['foo' => 'bar']));
        $clone = $request->withAttributes(['test' => '123']);

        $this->assertNull($clone->getAttribute('foo'));
        $this->assertEquals('123', $clone->getAttribute('test'));
    }

    public function testWithoutAttribute()
    {
        $request = $this->requestFactory();
        $attrProp = new ReflectionProperty($request, 'attributes');
        $attrProp->setAccessible(true);
        $attrProp->setValue($request, new GenericCollection(['foo' => 'bar']));
        $clone = $request->withoutAttribute('foo');

        $this->assertNull($clone->getAttribute('foo'));
    }

    /*******************************************************************************
     * Body
     ******************************************************************************/

    public function testGetParsedBodyForm()
    {
        $method = 'GET';
        $uri = new Uri('https', 'example.com', 443, '/foo/bar', 'abc=123', '', '');
        $headers = new Headers();
        $headers->set('Content-Type', 'application/x-www-form-urlencoded;charset=utf8');
        $cookies = [];
        $serverParams = [];
        $body = new RequestBody();
        $body->write('foo=bar');
        $request = new Request($method, $uri, $headers, $cookies, $serverParams, $body);
        $this->assertEquals(['foo' => 'bar'], $request->getParsedBody());
    }

    public function testGetParsedBodyJson()
    {
        $method = 'GET';
        $uri = new Uri('https', 'example.com', 443, '/foo/bar', 'abc=123', '', '');
        $headers = new Headers();
        $headers->set('Content-Type', 'application/json;charset=utf8');
        $cookies = [];
        $serverParams = [];
        $body = new RequestBody();
        $body->write('{"foo":"bar"}');
        $request = new Request($method, $uri, $headers, $cookies, $serverParams, $body);

        $this->assertEquals(['foo' => 'bar'], $request->getParsedBody());
    }

    public function testGetParsedBodyXml()
    {
        $method = 'GET';
        $uri = new Uri('https', 'example.com', 443, '/foo/bar', 'abc=123', '', '');
        $headers = new Headers();
        $headers->set('Content-Type', 'application/xml;charset=utf8');
        $cookies = [];
        $serverParams = [];
        $body = new RequestBody();
        $body->write('<person><name>Josh</name></person>');
        $request = new Request($method, $uri, $headers, $cookies, $serverParams, $body);

        $this->assertEquals('Josh', $request->getParsedBody()->name);
    }

    public function testGetParsedBodyXmlWithTextXMLMediaType()
    {
        $method = 'GET';
        $uri = new Uri('https', 'example.com', 443, '/foo/bar', 'abc=123', '', '');
        $headers = new Headers();
        $headers->set('Content-Type', 'text/xml');
        $cookies = [];
        $serverParams = [];
        $body = new RequestBody();
        $body->write('<person><name>Josh</name></person>');
        $request = new Request($method, $uri, $headers, $cookies, $serverParams, $body);

        $this->assertEquals('Josh', $request->getParsedBody()->name);
    }

    public function testGetParsedBodyWhenAlreadyParsed()
    {
        $request = $this->requestFactory();
        $prop = new ReflectionProperty($request, 'bodyParsed');
        $prop->setAccessible(true);
        $prop->setValue($request, ['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $request->getParsedBody());
    }

    public function testGetParsedBodyWhenBodyDoesNotExist()
    {
        $request = $this->requestFactory();
        $prop = new ReflectionProperty($request, 'body');
        $prop->setAccessible(true);
        $prop->setValue($request, null);

        $this->assertNull($request->getParsedBody());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetParsedBodyAsArray()
    {
        $uri = Uri::createFromString('https://example.com:443/foo/bar?abc=123');
        $headers = new Headers([
            'Content-Type' => 'application/json;charset=utf8',
        ]);
        $cookies = [];
        $serverParams = [];
        $body = new RequestBody();
        $body->write('{"foo": "bar"}');
        $body->rewind();
        $request = new Request('POST', $uri, $headers, $cookies, $serverParams, $body);
        $request->registerMediaTypeParser('application/json', function ($input) {
            return 10; // <-- Return invalid body value
        });
        $request->getParsedBody(); // <-- Triggers exception
    }

    public function testWithParsedBody()
    {
        $clone = $this->requestFactory()->withParsedBody(['xyz' => '123']);

        $this->assertAttributeEquals(['xyz' => '123'], 'bodyParsed', $clone);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWithParsedBodyInvalid()
    {
        $this->requestFactory()->withParsedBody(2);
    }

    /*******************************************************************************
     * Parameters
     ******************************************************************************/

    public function testGetParameterFromBody()
    {
        $body = new RequestBody();
        $body->write('foo=bar');
        $body->rewind();
        $request = $this->requestFactory()
                   ->withBody($body)
                   ->withHeader('Content-Type', 'application/x-www-form-urlencoded');

        $this->assertEquals('bar', $request->getParam('foo'));
    }

    public function testGetParameterFromQuery()
    {
        $request = $this->requestFactory()->withHeader('Content-Type', 'application/x-www-form-urlencoded');

        $this->assertEquals('123', $request->getParam('abc'));
    }

    public function testGetParameterFromBodyOverQuery()
    {
        $body = new RequestBody();
        $body->write('abc=xyz');
        $body->rewind();
        $request = $this->requestFactory()
                   ->withBody($body)
                   ->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $this->assertEquals('xyz', $request->getParam('abc'));
    }

    public function testGetParameterWithDefaultFromBodyOverQuery()
    {
        $body = new RequestBody();
        $body->write('abc=xyz');
        $body->rewind();
        $request = $this->requestFactory()
                   ->withBody($body)
                   ->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $this->assertEquals('xyz', $request->getParam('abc'));
        $this->assertEquals('bar', $request->getParam('foo', 'bar'));
    }

    public function testGetParameters()
    {
        $body = new RequestBody();
        $body->write('foo=bar');
        $body->rewind();
        $request = $this->requestFactory()
                   ->withBody($body)
                   ->withHeader('Content-Type', 'application/x-www-form-urlencoded');

        $this->assertEquals(['abc' => '123', 'foo' => 'bar'], $request->getParams());
    }

    public function testGetParametersWithBodyPriority()
    {
        $body = new RequestBody();
        $body->write('foo=bar&abc=xyz');
        $body->rewind();
        $request = $this->requestFactory()
                   ->withBody($body)
                   ->withHeader('Content-Type', 'application/x-www-form-urlencoded');

        $this->assertEquals(['abc' => 'xyz', 'foo' => 'bar'], $request->getParams());
    }

    public function testGetDeserializedBody()
    {
        $method = 'POST';
        $uri = new Uri('https', 'example.com', 443, '/foo/bar', 'abc=123', '', '');
        $headers = new Headers();
        $headers->set('Content-Type', 'application/x-www-form-urlencoded;charset=utf8');
        $_POST['foo'] = 'bar';
        $cookies = [];
        $serverParams = [];
        $body = new RequestBody();
        $body->write('foo=bar');
        $request = new Request($method, $uri, $headers, $cookies, $serverParams, $body);
        $this->assertEquals(new \Gishiki\Algorithms\Collections\SerializableCollection(['foo' => 'bar']), $request->getDeserializedBody());
    }

    public function testGetDeserializedBodyJson()
    {
        $method = 'GET';
        $uri = new Uri('https', 'example.com', 443, '/foo/bar', 'abc=123', '', '');
        $headers = new Headers();
        $headers->set('Content-Type', 'application/json;charset=utf8');
        $cookies = [];
        $serverParams = [];
        $body = new RequestBody();
        $body->write('{"foo":"bar"}');
        $request = new Request($method, $uri, $headers, $cookies, $serverParams, $body);

        $this->assertEquals(new \Gishiki\Algorithms\Collections\SerializableCollection(['foo' => 'bar']), $request->getDeserializedBody());
        $this->assertEquals(new \Gishiki\Algorithms\Collections\SerializableCollection(['foo' => 'bar']), $request->getDeserializedBody());
    }

    public function testGetDeserializedBodyXml()
    {
        $method = 'GET';
        $uri = new Uri('https', 'example.com', 443, '/foo/bar', 'abc=123', '', '');
        $headers = new Headers();
        $headers->set('Content-Type', 'application/xml;charset=utf8');
        $cookies = [];
        $serverParams = [];
        $body = new RequestBody();
        $xml = <<<XML
<?xml version="1.0"?>
<book>
    <id>bk101</id>
    <author>Gambardella, Matthew</author>
    <title type="string">XML Developer's Guide</title>
    <price type="float">40.5</price>
    <publish_date>2000-10-01</publish_date>
    <description>An in-depth look at creating applications with XML.</description>
</book>
XML;
        $body->write($xml);
        $request = new Request($method, $uri, $headers, $cookies, $serverParams, $body);

        //throw new \Exception(print_r($request->getDeserializedBody()->all(), true));

        $this->assertEquals([
            'id' => 'bk101',
            'author' => 'Gambardella, Matthew',
            'title' => "XML Developer's Guide",
            'price' => 40.5,
            'publish_date' => '2000-10-01',
            'description' => 'An in-depth look at creating applications with XML.',
        ], $request->getDeserializedBody()->all());
    }

    public function testGetDeserializedBodyComplexXml()
    {
        $method = 'GET';
        $uri = new Uri('https', 'example.com', 443, '/foo/bar', 'abc=123', '', '');
        $headers = new Headers();
        $headers->set('Content-Type', 'application/xml;charset=utf8');
        $cookies = [];
        $serverParams = [];
        $body = new RequestBody();
        $xml = <<<XML
<?xml version="1.0"?>
<CATALOG>
    <CD>
        <TITLE>Empire Burlesque</TITLE>
        <ARTIST>Bob Dylan</ARTIST>
        <COUNTRY>USA</COUNTRY>
        <COMPANY>Columbia</COMPANY>
        <PRICE type="float">10.90</PRICE>
        <YEAR type="integer">1985</YEAR>
    </CD>
    <CD>
        <TITLE>Hide your heart</TITLE>
        <ARTIST>Bonnie Tyler</ARTIST>
        <COUNTRY>UK</COUNTRY>
        <COMPANY>CBS Records</COMPANY>
        <PRICE type="float">9.90</PRICE>
        <YEAR type="integer">1988</YEAR>
    </CD>
</CATALOG>
XML;
        $body->write($xml);
        $request = new Request($method, $uri, $headers, $cookies, $serverParams, $body);

        $this->assertEquals([
            'CD' => [
                0 => [
                    'TITLE' => 'Empire Burlesque',
                    'ARTIST' => 'Bob Dylan',
                    'COUNTRY' => 'USA',
                    'COMPANY' => 'Columbia',
                    'PRICE' => 10.90,
                    'YEAR' => 1985,
                ],
                1 => [
                    'TITLE' => 'Hide your heart',
                    'ARTIST' => 'Bonnie Tyler',
                    'COUNTRY' => 'UK',
                    'COMPANY' => 'CBS Records',
                    'PRICE' => 9.90,
                    'YEAR' => 1988,
                ],
            ],
        ], $request->getDeserializedBody()->all());
    }
}
