---
layout: default
title: Documentation
---

## Creating the response

### Using the default constructor

#### Description

```php
<?php

public JSend::__construct(
	string $status,
	mixed $data = null,
	string $errorMessage = null,
	int $errorCode = null
): JSend
```

Returns a new `JSend` object.


#### Parameters

- `$status` a **required** string representing one of JSend status or a `JSend` status constant:
    - `JSend::STATUS_SUCCESS` which correspond to `success`;
    - `JSend::STATUS_FAIL` which correspond to `fail`;
    - `JSend::STATUS_ERROR` which correspond to `error`;
- `$data` represents the data to be send. can be:
    - an `array`
    - a `JsonSerializable` object
    - `null`
- `$errorMessage` a **non empty** string representing the error message.
- `$errorCode` an integer representing the error code.

<p class="message-notice">The <code>$errorMessage</code> parameter is required if you are creating a JSend error type response.</p>

#### Exceptions

- Emits an `OutOfRangeException` exception if the `$status` value is invalid.
- Emits an `InvalidArgumentException` if the other parameters are invalid.

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
$errorCode = 500;

$response = new JSend(JSend::STATUS_ERROR, $data, $errorMessage, $errorCode);
//or
$responseBis = new JSend('error', $data, $errorMessage, $errorCode);
```

To ease `JSend` instantiation named constructors can also be used.

### Depending on the response status

#### Description

```php
<?php

public static JSend::success(mixed $data = null): JSend
public static JSend::fail(mixed $data = null): JSend
public static JSend::error(string $errorMessage, int $errorCode = null, mixed $data = null): JSend
```

As per the [JSend specification](https://labs.omniti.com/labs/jsend), a JSend compliant response can have 3 status. As such, 3 separate named constructors to ease creating these response type are introduced.

#### Parameters

<p class="message-warning">The parameters follow the same restrictions as <code>JSend::__construct</code> method.</p>

#### Exceptions

<p class="message-warning">Emits the same exceptions as <code>JSend::__construct</code> method.</p>

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
$errorCode = 500;

$successResponse = JSend::success($data); //JSend success response object
$failedResponse = JSend::fail($data); //JSend fail response object
$errorResponse = JSend::error($errorMessage, $errorCode, $data); //JSend error object
```

### From a JSON string

#### Description

```php
<?php

public static JSend::createFromString(string $json, $depth = 512, $options = 0): JSend
```

Returns a new `JSend` object from a Json compliant string.

#### Parameter

- `$json` a valid JSON representation of a JSend response.
- `$depth` user specified recursion depth.
- `$options` bitmask of JSON decode options.

#### Exceptions

- Emits an `InvalidArgumentException` if the string is an invalid JSON.
- Emits an `OutOfRangeException` exception if the JSend status value is invalid or not found.
- Emits an `InvalidArgumentException` if the other parameters are invalid or not found.

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

Returns a new `JSend` object

#### Parameter

- `$data` a valid array representation of a JSend response.

#### Exceptions

<p class="message-warning">Emits the same exceptions as <code>JSend::__construct</code> method.</p>

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
public JSend::widthData(mixed $data): JSend
public JSend::withError(string $errorMessage, int $errorCode = null): JSend
```

Returns a new `JSend` object.

<p class="message-notice">When using <code>JSend::withError</code> on a non error type response, the returned object is a JSend error type response.</p>

#### Parameters

<p class="message-warning">The parameters follow the same restrictions as <code>JSend::__construct</code> method.</p>

#### Exceptions

<p class="message-warning">Emits the same exceptions as <code>JSend::__construct</code> method.</p>

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

- `$headers`: additional headers represented as an associative array where each key represents the header name and its corresponding value represents the header value.

You can use the optional `$header` parameter to override the default header value.

#### Exceptions

Emits an `InvalidArgumentException` if any of the additional header  is malformed.

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
