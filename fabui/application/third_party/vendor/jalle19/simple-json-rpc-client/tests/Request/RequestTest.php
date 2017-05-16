<?php

use SimpleJsonRpcClient\Request;

/**
 * Tests for Request
 *
 * @author Sam Stenvall <neggelandia@gmail.com>
 * @copyright Copyright &copy; Sam Stenvall 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
class RequestTest extends BaseRequestTest
{

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalid()
	{
		new Request\Request('method', 'parameters');
	}

	protected function getValidRequest()
	{
		return new Request\Request('Method');
	}

	public function jsonSerializeProvider()
	{
		return array(
			array(new Request\Request('Method', array('param1'=>'value1'), 0), '{"jsonrpc":"2.0","method":"Method","params":{"param1":"value1"},"id":0}'),
			array(new Request\Request('AnotherMethod', array('value1', 'value2'), 1), '{"jsonrpc":"2.0","method":"AnotherMethod","params":["value1","value2"],"id":1}'),
			array(new Request\Request('Method'), '{"jsonrpc":"2.0","method":"Method","id":0}'),
		);
	}

}
