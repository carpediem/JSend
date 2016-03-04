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
        $this->assertSame($data, $JSend->getData());
        if (JSend::STATUS_FAIL != $status) {
            $this->assertSame((string) $message, $JSend->getErrorMessage());
            $this->assertSame($code, $JSend->getErrorCode());
        }
    }

    public function toStringProvider()
    {
        return [
            'success' => [
                'status' => JSend::STATUS_SUCCESS,
                'data' => ['post' => ['id' => 1, 'title' => 'foo', 'author' => 'bar']],
                'message' => null,
                'code' => null,
                'expected' => '{"status":"success","data":{"post":{"id":1,"title":"foo","author":"bar"}}}',
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
            'failed' => [
                'status' => JSend::STATUS_FAIL,
                'data' => [],
                'message' => 'This is an error',
                'code' => 23,
                'expected' => '{"status":"fail","data":null}',
            ],
        ];
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testnewInstanceThrowsUnexpectedValueException()
    {
        new JSend('coucou', []);
    }


    /**
     * @expectedException \UnexpectedValueException
     */
    public function testnewInstanceThrowsInvalidArgumentException()
    {
        new JSend(JSend::STATUS_ERROR, []);
    }

    public function testSucces()
    {
        $response = JSend::success($this->dataSuccess);
        $this->assertEquals($this->JSendSuccess, $response);
        $this->assertTrue($response->isSuccess());
        $this->assertFalse($response->isFail());
        $this->assertFalse($response->isError());
    }

    public function testFail()
    {
        $response = JSend::fail([]);
        $this->assertSame('{"status":"fail","data":null}', (string) $response);
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
     */
    public function testCreateFromStringFailedWithInvalidJsonString()
    {
        JSend::createFromString('fqdsfsd');
    }

    /**
     * @expectedException \UnexpectedValueException
     * @dataProvider createFromArrayProvider
     */
    public function testCreateFromArrayThrowsUnexpectedValueException($data)
    {
        JSend::createFromArray($data);
    }

    public function createFromArrayProvider()
    {
        return [
            'Missing required status index' => [['data' => ['post' => 1], 'code' => 404]],
        ];
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
     */
    public function testOutputThrowsInvalidArgumentExceptionForInvalidHeaderName()
    {
        $this->JSendSuccess->send(["fdsfqfsdsdqf\nfdsqfqsd" => 'bar']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider outputInvaludValueProvider
     */
    public function testOutputThrowsInvalidArgumentExceptionForInvalidHeaderValue($headers)
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
    public function testOuput()
    {
        if (!function_exists('xdebug_get_headers')) {
            $this->markTestSkipped();
        }
        ob_start();
        $this->JSendSuccess->send(['Access-Control-Allow-Origin' => '*']);
        ob_get_clean();
        $headers = \xdebug_get_headers();
        $this->assertSame($headers[0], 'Access-Control-Allow-Origin: *');
        $this->assertSame($headers[1], 'Content-Type: application/json;charset=utf-8');
    }
}
