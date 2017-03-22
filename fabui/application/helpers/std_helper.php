<?php
/**
 * 
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('initializeSteps'))
{
	/**
	 * 
	 */
	function initializeSteps($steps)
	{
		$__step_count = 1;
		foreach($steps as $__idx => $step){ 
			if(!array_key_exists('number', $step))
				$steps[$__idx]['number'] = -1;
				
			if( $steps[$__idx]['number'] == -1) 
				$steps[$__idx]['number'] = $__step_count++; 
		}
		return $steps;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getStepNumber'))
{
	/**
	 * 
	 */
	function getStepNumber($steps, $step_name)
	{
		foreach($steps as $__idx => $step){ 1;
			if( array_key_exists('name', $steps[$__idx]) ) 
			{
				if ( ($steps[$__idx]['name'] == $step_name) 
				  && ($steps[$__idx]['number'] != -1) 
				) {
					return $steps[$__idx]['number'];
				}
			}
		}
		
		return -1;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('hasStep'))
{
	/**
	 * 
	 */
	function hasStep($steps, $step_name)
	{
		foreach($steps as $__idx => $step){ 1;
			if( array_key_exists('name', $steps[$__idx]) ) 
			{
				if($steps[$__idx]['name'] == $step_name)
					return true;
			}
		}
		
		return false;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getActiveStep'))
{
	/**
	 * 
	 */
	function getActiveStep($steps)
	{
		foreach($steps as $__idx => $step){ 1;
			if( array_key_exists('active', $steps[$__idx]) ) 
			{
				if($steps[$__idx]['active'] == true)
					return $__idx+1;
			}
		}
		
		return 1;
	}
}
 
?>
