---
layout: default
title: Documentation
---

## Creating the HTTP Response

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

<p class="message-warning">If the data is not compliant with the specification a <code>UnexpectedValueException</code> exception is thrown.</p>

#### Parameters

- `$status` a string representing one of JSend type or a `JSend` constant to ease managing the type
    - `JSend::STATUS_SUCCESS` which correspond to `success`;
    - `JSend::STATUS_FAIL` which correspond to `fail`;
    - `JSend::STATUS_ERROR` which correspond to `error`;
- `$data` an array representing the data to be send. *- optional parameter*
- `$errorMessage` an **non-empty** string representing the error message. *- optional parameter*
- `$errorCode` an integer representing the error code. *- optional parameter*

#### Example

```php
<?php

use Carpediem\JSend\JSend;

$response = new JSend(JSend::STATUS_ERROR, $data, $errorMessage, $errorCode);
//or
$responseBis = new JSend('error', $data, $errorMessage, $errorCode);
```

To ease `JSend` object creation named constructors are used to offer several ways to instantiate the object.

### Depending on the response status

#### Description

```php
<?php

public static JSend::success(array $data): JSend
public static JSend::fail(array $data): JSend
public static JSend::error(string $errorMessage, int $errorCode = null): JSend
```

As per the [JSend specification](https://labs.omniti.com/labs/jsend), a JSend compliant response can have 3 status. As such, 3 separate named constructors to ease creating these response type are introduced.

#### Parameters

All parameters are required except for the `$errorCode` parameter from the `JSend::error` method. When present, the `$errorCode` parameter **MUST** be a integer.

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

Returns a new `JSend` object from a Json compliant string.

#### Parameter

- `$json` a valid JSON representation of a JSend response.

<p class="message-warning">If the string is an invalid Json a <code>InvalidArgumentException</code> exception is thrown.</p>

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

#### Parameter

- `$data` a valid array representation of a JSend response.

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

## Accessing the response properties

Once the `JSend` object is instantiated you can access its properties using the following methods:

```php
<?php

public JSend::isSuccess(): bool
public JSend::isFail(): bool
public JSend::isError(): bool
public JSend::getStatus(): string
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

The parameters follow the same restrictions used for instantiating a `JSend` object.

<p class="message-warning">If the modification is not possible or forbidden an <code>InvalidArgumentException</code> or an <code>UnexpectedValueException</code> will be thrown.</p>

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

## Converting the response

### String conversion

#### Description

```php
<?php

public JSend::__toString(): string
```

Returns the string representation of a JSend response.<br> This method enables using `JSend` with the `echo` construct.

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
echo $response;
//displays {"status":"success","data":{"post":{"id":1,"title":"foo","author":"bar"}}};
```

### Array conversion

#### Description

```php
<?php

public JSend::toArray(): array
```

Returns the array representation of a JSend response

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

### Alternative Json conversion

#### Description

```php
<?php

public JSend::jsonSerialize(): array
```

Enables using the `JSend` object with PHP `json_encode` function.

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

Sends the JSend response by setting the following headers:

- `Content-Type` header set to `application/json` with a `UTF-8` charset.
- `Content-Length` header set with the length of the JSend response.

#### Parameters

- `$headers`: additional headers represented as an associative array where each key represents the header name and its corresponding value represents the header value. *- optional parameter*

You can use the optional `$header` parameter to override the default header value.

<p class="message-warning">If any of the additional header submitted is malformed an <code>InvalidArgumentException</code> is thrown.</p>

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
die;
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
