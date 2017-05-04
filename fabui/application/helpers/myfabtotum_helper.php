<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 * 
 * 
 * 
 */
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * MY.FABTOTUM.COM Helper
 * 
 */
if(!function_exists('callMyFabtotum'))
{
	/**
	 * Call remote service on my.fabtotum.com
	 * 
	 * @param  string $method method name
	 * @param  array  $args   arguments
	 * @return array  response from my.fabtotu.com services
	 */
	function callMyFabtotum($method, $args = array())
	{
		$CI =& get_instance();
		//load config
		$CI->config->load('fabtotum');
		$init['url'] = $CI->config->item('myfabtotum_url');
		//init jsonRPC library
		$CI->load->library('JsonRPC', $init, 'jsonRPC');
		//set api version
		$args['apiversion'] = $CI->config->item('myfabtotum_api_version');
		
		return $CI->jsonRPC->execute($method, $args);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if (!function_exists('fab_register_printer'))
{
	/**
	 * Register the printer into my.fabtotum.com 
	 * 
	 * @param string $fabid email used for my.fabtotum.com account
	 * @return 
	 */
	function fab_register_printer($fabid)
	{
		$CI =& get_instance();
		$CI->load->helpers(array('fabtotum_helper', 'os_helper'));
		
		$args = array();
		
		$args['fabid']    = $fabid;
		$args['serialno'] = getSerialNumber();
		$args['mac']      = getMACAddres();
		
		return callMyFabtotum('fab_register_printer', $args);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if (!function_exists('fab_info_update'))
{
	/**
	 * Insert or update infos about the printer to my.fabtotum.com's database
	 */
	function fab_info_update()
	{
		$CI =& get_instance();
		$CI->load->helpers(array('fabtotum_helper', 'os_helper'));
		
		$interfaces = getInterfaces();
		$head       = getInstalledHeadInfo();
		
		$macroResponse = doMacro('version');
		if($macroResponse['response']){
			$versions = $macroResponse['reply'];
		}
		
		$args = array();
		$args['serialno'] = getSerialNumber();
		$args['mac']      = getMACAddres();
		$args['data']     = array(
			'name'      => getUnitName(),
			'model'     => isset($versions['production']['batch']) ? $versions['production']['batch'] : '',
			'head'      => $head['name'],
			'fwversion' => isset($versions['firmware']['version']) ? $versions['firmware']['version'] : '',
			'iplan'     => isset($interfaces['wlan0']['ipv4_address']) ? $interfaces['wlan0']['ipv4_address'] : ''
		);
		return callMyFabtotum('fab_info_update', $args);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if (!function_exists('fab_polling'))
{
	/**
	 * check for remote commands
	 * 
	 */
	function fab_polling()
	{
		$CI =& get_instance();
		$CI->load->helpers(array('fabtotum_helper', 'os_helper'));
		
		$args = array();
		$args['serialno'] = getSerialNumber();
		$args['mac']      = getMACAddres();
		$args['state']    = getState();
		$args['data']     = array();
		
		return callMyFabtotum('fab_polling', $args);
	}
}