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
		// TODO
		return true;
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
				$_installed_plugins[] = trim($_key,'/');
			}
		}
		
		return $_installed_plugins;
		
	}
	 
}

if ( !function_exists('extendMenuWithPlugins'))
{
	/**
	 * 
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
		
		$tree = recursiveWalk($menu, '/', $tree);
		$installed = getInstalledPlugins();
		
		$items = array();
		
		foreach($installed as $plugin)
		{
			if( isPluginActive($plugin) )
			{
				$plugin_items = getPluginInfo($plugin);
				$items = array_merge($items, $plugin_items['menu'] );
			}
		}
		
		foreach($items as $item => $content)
		{
			$tmp = explode('/', $item, -1);
			$path = join('/', $tmp);
			if($path == '') $path = '/';
				
			$tmp = explode($path, $item);
			$slug = ltrim(end($tmp), '/');
			
			if($path == '/')
			{
				$tree['/'][$slug] = $content;
				if( array_key_exists('sub', $content) )
				{
					$path = '/'.$slug;
					$tree[$path] = &$tree['/'][$slug];
				}
			}
			else
			{
				$tree[$path]['sub'][$slug] = $content;
				if( array_key_exists('sub', $content) )
				{
					$path2 = rtrim($path,'/').'/'.$slug;
					$tree[$path2] = &$tree[$path]['sub'][$slug];
				}
			}
			
		}
	}
}


?>
