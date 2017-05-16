<?php

namespace SimpleJsonRpcClient\Request;

/**
 * Represents a batch request
 * 
 * @author Sam Stenvall <neggelandia@gmail.com>
 * @copyright Copyright &copy; Sam Stenvall 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
class BatchRequest extends BaseRequest
{

	/**
	 * @var Request\Request[] the individual requests
	 */
	private $_requests = array();

	/**
	 * Class constructor
	 * @param Request\Request[] $requests the individual requests
	 * @throws InvalidArgumentException if the requests are invalid
	 */
	public function __construct(array $requests)
	{
		foreach ($requests as $request)
			if (!$request instanceof BaseRequest)
				throw new \InvalidArgumentException('Requests must be descendants of BaseRequest');

		$this->_requests = $requests;
	}

	public function jsonSerialize()
	{
		return $this->_requests;
	}

}
