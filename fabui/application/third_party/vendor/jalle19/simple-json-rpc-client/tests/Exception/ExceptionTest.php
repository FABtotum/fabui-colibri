<?php

use SimpleJsonRpcClient\Exception;

/**
 * Pretty useless test, but it tests that all exceptions derive from 
 * BaseException.
 *
 * @author Sam Stenvall <neggelandia@gmail.com>
 * @copyright Copyright &copy; Sam Stenvall 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
class ExceptionTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @dataProvider exceptionProvider
	 */
	public function testInheritance($exception)
	{
		try
		{
			throw $exception;
		}
		catch (\Exception $e)
		{
			$this->assertTrue($e instanceof Exception\BaseException);
		}
	}

	public function exceptionProvider()
	{
		$error = new stdClass();
		$error->message = 'Message';
		$error->code = 1;

		return array(
			array(new Exception\ResponseErrorException(new \SimpleJsonRpcClient\Response\Error(json_encode($error)))),
			array(new Exception\InvalidResponseException('Message')),
			array(new Exception\ClientException('Message')),
		);
	}

}
