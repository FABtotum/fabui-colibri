<?php

use SimpleJsonRpcClient\Client;
use SimpleJsonRpcClient\Request;

/**
 * Tests for HttpPostClient
 *
 * @author Sam Stenvall <neggelandia@gmail.com>
 * @copyright Copyright &copy; Sam Stenvall 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
class HttpPostClientTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @var Client\HttpPostClient the JSON-RPC client
	 */
	private $_client;

	/**
	 * Instantiates the JSON-RPC client
	 */
	protected function setUp()
	{
		$server = 'http://'.WEB_SERVER_HOST.':'.WEB_SERVER_PORT;
		$this->_client = new Client\HttpPostClient($server, WEB_SERVER_USER, WEB_SERVER_PASS);
	}

	public function testSendRequest()
	{
		$request = new Request\Request('add', array('x'=>1, 'y'=>2));
		$response = $this->_client->sendRequest($request);
		$this->assertEquals(3, $response->result);
	}

	public function testSendNotification()
	{
		$notification = new Request\Notification('start');
		$this->_client->sendNotification($notification);
	}

	/**
	 * @expectedException SimpleJsonRpcClient\Exception\ClientException
	 */
	public function testInvalidServer()
	{
		// Connect to a completely invalid host
		$this->_client = new Client\HttpPostClient('test');
		$this->_client->sendRequest(new Request\Request('test'));
	}

	/**
	 * @expectedException SimpleJsonRpcClient\Exception\ResponseErrorException
	 */
	public function testInvalidRequest()
	{
		$this->_client->sendRequest(new Request\Request('nonexisting'));
	}

	/**
	 * @expectedException SimpleJsonRpcClient\Exception\ClientException
	 * @expectedExceptionMessage Unauthorized
	 */
	public function testInvalidCredentials()
	{
		$this->_client = new Client\HttpPostClient('http://'.WEB_SERVER_HOST.':'.WEB_SERVER_PORT);
		$this->_client->sendRequest(new Request\Request('add', array('x'=>1, 'y'=>2)));
	}

	public function testBatchRequest()
	{
		// The Zend JSON Server doesn't support batch requests
	}

}
