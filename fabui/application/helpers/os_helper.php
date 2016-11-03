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
if(!function_exists('configureWireless'))
{
	/**
	 * Configure a wireless interface.
	 */
	function configureWireless($iface, $ssid, $password, $psk, $mode = 'dhcp', $address = '', $netmask = '', $gateway = '')
	{
		$CI =& get_instance();
		$CI->load->helper('fabtotum');
		$args = '-i'.$iface.' -s "'.$ssid.'"';
		if($psk != '')
		{
			$args .= ' -k "'.$psk.'"';
		}
		else if($password != '')
		{
			$args .= ' -p "'.$password.'"';
		}
		
		switch($mode)
		{
			case "dhcp":
				$args .= ' -D';
				break;
			case "static":
				$args .= ' -S -a '.$address.' -n '.$netmask.' -g'.$gateway;
				break;
			case "static-ap":
				$args .= ' -A -a '.$address.' -n '.$netmask;
				break;
			default:
				return false;
		}
		$result = json_decode( startBashScript('set_wifi.sh', $args, false, true), true);
		return $result;
	}
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('configureEthernet'))
{
	/**
	 * Configure a wireless interface.
	 */
	function configureEthernet($iface, $mode = 'dhcp', $address = '', $netmask = '', $gateway = '')
	{
		$CI =& get_instance();
		$CI->load->helper('fabtotum');
		$args = '-i'.$iface;
		switch($mode)
		{
			case "dhcp":
				$args .= ' -D';
				break;
			case "static":
				$args .= ' -S -a '.$address.' -n '.$netmask.' -g'.$gateway;
				break;
			default:
				return false;
		}
		$result = json_decode( startBashScript('set_ethernet.sh', $args, false, true), true);
		return $result;
	}
} 
 
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getInterfaces'))
{
	/**
	 * Get network interfaces data
	 */
	function getInterfaces()
	{
		$CI =& get_instance();
		$CI->load->helper('fabtotum');
		$result = json_decode( startBashScript('get_net_interfaces.sh', '', false, true), true);
		return $result;
	}
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('cidr2NetmaskAddr'))
{
	function cidr2NetmaskAddr($cidr)
	{
		$ta = substr($cidr, strpos($cidr, '/') + 1) * 1;
		$netmask = str_split(str_pad(str_pad('', $ta, '1'), 32, '0'), 8);
		foreach ($netmask as &$element) $element = bindec($element);
		return join('.', $netmask);
    }
}

if(!function_exists('getHostName'))
{
	/**
	 * @return Hostname
	 */
	function getHostName()
	{
		return shell_exec('cat /etc/hostname');
	}
}

if(!function_exists('setHostName'))
{
	function setHostName($hostname, $name)
	{
		$CI =& get_instance();
		$CI->load->helper('fabtotum');
		$response = startBashScript('set_hostname.sh', '"'.$hostname.'" "'.$name.'"', false, true);
		return $response;
	}
}

if(!function_exists('getAvahiServiceName'))
{
	/**
	 * @return Service name stored in avahi fabtotum.service
	 */
	function getAvahiServiceName()
	{
		if(file_exists('/etc/avahi/services/fabtotum.service')){
			$xml_service = simplexml_load_file('/etc/avahi/services/fabtotum.service','SimpleXMLElement', LIBXML_NOCDATA);
			return trim(str_replace('(%h)', '', $xml_service->name));
		}else{
			return 'Fabtotum Personal Fabricator';
		}
	}
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('scanWlan'))
{
	/**
	 * @param string $interface wlan interface name
	 * @return array list of discovered wifi's nets 
	 */
	function scanWlan($interface = 'wlan0')
	{
		$CI =& get_instance();
		$CI->load->helper('fabtotum');
		#$scanCommand = 'sudo python '.$CI->config->item('ext_path').'py/scan_wifi.py '.$interface;
		$result = startPyScript('scan_wifi.py', $interface, false, true);
		$nets = json_decode( $result, true);
		
		return $nets;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getFromRegEx'))
{
	/**
	 * 
	 */
	function getFromRegEx($regEx, $string)
	{
		preg_match($regEx, $string, $tempResult);
		return isset($tempResult[1]) ? $tempResult[1] : '';
	}
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('disconnectFromWireless'))
{
	/**
	 * disconnect from wifi network
	 */
	function disconnectFromWireless()
	{
		//~ $CI =& get_instance();
		//~ $CI->config->load('fabtotum');
		//~ $setWifiCommand = 'sudo sh '.$CI->config->item('ext_path').'bash/set_wifi.sh';
		//~ log_message('debug', $setWifiCommand);
		//~ $scriptResult = shell_exec($setWifiCommand);
		return true;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('setSystemDate'))
{
	/**
	 * set system date format = YYYY-MM-DD HH:mm:ss
	 */
	function setSystemDate($date)
	{
		log_message('debug', 'Set system date: "'.$date.'"');
		shell_exec('sudo date -s "'.$date.'"');
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('isInternetAvaialable'))
{
	/**
	 * check if internet connection is avaialable
	 */
	function isInternetAvaialable()
	{
		return !$sock = @fsockopen('http://www.google.com', 80, $num, $error, 2) ? false : true;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('downloadRemoteFile'))
{
	/**
	 * download remote file 
	 */
	function downloadRemoteFile($remoteUrl, $path)
	{
		$curl = curl_init($remoteUrl);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$downloadedFile = curl_exec($curl); //make call
		$info = curl_getinfo($curl);
		if(isset($info['http_code']) && $info['http_code'] == 200){ //if response is OK
			$CI =& get_instance();
			$CI->load->helper('file_helper');
			write_file($path, $downloadedFile, 'w+');
			return true;
		}else{
			return false;
		}
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('setTimeZone'))
{
	/**
	 * set time zone 
	 */
	function setTimeZone($timeZone)
	{	
		if($timeZone != ''){
			$CI =& get_instance();
			$CI->load->helper('fabtotum');
			$scriptResult = startBashScript('set_time_zone.sh', $timeZone, false, true);
			log_message('debug', 'set_time_zone.sh' .' '.$timeZone);
			return true;
		}
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('transformSeconds'))
{
	function transformSeconds($seconds)
	{
		$sec_num = intval($seconds); // don't forget the second param
		$hours   = floor($sec_num / 3600);
		$minutes = floor(($sec_num - ($hours * 3600)) / 60);
		$seconds = $sec_num - ($hours * 3600) - ($minutes * 60);

		if ($hours   < 10) {$hours   = "0".$hours;}
		if ($minutes < 10) {$minutes = "0".$minutes;}
		if ($seconds < 10) {$seconds = "0".$seconds;}
		return $hours . ':' . $minutes . ':' . $seconds;
	}
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('humanFileSize'))
{
	/**
	 * Human readable byte size
	 * @param $int
	 * @return string
	 */
	function humanFileSize($bytes) {
		$bytes = intval($bytes);
		$ret = "unknown";
		if ($bytes > 1000000) {
			$bytes = round($bytes / 1000000, 2);
			$ret = "$bytes MB";
		} else if ($bytes > 1000) {
			$bytes = round($bytes / 1000, 2);
			$ret = "$bytes kB";
		} else {
			$ret = "$bytes bytes";
		}
		return $ret;
	}
}

?>
