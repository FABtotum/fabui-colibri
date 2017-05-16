<?php

use SimpleJsonRpcClient\Response;

/**
 * Tests for BatchResponse
 *
 * @author Sam Stenvall <neggelandia@gmail.com>
 * @copyright Copyright &copy; Sam Stenvall 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
class BatchResponseTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @expectedException \SimpleJsonRpcClient\Exception\ResponseErrorException
	 */
	public function testErrorResponse()
	{
		new Response\BatchResponse(json_encode($this->getData()));
	}

	public function testErrorResponseIgnored()
	{
		$responses = $this->getData();
		$batchResponse = new Response\BatchResponse(json_encode($responses), true);

		$this->assertEquals(3, count($batchResponse->getResponses()));
		$this->assertEquals($responses[0]->id, $batchResponse->getResponse(0)->id);
		$this->assertTrue($batchResponse->getResponse(1)->error instanceof \SimpleJsonRpcClient\Response\Error);
		$this->assertEquals($responses[2]->id, $batchResponse->getResponse(7)->id);
		$this->assertEquals(null, $batchResponse->getResponse(999));
	}

	private function getData()
	{
		$json1 = new stdClass();
		$json1->jsonrpc = '2.0';
		$json1->id = 0;
		$json1->result = 'This is the result';

		$json2 = new stdClass();
		$json2->jsonrpc = '2.0';
		$json2->id = 1;
		$error = new stdClass();
		$error->message = 'This is the error message';
		$error->code = 123;
		$json2->error = $error;

		$json3 = new stdClass();
		$json3->jsonrpc = '2.0';
		$json3->id = 7;
		$json3->result = 'This is the third result';

		return array(
			$json1,
			$json2,
			$json3,
		);
	}

}
