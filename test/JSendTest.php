<?php

namespace Carpediem\JSend\Test;

use Carpediem\JSend\JSend;
use PHPUnit_Framework_TestCase as TestCase;

class JSendTest extends TestCase
{
    protected $JSend;
    protected $JSendSuccess;
    protected $dataSuccess;

    public function setUp()
    {
        $this->JSendSuccess = new JSend(JSend::STATUS_SUCCESS, ['post' => ['id' => 1, 'title' => 'foo', 'author' => 'bar']]);
        $this->JSendSuccessJson  = '{"status":"success","data":{"post":{"id":1,"title":"foo","author":"bar"}}}';
        $this->dataSuccess  = ['post' => ['id' => 1, 'title' => 'foo', 'author' => 'bar']];
    }

    /**
     * @dataProvider toStringProvider
     */
    public function testToString($status, $data, $message, $code, $expected)
    {
        $JSend = new JSend($status, $data, $message, $code);
        $this->assertSame($expected, (string) $JSend);
        $this->assertSame($status, $JSend->getStatus());
        if ($data instanceof \JsonSerializable) {
            $this->assertSame($data->jsonSerialize(), $JSend->getData());
        } else {
            $this->assertSame($data, $JSend->getData());
        }
        if (JSend::STATUS_ERROR === $status) {
            $this->assertSame((string) $message, $JSend->getErrorMessage());
            $this->assertSame($code, $JSend->getErrorCode());
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

    /**
     * @expectedException \OutOfRangeException
     * @expectedExceptionMessage The given status does not conform to Jsend specification
     */
    public function testnewInstanceThrowsOutOfRangeExceptionWithUnknownStatus()
    {
        new JSend('coucou', []);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The data must be an array, a JsonSerializable object or null
     */
    public function testnewInstanceThrowsInvalidArgumentExceptionWithInvalidData()
    {
        new JSend('success', 3);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The error message must be a non empty string
     */
    public function testnewInstanceThrowsInvalidArgumentExceptionWithInvalidErrorMessage()
    {
        new JSend(JSend::STATUS_ERROR, []);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The error message must be a non empty string
     */
    public function testnewInstanceThrowsInvalidArgumentExceptionWithEmptyErrorMessage()
    {
        new JSend(JSend::STATUS_ERROR, [], '');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The error code must be a integer or null
     */
    public function testnewInstanceThrowsInvalidArgumentExceptionWithInvalidErrorCode()
    {
        new JSend(JSend::STATUS_ERROR, [], 'error message', 'error code');
    }

    public function testSucces()
    {
        $response = JSend::success($this->dataSuccess);
        $this->assertEquals($this->JSendSuccess, $response);
        $this->assertSame(JSend::STATUS_SUCCESS, $response->getStatus());
        $this->assertTrue($response->isSuccess());
        $this->assertFalse($response->isFail());
        $this->assertFalse($response->isError());
    }

    public function testFail()
    {
        $response = JSend::fail([]);
        $this->assertSame('{"status":"fail","data":null}', (string) $response);
        $this->assertSame(JSend::STATUS_FAIL, $response->getStatus());
        $this->assertFalse($response->isSuccess());
        $this->assertTrue($response->isFail());
        $this->assertFalse($response->isError());
    }

    public function testError()
    {
        $response = JSend::error('This is an error', 23);
        $this->assertSame(
            '{"status":"error","message":"This is an error","code":23}',
            (string) $response
        );
        $this->assertSame(JSend::STATUS_ERROR, $response->getStatus());
        $this->assertFalse($response->isSuccess());
        $this->assertFalse($response->isFail());
        $this->assertTrue($response->isError());
    }

    public function testCreateFromString()
    {
        $this->assertEquals($this->JSendSuccess, JSend::createFromString($this->JSendSuccessJson));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /Unable to decode the submitted JSON string: \w+/
     */
    public function testCreateFromStringFailedWithInvalidJsonString()
    {
        JSend::createFromString('fqdsfsd');
    }

    public function testSetState()
    {
        $a = JSend::success(['foo' => 'bar']);
        eval('$b = '.var_export($a, true).';');
        $this->assertEquals($a, $b);
    }

    public function testDebugInfo()
    {
        $this->assertSame($this->JSendSuccess->__debugInfo(), $this->JSendSuccess->toArray());
    }

    /**
     * @expectedException \OutOfRangeException
     * @expectedExceptionMessage The given status does not conform to Jsend specification
     */
    public function testCreateFromArrayThrowsOutOfRangeExceptionIfStatusIsMissing()
    {
        JSend::createFromArray(['data' => ['post' => 1], 'code' => 404]);
    }

    public function testWithStatusReturnSameInstance()
    {
        $newObj = $this->JSendSuccess->withStatus(JSend::STATUS_SUCCESS);
        $this->assertSame($newObj, $this->JSendSuccess);
    }

    public function testWithStatusReturnNewInstance()
    {
        $newObj = $this->JSendSuccess->withStatus(JSend::STATUS_FAIL);
        $this->assertSame(JSend::STATUS_FAIL, $newObj->getStatus());
    }

    public function testWithErrorReturnSameInstance()
    {
        $error = JSend::error('This is an error', 42);
        $newError = $error->withError('This is an error', 42);
        $this->assertSame($newError, $error);
    }

    public function testWithErrorReturnNewInstance()
    {
        $error = JSend::error('This is an error', 42);
        $newError = $error->withError('This is a bobo');
        $this->assertNotEquals($newError, $error);
        $this->assertSame(null, $newError->getErrorCode());
    }

    public function testWithDataReturnSameInstance()
    {
        $newObj = $this->JSendSuccess->withData($this->JSendSuccess->getData());
        $this->assertSame($newObj, $this->JSendSuccess);
    }

    public function testWithDataReturnNewInstance()
    {
        $newObj = $this->JSendSuccess->withData([]);
        $this->assertSame([], $newObj->getData());
        $this->assertNotEquals($newObj, $this->JSendSuccess);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid header name
     */
    public function testSendThrowsInvalidArgumentExceptionForInvalidHeaderName()
    {
        $this->JSendSuccess->send(["fdsfqfsdsdqf\nfdsqfqsd" => 'bar']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid header value
     * @dataProvider outputInvaludValueProvider
     */
    public function testSendThrowsInvalidArgumentExceptionForInvalidHeaderValue($headers)
    {
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
        $this->JSendSuccess->send(['Access-Control-Allow-Origin' => '*']);
        ob_get_clean();
        $headers = \xdebug_get_headers();
        $this->assertContains('Content-Type: application/json;charset=utf-8', $headers);
        $this->assertContains('Access-Control-Allow-Origin: *', $headers);
    }
}
