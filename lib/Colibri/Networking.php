<?php

namespace Colibri;

class Networking
{
	const CMD_NETWORK_RELOAD = 'sudo /etc/init.d/network restart';

	private $_options = array();

	public function __set ($name, $value)
	{
		$this->_options[$name] = $value;
	}

	public function __get ($name)
	{
		if (array_key_exists($name, $this->_options))
			return $this->_options[$name];
		elseif (defined($name))
			return constant($name);
		else
			throw new Exception ("Undefined ".__CLASS__." or global option: {$name}");
	}

	function setEthIP($ip)
	{
		$ip = '169.254.1.'.$ip;	 	
		$networkConfiguration = $this->networkConfiguration();

		$this->setNetworkConfiguration($ip, $networkConfiguration['wifi']);
		
		$response = exec(self::CMD_NETWORK_RELOAD, $output, $return);

		return ($return == 0);
	}

	public function setWifi($ssid, $password, $type='WPA')
	{
		$networkConfiguration = $this->networkConfiguration();

		$this->setNetworkConfiguration($networkConfiguration['eth'], array('ssid' => $ssid, 'password'=>$password, 'type'=>$type));

		$response = exec(self::CMD_NETWORK_RELOAD, $output, $return);

		// Simplified return condition for new code
		// (should we need to have anything else checked?)
		return ($return == 0);
	}

	private $_networkConfiguration = null;
	public function networkConfiguration()
	{
		if (empty($this->_networkconfiguration))
		{
			$interfaces = file_get_contents($this->NETWORK_INTERFACES);

			$wlan_section = strstr($interfaces, 'allow-hotplug wlan0');

			$temp = explode(PHP_EOL, $wlan_section);

			$wlan_ssid = '';
			$wlan_password = '';
			
			$wifi_type = 'OPEN';

			foreach ($temp as $line) {

				if (strpos(ltrim($line), '-ssid') !== false) {
					$wlan_ssid = trim(str_replace('"', '', str_replace('-ssid', '', strstr(ltrim($line), '-ssid'))));
					$wifi_type = 'WPA2';
				}

				if (strpos(ltrim($line), '-psk') !== false) {
					$wlan_password = trim(str_replace('"', '', str_replace('-psk', '', strstr(ltrim($line), '-psk'))));
					$wifi_type = 'WPA2';
				}
				
				//======================================================================================================
				
				if (strpos(ltrim($line), '-essid') !== false) {
					$wlan_ssid = trim(str_replace('"', '', str_replace('-essid', '', strstr(ltrim($line), '-essid'))));
				}
				
				if (strpos(ltrim($line), '-key') !== false) {
					$wlan_password = trim(str_replace('"', '', str_replace('-key', '', strstr(ltrim($line), '-key'))));
					$wifi_type = 'WEP';
				}
				

			}

			$interfaces = str_replace($wlan_section, '', $interfaces);

			$eth_section = strstr($interfaces, 'allow-hotplug eth0');

			$temp = explode(PHP_EOL, $eth_section);

			$address = '';

			foreach ($temp as $line) {

				if (strpos(ltrim($line), 'address') !== false) {
					$address = str_replace('"', '', str_replace('address', '', strstr(ltrim($line), 'address')));
				}

			}
			
			$this->_networkConfiguration = array('eth' => trim($address), 'wifi'=>array('ssid'=>trim($wlan_ssid), 'password'=>trim($wlan_password), 'type' => $wifi_type));
		}

		return $this->_networkConfiguration;

	}

	/**
	 * Set Network Configuration
	 */
	protected function setNetworkConfiguration($eth_address, $wifi)
	{

		$wifi_conf = array();
		if (isset($wifi['type']))
		switch($wifi['type'])
		{		
			case 'WPA':
			case 'WPA2':
				$wifi_conf[] = "    wpa-ssid \"{$wifi['ssid']}\"";
				$wifi_conf[] = "    wpa-psk  \"{$wifi['password']}\"";
				break;
			case 'WEP':
				$wifi_conf[] = "    wireless-essid \"{$wifi['ssid']}\"";
				$wifi_conf[] = "    wireless-key   \"{$wifi['password']}\"";
				break;
			default:
			case 'OPEN':
				$wifi_conf[] = "    wireless-essid \"{$wifi['ssid']}\"";
				$wifi_conf[] = "    wireless-mode  managed";
		}
		$wifi_conf = implode(PHP_EOL, $wifi_conf);

		$interfaces_file = $this->NETWORK_INTERFACES;  // Global conf parameter

		$new_configuration = <<<CONF
auto lo
iface lo inet loopback

allow-hotplug eth0
auto eth0
iface eth0 inet static
    address {$eth_address}
    netmask 255.255.0.0

allow-hotplug wlan0
auto wlan0
iface wlan0 inet dhcp
{$wifi_conf}
CONF;
		
		$backup_command = 'sudo cp '.$interfaces_file.' '.$interfaces_file.'.sav';
		shell_exec($backup_command);

		// can this be handled better?
		shell_exec('sudo chmod 666 '.$interfaces_file);
		file_put_contents($interfaces_file, $new_configuration);
		shell_exec('sudo chmod 644 '.$interfaces_file);
	}
}
