<?php

namespace SimpleJsonRpcClient\Request;

/**
 * Base class for JSON-RPC v2.0 requests.
 *
 * @author Sam Stenvall <neggelandia@gmail.com>
 * @copyright Copyright &copy; Sam Stenvall 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
abstract class BaseRequest implements \JsonSerializable
{

	/**
	 * Turns the request into its JSON representation
	 * @return string the JSON for the request
	 */
	public function __toString()
	{
		return json_encode($this->jsonSerialize());
	}

}
