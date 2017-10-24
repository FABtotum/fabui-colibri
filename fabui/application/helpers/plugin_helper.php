<?php
/**
 * 
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */

if ( ! function_exists('isPluginActive'))
{
	/**
	 * @return plugin metadata info
	 */
	function isPluginActive($plugin_slug){
		$CI =& get_instance();
		$CI->load->model('Plugins', 'plugins');
		
		return $CI->plugins->isActive($plugin_slug);
	}
}
 
if ( ! function_exists('getPluginInfo'))
{
	/**
	 * @return plugin metadata info
	 */
	function getPluginInfo($plugin_slug){
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$plugins_path = $CI->config->item('plugins_path');
		$plugin_dir = $plugins_path.$plugin_slug;
		$meta_file = $plugin_dir.'/meta.json';
		if(is_dir($plugin_dir))
		{
			if(file_exists($meta_file))
			{
				return json_decode(file_get_contents($meta_file), true);
			}
		}
		
		return false;
	}
}

if ( ! function_exists('getActivePlugins'))
{
	/**
	 * @return all active plugins
	 */
	function getActivePlugins(){
		$CI =& get_instance();
		$CI->load->model('Plugins', 'plugins');
		
		$plugins = $CI->plugins->get();
		
		$result = array();
		
		if( $plugins )
		{
			foreach($plugins as $plugin)
			{
				$info = json_decode($plugin['attributes'], true);
				//~ $result[$info['plugin_slug']] = $info;
				$slug = $info['plugin_slug'];
				$result[$slug] = getPluginInfo($slug);
			}
		}
		
		return $result;
	}
}

if ( ! function_exists('getInstalledPlugins'))
{
	/**
	 * @return all installed plugins
	 */
	function getInstalledPlugins(){
		
		$CI =& get_instance();
		
		$CI->load->helper('directory');
		$CI->config->load('fabtotum');
		$plugins_path = $CI->config->item('plugins_path');
		$_plugins_folders = directory_map($plugins_path);
		
		$_installed_plugins = array();
		
		foreach($_plugins_folders as $_key => $_value)
		{
			if(is_dir($plugins_path.$_key))
			{
				$slug = trim($_key,'/');
				if(file_exists($plugins_path.$_key.'meta.json'))
				{
					$_installed_plugins[$slug] = getPluginInfo($slug);
				}
			}
		}
		
		return $_installed_plugins;
	}
	 
}

if ( !function_exists('getOnlinePlugins'))
{
	/**
	 * @return all online plugins
	 */
	function getOnlinePlugins(){
		$CI =& get_instance();
		$CI->load->helper('os_helper');
		$CI->config->load('fabtotum');
		$repo = getRemoteFile($CI->config->item('plugins_endpoint').'cached.json', false);
		if($repo != false){
			return json_decode($repo, true);
		}else{
			return false;
		}
		
	}
}

if ( !function_exists('extendMenuWithPlugins'))
{
	/**
	 * Extend menu structure with "menu" specified in active plugins meta.json
	 */
	function extendMenuWithPlugins(&$menu)
	{
		
		function recursiveWalk(&$items, $path = '/', $tree)
		{    
			foreach($items as $item => &$content)
			{
				$tree[$path . $item] =& $content;
				if( array_key_exists('sub', $content) )
				{
					$tree = recursiveWalk($content['sub'], $path . $item . '/', $tree);
				}
			}
			
			return $tree;
		}
	
		$path = '/';    
		$tree = array('/' => &$menu);
		
		// generate a map of menu paths and corresponding meni item &reference
		$tree = recursiveWalk($menu, '/', $tree);
		#$installed = getInstalledPlugins();
		$active = getActivePlugins();
		
		
		$items = array();
		
		// Read all menu entries from all active plugins 
		// and merge them into one array
		foreach($active as $plugin => $info)
		{
			if(isset($info['menu']))
				$items = array_merge($items, $info['menu'] );
		}
		
		foreach($items as $item => $content)
		{
			$tmp = explode('/', $item, -1);
			$path = join('/', $tmp);
			if($path == '') $path = '/';
				
			$tmp = explode($path, $item);
			$slug = ltrim(end($tmp), '/');
			
			// Menu item path is the root of menu
			if($path == '/')
			{
				$tree['/'][$slug] = $content;
				if( !array_key_exists('url', $content) )
				{
					// Add the new item to the path tree map
					$path = '/'.$slug;
					$tree[$path] = &$tree['/'][$slug];
				}
			}
			else // Item is a sub-menu
			{
				$tree[$path]['sub'][$slug] = $content;
				if( !array_key_exists('url', $content) )
				{
					$path2 = rtrim($path,'/').'/'.$slug;
					$tree[$path2] = &$tree[$path]['sub'][$slug];
				}
			}
			
		}
	}
}

 
if ( ! function_exists('managePlugin'))
{
	/**
	 * Plugin manager wrapper.
	 * @param $action activate|deactivate|remove
	 * @param $plugin Plugin slug.
	 */
	function managePlugin($action, $plugin){
		
		$CI =& get_instance();
		$CI->load->helper('fabtotum');
		
		return startPyScript('plugin_manager.py', $action.' -p "'.$plugin.'"', false, true);
	}
	 
}

