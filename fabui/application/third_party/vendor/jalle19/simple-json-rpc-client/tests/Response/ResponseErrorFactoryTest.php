<?php

/**
 * Tests for ResponseErrorFactory
 *
 * @author Sam Stenvall <neggelandia@gmail.com>
 * @copyright Copyright &copy; Sam Stenvall 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
class ResponseErrorFactoryTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @dataProvider errorProvider
	 */
	public function testCreate($errorJson)
	{
		$error = new \SimpleJsonRpcClient\Response\Error($errorJson);
		$exception = \SimpleJsonRpcClient\Exception\ResponseErrorFactory::create($error);

		$this->assertEquals(get_class($exception), $error->message);
	}

	public function errorProvider()
	{
		$dataProvider = array();
		$codeExceptionMap = array(
			-32700=>'SimpleJsonRpcClient\Exception\ParseErrorException',
			-32600=>'SimpleJsonRpcClient\Exception\InvalidRequestException',
			-32601=>'SimpleJsonRpcClient\Exception\MethodNotFoundException',
			-32602=>'SimpleJsonRpcClient\Exception\InvalidParamsException',
			-32603=>'SimpleJsonRpcClient\Exception\InternalErrorException',
			-32000=>'SimpleJsonRpcClient\Exception\ServerErrorException',
			-32099=>'SimpleJsonRpcClient\Exception\ServerErrorException',
			-32769=>'SimpleJsonRpcClient\Exception\ResponseErrorException',
		);

		foreach ($codeExceptionMap as $code=> $exception)
		{
			$error = new stdClass();
			$error->code = $code;
			$error->message = $exception;

			$dataProvider[] = array(json_encode($error));
		}

		return $dataProvider;
	}

}
