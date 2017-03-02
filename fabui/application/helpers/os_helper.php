<?php

/**
 * 
 * @author Krios Mane
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */

if(!function_exists('getUsbStatus'))
{
	function getUsbStatus()
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		return file_exists($CI->config->item('usb_file'));
	}
}

if(!function_exists('storeNetworkSettings'))
{
	function storeNetworkSettings($net_type, $iface, $mode, $address, $netmask, $gateway, $ssid = '', $password = '', $psk = '', $hostname = '', $description = '')
	{
		$CI =& get_instance();
		$CI->load->model('Configuration', 'configuration');
		
		$raw = $CI->configuration->load('network', '{}');
		
		$network_settings = json_decode($raw, true);
		$data = array();
		
		switch($net_type)
		{
			case "eth":
				$data['net_type'] = $net_type;
				$data['mode'] = $mode;
				$data['address'] = $address;
				$data['netmask'] = $netmask;
				$data['gateway'] = $gateway;
				$network_settings['interfaces'][$iface] = $data;
				break;
			case "wlan":
				$data['net_type'] = $net_type;
				$data['mode'] = $mode;
				$data['address'] = $address;
				$data['netmask'] = $netmask;
				$data['gateway'] = $gateway;
				$data['ssid'] = $ssid;
				$data['password'] = $password;
				$data['psk'] = $psk;
				$network_settings['interfaces'][$iface] = $data;
				break;
			case "dnssd":
				$network_settings['hostname'] = $hostname;
				$network_settings['description'] = $description;
				break;
		}
		
		$CI->configuration->store('network', json_encode($network_settings) );
	}
}

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
if(!function_exists('getDNS'))
{
	/**
	 * Get system DNS data
	 */
	function getDNS()
	{
		$CI =& get_instance();
		$CI->load->helper('fabtotum');
		$result = startBashScript('get_dns.sh', '', false, true);
		
		$dns = array(
			'head1' => array(),
			'current' => array(),
			'tail' => array()
		);
		
		$list = explode(";", $result);
		
		foreach($list as $entry)
		{
			$entry = trim($entry);
			if( startsWith($entry, 'H=') ) {
				$dns['head'][] = ltrim($entry, "H=");
			}
			else if ( startsWith($entry, 'C=') ) {
				$c = ltrim($entry, "C=");
				if(!in_array($c, $dns['head']) && !in_array($c, $dns['tail'])) {
					$dns['current'][] = $c;
				}
			}
			else if ( startsWith($entry, 'T=') ) {
				$dns['tail'][] = ltrim($entry, "T=");
			}
		}
		
		return $dns;
	}
}

if(!function_exists('configureDNS'))
{
	function configureDNS($dns_settings)
	{
		
		$head_content = '';
		if(isset($dns_settings['head'])) {
			foreach($dns_settings['head'] as $entry)
			{
				$head_content .= "nameserver " . $entry . PHP_EOL;
			}
		}
		
		$current_content = '';
		if(isset($dns_settings['current'])) {
			foreach($dns_settings['current'] as $entry)
			{
				$current_content .= "nameserver " . $entry . PHP_EOL;
			}
		}
		
		$tail_content = '';
		if(isset($dns_settings['tail'])) {
			foreach($dns_settings['tail'] as $entry)
			{
				$tail_content .= "nameserver " . $entry . PHP_EOL;
			}
		}
		
		file_put_contents('/tmp/fabui/resolv.conf.head', $head_content);
		file_put_contents('/tmp/fabui/resolv.conf', $head_content . $current_content . $tail_content);
		file_put_contents('/tmp/fabui/resolv.conf.tail', $tail_content);
		
		shell_exec("sudo mv /tmp/fabui/resolv.conf.head /etc/resolv.conf.head");
		shell_exec("sudo mv /tmp/fabui/resolv.conf /etc/resolv.conf");
		shell_exec("sudo mv /tmp/fabui/resolv.conf.tail /etc/resolv.conf.tail");
		
		return true;
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
	function disconnectFromWireless($interface)
	{
		$CI =& get_instance();
		$CI->load->helper('fabtotum');
		$result = startBashScript('disconnect_wifi.sh', $interface, false, true);
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
		$CI =& get_instance();
		$CI->load->helper('fabtotum');
		$result = startBashScript('internet.sh', null, false, true);
		return trim($result) == 'online';
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('downloadRemoteFile'))
{
	/**
	 * download remote file 
	 */
	function downloadRemoteFile($remoteUrl, $path, $timeout=3, $do_internet_check=true)
	{	
		if($do_internet_check)
		{
			if(!isInternetAvaialable())
			{
				log_message('debug', 'Internet connection not available');
				return false;
			}
		}

		$curl = curl_init($remoteUrl);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
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
if(!function_exists('getRemoteFile'))
{
	/**
	 * 
	 */
	function getRemoteFile($url, $do_internet_check=true)
	{
		if($do_internet_check)
		{
			if(!isInternetAvaialable())
			{
				log_message('debug', 'Internet connection not available');
				return false;
			}
		}
		
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$content = curl_exec($curl); //make call
		$info = curl_getinfo($curl);
		if(isset($info['http_code']) && $info['http_code'] == 200){ //if response is OK
			return $content;
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
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('downloadBlogFeeds'))
{
	/**
	 * 
	 */
	function downloadBlogFeeds()
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		
		$xmlEndPoint = $CI->config->item('blog_feed_url').'?cat='.$CI->config->item('blog_post_categories');
		
		if(downloadRemoteFile($xmlEndPoint, $CI->config->item('blog_feed_file'), 5)){
			log_message('debug', 'Blog feeds updated');
			return true;
		}else{
			log_message('debug', 'Blog feeds unavailable');
			return false;
		}
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('downloadTwitterFeeds'))
{
	/**
	 *
	 */
	function downloadTwitterFeeds()
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');

		if(downloadRemoteFile($CI->config->item('twitter_feed_url'), $CI->config->item('twitter_feed_file'))){
			log_message('debug', 'Twitter feeds updated');
			return true;
		}else{
			log_message('debug', 'Twitter feeds unavailable');
			return false;
		}
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('downloadInstagramFeeds'))
{
	/**
	 *
	 */
	function downloadInstagramFeeds()
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');

		if(downloadRemoteFile($CI->config->item('instagram_feed_url'), $CI->config->item('instagram_feed_file'))){
			log_message('debug', 'Instagram feeds updated');
			return true;
		}else{
			log_message('debug', 'Instagram feeds unavailable');
			return false;
		}
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('writeNetworkInfo'))
{
	function writeNetworkInfo(){
		$CI =& get_instance();
		//load config, helpers
		$CI->config->load('fabtotum');
		$CI->load->helper('file');
		
		$data['interfaces'] = getInterfaces();
		$data['internet'] = isInternetAvaialable();
		write_file($CI->config->item('network_info_file'), json_encode($data));
	}
}
?>
