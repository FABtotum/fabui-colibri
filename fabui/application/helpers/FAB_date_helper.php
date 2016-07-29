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
	 * @param	string	classname
	 * @param	string	menu name
	 * @param	mixed	attributes
	 * @return	string
	 */
	function timezone_menu($class = '', $name = 'timezones', $attributes = '')
	{
		$zones_array = array();
		$timestamp = time();
		foreach(timezone_identifiers_list() as $key => $zone) {
			date_default_timezone_set($zone);
			$zones_array[$key]['zone'] = $zone;
			$zones_array[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
		}
		$html = '<select class="'.$class.'" name="'.$name.'" '._stringify_attributes($attributes).'>';
		foreach($zones_array as $t){
			$html .= '<option value="'.$t['zone'].'">'.($t['diff_from_GMT'] . ' - ' . $t['zone']).'</option>';
		}
		$html .= '</select>';
		return $html;
	}
}

?>