<?php

use SimpleJsonRpcClient\Response;

/**
 * Tests for Error
 *
 * @author Sam Stenvall <neggelandia@gmail.com>
 * @copyright Copyright &copy; Sam Stenvall 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
class ErrorTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @dataProvider invalidProvider
	 * @expectedException InvalidArgumentException
	 */
	public function testInvalidError($json)
	{
		new Response\Error($json);
	}

	/**
	 * @dataProvider validProvider
	 */
	public function testValidError($hasData, $json)
	{
		$error = new Response\Error(json_encode($json));
		$this->assertEquals($json->message, $error->message);
		$this->assertEquals($json->code, $error->code);

		if ($hasData)
			$this->assertEquals($json->data, $error->data);
		else
			$this->assertEquals($error->data, null);
	}

	public function invalidProvider()
	{
		$json1 = new stdClass();
		$json1->message = 'The message';
		$json2 = new stdClass();
		$json2->code = 123;

		return array(
			array(json_encode($json1)),
			array(json_encode($json2)),
		);
	}

	public function validProvider()
	{
		$json1 = new stdClass();
		$json1->message = 'The message';
		$json1->code = 123;
		$json2 = clone $json1;
		$data = new stdClass();
		$data->extra = 'This is extra data';
		$json2->data = $data;

		return array(
			array(false, $json1),
			array(true, $json2),
		);
	}

}
