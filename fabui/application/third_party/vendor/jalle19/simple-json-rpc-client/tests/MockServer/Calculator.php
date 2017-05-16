<?php

/**
 * Calculator - sample class to expose via JSON-RPC
 *
 * @author http://framework.zend.com/manual/2.1/en/modules/zend.json.server.html
 */
class Calculator
{

	/**
	 * Return sum of two variables
	 *
	 * @param  int $x
	 * @param  int $y
	 * @return int
	 */
	public function add($x, $y)
	{
		return $x + $y;
	}

	/**
	 * Return difference of two variables
	 *
	 * @param  int $x
	 * @param  int $y
	 * @return int
	 */
	public function subtract($x, $y)
	{
		return $x - $y;
	}

	/**
	 * Return product of two variables
	 *
	 * @param  int $x
	 * @param  int $y
	 * @return int
	 */
	public function multiply($x, $y)
	{
		return $x * $y;
	}

	/**
	 * Return the division of two variables
	 *
	 * @param  int $x
	 * @param  int $y
	 * @return float
	 */
	public function divide($x, $y)
	{
		return $x / $y;
	}
	
	/**
	 * Notification test method
	 */
	public function start()
	{
		
	}

}
