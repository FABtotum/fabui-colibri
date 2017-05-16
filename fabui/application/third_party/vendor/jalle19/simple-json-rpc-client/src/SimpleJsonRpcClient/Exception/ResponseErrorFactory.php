<?php

namespace SimpleJsonRpcClient\Exception;

use SimpleJsonRpcClient\Response\Error;

/**
 * Factory for returning the appropriate exception in response to a JSON-RPC 
 * error.
 *
 * @author Sam Stenvall <neggelandia@gmail.com>
 * @copyright Copyright &copy; Sam Stenvall 2014-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
class ResponseErrorFactory
{

	/**
	 * Force use of ::create()
	 */
	private function __construct()
	{
		
	}

	/**
	 * Factory method which returns a different exception based on the error 
	 * code
	 * @param \SimpleJsonRpcClient\Response\Error $error
	 * @return \SimpleJsonRpcClient\Exception\ResponseErrorException the exception
	 */
	public static function create(Error $error)
	{
		// Handle pre-defined single codes
		switch ($error->code)
		{
			case Error::CODE_PARSE_ERROR:
				return new ParseErrorException($error);
			case Error::CODE_INVALID_REQUEST:
				return new InvalidRequestException($error);
			case Error::CODE_METHOD_NOT_FOUND:
				return new MethodNotFoundException($error);
			case Error::CODE_INVALID_PARAMS:
				return new InvalidParamsException($error);
			case Error::CODE_INTERNAL_ERROR:
				return new InternalErrorException($error);
		}

		// Check for server error exceptions
		if ($error->code >= -32099 && $error->code <= -32000)
			return new ServerErrorException($error);

		// Implementation-specific
		return new ResponseErrorException($error);
	}

}
