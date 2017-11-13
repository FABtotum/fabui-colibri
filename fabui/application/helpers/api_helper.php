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
if(!function_exists('exists_serial_number'))
{
	/**
	 * remote validate for serial number
	 */
	function exists_serial_number($serial)
	{
		$response = call_remote_api('/printer/serial_number/'.$serial);
		if(isset($reponse['content'])){
			$content = json_decode($response['content']);
			return $content['status'];
		}
		return false;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('call_remote_api'))
{
	/**
	 * make call to remote api url (http://app.fabtotum.com)
	 */
	function call_remote_api($endpoint, $data = array())
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		
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
