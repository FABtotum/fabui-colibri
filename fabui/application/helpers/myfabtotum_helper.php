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

define("SERVICE_SUCCESS",            200);
define("SERVICE_UNAUTHORIZED",       401);
define("SERVICE_FORBIDDEN",          403);
define("SERVICE_SERVER_ERROR",       500);
define("SERVICE_INVALID_PARAMETER",  1001);
define("SERVICE_ALREADY_REGISTERED", 1002);
define("SERVICE_PRINTER_UNKNOWN",    1003);
define("SERVICE_USER_UNKNOWN",       1004);

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
	function callMyFabtotum($method, $args = array(), $apiVersion = true)
	{
		$CI =& get_instance();
		//load config
		$CI->config->load('fabtotum');
		$init['url'] = $CI->config->item('myfabtotum_url');
		//init jsonRPC library
		$CI->load->library('JsonRPC', $init, 'jsonRPC');
		//set api version
		if($apiVersion) $args['apiversion'] = $CI->config->item('myfabtotum_api_version');
		
		$response = $CI->jsonRPC->execute($method, $args);
		
		if(is_array($response)){
			return $response;
		}else{
			return $response->getMessage();
		}
		
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
	function fab_register_printer($fabid, $serialno = "")
	{
		$CI =& get_instance();
		$CI->load->helpers(array('fabtotum_helper', 'os_helper'));
		$CI->load->database();
		
		$args = array();
		
		if($serialno == '') getSerialNumber();
		
		$args['fabid']    = $fabid;
		$args['serialno'] = $serialno;
		$args['mac']      = getMACAddres();
		
		$return = array(
			'status' => false,
			'message' => ''
		);
		
		$response = callMyFabtotum('fab_register_printer', $args);
		
		if(is_array($response)){
			if($response['status_code'] == SERVICE_SUCCESS){
				$return['status'] = true;
			}
			$return['message'] = fab_get_status_description($response['status_code']);
		}else{
			$return['message'] = $response;
		}
		
		return $return;
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
			'iplan'     => isset($interfaces['wlan0']['wireless']['ip_address']) ? $interfaces['wlan0']['wireless']['ip_address']: ''
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
		
		return callMyFabtotum('fab_polling', $args);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('fab_is_printer_registered'))
{
	/**
	 * check if the printer is registered to my.fabtotum.com
	 */
	function fab_is_printer_registered()
	{
		$CI =& get_instance();
		$CI->load->helpers(array('fabtotum_helper', 'os_helper'));
		
		$args = array();
		$args['serialno'] = getSerialNumber();
		$args['mac']      = getMACAddres();
		
		return callMyFabtotum('fab_is_printer_registered', $args, false);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('fab_is_fabid_registered'))
{
	/**
	 * check if fabid is registered to my.fabtotum.com
	 */
	function fab_is_fabid_registered($email, $password)
	{
		$CI =& get_instance();
		$CI->load->helpers(array('fabtotum_helper', 'os_helper'));
		
		$args = array();
		$args['email'] = $email;
		$args['password'] = $password;
		
		return callMyFabtotum('fab_is_fabid_registered', $args, false);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('reload_myfabtotum'))
{
	/**
	 * reload my.fabtotum.com 
	 * credentials and settings
	 */
	function reload_myfabtotum()
	{
		$CI =& get_instance();
		$CI->load->helpers(array('fabtotum_helper'));
		return sendToXmlrpcServer('do_mfc_reload');
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('myfabtotum_connect'))
{
	/**
	 * 
	 */
	function myfabtotum_connect($fabid, $password)
	{
		$response = fab_is_fabid_registered($fabid, $password);
		
		$return = array(
				'status' => false,
				'message' => ''
		);
		
		if(is_array($response)){
			if($response['status_code'] == SERVICE_SUCCESS){
				$return['status'] = true;
			}
			$return['message'] = fab_get_status_description($response['status_code']);
		}else{
			$return['message'] = $response;
		}
		
		return $return;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('fab_get_status_description'))
{
	/**
	 * 
	 */
	function fab_get_status_description($code)
	{
		switch($code)
		{
			case SERVICE_SUCCESS : 
				return 'OK';
			case SERVICE_UNAUTHORIZED:
				return _('Service unauthorized');
			case SERVICE_FORBIDDEN:
				return _('Service forbidden');
			case SERVICE_SERVER_ERROR:
				return _('Service server error');
			case SERVICE_INVALID_PARAMETER:
				return _('Service invalid parameter');
			case SERVICE_ALREADY_REGISTERED:
				return _('Printer already registered');
			case SERVICE_PRINTER_UNKNOWN:
				return _('Printer unknown');
			case SERVICE_USER_UNKNOWN:
				return _('Your sign in details were not recognized, please check and try again');
			default:
				return 'UNKNOWN';
		}
	}
}