<?php

/**
 * This file is part of Carpediem\JSend, a JSend Response object.
 *
 * @copyright Carpe Diem. All rights reserved
 * @license MIT See LICENSE.md at the root of the project for more info
 */

namespace Carpediem\JSend\Test;

use Carpediem\JSend\Exception;
use Carpediem\JSend\JSend;
use JsonSerializable;
use PHPUnit\Framework\TestCase;
use TypeError;
use function function_exists;
use function ob_get_clean;
use function ob_start;
use function var_export;

class JSendTest extends TestCase
{
    private $JSend;
    private $JSendSuccess;
    private $JSendSuccessJson;
    private $dataSuccess;

    public function setUp()
    {
        $this->JSendSuccess = JSend::success(['post' => ['id' => 1, 'title' => 'foo', 'author' => 'bar']]);
        $this->JSendSuccessJson  = '{"status":"success","data":{"post":{"id":1,"title":"foo","author":"bar"}}}';
        $this->dataSuccess  = ['post' => ['id' => 1, 'title' => 'foo', 'author' => 'bar']];
    }

    /**
     * @dataProvider toStringProvider
     */
    public function testToString(
        JSend $response,
        string $status,
        $data,
        $message,
        int $code = null,
        string $expected
    ) {
        self::assertSame($expected, (string) $response);
        self::assertSame($status, $response->getStatus());
        if ($data instanceof JsonSerializable) {
            $data = $data->jsonSerialize();
        }

        self::assertSame($data, $response->getData());
        if (JSend::STATUS_ERROR === $status) {
            self::assertSame((string) $message, $response->getErrorMessage());
            self::assertSame($code, $response->getErrorCode());
        }
    }

    public function toStringProvider()
    {
        $data = ['post' => ['id' => 1, 'title' => 'foo', 'author' => 'bar']];

        return [
            'success with data' => [
                'obj' => Jsend::success($data),
                'status' => JSend::STATUS_SUCCESS,
                'data' => $data,
                'message' => null,
                'code' => null,
                'expected' => '{"status":"success","data":{"post":{"id":1,"title":"foo","author":"bar"}}}',
            ],
            'success without data' => [
                'obj' => Jsend::success([]),
                'status' => JSend::STATUS_SUCCESS,
                'data' => [],
                'message' => null,
                'code' => null,
                'expected' => '{"status":"success","data":null}',
            ],
            'success with JsonSerializable object' => [
                'obj' => Jsend::success(JSend::success()),
                'status' => JSend::STATUS_SUCCESS,
                'data' => JSend::success(),
                'message' => null,
                'code' => null,
                'expected' => '{"status":"success","data":{"status":"success","data":null}}',
            ],
            'fail with data' => [
                'obj' => Jsend::fail($data),
                'status' => JSend::STATUS_FAIL,
                'data' => $data,
                'message' => 'This is an error',
                'code' => 23,
                'expected' => '{"status":"fail","data":{"post":{"id":1,"title":"foo","author":"bar"}}}',
            ],
            'fail without data' => [
                'obj' => Jsend::fail(),
                'status' => JSend::STATUS_FAIL,
                'data' => [],
                'message' => 'This is an error',
                'code' => 23,
                'expected' => '{"status":"fail","data":null}',
            ],
            'error without code' => [
                'obj' => Jsend::error('This is an error'),
                'status' => JSend::STATUS_ERROR,
                'data' => [],
                'message' => 'This is an error',
                'code' => null,
                'expected' => '{"status":"error","message":"This is an error"}',
            ],
            'error without 0 code' => [
                'obj' => Jsend::error('This is an error', 0),
                'status' => JSend::STATUS_ERROR,
                'data' => [],
                'message' => 'This is an error',
                'code' => 0,
                'expected' => '{"status":"error","message":"This is an error","code":0}',
            ],
            'error with code' => [
                'obj' => Jsend::error('This is an error', 23),
                'status' => JSend::STATUS_ERROR,
                'data' => [],
                'message' => 'This is an error',
                'code' => 23,
                'expected' => '{"status":"error","message":"This is an error","code":23}',
            ],
            'error with data' => [
                'obj' => Jsend::error('This is an error', null, $data),
                'status' => JSend::STATUS_ERROR,
                'data' => $data,
                'message' => 'This is an error',
                'code' => null,
                'expected' => '{"status":"error","data":{"post":{"id":1,"title":"foo","author":"bar"}},"message":"This is an error"}',
            ],
            'error with code and data' => [
                'obj' => Jsend::error('This is an error', 23, $data),
                'status' => JSend::STATUS_ERROR,
                'data' => $data,
                'message' => 'This is an error',
                'code' => 23,
                'expected' => '{"status":"error","data":{"post":{"id":1,"title":"foo","author":"bar"}},"message":"This is an error","code":23}',
            ],
        ];
    }

    public function testInstanceAcceptsJsonSerializableObject()
    {
        self::assertSame($this->JSendSuccess->toArray(), JSend::success($this->JSendSuccess)->getData());
    }

    public function testnewInstanceThrowsExceptionWithInvalidData()
    {
        self::expectException(TypeError::class);
        self::expectExceptionMessage('The data must be an array, a JsonSerializable object or null');
        JSend::success(3);
    }

    public function testnewInstanceThrowsExceptionWithInvalidData2()
    {
        $mock = new class() implements JsonSerializable {
            public function jsonSerialize()
            {
                return 3;
            }
        };
        self::expectException(Exception::class);
        self::expectExceptionMessage('The JsonSerializable object must return an array integer returned');
        JSend::success($mock);
    }

