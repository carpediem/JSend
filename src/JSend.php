<?php

declare(strict_types=1);

namespace Carpediem\JSend;

use JsonSerializable;
use const JSON_ERROR_NONE;
use const JSON_HEX_AMP;
use const JSON_HEX_APOS;
use const JSON_HEX_QUOT;
use function array_merge;
use function header;
use function is_array;
use function is_object;
use function is_scalar;
use function json_decode;
use function json_encode;
use function json_last_error;
use function json_last_error_msg;
use function method_exists;
use function preg_match;
use function sprintf;
use function strlen;
use function trim;

/**
 * A Immutable Value Object Class to represent a JSend object.
 */
final class JSend implements JsonSerializable
{
    const STATUS_SUCCESS = 'success';

    const STATUS_ERROR = 'error';

    const STATUS_FAIL = 'fail';

    /**
     * JSend status.
     *
     * @var string
     */
    private $status;

    /**
     * JSend Data.
     *
     * @var array
     */
    private $data;

    /**
     * JSend Error Message.
     *
     * @var string|null
     */
    private $errorMessage;

    /**
     * JSend Error Code.
     *
     * @var int|null
     */
    private $errorCode;

    /**
     * Returns a new instance from a JSON string.
     *
     * @throws Exception If the string can not be decode
     */
    public static function createFromString(string $json, int $depth = 512, int $options = 0): self
    {
        $raw = json_decode($json, true, $depth, $options);
        if (JSON_ERROR_NONE === json_last_error()) {
            return static::createFromArray($raw);
        }

        throw new Exception(sprintf(
            'Unable to decode the submitted JSON string: %s',
            json_last_error_msg()
        ));
    }

    /**
     * Returns a new instance from an array.
     */
    public static function createFromArray(array $arr): self
    {
        return new self(
            $arr['status'] ?? '',
            $arr['data'] ?? null,
            $arr['emessage'] ?? null,
            $arr['code'] ?? null
        );
    }

    /**
     * Returns a successful JSend object with the specified data.
     *
     * @param null|mixed $data
     */
    public static function success($data = null): self
    {
        return new self(self::STATUS_SUCCESS, $data);
    }

    /**
     * Returns a failed JSend object with the specified data.
     *
     * @param null|mixed $data
     */
    public static function fail($data = null): self
    {
        return new self(self::STATUS_FAIL, $data);
    }

    /**
     * Returns a error JSend object with the specified error message and error code.
     *
     * @param null|mixed $data
     */
    public static function error(string $errorMessage, int $errorCode = null, $data = null): self
    {
        return new self(self::STATUS_ERROR, $data, $errorMessage, $errorCode);
    }

    /**
     * {@inheritdoc}
     */
    public static function __set_state(array $properties)
    {
        return new self(
            $properties['status'],
            $properties['data'],
            $properties['errorMessage'],
            $properties['errorCode']
        );
    }

    /**
     * New Instance.
     *
     * @param null|mixed $data
     * @param null|mixed $errorMessage
     */
    public function __construct(
        string $status,
        $data = null,
        $errorMessage = null,
        int $errorCode = null
    ) {
        $this->status = $this->filterStatus($status);
        $this->data = $this->filterData($data);
        list($this->errorMessage, $this->errorCode) = $this->filterError($errorMessage, $errorCode);
    }

    /**
     * Filter and Validate the JSend Status.
     *
     * @throws Exception If the status value does not conform to JSend Spec.
     */
    private function filterStatus(string $status): string
    {
        static $res = [self::STATUS_SUCCESS => 1, self::STATUS_ERROR => 1, self::STATUS_FAIL => 1];
        if (isset($res[$status])) {
            return $status;
        }

        throw new Exception('The given status does not conform to Jsend specification');
    }

    /**
     * Filter and Validate the JSend Data.
     *
     * @param mixed $data The data can be
     *                    <ul>
     *                    <li>An Array
     *                    <li>A JsonSerializable object
     *                    <li>null
     *                    </ul>
     *
     * @throws Exception If the input does not conform to one of the valid type
     */
    private function filterData($data)
    {
        if (null === $data) {
            return [];
        }

        if ($data instanceof JsonSerializable) {
            return (array) $data->jsonSerialize();
        }

        if (is_array($data)) {
            return $data;
        }

        throw new Exception('The data must be an array, a JsonSerializable object or null');
    }

    /**
     * Filter and Validate the JSend Error properties.
     */
    private function filterError($errorMessage, int $errorCode = null): array
    {
        if (self::STATUS_ERROR === $this->status) {
            return [$this->filterErrorMessage($errorMessage), $errorCode];
        }

        return [null, null];
    }

