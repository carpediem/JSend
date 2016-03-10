---
layout: default
title: Documentation
---

## Creating the HTTP Response

To ease `JSend` object creation named constructors are used to offer several ways to instantiate the object.

<p class="message-warning">If the submitted data is not compliant with the specification a PHP Exception will be thrown.</p>

### Depending on the response status

#### Description

```php
<?php

public static JSend::success(array $data): JSend
public static JSend::fail(array $data): JSend
public static JSend::error(string $errorMessage, int $errorCode = null): JSend
```

As per the JSend specification, a JSend compliant response can have 3 status:

- `success`
- `fail`
- `error`


The class comes with 3 separate named constructors to ease creating these response type.

#### Example

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

#### Description

```php
<?php

public static JSend::createFromString(string $json): JSend
```

Since a JSend response is represented by a JSON string. If you already have a JSend string you can create a new `JSend` object from that string if it complies with JSend specification.

#### Example

```php
<?php

use Carpediem\JSend\JSend;

$jsonString = '{"status":"success","data":{"post":{"id":1,"title":"foo","author":"bar"}}}';
$response = JSend::createFromString($jsonString);
```

### From a PHP array

#### Description

```php
<?php

public static JSend::createFromArray(array $data): JSend
```

If you prefer working with arrays, `JSend::createFromArray` will return a new JSend object

#### Example

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

#### Description

```php
<?php

public JSend::__construct(
	string $status,
	array $data = null,
	string $errorMessage = null,
	int $errorCode = null
): JSend
```

#### Parameters

- `$status` a string representing one of JSend type or a `JSend` constant to ease managing the type
    - `JSend::STATUS_SUCCESS` which correspond to `success`;
    - `JSend::STATUS_FAIL` which correspond to `fail`;
    - `JSend::STATUS_ERROR` which correspond to `error`;
- `$data` an array representing the data to be send. The parameter is optional.
- `$errorMessage` an string representing the error message. The parameter is optional.
- `$errorCode` an integer representing the error code. The parameter is optional.

#### Example

```php
<?php

use Carpediem\JSend\JSend;

$response = new JSend(JSend::STATUS_ERROR, $data, $errorMessage, $errorCode);
//or
$responseBis = new JSend('error', $data, $errorMessage, $errorCode);
```

## Accessing the response properties

Once the `JSend` object is instantiated you can get access to all its information using the following methods:

```php
<?php

public JSend::getStatus(): string
public JSend::isSuccess(): bool
public JSend::isFail(): bool
public JSend::isError(): bool
public JSend::getData(): array
public JSend::getErrorMessage(): string
public JSend::getErrorCode(): int|null
```

#### Example

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
$response->getStatus(); //returns 'success'
$response->isSuccess(); //returns true
$response->isFail(); //returns false
$response->isError(); //returns false
$response->getData(); //returns an array
$response->getErrorMessage(); //returns a empty string
$response->getErrorCode(); //returns null
```

## Modifying the response

`JSend` is an immutable value object as such modifying any of its properties returns a new instance with the modified properties while leaving the current instance unchanged. The class uses the following modifiers:

```php
<?php

public JSend::withStatus(string $status): JSend
public JSend::widthData(array $data): JSend
public JSend::withError(string $errorMessage = null, int $errorCode = null): JSend
```
<p class="message-warning">If the modification is not possible or forbidden a PHP Exception will be thrown.</p>

#### Example

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

## Conversion methods

### Array conversion

#### Description

```php
<?php

public JSend::toArray(): array
```

The class returns the `JSend` array representation using the `toArray` method;

#### Example

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

#### Description

```php
<?php

public JSend::__toString(): string
```

The class implements the `__toString` method so you can output the JSON representation of the class using the `echo` construct.

#### Example

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

#### Description

```php
<?php

public JSend::jsonSerialize(): string
```

If you want to change the object string output you can use the fact that the class implements PHP's `JsonSerializable` interface

#### Example

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

#### Description

```php
<?php

public JSend::send(array $headers = []): string
```

You can directly send the JSend object with the `application/json` header using the `JSend::send` method. Optionally, you can add more headers information using this method.

#### Parameters

- `$headers` additional headers as an associative array. This parameter is optional.

<p class="message-warning">If any of the additional header submitted are malformed an exception will be thrown.</p>

#### Example

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

## Debugging methods

`JSend` debugging is made easy using the following PHP's magic methods:

```php
<?php

public static JSend::__set_state(array $data): JSend
public static JSend::__debugInfo(): array
```
