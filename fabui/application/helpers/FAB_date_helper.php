<?php
/**
 *
 * @author Krios Mane
 * @autor Fabtotum Development Team
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 *
 */

if ( ! function_exists('timezone_menu'))
{
	/**
	 * Timezone Menu
	 *
	 * Generates a drop-down menu of timezones.
	 *
	 * @param	string	menu name
	 * @param	mixed	selected
	 * @return	string
	 */
	function timezone_menu($name = 'timezones', $selected = '', $attributes = '')
	{
		$CI =& get_instance();
		$CI->load->helper('form');
		
		$zones_array = array();
		$timestamp = time();
		
		
		foreach(timezone_identifiers_list() as $key => $zone) {
			date_default_timezone_set($zone);			
			$zones_array[$zone] = 'UTC/GMT ' . date('P', $timestamp).' - '.$zone;
		}
		
		return form_dropdown($name, $zones_array, $selected, $attributes);
	}
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(! function_exists('months_menu'))
{
	/**
	 * Months menu
	 * 
	 * Generates a drop-down menu of months
	 * 
	 * @param string name
	 * @param mixed selected
	 * @param mixed attributes
	 * 
	 * return string
	 */
	function months_menu($name = 'months', $selected = '', $attributes = '')
	{
		$CI =& get_instance();
		$CI->load->helper('form');
		
		$months_list[1]  = _("Jannuary");
		$months_list[2]  = _("February");
		$months_list[3]  = _("March");
		$months_list[4]  = _("April");
		$months_list[5]  = _("May");
		$months_list[6]  = _("June");
		$months_list[7]  = _("July");
		$months_list[8]  = _("August");
		$months_list[9]  = _("September");
		$months_list[10] = _("October");
		$months_list[11] = _("November");
		$months_list[12] = _("December");

		return form_dropdown($name, $months_list, $selected, $attributes);
	}
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(! function_exists('days_menu'))
{
	/**
	 * Months menu
	 *
	 * Generates a drop-down menu of days of the month
	 *
	 * @param string name
	 * @param mixed selected
	 * @param mixed attributes
	 *
	 * return string
	 */
	function days_menu($name = 'days', $selected = '', $attributes = '')
	{
		$CI =& get_instance();
		$CI->load->helper('form');
		
		$days_list = array();
		
		for($i = 1; $i<32; $i++){
			$days_list[$i] = $i;
		}
		
		return form_dropdown($name, $days_list, $selected, $attributes);
	}
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(! function_exists('years_menu'))
{
	/**
	 * Months menu
	 *
	 * Generates a drop-down menu of days of the month
	 *
	 * @param string name
	 * @param mixed selected
	 * @param mixed attributes
	 *
	 * return string
	 */
	function years_menu($name = 'years', $min = 1975, $max = 2015, $selected = '', $attributes = '')
	{
		$CI =& get_instance();
		$CI->load->helper('form');
		$years = array();
		
		if($min < 2017){
		    $min = 2020;
		}
		
		for($i = $max; $i <= $min; $i++){
			$years[$i] = $i;
		}
		
		return form_dropdown($name, $years, $selected, $attributes);
	}
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(! function_exists('hours_menu'))
{
	/**
	 * Months menu
	 *
	 * Generates a drop-down menu of days of the month
	 *
	 * @param string name
	 * @param mixed selected
	 * @param mixed attributes
	 *
	 * return string
	 */
	function hours_menu($name = 'hours',  $selected = '', $attributes = '')
	{
		$CI =& get_instance();
		$CI->load->helper('form');
		
		$hours_list = array();
		
		for($i = 1; $i <= 24; $i++){
			$hours_list[$i] = $i;
		}
		
		return form_dropdown($name, $hours_list, $selected, $attributes);
	}
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(! function_exists('minutes_menu'))
{
	/**
	 * Months menu
	 *
	 * Generates a drop-down menu of days of the month
	 *
	 * @param string name
	 * @param mixed selected
	 * @param mixed attributes
	 *
	 * return string
	 */
	function minutes_menu($name = 'minutes',  $selected = '', $attributes = '')
	{
		$CI =& get_instance();
		$CI->load->helper('form');
		
		$minutes_list = array();
		
		for($i = 1; $i <= 60; $i++){
			$minutes_list[$i] = $i;
		}
		
		return form_dropdown($name, $minutes_list, $selected, $attributes);
	}
}
?>