    /**
     * Validate a string.
     *
     * @throws Exception If the data value is not a empty string
     */
    private function filterErrorMessage($str): string
    {
        if (!is_scalar($str) && !(is_object($str) && method_exists($str, '__toString'))) {
            throw new Exception('The error message must be a scalar or a object implementing the __toString method.');
        }

        $str = (string) $str;
        if ('' !== trim($str)) {
            return $str;
        }

        throw new Exception('The error message can not be empty.');
    }

    /**
     * Returns the status.
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Returns the data.
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Returns the error message.
     *
     * @return null|string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Returns the error code.
     *
     * @return null|int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * Returns whether the status is success.
     */
    public function isSuccess(): bool
    {
        return self::STATUS_SUCCESS === $this->status;
    }

    /**
     * Returns whether the status is fail.
     */
    public function isFail(): bool
    {
        return self::STATUS_FAIL === $this->status;
    }

    /**
     * Returns whether the status is error.
     */
    public function isError(): bool
    {
        return self::STATUS_ERROR === $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Returns the array representation.
     */
    public function toArray(): array
    {
        $arr = ['status' => $this->status, 'data' => $this->data ?: null];
        if (self::STATUS_ERROR !== $this->status) {
            return $arr;
        }

        $arr['message'] = (string) $this->errorMessage;
        if (null !== $this->errorCode) {
            $arr['code'] = $this->errorCode;
        }

        if (null === $arr['data']) {
            unset($arr['data']);
        }

        return $arr;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return json_encode($this->toArray(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }

    /**
     * {@inheritdoc}
     */
    public function __debugInfo()
    {
        return $this->toArray();
    }

    /**
     * Output all the data of the JSend object.
     *
     * @param array $headers Optional headers to add to the response
     *
     * @return int Returns the number of characters read from the JSend object
     *             and passed throught to the output
     */
    public function send(array $headers = []): int
    {
        $body = $this->__toString();
        $length = strlen($body);
        $headers = $this->filterHeaders(array_merge([
            'Content-Type' => 'application/json;charset=utf-8',
            'Content-Length' => (string) $length,
        ], $headers));

        foreach ($headers as $header) {
            header($header);
        }
        echo $body;

        return $length;
    }

    /**
     * Filter Submitted Headers.
     *
     * @param array $headers a Collection of key/value headers
     */
    private function filterHeaders(array $headers): array
    {
        $formattedHeaders = [];
        foreach ($headers as $name => $value) {
            $formattedHeaders[] = $this->validateHeaderName($name).': '.$this->validateHeaderValue($value);
        }

        return $formattedHeaders;
    }

    /**
     * Validate Header name.
     *
     * @throws Exception if the header name is invalid
     */
    private function validateHeaderName(string $name): string
    {
        if (preg_match('/^[a-zA-Z0-9\'`#$%&*+.^_|~!-]+$/', $name)) {
            return $name;
        }

        throw new Exception('Invalid header name');
    }

    /**
     * Validate Header value.
     *
     * @throws Exception if the header value is invalid
     */
    private function validateHeaderValue(string $value): string
    {
        if (!preg_match("#(?:(?:(?<!\r)\n)|(?:\r(?!\n))|(?:\r\n(?![ \t])))#", $value)
            && !preg_match('/[^\x09\x0a\x0d\x20-\x7E\x80-\xFE]/', $value)
        ) {
            return $value;
        }

        throw new Exception('Invalid header value');
    }

    /**
     * Returns an instance with the specified status.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified status.
     */
    public function withStatus(string $status): self
    {
        if ($status === $this->status) {
            return $this;
        }

        return new self($status, $this->data, $this->errorMessage, $this->errorCode);
    }

    /**
     * Returns an instance with the specified data.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified data.
     */
    public function withData($data): self
    {
        $data = $this->filterData($data);
        if ($data === $this->data) {
            return $this;
        }

        $clone = clone $this;
        $clone->data = $data;

        return $clone;
    }

    /**
     * Returns an instance with the specified error message and error code.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified error message and error code.
     */
    public function withError($errorMessage, int $errorCode = null): self
    {
        list($errorMessage, $errorCode) = $this->filterError($errorMessage, $errorCode);
        if ($this->isError() && $errorMessage === $this->errorMessage && $errorCode === $this->errorCode) {
            return $this;
        }

        $clone = clone $this;
        $clone->errorMessage = $errorMessage;
        $clone->errorCode = $errorCode;
        $clone->status = self::STATUS_ERROR;

        return $clone;
    }
}
