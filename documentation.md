---
layout: default
title: Documentation
---

## Creating an HTTP Response

To ease `JSend` object creation named constructors are used to offer several ways to instantiate the object.

<p class="message-warning">If the submitted data is not compliant with the specification a PHP Exception will be thrown.</p>

### Depending on the response status

As per the JSend specification, a JSend compliant response can have 3 status:

- `success`
- `fail`
- `error`

The class comes with 3 separate named constructor to ease creating the 3 types of responses.

```php
<?php

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

$successResponse = JSend::success($data); //JSend success response object
$failedResponse = JSend::fail($data); //JSend fail response object
$errorResponse = JSend::error($errorMessage, $errorCode, $data); //JSend error object
```

### From a JSON string

Since a JSend response is represented by a JSON string. If you already have a JSend string you can create a new `JSend` object from that string if it complies with JSend specification.

```php
<?php

use Carpediem\JSend\JSend;

$jsonString = '{"status":"success","data":{"post":{"id":1,"title":"foo","author":"bar"}}}';
$response = JSend::createFromString($jsonString);
```

### From a PHP array

If you prefer working with arrays, `JSend::createFromArray` will return a new JSend object

```php
<?php

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
$response = JSend::createFromArray($arr);
```

### Using the default constructor

You can always fallback to use the default constructor to instantiate a new `JSend` object.

```php
<?php

use Carpediem\JSend\JSend;

$response = new JSend(JSend::STATUS_ERROR, $data, $errorMessage, $errorCode);
//or
$responseBis = new JSend('error', $data, $errorMessage, $errorCode);
```

- The class comes bundle with constant to ease writing the 3 available response type:
    - `JSend::STATUS_SUCCESS` which correspond to `success`;
    - `JSend::STATUS_FAIL` which correspond to `fail`;
    - `JSend::STATUS_ERROR` which correspond to `error`;

## Accessing the response properties

Once the `JSend` object is instantiated you can get access to all its information using the following methods:

```php
<?php

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

## Modifying the response

`JSend` is an immutable value object as such modifying any of its properties returns a new instance with the modified properties while leaving the current instance unchanged. The class uses the following modifiers:

- `JSend::withStatus($status)` to modify the status
- `JSend::withData(array $data)` to modify the data
- `JSend::withError($errorMessage, $errorCode = null)` to modify the error properties

```php
<?php

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

<p class="message-warning">If the modification is not possible or forbidden a PHP Exception will be thrown.</p>

## Conversion methods

### Array conversion

The class returns the `JSend` array representation using the `toArray` method;

```php
<?php

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

### String conversion

The class implements the `__toString` method so you can output the JSON representation of the class using the `echo` construct.

```php
<?php

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

### Json conversion

If you want to change the object string output you can use the fact that the class implements PHP's `JsonSerializable` interface

```php
<?php

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

## Sending the response

You can directly send the JSend object with the `application/json` header using the `JSend::send` method. Optionally, you can add more headers information using this method.

```php
<?php

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
<p class="message-warning">If any of the additional header submitted are malformed an exception will be thrown.</p>

## Debugging methods

`JSend` debugging is made easy using the following PHP's magic methods:

- JSend implements `__set_state` method which is a alias of the JSend::createFromArray method.
- JSend implements `__debugInfo` method available for PHP5.6+
