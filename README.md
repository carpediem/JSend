JSend
==========

[![Latest Version](https://img.shields.io/github/release/carpediem/jsend.svg?style=flat-square)](https://github.com/carpediem/jsend/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/carpediem/JSend/master.svg?style=flat-square)](https://travis-ci.org/carpediem/JSend)
[![HHVM Status](https://img.shields.io/hhvm/carpediem/jsend.svg?style=flat-square)](http://hhvm.h4cc.de/package/carpediem/jsend)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/carpediem/jsend.svg?style=flat-square)](https://scrutinizer-ci.com/g/carpediem/jsend/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/carpediem/jsend.svg?style=flat-square)](https://scrutinizer-ci.com/g/carpediem/jsend)
[![Total Downloads](https://img.shields.io/packagist/dt/carpediem/jsend.svg?style=flat-square)](https://packagist.org/packages/carpediem/jsend)


JSend is a simple library to ease creation of [JSend compliant](https://labs.omniti.com/labs/jsend) HTTP response.

Highlights
-------

* Simple API
* Immutable Value Object
* Fully documented
* Fully Unit tested
* Framework-agnostic
* Composer ready, [PSR-2] and [PSR-4] compliant

System Requirements
-------

You need **PHP >= 5.5.0** to use `JSend` but the latest stable version of PHP/HHVM is recommended.

Install
-------

Install `JSend` using Composer.

```
$ composer require carpediem/jsend
```

Documentation
-------

### Instantiation

To ease JSend object creation named constructors are used to offer several ways to instantiate the object.

#### From a JSON string

```php
use Carpediem\JSend\JSend;

$jsonString = '{"status":"success","data":{"post":{"id":1,"title":"foo","author":"bar"}}}';
$JSend = JSend::createFromString($jsonString);
```

#### From a PHP array

```php
use Carpediem\JSend\JSend;

$arr = [
	'status' => 'success',
	'data' => [
		'post' => [
			'id' => 1,
			'title' => 'foo',
			'author' = 'bar'
		],
	],
];
$JSend = JSend::createFromArray($arr);
```

#### Depending on the JSend status

```php
use Carpediem\JSend\JSend;

$data = [
	'post' => [
		'id' => 1,
		'title' => 'foo',
		'author' = 'bar'
	],
];

$errorMessage = 'An error occurs';
$errorCode = 42;

$JSendSuccess = JSend::success($data); //JSend success response object
$JSendFailed = JSend::fail($data); //JSend fail response object
$JSendError = JSend::error($errorMessage, $errorCode, $data); //JSend error object
```

#### Using the default constructor

```php
use Carpediem\JSend\JSend;

$response = new JSend(JSend::STATUS_ERROR, $data, $errorMessage, $errorCode);
//or
$responseBis = new JSend('error', $data, $errorMessage, $errorCode);
```

- If a `JSend` response object can not be created an PHP `Exception` is thrown
- The class comes bundle with constant to ease writing the 3 available states:
    - `JSend::STATUS_SUCCESS` which correspond to `success`;
    - `JSend::STATUS_FAIL` which correspond to `fail`;
    - `JSend::STATUS_ERROR` which correspond to `error`;

### JSend properties

Once the JSend object is instantiated you can get access to all its information:

```php
use Carpediem\JSend\JSend;

$data = [
		'post' => [
			'id' => 1,
			'title' => 'foo',
			'author' = 'bar'
		],
	],
];
$response = JSend::success($data); //JSend success response object
$response->getStatus(); //returns 'success' (can also be 'fail' or 'error');
$response->isSuccess(); //returns true (depends on the JSend status value)
$response->isFail(); //returns false  (depends on the JSend status value)
$response->isError(); //returns false  (depends on the JSend status value)
$response->getData(); //returns an array
$response->getErrorMessage(); //returns a string
$response->getErrorCode(); //returns an integer OR null if no code was given;
```

**Added in version 1.1**: `Jsend::isSuccess`,  `Jsend::isFail`, `Jsend::isError`

### Modifying a JSend object

`Carpediem\JSend` is an immutable value object as such modifying any of its properties returns a new instance with the modified properties while leaving the current instance unchanged. The class uses the following modifiers:

- `JSend::withStatus($status)` to modify the status
- `JSend::withData(array $data)` to modify the data
- `JSend::withError($errorMessage, $errorCode = null)` to modify the error properties

```php
use Carpediem\JSend\JSend;

$data = [
		'post' => [
			'id' => 1,
			'title' => 'foo',
			'author' = 'bar'
		],
	],
];
$response = JSend::success($data);
$newResponse = $response->withData(['test' => 'ok'])->withStatus(JSend::STATUS_FAIL);
$newResponse->getData();   //returns ['test' => 'ok'];
$newResponse->getStatus(); //returns 'fail';
$response->getData();      //returns an array equals to $data
```

**If the modification is not possible or forbidden a PHP Exception will be thrown.**

### JSend conversion methods

#### Array conversion

The class returns the object array representation using the `toArray` method;


```php
use Carpediem\JSend\JSend;

$data = [
		'post' => [
			'id' => 1,
			'title' => 'foo',
			'author' = 'bar'
		],
	],
];
$response = JSend::success($data);
$response->toArray();
//returns
//[
//	'status' => 'success',
//	'data' => [
//		'post' => [
//			'id' => 1,
//			'title' => 'foo',
//			'author' = 'bar'
//		],
//	],
//];
```

#### String conversion

The class implements the `__toString` method so you can output the JSON representation of the class using the `echo` construct.

```php
use Carpediem\JSend\JSend;

$data = [
		'post' => [
			'id' => 1,
			'title' => 'foo',
			'author' = 'bar'
		],
	],
];
$response = JSend::success($data);
echo $response; //returns {"status":"success","data":{"post":{"id":1,"title":"foo","author":"bar"}}};
```

#### Implements the `JsonSerializable` interface

If you want to change the object string output you can use the fact that the class implements PHP's `JsonSerializable` interface

```php
use Carpediem\JSend\JSend;

$data = [
		'post' => [
			'id' => 1,
			'title' => 'foo',
			'author' = 'bar'
		],
	],
];
$response = JSend::success($data);
echo json_encode($response, JSON_PRETTY_PRINT);
//returns
//{
//    "status":"success",
//    "data":
//    {
//        "post":
//        {
//            "id":1,
//            "title":"foo",
//            "author":"bar"
//        }
//    }
//};
```

### Sending JSend object as HTTP Response

You can directly send the JSend object with the `application/json` header as well as optionals headers with the `JSend::send` method.

```php
use Carpediem\JSend\JSend;

$data = [
		'post' => [
			'id' => 1,
			'title' => 'foo',
			'author' = 'bar'
		],
	],
];
$response = JSend::success($data);
$response->send(['Access-Control-Allow-Origin' => 'example.com']);
// the headers will contain the following headers
// Content-Type and Access-Control-Allow-Origin
```

### Magic Debugging methods

**new in JSend version 1.2**

JSend debug is made easy using the following PHP's magic methods:

- JSend implements `__set_state` method which is a alias of the JSend::createFromArray method.
- JSend implements `__debugInfo` method available for PHP5.6+


Testing
-------

`JSend` has a [PHPUnit](https://phpunit.de) test suite and a coding style compliance test suite using [PHP CS Fixer](http://cs.sensiolabs.org/). To run the tests, run the following command from the project folder.

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
