JSend
==========

[![Latest Version](https://img.shields.io/github/release/carpediem/jsend.svg?style=flat-square)](https://github.com/carpediem/jsend/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/carpediem/JSend/master.svg?style=flat-square)](https://travis-ci.org/carpediem/JSend)
[![HHVM Status](https://img.shields.io/hhvm/carpediem/jsend.svg?style=flat-square)](http://hhvm.h4cc.de/package/carpediem/jsend)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/carpediem/jsend.svg?style=flat-square)](https://scrutinizer-ci.com/g/carpediem/jsend/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/carpediem/jsend.svg?style=flat-square)](https://scrutinizer-ci.com/g/carpediem/jsend)
[![Total Downloads](https://img.shields.io/packagist/dt/carpediem/jsend.svg?style=flat-square)](https://packagist.org/packages/carpediem/jsend)

JSend is a simple library to ease the use of [JSend compliant](https://labs.omniti.com/labs/jsend) objects.

```php
<?php

use Carpediem\JSend\JSend;

$data = [
    'post' => [
        'id' => 1,
        'title' => 'foo',
        'author' => 'bar',
    ],
];
$response = JSend::success($data);
$response->send(['Access-Control-Allow-Origin' => 'example.com']);
die;
```

Highlights
-------

* Simple API
* Immutable Value Object

System Requirements
-------

You need **PHP >= 7.0** to use `JSend` but the latest stable version of PHP/HHVM is recommended.

Install
-------

Install `JSend` using Composer.

```
$ composer require carpediem/jsend
```

Documentation
-------

### Class summary

```php
final class JSend implements JsonSerializable
{
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';
    const STATUS_FAIL = 'fail';

    public static function fromJSON($json, int $depth = 512, int $options = 0): self;
    public static function fromArray(array $arr): self;
    public static function success($data = null): self;
    public static function fail($data = null): self;
    public static function error($errorMessage, int $errorCode = null, $data = null): self;
    public function getStatus(): string;
    public function getData(): array;
    public function getErrorMessage(): ?string;
    public function getErrorCode(): ?int;
    public function isSuccess(): bool;
    public function isFail(): bool;
    public function isError(): bool;
    public function toArray(): array;
    public function __toString(): string;
    public function jsonSerialize(): array;
    public function send(array $headers = []): int;
    public function withStatus(string $status): self;
    public function withData($data): self;
    public function withError($errorMessage, int $errorCode = null): self;
}
```

### Class Import

```php
<?php

use Carpediem\JSend\JSend;
```

### Create new instances

Use named constructors to instantiate a `JSend` object creation

```php
$success = JSend::success($data);
$fail = JSend::fail($data);
$error = JSend::error('Not Found', 404, $data);
$response = JSend::fromJSON('{"status":"success","data":{"post":{"id":1,"title":"foo","author":"bar"}}}');
$altResponse = JSend::fromArray(['data' => ['post' => 1], 'code' => 404, 'message' => 'Post not Found']);
```

If the object can not be created a `Carpediem\JSend\Exception` will be thrown.

### Access properties

```php
$response = JSend::error('Not Found', 404, ['post' => 1234]);
$response->getStatus();       // returns 'success, 'error', 'fail'
$response->getErrorMessage(); // returns 'Not Found'
$response->getErrorCode();    // returns 404
$response->getData();         // returns $data
$response->isSuccess();       // boolean
$response->isFail();          // boolean
$response->isError();         // boolean
```

- `JSend::getErrorMessage` returns `null` when `JSend::geStatus` is different that 'error';
- `JSend::getErrorCode` is an integer or `null`;


### Manipulations

```php
$response = JSend::success(['post' => 1234]);
(string) $response;           // returns {"status": "success", "data": {"post": 1234}}
json_encode($response, JSON_PRETTY_PRINT); // the JSend object is usable directly with PHP json_encode function
$response->toArray();
// returns [
//     'status' => 'success',
//     'data' => [
//         'post' => 1234,
//     ]
// ]
```

### Updates

The `JSend` object is immutable so any changes to the object will return a new object

```php
$response = JSend::success();
$newResponse = $response->withData(['post' => 1234]);
$failResponse = $response->witStatus(JSend::STATUS_FAIL);
$errorResponse = $response->withError('This is an error', 404);

echo $response;      // returns {"status": "success"}
echo $newResponse;   // returns {"status": "success", "data": {"post": 1234}}
echo $failResponse;  // returns {"status": "fail"}
echo $errorResponse; // returns {"status": "error", "message": "This is an error", code: 404}
```

**`JSend::withData` accepts the `null` value, an `array` or a `JsonSerializable` object whose `jsonSerialize` method returns an `array`**

### Creating an HTTP Response

```php
header('HTTP/1.1 404 Not Found'); // don't forget to add the HTTP header
$response = JSend::fail(['post' => 1234]);
$response->send(['Access-Control-Allow-Origin' => '*']);
die;
```

Testing
-------

the library has a:

- a [PHPUnit](https://phpunit.de) test suite
- a coding style compliance test suite using [PHP CS Fixer](http://cs.sensiolabs.org/).
- a code analysis compliance test suite using [PHPStan](https://github.com/phpstan/phpstan).

To run the tests, run the following command from the project folder.

``` bash
$ composer test
```

Contributing
-------

Contributions are welcome and will be fully credited. Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

Security
-------

If you discover any security related issues, please email dev@carpediem.fr instead of using the issue tracker.

Credits
-------

- [All Contributors](https://github.com/carpediem/JSend/graphs/contributors)

License
-------

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

[PSR-2]: http://www.php-fig.org/psr/psr-2/
[PSR-4]: http://www.php-fig.org/psr/psr-4/
