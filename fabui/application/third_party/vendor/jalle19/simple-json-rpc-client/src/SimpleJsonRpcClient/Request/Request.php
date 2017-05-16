<?php

namespace SimpleJsonRpcClient\Request;

/**
 * Represents a standard JSON-RPC v2.0 request
 *
 * @author Sam Stenvall <neggelandia@gmail.com>
 * @copyright Copyright &copy; Sam Stenvall 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
class Request extends SingleRequest
{
	
	/**
	 * @var mixed the request ID
	 */
	private $_id;

	/**
	 * Class constructor
	 * @param string the method
	 * @param mixed the request parameters. Defaults to null, meaning the 
	 * request is sent without parameters
	 * @param mixed optional request ID. Defaults to 0
	 * @throws Exception if any of the parameters are malformed
	 */
	function __construct($method, $params = null, $id = 0)
	{
		parent::__construct($method, $params);
		$this->_id = $id;
	}
	
	public function jsonSerialize()
	{
		$object = parent::jsonSerialize();
		$object->id = $this->_id;

		return $object;
	}

}