<?php

namespace Carpediem\JSend\Test;

use Carpediem\JSend\Exception;
use Carpediem\JSend\JSend;
use JsonSerializable;
use PHPUnit\Framework\TestCase;
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
    public function testToString($status, $data, $message, $code, $expected)
    {
        $JSend = new JSend($status, $data, $message, $code);
        self::assertSame($expected, (string) $JSend);
        self::assertSame($status, $JSend->getStatus());
        if ($data instanceof JsonSerializable) {
            self::assertSame($data->jsonSerialize(), $JSend->getData());
        } else {
            self::assertSame($data, $JSend->getData());
        }
        if (JSend::STATUS_ERROR === $status) {
            self::assertSame((string) $message, $JSend->getErrorMessage());
            self::assertSame($code, $JSend->getErrorCode());
        }
    }

    public function toStringProvider()
    {
        return [
            'success with data' => [
                'status' => JSend::STATUS_SUCCESS,
                'data' => ['post' => ['id' => 1, 'title' => 'foo', 'author' => 'bar']],
                'message' => null,
                'code' => null,
                'expected' => '{"status":"success","data":{"post":{"id":1,"title":"foo","author":"bar"}}}',
            ],
            'success without data' => [
                'status' => JSend::STATUS_SUCCESS,
                'data' => [],
                'message' => null,
                'code' => null,
                'expected' => '{"status":"success","data":null}',
            ],
            'success with JsonSerializable object' => [
                'status' => JSend::STATUS_SUCCESS,
                'data' => JSend::success(),
                'message' => null,
                'code' => null,
                'expected' => '{"status":"success","data":{"status":"success","data":null}}',
            ],
            'fail with data' => [
                'status' => JSend::STATUS_FAIL,
                'data' => ['post' => ['id' => 1, 'title' => 'foo', 'author' => 'bar']],
                'message' => 'This is an error',
                'code' => 23,
                'expected' => '{"status":"fail","data":{"post":{"id":1,"title":"foo","author":"bar"}}}',
            ],
            'fail without data' => [
                'status' => JSend::STATUS_FAIL,
                'data' => [],
                'message' => 'This is an error',
                'code' => 23,
                'expected' => '{"status":"fail","data":null}',
            ],
            'error without code' => [
                'status' => JSend::STATUS_ERROR,
                'data' => [],
                'message' => 'This is an error',
                'code' => null,
                'expected' => '{"status":"error","message":"This is an error"}',
            ],
            'error with code' => [
                'status' => JSend::STATUS_ERROR,
                'data' => [],
                'message' => 'This is an error',
                'code' => 23,
                'expected' => '{"status":"error","message":"This is an error","code":23}',
            ],
            'error with data' => [
                'status' => JSend::STATUS_ERROR,
                'data' => ['post' => ['id' => 1, 'title' => 'foo', 'author' => 'bar']],
                'message' => 'This is an error',
                'code' => null,
                'expected' => '{"status":"error","data":{"post":{"id":1,"title":"foo","author":"bar"}},"message":"This is an error"}',
            ],
            'error with code and data' => [
                'status' => JSend::STATUS_ERROR,
                'data' => ['post' => ['id' => 1, 'title' => 'foo', 'author' => 'bar']],
                'message' => 'This is an error',
                'code' => 23,
                'expected' => '{"status":"error","data":{"post":{"id":1,"title":"foo","author":"bar"}},"message":"This is an error","code":23}',
            ],
        ];
    }

    public function testnewInstanceThrowsOutOfRangeExceptionWithUnknownStatus()
    {
        self::expectException(Exception::class);
        self::expectExceptionMessage('The given status does not conform to Jsend specification');
        new JSend('coucou', []);
    }

    public function testnewInstanceThrowsInvalidArgumentExceptionWithInvalidData()
    {
        self::expectException(Exception::class);
        self::expectExceptionMessage('The data must be an array, a JsonSerializable object or null');
        new JSend('success', 3);
    }

    public function testnewInstanceThrowsInvalidArgumentExceptionWithInvalidErrorMessage()
    {
        self::expectException(Exception::class);
        self::expectExceptionMessage('The error message must be a scalar or a object implementing the __toString method.');
        new JSend(JSend::STATUS_ERROR, []);
    }

    public function testnewInstanceThrowsInvalidArgumentExceptionWithEmptyErrorMessage()
    {
        self::expectException(Exception::class);
        self::expectExceptionMessage('The error message can not be empty.');
        new JSend(JSend::STATUS_ERROR, [], '');
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

    public function testCreateFromString()
    {
        self::assertEquals($this->JSendSuccess, JSend::createFromString($this->JSendSuccessJson));
    }

    public function testCreateFromStringFailedWithInvalidJsonString()
    {
        self::expectException(Exception::class);
        self::expectExceptionMessageRegExp('/Unable to decode the submitted JSON string: \w+/');
        JSend::createFromString('fqdsfsd');
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
        JSend::createFromArray(['data' => ['post' => 1], 'code' => 404]);
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

    public function testSendThrowsInvalidArgumentExceptionForInvalidHeaderName()
    {
        self::expectException(Exception::class);
        self::expectExceptionMessage('Invalid header name');
        $this->JSendSuccess->send(["fdsfqfsdsdqf\nfdsqfqsd" => 'bar']);
    }

    /**
     * @dataProvider outputInvaludValueProvider
     */
    public function testSendThrowsInvalidArgumentExceptionForInvalidHeaderValue($headers)
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
