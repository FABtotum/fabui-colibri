<?php
/**
 *
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 *
 */
if ( ! function_exists('isInternetAvaiable'))
{
	/**
	 * Check whether there is access to the internet.
	 */
	function isInternetAvaiable() {
		return !$sock = @fsockopen('www.google.com', 80, $num, $error, 2) ? false : true;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getLocalBundles')){
	
	/**
	 * return all bundles
	 */
	function getLocalBundles()
	{
		
		$list = explode(PHP_EOL, trim(shell_exec('colibrimngr list')));
		unset($list[0]); //remove comand argument
		
		$bundles = array();
		$states = array('A'=> 'active', 'D'=>'disabled', '-' => 'unknown');
		
		$re = '/\[(\!*)(A|D|-|)\]\s(\d+)\s:(\s.*?\s):\s([0-9.]+)/';
		
		foreach($list as $line){
			preg_match_all($re, $line, $matches);
			// @TODO: add failsafe in case the matches does not match enough
			$bundle_name = isset($matches[4]) ? trim($matches[4][0]) : '';
			$temp = array(
				'name'     => $bundle_name, 
				'version'  => isset($matches[5]) ? $matches[5][0] : '',
				'state'    => isset($matches[2]) ? $states[$matches[2][0]] : '',
				'priority' => isset($matches[3]) ? $matches[3][0] : '',
				'invalid'  => isset($matches[1]) && $matches[1][0] == '!' ? true : false,
				'info'     => getBundleInfo($bundle_name, 'info'),
				'licenses' => getBundleInfo($bundle_name, 'licenses'),
				'packages' => getBundleInfo($bundle_name, 'packages')		
			);
			$bundles[$bundle_name] = $temp;
		}
		return $bundles;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getLocalBundle')){

	/**
	 * return local bundle
	 */
	function getLocalBundle($bundle_name)
	{
		$bundles = getLocalBundles();
		
		if(array_key_exists($bundle_name, $bundles)){
			return $bundles[$bundle_name];	
		}
		
		return false;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getRemoteBundles')){
	
	/**
	 * 
	 */
	function getRemoteBundles()
	{
		$systemRemoteVersions = getSystemRemoteVersions();
		if($systemRemoteVersions != false)
			return $systemRemoteVersions['bundles'];
		return false;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getSystemRemoteVersions')){

	/**
	 *
	 */
	function getSystemRemoteVersions()
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$CI->load->helper('os_helper');
		$endpoint = $CI->config->item('colibri_endpoint').getArchitecture().'/version.json';
		$remoteContent = getRemoteFile($endpoint);
		if($remoteContent != false) return json_decode($remoteContent, true);
		else false;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getArchitecture'))
{
	/**
	 * return architecture for updates
	 */
	function getArchitecture()
	{
		$versions = array(
			'armv7l' => 'armhf',
			'armv6'  => 'armhf',
			'armv6l' => 'armhf'
		);
		$arch = trim(str_replace(PHP_EOL, '', shell_exec('uname -m')));
		return $versions[$arch];
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getBundlesStatus'))
{
	/**
	 *  check wich bundle needs an update 
	 */
	function getUpdateStatus()
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$CI->load->helper('fabtotum_helper');
		$localBundles      = getLocalBundles();
		$remoteBundles     = getRemoteBundles();
		$firmwareRemote    = getRemoteFwVersions();
		$installedFirmware = firmwareInfo();
		
		$bundlesEndpoint = $CI->config->item('colibri_endpoint').getArchitecture();
		$fwEndpoint      = $CI->config->item('firmware_endpoint').'fablin/atmega1280/';
		
		$latestFirmwareRemote = $firmwareRemote['firmware']['latest'];
		
		$firmware['installed']   = $installedFirmware['version'];
		$firmware['need_update'] = version_compare($installedFirmware['version'], $firmwareRemote['firmware']['latest']) == -1 ? true : false;
		$firmware['remote']      = $firmwareRemote['firmware'][$firmwareRemote['firmware']['latest']];
		$firmware['remote']['changelog'] = getRemoteFile($fwEndpoint.'/latest/changelog.txt');
		
		$status = array(
			'bundles'    => array(),
			'boot' => array(),
			'images' => array(),
			'firmware'   => $firmware,
			'update' => array(
				'available' => false,
				'bundles'   => 0,
				'firmware'  => false,
				'endpoint'  => array(
					'bundles' =>$bundlesEndpoint,
					'fimware' => $fwEndpoint
				)
			),
			'remote_connection' =>  $remoteBundles ? true : false,
			'date' => date("Y-m-d H:i:s")
		);
		
		foreach($localBundles as $bundleName => $localBundleData)
		{
			if($remoteBundles){
				$remoteBundle = $remoteBundles[$bundleName];
				$latestVersion = str_replace('v', '', $remoteBundle['latest']);
				$needUpdate = version_compare($localBundleData['version'], $latestVersion) == -1 ? true : false;
				$changelog = '';
				if($needUpdate) {
					$remoteContent = getRemoteFile($bundlesEndpoint.'/bundles/'.$bundleName.'/changelog.json');
					if($remoteContent != false){
						$temp = json_decode($remoteContent, true);
						$changelog = ($temp[$remoteBundle['latest']]);
						$status['update']['bundles'] += 1;
					}
				}
			}else{
				$latestVersion = $changelog =  'unknown';
				$needUpdate = false;
			}
 			$status['bundles'][$bundleName] = array(
				'latest'      => $latestVersion,
				'local'       => $localBundleData['version'],
				'need_update' => $needUpdate,
 				'changelog'   => $changelog,
 				'info'        => $localBundleData['info'],
 				'licenses'    => $localBundleData['licenses'],
 				'packages'    => $localBundleData['packages'],
			);
		}
		$status['update']['available'] = $status['update']['bundles'] > 0 || $firmware['need_update'] ? true : false;
		return $status;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('flashFirmware'))
{
	/**
	 *  Flash Firmware
	 */
	function flashFirmware($type, $argument = '')
	{
		$CI =& get_instance();
		$CI->load->helper('fabtotum');
		$args = '';

		switch($type)
		{
			case "factory":
				$args = 'factory';
				break;
			case "custom":
				$args = 'update '.$argument;
				break;
			case "remote": // remote update
				$args = 'remote-update '.$argument;
				break;
			default:
				return false;
		}

		return startBashScript('totumduino_manager.sh', $args, false, true);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getBundleInfo'))
{
	/**
	 *  read and return specific bundle info
	 */
	function getBundleInfo($bundle, $type)
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		
		$infos = array();
		
		if(file_exists($CI->config->item('bundles_path').$bundle.'/'.$type)){
			$content = explode(PHP_EOL, trim(file_get_contents($CI->config->item('bundles_path').$bundle.'/'.$type)));
			foreach($content as $row)
			{
				if($row != ''){
					$temp = explode(':', trim($row));
					$key = str_replace('-', '_', trim($temp[0]));
					$infos[$key] = trim($temp[1]);
				}
			}
		}
		return $infos;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getRemoteFwVersions'))
{
	/**
	 *  return remote fw versions
	 */
	function getRemoteFwVersions()
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$CI->load->helper('os_helper');
		$endpoint = $CI->config->item('firmware_endpoint').'fablin/atmega1280/version.json';
		$remoteContent = getRemoteFile($endpoint);
		if($remoteContent != false) return json_decode($remoteContent, true);
		else false;
	}
}

?>
