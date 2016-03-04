<?php

namespace Carpediem\JSend;

use InvalidArgumentException;
use JsonSerializable;
use UnexpectedValueException;

/**
 * A Immutable Value Object Class to represent a JSend object
 */
class JSend implements JsonSerializable
{
    const STATUS_SUCCESS = 'success';

    const STATUS_ERROR = 'error';

    const STATUS_FAIL = 'fail';

    /**
     * JSend status
     *
     * @var string
     */
    protected $status;

    /**
     * JSend Data
     *
     * @var array
     */
    protected $data = [];

    /**
     * JSend Error Message
     *
     * @var string
     */
    protected $errorMessage = '';

    /**
     * JSend Error Code
     * @var int|null
     */
    protected $errorCode;

    /**
     * New Instance
     *
     * @param string $status
     * @param array  $data
     * @param string $errorMessage
     * @param int    $errorCode
     */
    public function __construct($status, array $data = null, $errorMessage = null, $errorCode = null)
    {
        $this->status = $this->filterStatus($status);
        $this->data = $data ?: [];
        $this->filterError($errorMessage, $errorCode);
    }

    /**
     * Filter and Validate the JSend Status
     *
     * @param string $status
     *
     * @throws UnexpectedValueException If the status value does not conform to JSend Spec.
     *
     * @return string
     */
    protected function filterStatus($status)
    {
        $res = [self::STATUS_SUCCESS => 1, self::STATUS_ERROR => 1, self::STATUS_FAIL => 1];
        if (isset($res[$status])) {
            return $status;
        }

        throw new UnexpectedValueException('The given status does not conform to Jsend specification');
    }

    /**
     * Filter and Validate the JSend Error properties
     *
     * @param string $errorMessage
     * @param int    $errorCode
     */
    protected function filterError($errorMessage, $errorCode)
    {
        if (self::STATUS_ERROR != $this->status) {
            return;
        }

        $this->errorMessage = $this->validateType('string', 'is_string', $errorMessage);
        if (!is_null($errorCode)) {
            $this->errorCode = $this->validateType('numeric', 'is_numeric', $errorCode);
        }
    }

    /**
     * Validate a Type
     *
     * @param string   $type
     * @param callable $func
     * @param mixed    $str
     *
     * @throws UnexpectedValueException If the data value does not conform to the specified type
     *
     * @return mixed
     */
    protected function validateType($type, callable $func, $str)
    {
        if ($func($str)) {
            return $str;
        }

        throw new UnexpectedValueException(sprintf(
            'Expected data to be a %s; received "%s"',
            $type,
            (is_object($str) ? get_class($str) : gettype($str))
        ));
    }

    /**
     * Returns the JSend status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Returns the JSend data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Returns the JSend error message
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Returns the JSend error code
     *
     * @return int|null
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * Returns true if the status is success
     *
     * @return bool
     */
    public function isSuccess()
    {
        return self::STATUS_SUCCESS === $this->status;
    }

    /**
     * Returns true if the status is fail
     *
     * @return bool
     */
    public function isFail()
    {
        return self::STATUS_FAIL === $this->status;
    }

    /**
     * Returns true if the status is error
     *
     * @return bool
     */
    public function isError()
    {
        return self::STATUS_ERROR === $this->status;
    }

