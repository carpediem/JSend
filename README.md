JSend
==========

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/carpediem/jsend/master.svg?style=flat-square)](https://travis-ci.org/carpediem/jsend)

JSend is a simple library to ease creation of JSend compliant response.

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

You need **PHP >= 5.4.0** to use `Carpediem\JSend` but the latest stable version of PHP/HHVM is recommended.

Install
-------

Install `use Carpediem\JSend` using Composer.

```
$ composer require carpediem/jsend
```

Documentation
-------

### Instantiation

To ease use `Carpediem\JSend\Jsend` response instantiation, and because creating JSend response comes in different ways named constructors are used to offer several ways to instantiate the object.

#### From a JSON string

```php
use Carpediem\JSend\Jsend;

$jsonString = '{"status":"success","data":{"post":{"id":1,"title":"foo","author":"bar"}}}';
$jsend = Jsend::createFromString($jsonString);
```

#### From a PHP array

```php
use Carpediem\JSend\Jsend;

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
$jsend = Jsend::createFromString($arr);
```

#### Depending on the JSend response status

```php
use Carpediem\JSend\Jsend;

$data = [
	'post' => [
		'id' => 1,
		'title' => 'foo',
		'author' = 'bar'
	],
];
$jsendSuccess = Jsend::success($data); //JSend success response object
$jsendFailed = Jsend::fail($data); //JSend fail response object
$jsendError = Jsend::error('An error occurs', 42, $data); //JSend error object
```

#### Using the default constructor

```php
use Carpediem\JSend\Jsend;

$data = [
		'post' => [
			'id' => 1,
			'title' => 'foo',
			'author' = 'bar'
		],
	],
];
$jsendSuccess = Jsend::success('success', $data); //JSend success response object
$jsendFailed = Jsend::fail('fail', $arr); //JSend fail response object
$jsendError = Jsend::error('error', null, An error occurs', 42); //JSend error object
```

- If a `Jsend` response object can not be created an PHP `Exception` is thrown
- The class comes bundle with constant to ease writing the 3 success state:
    - `JSend::STATUS_SUCCES`
    - `JSend::STATUS_FAIL`
    - `JSend::STATUS_ERROR`

### JSend response properties

Once the Jsend object is instantiated you can get access to all its information:

```php
use Carpediem\JSend\Jsend;

$data = [
		'post' => [
			'id' => 1,
			'title' => 'foo',
			'author' = 'bar'
		],
	],
];
$response = Jsend::success('success', $data); //JSend success response object
$response->getStatus(); //return 'success';
$response->getData(); //return an array
$response->getErrorMessage(); //return a string
$response->getErrorCode(); return an integer OR null if no code was given;
```

### Modifying a JSend response object

`Carpediem\Jsend` is an immutable value object as such modifying any of its property returns a new instance with the modified properties while leaving the current instance unchanged. The class uses the following modifiers:

- `JSend::withStatus($status)` to modify the response status
- `JSend::withData(array $data)` to modify the response data
- `JSend::withError($errorMessage, $errorCode = null)` to modify the response error properties

```php
use Carpediem\JSend\Jsend;

$data = [
		'post' => [
			'id' => 1,
			'title' => 'foo',
			'author' = 'bar'
		],
	],
];
$response = Jsend::success($data);
$newResponse = $response->withData(['test' => 'ok']);
$newResponse->getData(); //return ['test' => 'ok'];
$response->getData(); //return an array equals to $data
```

Again if the modification is not possible or forbiddent a PHP Exception will be thrown.

### Convert the object

#### Converting to string

The class implements the `__toString` method so you can output the JSON representation of the class using the `echo` construct.

```php
use Carpediem\JSend\Jsend;

$data = [
		'post' => [
			'id' => 1,
			'title' => 'foo',
			'author' = 'bar'
		],
	],
];
$response = Jsend::success($data);
echo $response; //returns {"status":"success","data":{"post":{"id":1,"title":"foo","author":"bar"}}};
```

#### Converting to array

The class returns the array representation of the JSON response object using the `toArray` method;


```php
use Carpediem\JSend\Jsend;

$data = [
		'post' => [
			'id' => 1,
			'title' => 'foo',
			'author' = 'bar'
		],
	],
];
$response = Jsend::success($data);
$response->toArray();
//returns
//$arr = [
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

### Implements the `JsonSerializable` interface

If you want to change how the JSON Response object is converted to string you can use the fact that the class implements PHP's `JsonSerializable` interface

```php
use Carpediem\JSend\Jsend;

$data = [
		'post' => [
			'id' => 1,
			'title' => 'foo',
			'author' = 'bar'
		],
	],
];
$response = Jsend::success($data);
echo json_encode($response,  JSON_PRETTY_PRINT);
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
use Carpediem\JSend\Jsend;

$data = [
		'post' => [
			'id' => 1,
			'title' => 'foo',
			'author' = 'bar'
		],
	],
];
$response = Jsend::success($data);
$response->send(['Access-Control-Allow-Origin' => 'example.com']);
// the headers will contain the following headers
// Content-Type and Access-Control-Allow-Origin
```

Testing
-------

`Carpediem\JSend` has a [PHPUnit](https://phpunit.de) test suite and a coding style compliance test suite using [PHP CS Fixer](http://cs.sensiolabs.org/). To run the tests, run the following command from the project folder.

``` bash
$ composer test
```

Contributing
-------

Contributions are welcome and will be fully credited. Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

Security
-------

If you discover any security related issues, please email dev@carpediem.fr instead of using the issue tracker.

Credits
-------

- [All Contributors](https://github.com/carpediem/jsend/graphs/contributors)

License
-------

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

[PSR-2]: http://www.php-fig.org/psr/psr-2/
[PSR-4]: http://www.php-fig.org/psr/psr-4/
