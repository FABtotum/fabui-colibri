<?php

use SimpleJsonRpcClient\Request;

/**
 * Base class for request tests
 *
 * @author Sam Stenvall <neggelandia@gmail.com>
 * @copyright Copyright &copy; Sam Stenvall 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
abstract class BaseRequestTest extends PHPUnit_Framework_TestCase
{

	public function testValid()
	{
		try
		{
			$this->getValidRequest();
		}
		catch (\Exception $e)
		{
			$this->fail($e->getMessage());
		}
	}

	abstract protected function getValidRequest();

	/**
	 * @dataProvider jsonSerializeProvider
	 */
	public function testJsonSerialize($request, $expectedJson)
	{
		$this->assertEquals($expectedJson, json_encode($request));
	}

	abstract public function jsonSerializeProvider();

	public function testToString()
	{
		$request = new Request\Request('Method');
		$this->assertEquals(json_encode($request), $request->__toString());
	}

}