    /**
     * Transcode the JSend object into an array
     *
     * @return array
     */
    public function toArray()
    {
        $arr = ['status' => $this->status, 'data' => $this->data ?: null];
        if (self::STATUS_ERROR !== $this->status) {
            return $arr;
        }

        $arr['message'] = $this->errorMessage;
        if (!is_null($this->errorCode)) {
            $arr['code'] = $this->errorCode;
        }

        if (is_null($arr['data'])) {
            unset($arr['data']);
        }

        return $arr;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return json_encode($this, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }

    /**
     * Encode and Send the JSend object as an HTTP Response
     *
     * @param array $headers Optional headers to add to the response
     *
     * @return string
     */
    public function send(array $headers = [])
    {
        $headers = $this->filterHeaders($headers);
        $headers[] = 'Content-Type: application/json;charset=utf-8';
        foreach ($headers as $header) {
            header($header);
        }
        echo $this;
    }

    /**
     * Filter Submitted Headers
     *
     * @param array $headers a Collection of key/value headers
     *
     * @return array
     */
    protected function filterHeaders(array $headers)
    {
        $formattedHeaders = [];
        foreach ($headers as $name => $value) {
            $formattedHeaders[] = $this->validateHeaderName($name).': '.$this->validateHeaderValue($value);
        }

        return $formattedHeaders;
    }

    /**
     * Validate Header name
     *
     * @param string $name
     *
     * @throws InvalidArgumentException if the header name is invalid
     *
     * @return string
     */
    protected function validateHeaderName($name)
    {
        if (!preg_match('/^[a-zA-Z0-9\'`#$%&*+.^_|~!-]+$/', $name)) {
            throw new InvalidArgumentException('Invalid header name');
        }

        return $name;
    }

    /**
     * Validate Header value
     *
     * @param string $name
     *
     * @throws InvalidArgumentException if the header value is invalid
     *
     * @return string
     */
    protected function validateHeaderValue($value)
    {
        if (preg_match("#(?:(?:(?<!\r)\n)|(?:\r(?!\n))|(?:\r\n(?![ \t])))#", $value)
            || preg_match('/[^\x09\x0a\x0d\x20-\x7E\x80-\xFE]/', $value)
        ) {
            throw new InvalidArgumentException('Invalid header value');
        }

        return $value;
    }

    /**
     * Returns an instance with the specified status.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified status.
     *
     * @param string $status The status to use with the new instance.
     *
     * @return static A new instance with the specified status.
     */
    public function withStatus($status)
    {
        if ($status === $this->status) {
            return $this;
        }

        return new static($status, $this->data, $this->errorMessage, $this->errorCode);
    }

    /**
     * Returns an instance with the specified data.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified data.
     *
     * @param array $data The data to use with the new instance.
     *
     * @return static A new instance with the specified data.
     */
    public function withData(array $data)
    {
        if ($data === $this->data) {
            return $this;
        }

        return new static($this->status, $data, $this->errorMessage, $this->errorCode);
    }

    /**
     * Returns an instance with the specified error message and error code.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified error message and error code.
     *
     * @param string   $errorMessage The error message to use with the new instance.
     * @param int|null $errorCode    The error code to use with the new instance.
     *
     * @return static A new instance with the specified status.
     */
    public function withError($errorMessage, $errorCode = null)
    {
        if ($errorMessage == $this->errorMessage && $errorCode == $this->errorCode) {
            return $this;
        }

        return new static($this->status, $this->data, $errorMessage, $errorCode);
    }

    /**
     * Returns a successful JSend object with the specified data
     *
     * @param array $data The data to use with the new instance.
     *
     * @return static A new succesful instance with the specified data.
     */
    public static function success(array $data = [])
    {
        return new static(static::STATUS_SUCCESS, $data);
    }

    /**
     * Returns a failed JSend object with the specified data
     *
     * @param array $data The data to use with the new instance.
     *
     * @return static A new failed instance with the specified data.
     */
    public static function fail(array $data = [])
    {
        return new static(static::STATUS_FAIL, $data);
    }

    /**
     * Returns a error JSend object with the specified error message and error code.
     *
     * @param string   $errorMessage The error message to use with the new instance.
     * @param int|null $errorCode    The error code to use with the new instance.
     * @param array    $data         The optional data to use with the new instance.
     *
     * @return static A new failed instance with the specified data.
     */
    public static function error($errorMessage, $errorCode = null, $data = null)
    {
        return new static(static::STATUS_ERROR, $data, $errorMessage, $errorCode);
    }

    /**
     * Returns a new instance from a JSON string
     *
     * @param string $json    The string being decoded
     * @param int    $depth   User specified recursion depth.
     * @param int    $options Bitmask of JSON decode options
     *
     * @throws InvalidArgumentException If the string can not be decode
     *
     * @return static
     */
    public static function createFromString($json, $depth = 512, $options = 0)
    {
        $raw = json_decode($json, true, $depth, $options);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException(sprintf(
                'Unable to decode JSON to array in %s: %s',
                __CLASS__,
                json_last_error_msg()
            ));
        }

        return static::createFromArray($raw);
    }

    /**
     * Returns a new instance from an array
     *
     * @param array $arr The array to build a new JSend object with
     *
     * @return static
     */
    public static function createFromArray(array $arr)
    {
        $defaultValues = ['status' => null, 'data' => null, 'message' => null, 'code' => null];
        $arr = array_replace($defaultValues, array_intersect_key($arr, $defaultValues));

        return new static($arr['status'], $arr['data'], $arr['message'], $arr['code']);
    }
}
