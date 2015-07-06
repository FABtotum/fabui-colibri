<?php

namespace System;

class Networking
{
	/**
	 * Set Network Configuration
	 */
	public function setNetworkConfiguration($eth_address, $wifi)
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

		$interfaces_file = NETWORK_INTERFACES;  // Global conf parameter

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
