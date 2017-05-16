<?php

use SimpleJsonRpcClient\Response;

/**
 * Tests for Response
 *
 * @author Sam Stenvall <neggelandia@gmail.com>
 * @copyright Copyright &copy; Sam Stenvall 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
class ResponseTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @dataProvider malformedJSONProvider
	 * @expectedException SimpleJsonRpcClient\Exception\InvalidResponseException
	 */
	public function testInvalidResponse($json)
	{
		new Response\Response($json);
	}

	public function testResponseError()
	{
		$json = new stdClass();
		$json->jsonrpc = '2.0';
		$json->id = 0;
		$json->error = new stdClass();
		$json->error->message = 'This is the error message';
		$json->error->code = 123;
		$json->error->data = 'This is the extra data';

		try
		{
			new Response\Response(json_encode($json));
		}
		catch (\SimpleJsonRpcClient\Exception\ResponseErrorException $e)
		{
			$this->assertNotEmpty($e->getData());
		}
	}

	public function testValidResponse()
	{
		$json = new stdClass();
		$json->jsonrpc = '2.0';
		$json->id = 0;
		$json->result = 'This is the result';

		$response = new Response\Response(json_encode($json));
		$this->assertEquals($json->jsonrpc, $response->jsonrpc);
		$this->assertEquals($json->id, $response->id);
		$this->assertEquals($json->result, $response->result);
	}
	
	public function testRawResponse()
	{
		$json = new stdClass();
		$json->jsonrpc = '2.0';
		$json->id = 0;
		$json->result = 'This is the result';

		$response = new Response\Response(json_encode($json));
		$rawResponse = $response->getRawResponse();
		$newResponse = new Response\Response($rawResponse);
		$this->assertEquals($response, $newResponse);
	}

	public function malformedJSONProvider()
	{
		$json1 = new stdClass();
		$json1->jsonrpc = '2.0';
		$json2 = new stdClass();
		$json2->id = 0;
		$json3 = new stdClass();
		$json3->jsonrpc = '1.0';
		$json3->id = 0;

		return array(
			array(json_encode($json1)),
			array(json_encode($json2)),
			array(json_encode($json3)),
			array('{invalid}')
		);
	}

}
