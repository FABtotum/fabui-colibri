<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
if ( !function_exists('arrayFromPost'))
{
	/**
	 * 
	 * crate list array from post data, converting keys with '-' in arrays
	 * 
	 * es: feeder-show => $item['feeder']['show']
	 * 
	 */
	function arrayFromPost($data)
	{
		$stringToEval = '$stringArray = array(); ';
		foreach($data as $key => $value){
			$temp = explode('-', $key);
			if(count($temp)> 1){
				$tempString = '$stringArray';
				foreach($temp as $k){
					$tempString .= '["'.$k.'"]';
				}
				$stringToEval .= $tempString;
			}else{
				$stringToEval .= '$stringArray["'.$key.'"]';
			}
			$stringToEval .= ' = "'.$value.'";';
		}
		return eval($stringToEval.' return $stringArray;');
	}	
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('is_assoc'))
{
	/**
	 * 
	 * 
	 */
	function is_assoc($array)
	{
		// Keys of the array	
		$keys = array_keys($array);
		// If the array keys of the keys match the keys, then the array must
    	// not be associative (e.g. the keys array looked like {0:0, 1:1...}).
		return array_keys($keys) !== $keys;
	}
} 
?>