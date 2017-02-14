<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getAvailableLanguages'))
{
	/**
	 * Get available languages
	 *
	 * Get a list of available languages.
	 * 
	 * @return	list Array
	 */
	function getAvailableLanguages()
	{
		$ini = parse_ini_file("/var/lib/fabui/lang.ini", true);
		unset($ini['language']);
		return $ini;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('langauges_menu'))
{
	/**
	 * Languages Menu
	 *
	 * Generates a drop-down menu of available languages.
	 *
	 * @param	string	classname
	 * @param	string	menu name
	 * @param	mixed	attributes
	 * @return	string
	 */
	function langauges_menu($class = '', $name = 'languages', $attributes = '')
	{
		$languages = getAvailableLanguages();		
		$html = '<select class="'.$class.'" name="'.$name.'" '._stringify_attributes($attributes).'>';
		foreach($languages as $name => $lang){
			$html .= '<option value="'.$lang['code'].'">'.$lang['description'].'</option>';
		}
		$html .= '</select>';
		return $html;
	}
}
?>
