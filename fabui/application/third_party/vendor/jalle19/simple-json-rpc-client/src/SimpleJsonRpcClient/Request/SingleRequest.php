<?php

namespace SimpleJsonRpcClient\Request;

/**
 * Represents a single request (in contrast to multiple requests)
 * 
 * @author Sam Stenvall <neggelandia@gmail.com>
 * @copyright Copyright &copy; Sam Stenvall 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
abstract class SingleRequest extends BaseRequest
{

	/**
	 * @var string the method
	 */
	protected $_method;

	/**
	 * @var mixed the request parameters
	 */
	protected $_params;

	/**
	 * Class constructor
	 * @param string the method
	 * @param mixed the request parameters. Defaults to null, meaning the 
	 * request is sent without parameters
	 * @throws InvalidArgumentException if the parameter is of incorrect type
	 */
	function __construct($method, $params = null)
	{
		if ($params !== null && !is_array($params) && !is_object($params))
			throw new \InvalidArgumentException('Parameters must be either an array or an object');

		$this->_method = $method;
		$this->_params = $params;
	}

	public function jsonSerialize()
	{
		$object = new \stdClass();
		$object->jsonrpc = '2.0';
		$object->method = $this->_method;

		if ($this->_params !== null)
			$object->params = $this->_params;

		return $object;
	}

}
