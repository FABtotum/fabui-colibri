<?php

use SimpleJsonRpcClient\Request;

/**
 * Test for BatchRequest
 *
 * @author Sam Stenvall <neggelandia@gmail.com>
 * @copyright Copyright &copy; Sam Stenvall 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
class BatchRequestTest extends BaseRequestTest
{

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testInvalidRequest()
	{
		new Request\BatchRequest(array(new stdClass()));
	}

	protected function getValidRequest()
	{
		return new Request\BatchRequest($this->getRequests());
	}

	public function jsonSerializeProvider()
	{
		return array(
			array(new Request\BatchRequest($this->getRequests()), '[{"jsonrpc":"2.0","method":"Method","id":0},{"jsonrpc":"2.0","method":"Notification","params":{"param1":"value1"}}]'),
		);
	}

	private function getRequests()
	{
		return array(
			new Request\Request('Method'),
			new Request\Notification('Notification', array('param1'=>'value1')),
		);
	}

}
