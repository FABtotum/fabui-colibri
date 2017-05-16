[![Build Status](https://travis-ci.org/Jalle19/simple-json-rpc-client.png?branch=master)](https://travis-ci.org/Jalle19/simple-json-rpc-client) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Jalle19/simple-json-rpc-client/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Jalle19/simple-json-rpc-client/?branch=master) [![Coverage Status](https://coveralls.io/repos/Jalle19/simple-json-rpc-client/badge.png)](https://coveralls.io/r/Jalle19/simple-json-rpc-client)

simple-json-rpc-client
======================

Simple yet powerful JSON-RPC client which fully implements the JSON-RPC 2.0 specifications. It provides an interface for creating custom clients and comes with a default implementation which sends requests over HTTP using POST. Being standard-compliant it supports standard requests, notifications as well as batch requests.

## Requirements

PHP 5.4 is required.

## Installation

Install using Composer (the package is published on Packagist). Install with `--no-dev` if you don't want or need the ability to run the test suite.

## Usage

### Standard requests

```php
<?php

use SimpleJsonRpcClient\Client\HttpPostClient as Client;

use SimpleJsonRpcClient\Request\Request;
use SimpleJsonRpcClient\Exception\BaseException;
use SimpleJsonRpcClient\Response\Response;

// Initialize the client. Credentials are optional.
$client = new Client('localhost', 'username', 'password');

try 
{
	// Send a request without parameters. The "id" will be added automatically unless supplied.
	// Request objects return their JSON representation when treated as strings.
	$request = new Request('method');
	$response = $client->sendRequest($request);
	
	// Send a request with parameters specified as an array
	$request = new Request('method', array('param1'=>'value1'));
	$response = $client->sendRequest($request);
	
	// Send a request with parameters specified as an object
	$params = new stdClass();
	$params->param1 = 'value1';
	$request = new Request('method', $params);
	$response = $client->sendRequest($request);
	
	// Send a parameter-less request with specific "id"
	$request = new Request('method', null, 2);
	$response = $client->sendRequest($request);
}
catch (BaseException $e) 
{
	echo $e->getMessage();
}
```

### Notifications

```php
<?php

use SimpleJsonRpcClient\Client\HttpPostClient as Client;

use SimpleJsonRpcClient\Request\Notification;
use SimpleJsonRpcClient\Exception\BaseException;

$client = new Client('localhost', 'username', 'password');

try 
{
	$request = new Notification('notification');
	$client->sendNotification($request);
}
catch (BaseException $e) 
{
	echo $e->getMessage();
}
```

### Batch requests

```php
<?php

use SimpleJsonRpcClient\Client\HttpPostClient as Client;

use SimpleJsonRpcClient\Request;
use SimpleJsonRpcClient\Exception\BaseException;

$client = new Client('localhost', 'username', 'password');

try 
{
	$request = new Request\BatchRequest(array(
		new Request\Request('method'),
		new Request\Notification('anotherMethod'),
		new Request\Request('yetAnotherMethod', null, 3)
	));
	
	$batchResponse = $client->sendBatchRequest($request);
	
	// Retrieve all response objects
	$responses = $batchResponse->getResponses();
	
	// Get specific response
	$response = $batchResponse->getResponse(3);
}
catch (BaseException $e) 
{
	echo $e->getMessage();
}
```

### Exception handling

All exceptions derive from the base class BaseException. If you don't want to handle specific exceptions differently from others you can simply catch BaseException like in the examples above. Here's an example which illustrates the exception hierarchy:

```php
<?php

use SimpleJsonRpcClient\Client\HttpPostClient as Client;

use SimpleJsonRpcClient\Request;
use SimpleJsonRpcClient\Exception;
use SimpleJsonRpcClient\Response;

$client = new Client('localhost', 'username', 'password');

try 
{
	$request = new Request\Request('method', array('param1'=>'value1'));
	$response = $client->sendRequest($request);
}
catch (ClientException $ce) {
	// The client failed to execute the request
}
catch (InvalidResponseException $e) 
{
	// The response was invalid, e.g. it could not be parsed or it is not standard-compliant
}
catch (ResponseErrorException $re) {
	// The request itself was successful but the JSON-RPC response indicates an error.
	// A subclass of ResponseErrorException is thrown for pre-defined errors (see http://www.jsonrpc.org/specification#error_object)
}
catch (Exception $e) {
	// Anything else, usually InvalidArgumentException
}
```

### Error handling

Some JSON-RPC servers may return a "data" property in their response error. This property may contain valuable information as to the nature of the error. The special ResponseErrorException is thrown whenever a response indicates an error. The exception has a `getData()` method which returns an object representation of the error data.

```php
try {
	$response = $client->sendRequest($request);
}
catch(ResponseErrorException $e) {
	$data = $e->getData();
}

```

### Flags

The client constructor takes a set of flags as the forth parameter. These flags can be used to alter the behavior of the client, mostly useful for working with buggy servers. For example, the `FLAG_ATTEMPT_UTF8_RECOVERY` flag will cause the Response class to attempt to avoid "Malformed UTF-8 in response" errors by re-encoding the raw response as UTF-8 before passing it to `json_decode()`. This is only done if the raw response is determined not to be valid UTF-8.


## Test suite

Run `vendor/bin/phpunit` in the project root folder to run the unit tests. The test suite will launch a mock JSON-RPC server on localhost:8585 using PHP's internal web server. If this port is not available on your system you can change it by editing `phpunit.xml`.

## License

This code is licensed under the [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
