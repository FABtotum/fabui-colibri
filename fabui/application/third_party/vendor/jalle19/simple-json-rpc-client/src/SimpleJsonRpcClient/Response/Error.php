<?php

namespace SimpleJsonRpcClient\Response;

/**
 * Respresents the error part of a JSON-RPC response
 *
 * @author Sam Stenvall <neggelandia@gmail.com>
 * @copyright Copyright &copy; Sam Stenvall 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
class Error
{
	
	const CODE_PARSE_ERROR = -32700;
	const CODE_INVALID_REQUEST = -32600;
	const CODE_METHOD_NOT_FOUND = -32601;
	const CODE_INVALID_PARAMS = -32602;
	const CODE_INTERNAL_ERROR = -32603;

	/**
	 * @var string the error message
	 */
	public $message;

	/**
	 * @var int the error code
	 */
	public $code;

	/**
	 * @var mixed (optional) the error data
	 */
	public $data;

	/**
	 * Class constructor. It constructs itself from the provided JSON data
	 * @param string $json the JSON representation of the error
	 * @throws InvalidArgumentException if mandatory fields are missing
	 */
	public function __construct($json)
	{
		$error = json_decode($json);

		// Mandatory fields
		foreach (array('message', 'code') as $field)
			if (!isset($error->{$field}))
				throw new \InvalidArgumentException('Could not construct error object: field "'.$field.'" is missing');
			else
				$this->{$field} = $error->{$field};

		if (isset($error->data))
			$this->data = $error->data;
	}

}
