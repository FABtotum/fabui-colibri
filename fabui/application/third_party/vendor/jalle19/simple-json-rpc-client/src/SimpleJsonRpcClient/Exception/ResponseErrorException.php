<?php

namespace SimpleJsonRpcClient\Exception;
use SimpleJsonRpcClient\Response\Error;

/**
 * Exception class for response exceptions. It has a custom constructor that 
 * takes an Error object through which the catcher can obtain the error data 
 * that was part of the request.
 *
 * @author Sam Stenvall <neggelandia@gmail.com>
 * @copyright Copyright &copy; Sam Stenvall 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
class ResponseErrorException extends BaseException
{

	/**
	 * @var mixed (optional) the error data
	 */
	private $_data;

	/**
	 * Class constructor
	 * @param Error $error
	 */
	public function __construct(Error $error)
	{
		parent::__construct($error->message, $error->code, null);

		$this->_data = $error->data;
	}

	/**
	 * Returns the error data
	 * @return mixed
	 */
	public function getData()
	{
		return $this->_data;
	}

}
