<?php

/**
 *
 * @author Krios Mane
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 *
 */
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('subscription_exists'))
{
	/**
	 *
	 */
	function subscription_exists()
	{
		$CI =& get_instance();
		$CI->config->load('cam');
		return file_exists($CI->config->item('subscription_file'));
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('load_subscription'))
{
	/**
	 *
	 */
	function load_subscription()
	{
		//load plugin config
		$CI =& get_instance();
		$CI->config->load('cam');
		
		if(file_exists($CI->config->item('subscription_file'))){
			$subscription =  json_decode(file_get_contents($CI->config->item('subscription_file')), true);
			return $subscription;
		}
		return false;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('http_code_description'))
{
	/**
	 *
	 */
	function http_code_description($http_code)
	{
		switch($http_code)
		{
			case 100:
				return _("Connection timeout");
				break;
			default:
				return $http_code;
		}
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('write_subscription'))
{
	/**
	 *
	 */
	function write_subscription($subscription)
	{
		$CI =& get_instance();
		$CI->config->load('cam');
		
		$CI->load->helper(array('file'));
		
		$info = json_decode($subscription['target'], true);
		$data = array(
			'code'            => $subscription['link'],
			'status'          => $info['status'],
			'start_date'      => $info['start_date'],
			'expiration_date' => $subscription['exp_date']
		);
		
		return write_file($CI->config->item('subscription_file'), json_encode($data));
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('load_presets'))
{
	/**
	 *
	 */
	function load_presets($type="laser")
	{
		
		$CI =& get_instance();
		$CI->config->load('cam');
		$CI->load->helper(array('file_helper'));
		
		$preset_path = $CI->config->item('preset_path');
		
		$presets = array();
		
		$files = get_filenames($preset_path.'/'.$type.'/');
		
		foreach($files as $file)
		{
			$data =  json_decode(file_get_contents($preset_path.'/'.$type.'/'.$file), true);
			$data['filename'] = $file;
			if(json_last_error() == JSON_ERROR_NONE){
				$presets[$file] = $data;
			}
		}
		return $presets;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('active_subscription'))
{
	/**
	 * 
	 */
	function active_subscription($code)
	{
		$CI =& get_instance();
		
		
		if(isset($CI->session->user['settings']['fabid']['email']))
			$fabid = $CI->session->user['settings']['fabid']['email'];
			else
				return json_encode(array(
						'status' => false,
						'message' => _("Missing FABID")
				));
		
		$response = call_service('/subscription/active/'.$code);
		
		if($response['content']){
			
			$contentDecoded = json_decode($response['content'], true);
			
			if($contentDecoded['status'] == true){
				write_subscription($contentDecoded['subscription']);
			}
			return $response['content'];	
		}
		
		return json_encode(array(
			'status' => false,
			'message' => http_code_description($response['info']['http_code'])
		));
		
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('get_subscription_code'))
{
	function get_subscription_code()
	{
		$subscription = load_subscription();
		if(is_array($subscription)){
			return $subscription["code"];
		}
		return false;
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('remove_subscription'))
{
	/**
	 * 
	 */
	function remove_subscription()
	{
		$CI =& get_instance();
		$CI->config->load('cam');
		
		$result = false;
		if(file_exists($CI->config->item('subscription_file'))){
			shell_exec('sudo rm '.$CI->config->item('subscription_file'));
			$result = true;
		}
		return json_encode(array('status' => $result));
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('get_img_extra_info'))
{
	/**
	 *
	 */
	function get_img_extra_info($file)
	{
		$CI =& get_instance();
		$CI->load->helper(array('fabtotum_helper'));
		
		$args = array(
				'-i' => $file,
				'-I' => ''
		);
		$response = startPyScript('cam/utils.py', $args, false);
		
		return json_decode($response, true);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('call_service'))
{
	/**
	 * 
	 */
	function call_service($endpoint, $data = array())
	{
		$CI =& get_instance();
		$CI->config->load('cam');

		if(isset($CI->session->user['settings']['fabid']['email']))
			$fabid = $CI->session->user['settings']['fabid']['email'];
		else
			return false;
		
		$data['fabid']        = json_encode(array('email' => $fabid));
		$data['subscription'] = get_subscription_code();
		
		$url = $CI->config->item('api_url').$CI->config->item('api_version').'/'.$endpoint;
		
		//prepare call
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_URL,            $url);
		curl_setopt($ch, CURLOPT_POST,           true);
		curl_setopt($ch, CURLOPT_POSTFIELDS,     $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0);
		curl_setopt($ch, CURLOPT_TIMEOUT,        $CI->config->item('connection_timeout'));
		
		//exec call
		$content = curl_exec ($ch);
		//get call info
		$info = curl_getinfo ($ch);
		//close call
		curl_close ($ch);
		return array('content' =>$content, 'info' => $info );
	}
}