if ( ! function_exists('extendAllowedTypesWithPlugins'))
{
	/**
	 * Extend allowed types by filetypes specified in active plugin meta.json.
	 */
	function extendAllowedTypesWithPlugins($allowed_types)
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		
		$allowed_types = explode('|', $allowed_types);
		
		$plugins = getActivePlugins();
		
		foreach($plugins as $plugin => $info)
		{
			if(isset($info['menu']))
			{
				foreach($info['filetypes'] as $ext)
				{
					$allowed_types[] = $ext;
				}
			}
		}
		
		return join($allowed_types, '|');
	}
}

if ( ! function_exists('getFileActionList'))
{
	/**
	 * Return a list of file actions provided by active plugins
	 * @param ext File extension
	 * @param type File print type
	 * @return array of file-action hooks
	 */
	function getFileActionList($ext, $type = '')
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$plugins = getActivePlugins();
		
		$action_type = 'file-action';
		
		$actions = array();
		
		foreach($plugins as $plugin => $info)
		{
			foreach($info['hooks'] as $hook)
			{
				// safety check if "action" is defined
				if( array_key_exists("action",$hook) && array_key_exists("filetypes", $hook) )
				{
					$type_match = false;
					if(array_key_exists('printtypes', $hook))
					{
						$type_match = in_array($type, $hook['printtypes']);
					}
					
					$ext_match = false;
					if(array_key_exists('filetypes', $hook))
					{
						$ext_match = in_array($ext, $hook['filetypes']);
					}
					// check if the actions is what we are looking for
					if( $hook['action'] == $action_type && ($ext_match || $type_match) )
					{
						$actions[] = $hook;
					}
				}
			}
		}
		
		return $actions;
	}
}

if ( ! function_exists('checkPluginManufacturing') )
{
	/**
	 * @param (string) $file_path
	 * @param (int) $numLines, number of lines to read
	 * retunr what type of manufactoring file is
	 */
	function checkPluginManufacturing($filePath, $numLines = 500)
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$plugins = getActivePlugins();
		
		$manufactoring = '';
		
		$action_type = 'check-manufacturing';
		
		$params = '-f ' . $filePath . ' -n ' . $numLines;
		
		foreach($plugins as $plugin => $info)
		{
			foreach($info['hooks'] as $hook)
			{
				// safety check if "script" is defined
				if( array_key_exists("script",$hook) )
				{
					
					// safety check if "filetypes" is defined
					if( array_key_exists("filetypes", $hook) )
					{
						$ext = getFileExtension($filePath);
						if( !in_array($ext, $hook['filetypes']) )
							continue;
					}
					
					$manufactoring = startPluginPyScript($hook['script'], $params, false, false, $info['plugin_slug']);
					$manufactoring = trim($manufactoring);
					if($manufactoring != '')
						return $manufactoring;
				}
			}
		}
		
		return $manufactoring;
	}
}

if ( ! function_exists('getObjectActionList'))
{
	/**
	 * Return a list of object actions provided by active plugins
	 * @param ext File extension
	 * @return array of object-action hooks
	 */
	function getObjectActionList($ext)
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$plugins = getActivePlugins();
		
		$action_type = 'object-action';
		
		$actions = array();
		
		foreach($plugins as $plugin => $info)
		{
			foreach($info['hooks'] as $hook)
			{
				// safety check if "action" is defined
				if( array_key_exists("action",$hook) && array_key_exists("tags", $hook) )
				{
					// check if the actions is what we are looking for
					if( $hook['action'] == $action_type )
					{
						$actions[] = $hook;
					}
				}
			}
		}
		
		return $actions;
	}
}

if ( ! function_exists('plugin_url'))
{
	function plugin_url($url='', $ajax_firendly=false, $standalone=false)
	{
		$CI =& get_instance();
		$plugin_name = str_replace('plugin_', '', $CI->router->class);
		if($ajax_firendly)
			return 'plugin/'.$plugin_name.'/'.$url;
		else
			return $standalone ? '/fabui/plugin/'.$plugin_name.'/'.$url : '/plugin/'.$plugin_name.'/'.$url;
			//return '/plugin/'.$plugin_name.'/'.$url;
	}
}

if ( ! function_exists('plugin_assets_url'))
{
	function plugin_assets_url($url)
	{
		$CI =& get_instance();
		$plugin_name = str_replace('plugin_', '', $CI->router->class);
		return '/assets/plugin/'.$plugin_name.'/'.$url;
	}
}

