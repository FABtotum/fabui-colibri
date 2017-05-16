<?php

namespace SimpleJsonRpcClient\Client;

use SimpleJsonRpcClient\Response;
use SimpleJsonRpcClient\Request;
use SimpleJsonRpcClient\Exception\ClientException;

/**
 * Client implementation which sends requests using HTTP POST, using Zend for 
 * HTTP functionality.
 *
 * @author Sam Stenvall <neggelandia@gmail.com>
 * @copyright Copyright &copy; Sam Stenvall 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
class HttpPostClient extends BaseClient implements ClientInterface
{

	/**
	 * @var \Zend\Http\Client the HTTP client
	 */
	private $_httpClient;

	/**
	 * @var string the JSON-RPC API end-point URL
	 */
	private $_endPoint;

	/**
	 * @var string the username used with HTTP authentication
	 */
	private $_username;

	/**
	 * @var string password the username used with HTTP authentication
	 */
	private $_password;

	/**
	 * Class constructor
	 * @param string $endPoint the URL JSON-RPC API endpoint
	 * @param string $username (optional) username to use
	 * @param string $password (optional) password to use
	 * @param int $flags flags for the client
	 * @param array $options options to pass to the underlying HTTP client
	 */
	public function __construct($endPoint, $username = null, $password = null, 
			$flags = self::FLAG_NONE, $options = array())
	{
		parent::__construct($flags);

		$this->_endPoint = $endPoint;
		$this->_username = $username;
		$this->_password = $password;

		// Initialize the HTTP client
		$clientOptions = array_merge(array('keepalive'=>true), $options);
		
		$this->_httpClient = new \Zend\Http\Client();
		$this->_httpClient->setOptions($clientOptions);

		if ($this->_username && $this->_password)
			$this->_httpClient->setAuth($this->_username, $this->_password);
	}

	public function sendRequest(Request\Request $request)
	{
		$httpRequest = $this->createHttpRequest($request);
		$httpResponse = $this->performHttpRequest($httpRequest);

		return new Response\Response($httpResponse->getContent());
	}

	public function sendNotification(Request\Notification $notification)
	{
		$httpRequest = $this->createHttpRequest($notification);
		$this->performHttpRequest($httpRequest, false);
	}
	
	public function sendBatchRequest(Request\BatchRequest $batchRequest)
	{
		$httpRequest = $this->createHttpRequest($batchRequest);
		$httpResponse = $this->performHttpRequest($httpRequest);
		
		return new Response\BatchResponse($httpResponse->getContent());
	}
	
	/**
	 * Creates a new HTTP POST request with the appropriate content, headers 
	 * and such, which can then be used to send JSON-RPC requests.
	 * @param string $content the request content
	 * @return \Zend\Http\Request the request
	 * @throws ClientException if the Content-Type header cannot be set
	 */
	private function createHttpRequest($content)
	{
		$httpRequest = new \Zend\Http\Request();
		$httpRequest->setUri($this->_endPoint);
		$httpRequest->setMethod(\Zend\Http\Request::METHOD_POST);
		$httpRequest->setContent($content);

		// Set headers
		$headers = $httpRequest->getHeaders();
		
		if (!($headers instanceof \Zend\Http\Headers))
			throw new ClientException('Unable to configure HTTP headers');

		$headers->addHeaderLine('Content-Type', 'application/json');
		$httpRequest->setHeaders($headers);

		return $httpRequest;
	}
	
	/**
	 * Performs the specified HTTP request and returns the HTTP response
	 * @param \Zend\Http\Request $httpRequest the request
	 * @param boolean $ensureSuccess throw exception if the request was 
	 * unsuccessful
	 * @return \Zend\Http\Response the response
	 * @throws ClientException if the request was unsuccessful
	 */
	private function performHttpRequest($httpRequest, $ensureSuccess = true)
	{
		$httpResponse = null;
	
		// See if the requests succeeds at all
		try
		{
			$httpResponse = $this->_httpClient->dispatch($httpRequest);
		}
		catch (\Exception $e)
		{
			if ($ensureSuccess)
				throw new ClientException($e->getMessage(), $e->getCode());
		}

		// Check the request status
		if ($ensureSuccess && $httpResponse !== null && !$httpResponse->isSuccess())
		{
			throw new ClientException(
			$httpResponse->getReasonPhrase(), $httpResponse->getStatusCode());
		}

		return $httpResponse;
	}

}
