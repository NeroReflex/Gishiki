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
use Gishiki\HttpKernel\Body;
use Gishiki\HttpKernel\Headers;
use Gishiki\HttpKernel\Response;
use Gishiki\Algorithms\Collections\SerializableCollection;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    /*******************************************************************************
     * Create
     ******************************************************************************/

    public function testConstructoWithDefaultArgs()
    {
        $response = new Response();

        $this->assertAttributeEquals(200, 'status', $response);
        $this->assertAttributeInstanceOf('\Gishiki\HttpKernel\Headers', 'headers', $response);
        $this->assertAttributeInstanceOf('\Psr\Http\Message\StreamInterface', 'body', $response);
    }

    public function testConstructorWithCustomArgs()
    {
        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(404, $headers, $body);

        $this->assertAttributeEquals(404, 'status', $response);
        $this->assertAttributeSame($headers, 'headers', $response);
        $this->assertAttributeSame($body, 'body', $response);
    }

    public function testDeepCopyClone()
    {
        $headers = new Headers();
        $body = new Body(fopen('php://temp', 'r+'));
        $response = new Response(404, $headers, $body);
        $clone = clone $response;

        $this->assertAttributeEquals('1.1', 'protocolVersion', $clone);
        $this->assertAttributeEquals(404, 'status', $clone);
        $this->assertAttributeNotSame($headers, 'headers', $clone);
        $this->assertAttributeNotSame($body, 'body', $clone);
    }

    public function testDisableSetter()
    {
        $response = new Response();
        $response->foo = 'bar';

        $this->assertFalse(property_exists($response, 'foo'));
    }

    /*******************************************************************************
     * Status
     ******************************************************************************/

    public function testGetStatusCode()
    {
        $response = new Response();
        $responseStatus = new ReflectionProperty($response, 'status');
        $responseStatus->setAccessible(true);
        $responseStatus->setValue($response, '404');

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testWithStatus()
    {
        $response = new Response();
        $clone = $response->withStatus(302);

        $this->assertAttributeEquals(302, 'status', $clone);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWithStatusInvalidStatusCodeThrowsException()
    {
        $response = new Response();
        $response->withStatus(800);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage ReasonPhrase must be a string
     */
    public function testWithStatusInvalidReasonPhraseThrowsException()
    {
        $response = new Response();
        $response->withStatus(200, null);
    }

    public function testWithStatusEmptyReasonPhrase()
    {
        $response = new Response();
        $clone = $response->withStatus(207);
        $responsePhrase = new ReflectionProperty($response, 'reasonPhrase');
        $responsePhrase->setAccessible(true);

        $this->assertEquals('Multi-Status', $responsePhrase->getValue($clone));
    }

    public function testGetReasonPhrase()
    {
        $response = new Response();
        $responseStatus = new ReflectionProperty($response, 'status');
        $responseStatus->setAccessible(true);
        $responseStatus->setValue($response, '404');

        $this->assertEquals('Not Found', $response->getReasonPhrase());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage ReasonPhrase must be supplied for this code
     */
    public function testMustSetReasonPhraseForUnrecognisedCode()
    {
        $response = new Response();
        $response = $response->withStatus(499);
    }

    public function testSetReasonPhraseForUnrecognisedCode()
    {
        $response = new Response();
        $response = $response->withStatus(499, 'Authentication timeout');

        $this->assertEquals('Authentication timeout', $response->getReasonPhrase());
    }

    public function testGetCustomReasonPhrase()
    {
        $response = new Response();
        $clone = $response->withStatus(200, 'Custom Phrase');

        $this->assertEquals('Custom Phrase', $clone->getReasonPhrase());
    }

    /**
     * @covers Gishiki\HttpKernel\Response::withRedirect
     */
    public function testWithRedirect()
    {
        $response = new Response(200);
        $clone = $response->withRedirect('/foo', 301);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertFalse($response->hasHeader('Location'));

        $this->assertSame(301, $clone->getStatusCode());
        $this->assertTrue($clone->hasHeader('Location'));
        $this->assertEquals('/foo', $clone->getHeaderLine('Location'));
    }

    /*******************************************************************************
     * Behaviors
     ******************************************************************************/

    public function testIsEmpty()
    {
        $response = new Response();
        $prop = new ReflectionProperty($response, 'status');
        $prop->setAccessible(true);
        $prop->setValue($response, 204);

        $this->assertTrue($response->isEmpty());
    }

    public function testIsInformational()
    {
        $response = new Response();
        $prop = new ReflectionProperty($response, 'status');
        $prop->setAccessible(true);
        $prop->setValue($response, 100);

        $this->assertTrue($response->isInformational());
    }

    public function testIsOk()
    {
        $response = new Response();
        $prop = new ReflectionProperty($response, 'status');
        $prop->setAccessible(true);
        $prop->setValue($response, 200);

        $this->assertTrue($response->isOk());
    }

    public function testIsSuccessful()
    {
        $response = new Response();
        $prop = new ReflectionProperty($response, 'status');
        $prop->setAccessible(true);
        $prop->setValue($response, 201);

        $this->assertTrue($response->isSuccessful());
    }

    public function testIsRedirect()
    {
        $response = new Response();
        $prop = new ReflectionProperty($response, 'status');
        $prop->setAccessible(true);
        $prop->setValue($response, 302);

        $this->assertTrue($response->isRedirect());
    }

    public function testIsRedirection()
    {
        $response = new Response();
        $prop = new ReflectionProperty($response, 'status');
        $prop->setAccessible(true);
        $prop->setValue($response, 308);

        $this->assertTrue($response->isRedirection());
    }

    public function testIsForbidden()
    {
        $response = new Response();
        $prop = new ReflectionProperty($response, 'status');
        $prop->setAccessible(true);
        $prop->setValue($response, 403);

        $this->assertTrue($response->isForbidden());
    }

    public function testIsNotFound()
    {
        $response = new Response();
        $prop = new ReflectionProperty($response, 'status');
        $prop->setAccessible(true);
        $prop->setValue($response, 404);

        $this->assertTrue($response->isNotFound());
    }

    public function testIsClientError()
    {
        $response = new Response();
        $prop = new ReflectionProperty($response, 'status');
        $prop->setAccessible(true);
        $prop->setValue($response, 400);

        $this->assertTrue($response->isClientError());
    }

    public function testIsServerError()
    {
        $response = new Response();
        $prop = new ReflectionProperty($response, 'status');
        $prop->setAccessible(true);
        $prop->setValue($response, 503);

        $this->assertTrue($response->isServerError());
    }

    public function testToString()
    {
        $output = 'HTTP/1.1 404 Not Found'.PHP_EOL.
                  'X-Foo: Bar'.PHP_EOL.PHP_EOL.
                  'Where am I?';
        $this->expectOutputString($output);
        $response = new Response();
        $response = $response->withStatus(404)->withHeader('X-Foo', 'Bar')->write('Where am I?');

        echo $response;
    }

    public function testWithJson()
    {
        $data = ['foo' => 'bar1&bar2'];

        $response = new Response();
        $response = $response->withJson($data, 201);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('application/json;charset=utf-8', $response->getHeaderLine('Content-Type'));

        $body = $response->getBody();
        $body->rewind();
        $dataJson = $body->getContents(); //json_decode($body->getContents(), true);

        $this->assertEquals('{"foo":"bar1&bar2"}', $dataJson);
        $this->assertEquals($data['foo'], json_decode($dataJson, true)['foo']);

        // Test encoding option
        $response = $response->withJson($data, 200, JSON_HEX_AMP);

        $body = $response->getBody();
        $body->rewind();
        $dataJson = $body->getContents();

        $this->assertEquals('{"foo":"bar1\u0026bar2"}', $dataJson);
        $this->assertEquals($data['foo'], json_decode($dataJson, true)['foo']);
    }

    public function testSendResponse()
    {
        //setup a new response
        $response = new Response();

        //generate some binary-safe data
        $message = base64_encode(openssl_random_pseudo_bytes(1024));

        //write data to the stream
        $response->write($message);

        //remove the content length
        $response = $response->withoutHeader('Content-Length');

        //test the output
        $this->assertEquals(strlen($message), $response->send(24, true));
    }

    public function testSendFixedLengthResponse()
    {
        //setup a new response
        $response = new Response();

        //generate some binary-safe data
        $message = base64_encode(openssl_random_pseudo_bytes(1024));

        //write data to the stream
        $response->write($message);

        //re-test data stream-write
        $response = $response->withHeader('Content-Length', ''.strlen($message));

        //test the output (fixed length)
        $this->assertEquals(strlen($message), $response->send(31, true));
    }

    public function testSerializationResponse()
    {
        //setup a new response
        $response = new Response();

        //write data to the stream
        $response = $response->withHeader('Content-Type', 'application/xml');
        $response->setSerializedBody(new SerializableCollection([
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
        ]));

        //test the output deserialization result
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
        ], SerializableCollection::deserialize((string) $response->getBody(), SerializableCollection::XML)->all());
    }

    public function requestFactory()
    {
        $env = \Gishiki\Core\Environment::mock();

        $uri = \Gishiki\HttpKernel\Uri::createFromString('https://example.com:443/foo/bar?abc=123');
        $headers = Headers::createFromEnvironment($env);
        $cookies = [
            'user' => 'john',
            'id' => '123',
        ];
        $serverParams = $env->all();
        $body = new \Gishiki\HttpKernel\RequestBody();
        $uploadedFiles = \Gishiki\HttpKernel\UploadedFile::createFromEnvironment($env);
        $request = new \Gishiki\HttpKernel\Request('GET', $uri, $headers, $cookies, $serverParams, $body, $uploadedFiles);

        return $request;
    }

    public function testCompleteYamlSerialization()
    {
        //generate a stupid request for testing purpouses
        $request = $this->requestFactory();

        //expecting a yaml output....
        $response = Response::deriveFromRequest($request->withAddedHeader('Accept', 'application/x-yaml'));
        $testArray = [
            'a' => [0, 1, 4, 6],
            'b' => 'this is a test',
            'c' => 1,
            'd' => 20.5,
            'e' => [
                'f' => 'nestedtest',
                'g' => 9,
                'h' => true,
                'i' => null,
            ],
        ];
        $response->setSerializedBody(new SerializableCollection($testArray));

        //check for the content type
        $this->assertEquals('application/x-yaml', explode(';', $response->getHeader('Content-Type')[0])[0]);

        //check the serialization result
        $this->assertEquals($testArray, \Symfony\Component\Yaml\Yaml::parse((string) $response->getBody()));
    }
}
