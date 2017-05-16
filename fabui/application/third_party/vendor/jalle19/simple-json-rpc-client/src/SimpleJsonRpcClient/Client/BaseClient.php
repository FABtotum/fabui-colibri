<?php

namespace SimpleJsonRpcClient\Client;

/**
 * Base JSON-RPC client. All JSON-RPC clients should extend from this class and 
 * implement the ClientInterface interface.
 * 
 * This class only contains functionality for setting flags that can alter the 
 * behavior of the library. 
 *
 * @author Sam Stenvall <neggelandia@gmail.com>
 * @copyright Copyright &copy; Sam Stenvall 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
class BaseClient
{

	const FLAG_NONE = 0;

	/**
	 * When set, the client should attempt to handle malformed UTF-8 by first 
	 * decoding the JSON and then re-encoding it as UTF-8 before attempting the 
	 * actual parsing.
	 */
	const FLAG_ATTEMPT_UTF8_RECOVERY = 1;

	/**
	 * @var int the flags that have been set
	 */
	public static $flags;

	/**
	 * Class constructor. Flags are set here.
	 * @param int $flags the desired flags
	 */
	public function __construct($flags = self::FLAG_NONE)
	{
		self::$flags = $flags;
	}

}
