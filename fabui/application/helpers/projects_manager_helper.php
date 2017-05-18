<?php
/**
 * @file fabtotum_helper.php
 * @brief FABtotum helper function
 *
 * @author Krios Mane <km@fabtotum.com>
 * @author Daniel Kesler <dk@fabtotum.com>
 * @version 0.1
 * @copyright https://opensource.org/licenses/GPL-3.0
 *
 */
if ( !function_exists('get_make_default_action'))
{
	/***
	 * 
	 */
	function get_make_default_action($id_file)
	{
		$CI =& get_instance();
		$CI->load->model('Files', 'files');
		$CI->load->helper('file_helper');
		$CI->load->helpers('plugin_helper');
		
		$file = $CI->files->get($id_file, 1);
		$ext = getFileExtension($file['full_path']);

		$default_action = array();

		$builtin_actions = array();
		
		if($file['print_type'] == 'additive'){

			$builtin_actions[] = array(
					"title" => _("Print"),
					"icon" => "fa-rotate-90 fa-play",
					"url" => "#make/print/".$id_file
			);
			$default_action = $builtin_actions[0];

		}else if($file['print_type'] == 'subtractive'){
			
			$builtin_actions[] = array(
					"title" => _("Mill"),
					"icon" => "fa-rotate-90 fa-play",
					"url" => "#make/mill/".$id_file
			);
			$default_action = $builtin_actions[0];
		}
		
		$plugin_actions = array();
		$actions = getFileActionList($ext, $file['print_type']);

		foreach($actions as $action)
		{
			$plugin_actions[] = array(
					'title' => $action['title'],
					'icon' => $action['icon'],
					'url' => '#'.str_replace('$1', $id_file, $action['url'] )
			);
		}

		if( count($builtin_actions) == 0)
		{
			if( count($plugin_actions) > 0 ) // Plugins provide an actions
			{
				$default_action = $plugin_actions[0];
				unset($plugin_actions[0]);
			}
			else
			{
				$default_action = $builtin_actions[0];
				unset($builtin_actions[0]);
			}
		}
		else // There are some built
		{
			unset($builtin_actions[0]);
		}
		
		return $default_action;
	}
}
?>
