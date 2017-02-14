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
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('loadTranslation'))
{
	/**
	 * 
	 */
	function loadTranslation()
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		
		if(isset($CI->session->user['settings']['language'])){
			$language_code = $CI->session->user['settings']['language'];
		}else{
			$language_code = 'en_US';
		}
		
		putenv('LC_ALL='.$language_code.'.UTF-8');
		setlocale(LC_ALL, $language_code.'.UTF-8');
		bindtextdomain("fabui", $CI->config->item('locale_path'));
		textdomain("fabui");
		
	}
}
?>