if ( ! function_exists('plugin_path'))
{
	function plugin_path($plugin_name = '')
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		if( $plugin_name == '')
			$plugin_name = str_replace('plugin_', '', $CI->router->class);
			
		$plugins_path = $CI->config->item('plugins_path');
		return $plugins_path . $plugin_name;
	}
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('startPluginPyScript'))
{
	/**
	 * start python task
	 */
	function startPluginPyScript($script, $params = '', $background = true, $sudo = false, $plugin_name = '')
	{
		$CI =& get_instance();
		$CI->load->helper('fabtotum_helper');
		$CI->config->load('fabtotum');
		//~ $extPath = $CI->config->item('ext_path');
		$extPath = plugin_path($plugin_name) . '/scripts/';
		// TODO: check trailing /
		$cmd = 'python';
		if($sudo)
			$cmd = 'sudo ' . $cmd;
			
		return doCommandLine($cmd, $extPath.'py/'.$script, $params, $background);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('startPluginBashScript'))
{
	/**
	 * start bash script
	 */
	function startPluginBashScript($script, $params = '', $background = true, $sudo = false, $plugin_name = '')
	{
		$CI =& get_instance();
		$CI->load->helper('fabtotum_helper');
		$CI->config->load('fabtotum');
		//~ $extPath = $CI->config->item('ext_path');
		$extPath = plugin_path($plugin_name) . '/scripts/';
		// TODO: check trailing /
		$cmd = 'bash';
		if($sudo)
			$cmd = 'sudo ' . $cmd;
			return doCommandLine($cmd, $extPath.'bash/'.$script, $params, $background);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('startPluginScript'))
{
	/**
	 * start script from sub-directory
	 */
	function startPluginScript($script, $subdir='bash', $params = '', $background = true, $sudo = false, $plugin_name = '')
	{
		$CI =& get_instance();
		$CI->load->helper('fabtotum_helper');
		$CI->config->load('fabtotum');
		//~ $extPath = $CI->config->item('ext_path');
		$extPath = plugin_path($plugin_name) . '/scripts/';
		// TODO: check trailing /
		switch($subdir)
		{
			case 'bash':
				$cmd = 'bash';
				break;
			case 'py':
				$cmd = 'python';
				break;
			case 'php':
				$cmd = 'php';
				break;
			default:
				return 'Unknown script type';
		}
		
		if($sudo)
			$cmd = 'sudo ' . $cmd;
			return doCommandLine($cmd, $extPath.$subdir.'/'.$script, $params, $background);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('createPlugin'))
{
	/**
	 * start python task
	 */
	function createPlugin($meta)
	{
		$CI =& get_instance();
		$CI->load->helper('fabtotum_helper');
		$CI->config->load('fabtotum');
		$tmpPath = $CI->config->item('temp_path');
		
		file_put_contents($tmpPath.'new_plugin_meta.json', json_encode($meta));
		
		$params = array(
			'plugin' => '',
			'-i' => $tmpPath.'new_plugin_meta.json',
			'-d' => $tmpPath
		);
		
		startPyScript('fab_creator.py', $params, false);
		unlink($tmpPath.'new_plugin_meta.json');
		return $tmpPath.$meta['plugin']['slug'];
	}
}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('extendLanguageWithPlugins'))
{
	/**
	 * hook for setLanguage - language_helper.php
	 */
	function extendLanguageWithPlugins()
	{
		$active = getActivePlugins();
		
		foreach($active as $plugin => $info)
		{
			loadPluginTranslation($plugin);
		}
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('loadPluginTranslation'))
{
	/**
	 * load plugin translation
	 */
	function loadPluginTranslation($plugin = '', $domain = 'plugin')
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		
		if($plugin == '') $plugin = str_replace('plugin_', '', $CI->router->class);

		if(file_exists($CI->config->item('plugins_path').$plugin.'/locale')){
			
			bindtextdomain($domain, $CI->config->item('plugins_path').$plugin.'/locale');
			textdomain($domain);
			bind_textdomain_codeset($domain, "UTF-8");
			
			return true;
		}
		
		return false;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('loadPluginConfig'))
{
	/**
	 * Load Plugin Config File
	 *
	 * @param	string	$file			Configuration file name
	 * @param	bool	$use_sections		Whether configuration values should be loaded into their own section
	 * @return	bool	TRUE if the file was loaded correctly or FALSE on failure
	 */
	function loadPluginConfig($file='', $use_sections = FALSE)
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		
		$plugin = str_replace('plugin_', '', $CI->router->class);
		
		$config_plugin_path = $CI->config->item('plugins_path').$plugin.'/config/';
		
		$file_path = $config_plugin_path.$file.'.php';
		
		if(file_exists($file_path))
		{
			include($file_path);
			
			if( ! isset($config) OR !is_array($config))
			{				
				show_error('Your '.$file_path.' file does not appear to contain a valid configuration array.');
			}
			
			if ($use_sections === TRUE)
			{
				$CI->config->config[$file] = isset($CI->config->config[$file]) ? array_merge($CI->config->config[$file], $config) : $config;
			}
			else
			{
				$CI->config->config = array_merge($CI->config->config, $config);
			}
			
			$CI->config->is_loaded[] = $file_path;
			$config = NULL;
			log_message('debug', 'Config file loaded: '.$file_path);
			
			return true;
			
		}
		show_error('The configuration file '.$file.'.php does not exist.');
	}
}
?>
