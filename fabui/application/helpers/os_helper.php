<?php

/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
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
		 $response = shell_exec('sudo sh /usr/share/fabui/ext/bash/set_hostname.sh "'.$hostname.'" "'.$name.'"');
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
 
if(!function_exists('getEthInfo'))
{
	/**
	 * @param string interface name
	 * @return array infos about wlan interface
	 */
	function getEthInfo(){
			
		exec('sudo ifconfig eth0', $info);
		$info = implode(" ",$info);
		$info = preg_replace('/\s\s+/', ' ', $info);
		
		preg_match('/inet addr:([0-9]+.[0-9]+.[0-9]+.[0-9]+)/i',$info,$result);
		$inet_address = isset($result[1]) ? $result[1] : '';
		
		preg_match('/Bcast:([0-9]+.[0-9]+.[0-9]+.[0-9]+)/i',$info,$result);
		$broadcast = isset($result[1]) ? $result[1] : '';
		
		preg_match('/HWaddr ([0-9a-f:]+)/i',$info,$result);
		$mac_address = isset($result[1]) ? $result[1] : '';
		
		preg_match('/RX Bytes:(\d+ \(\d+.\d+ MiB\))/i',$info,$result);
		$received_bytes = isset($result[1]) ? $result[1] : '';
		
		preg_match('/TX Bytes:(\d+ \(\d+.\d+ [K|M|G]iB\))/i',$info,$result);
		$transferred_bytes = isset($result[1]) ? $result[1] : '';
			
		return array(
			'inet_address' => $inet_address,
			'broadcast' => $broadcast,
			'mac_address' => $mac_address,
			'received_bytes' => $received_bytes,
			'transferred_bytes' => $transferred_bytes
		);
	}

}

if(!function_exists('setEthIPAddress'))
{
	function setEthIPAddress($ip_address)
	{
		 $response = shell_exec('sudo sh /usr/share/fabui/ext/bash/set_ethernet.sh "'.$ip_address.'"');
		 return $response;
	}
}
 
if(!function_exists('getWlanInfo'))
{
	/**
	 * @param string interface name
	 * @return array infos about wlan interface
	 */
	function getWlanInfo($interface = 'wlan0')
	{
		exec('ifconfig '.$interface, $info);
		exec('iwconfig '.$interface, $info);
		
		$strInterface = implode(" ",$info);
		$strInterface = preg_replace('/\s\s+/', ' ', $strInterface);
		
		return array(
			'mac_address'         => getFromRegEx('/HWaddr ([0-9a-f:]+)/i', $strInterface),
			'ip_address'          => getFromRegEx('/inet addr:([0-9.]+)/i', $strInterface),
			'subnet_mask'         => getFromRegEx('/Mask:([0-9.]+)/i', $strInterface),
			'received_packets'    => getFromRegEx('/RX packets:(\d+)/', $strInterface),
			'transferred_packets' => getFromRegEx('/TX packets:(\d+)/', $strInterface),
			'received_bytes'      => getFromRegEx('/RX Bytes:(\d+ \(\d+.\d+ MiB\))/i', $strInterface),
			'transferred_bytes'   => getFromRegEx('/TX Bytes:(\d+ \(\d+.\d+ [K|M|G]iB\))/i', $strInterface),
			'ssid'                => getFromRegEx('/ESSID:\"((?:(?![\n\s]).)*)\"/i', $strInterface),
			'ap_mac_address'      => getFromRegEx('/Access Point: ([0-9a-f:]+)/i', $strInterface),
			'bitrate'             => getFromRegEx('/Bit Rate:([0-9]+.[0-9]+\s[a-z]+\/[a-z]+)/i', $strInterface),
			'link_quality'        => getFromRegEx('/Link Quality=([0-9]+\/[0-9]+)/i', $strInterface),
			'signal_level'        => decodeWifiSignal($strInterface),
			'power_management'    => getFromRegEx('/Power Management:([a-zA-Z]+)/i', $strInterface),
			'frequency'           => getFromRegEx('/Frequency:([0-9]+.[0-9]+\s[a-z]+)/i', $strInterface),
			'ieee'                => getFromRegEx('/IEEE ([0-9]+.[0-9]+[a-z]+)/i', $strInterface)
		);
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
		$scanResult = shell_exec('sudo iwlist '.$interface.' scan && sleep 1');
		$scanResult = preg_replace('/\s\s+/', ' ', $scanResult);
		$scanResult = str_replace($interface.' Scan completed :', '', $scanResult);
		$scanResult = explode('Cell ', $scanResult);
		
		$nets = array();
		foreach($scanResult as $net){
			if(trim($net) != ''){
				$temp = array();
				$temp['address']   		= getFromRegEx('/Address:\s([0-9a-f:]+)/i', $net);
				$temp['essid']     		= getFromRegEx('/ESSID:\"((?:.)*)\"/i', $net);	
				$temp['protocol']  		= getFromRegEx('/IEEE\s([0-9]+.[0-9]+[a-z]+)/i', $net);
				$temp['mode']      		= getFromRegEx('/Mode:([a-zA-Z]+)/i', $net);
				$temp['frequency'] 		= getFromRegEx('/Frequency:([0-9]+.[0-9]+\s[a-z]+)/i', $net);
				$temp['channel']  		= getFromRegEx('/Channel ([0-9]+)/i', $net);
				$temp['encryption_key'] = getFromRegEx('/Encryption key:([a-zA-Z]+)/i', $net);	
				$temp['bit_rates']      = getFromRegEx('/Bit Rates:([0-9]+.[0-9]+\s[a-z]+\/[a-z]+)/i', $net);
				$temp['quality']        = getFromRegEx('/Quality=([0-9]+)/i', $net);
				$temp['signal_level']   = decodeWifiSignal($net);
				//add to nets lists
				$nets[] = $temp;
			}
		}
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
if(!function_exists('wifiConnect'))
{
	/**
	 * connecto to a wifi network
	 */
	function wifiConnect($essid, $password)
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$setWifiCommand = 'sudo sh '.$CI->config->item('ext_path').'bash/set_wifi.sh "'.$essid.'" "'.$password.'"';
		log_message('debug', $setWifiCommand);
		$scriptResult = shell_exec($setWifiCommand);
		return true;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('wifiDisconnect'))
{
	/**
	 * disconnecto from wifi network
	 */
	function wifiDisconnect()
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$setWifiCommand = 'sudo sh '.$CI->config->item('ext_path').'bash/set_wifi.sh';
		log_message('debug', $setWifiCommand);
		$scriptResult = shell_exec($setWifiCommand);
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
			$CI->config->load('fabtotum');
			$setTimeZoneCommand = 'sudo sh '.$CI->config->item('ext_path').'bash/set_time_zone.sh '.$timeZone;
			log_message('debug', $setTimeZoneCommand);
			$scriptResult = shell_exec($setTimeZoneCommand);
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
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('decodeWifiSignal'))
{
	/**
	 * 
	 */
	function decodeWifiSignal($string)
	{
		preg_match('/Signal level=[+|-]([0-9]+)\s(\w+)/i', $string, $tempResult);
		if(isset($tempResult[2]) && $tempResult[2] == 'dBm'){
			$value = $tempResult[1];
			if($value < 50){
				return 100;
			}elseif($value > 50 && $value < 60){
				return 75;
			}elseif($value > 60 && $value < 70){
				return 50;
			}else{
				return 25;
			}
		}
		preg_match('/Signal level=([0-9]+)/i', $string, $tempResult);
		if(isset($tempResult[1]))
			return $tempResult[1];
		}
}
?>