    public function testnewInstanceThrowsExceptionWithInvalidErrorMessage()
    {
        self::expectException(Exception::class);
        self::expectExceptionMessage('The error message must be a scalar or a object implementing the __toString method.');
        JSend::error([]);
    }

    public function testnewInstanceThrowsExceptionWithEmptyErrorMessage()
    {
        self::expectException(Exception::class);
        self::expectExceptionMessage('The error message can not be empty.');
        JSend::error('     ');
    }

    public function testSucces()
    {
        $response = JSend::success($this->dataSuccess);
        self::assertEquals($this->JSendSuccess, $response);
        self::assertSame(JSend::STATUS_SUCCESS, $response->getStatus());
        self::assertTrue($response->isSuccess());
        self::assertFalse($response->isFail());
        self::assertFalse($response->isError());
    }

    public function testFail()
    {
        $response = JSend::fail([]);
        self::assertSame('{"status":"fail","data":null}', (string) $response);
        self::assertSame(JSend::STATUS_FAIL, $response->getStatus());
        self::assertFalse($response->isSuccess());
        self::assertTrue($response->isFail());
        self::assertFalse($response->isError());
    }

    public function testError()
    {
        $response = JSend::error('This is an error', 23);
        self::assertSame(
            '{"status":"error","message":"This is an error","code":23}',
            (string) $response
        );
        self::assertSame(JSend::STATUS_ERROR, $response->getStatus());
        self::assertFalse($response->isSuccess());
        self::assertFalse($response->isFail());
        self::assertTrue($response->isError());
    }

    public function testFromJSON()
    {
        self::assertEquals($this->JSendSuccess, JSend::fromJSON($this->JSendSuccessJson));
        self::assertEquals($this->JSendSuccess, JSend::fromJSON($this->JSendSuccess));
    }

    public function testFromJSONFailedWithInvalidJsonString()
    {
        self::expectException(Exception::class);
        self::expectExceptionMessageRegExp('/Unable to decode the submitted JSON string: \w+/');
        JSend::fromJSON('fqdsfsd');
    }

    public function testFromJSONThrowsTypeError()
    {
        self::expectException(TypeError::class);
        JSend::fromJSON(tmpfile());
    }

    public function testSetState()
    {
        $a = JSend::success(['foo' => 'bar']);
        $b = eval('return '.var_export($a, true).';');
        self::assertEquals($a, $b);
    }

    public function testDebugInfo()
    {
        self::assertSame($this->JSendSuccess->__debugInfo(), $this->JSendSuccess->toArray());
    }

    public function testCreateFromArrayThrowsOutOfRangeExceptionIfStatusIsMissing()
    {
        self::expectException(Exception::class);
        self::expectExceptionMessage('The given status does not conform to Jsend specification');
        JSend::fromArray(['data' => ['post' => 1], 'code' => 404]);
    }

    public function testWithStatusReturnSameInstance()
    {
        $newObj = $this->JSendSuccess->withStatus(JSend::STATUS_SUCCESS);
        self::assertSame($newObj, $this->JSendSuccess);
    }

    public function testWithStatusReturnNewInstance()
    {
        $newObj = $this->JSendSuccess->withStatus(JSend::STATUS_FAIL);
        self::assertSame(JSend::STATUS_FAIL, $newObj->getStatus());
    }

    public function testWithErrorReturnSameInstance()
    {
        $error = JSend::error('This is an error', 42);
        $newError = $error->withError('This is an error', 42);
        self::assertSame($newError, $error);
    }

    public function testWithErrorReturnNewInstance()
    {
        $error = JSend::error('This is an error', 42);
        $newError = $error->withError('This is a bobo');
        self::assertNotEquals($newError, $error);
        self::assertNull($newError->getErrorCode());
    }

    public function testWithDataReturnSameInstance()
    {
        $newObj = $this->JSendSuccess->withData($this->JSendSuccess->getData());
        self::assertSame($newObj, $this->JSendSuccess);
    }

    public function testWithDataReturnNewInstance()
    {
        $newObj = $this->JSendSuccess->withData([]);
        self::assertSame([], $newObj->getData());
        self::assertNotEquals($newObj, $this->JSendSuccess);
    }

    public function testSendThrowsExceptionForInvalidHeaderName()
    {
        self::expectException(Exception::class);
        self::expectExceptionMessage('Invalid header name');
        $this->JSendSuccess->send(["fdsfqfsdsdqf\nfdsqfqsd" => 'bar']);
    }

    /**
     * @dataProvider outputInvaludValueProvider
     */
    public function testSendThrowsExceptionForInvalidHeaderValue($headers)
    {
        self::expectException(Exception::class);
        self::expectExceptionMessage('Invalid header value');
        $this->JSendSuccess->send($headers);
    }

    public function outputInvaludValueProvider($headers)
    {
        return [
            [['foo' => "fdsfqfsdsdqf\r\nfdsqfqsd"]],
            [['foo' => "fdsfqfsdsdqf\0fdsqfqsd"]],
        ];
    }

    /**
     * @runInSeparateProcess
     */
    public function testSend()
    {
        if (!function_exists('xdebug_get_headers')) {
            $this->markTestSkipped();
        }
        ob_start();
        $res = $this->JSendSuccess->send(['Access-Control-Allow-Origin' => '*']);
        self::assertInternalType('int', $res);
        ob_get_clean();
        $headers = \xdebug_get_headers();
        self::assertContains('Content-Type: application/json;charset=utf-8', $headers);
        self::assertContains('Access-Control-Allow-Origin: *', $headers);
    }
}
