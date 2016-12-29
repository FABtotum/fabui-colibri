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
	function getBundlesStatus()
	{
		$localBundles  = getLocalBundles();
		$remoteBundles = getRemoteBundles();
		
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		
		$endpoint = $CI->config->item('colibri_endpoint').getArchitecture();
			
		$status = array(
			'bundles'    => array(),
			'update' => array(
				'available' => false,
				'number' => 0,
				'endpoint'   => $endpoint
			),
			'remote_connection' =>  $remoteBundles ? true : false,
		);
		foreach($localBundles as $bundleName => $localBundleData)
		{
			if($remoteBundles){
				$remoteBundle = $remoteBundles[$bundleName];
				$latestVersion = str_replace('v', '', $remoteBundle['latest']);
				$needUpdate = version_compare($localBundleData['version'], $latestVersion) == -1 ? true : false;
				$changelog = '';
				if($needUpdate) {
					$status['update']['available'] = true;
					$remoteContent = getRemoteFile($endpoint.'/bundles/'.$bundleName.'/changelog.json');
					if($remoteContent != false){
						$temp = json_decode($remoteContent, true);
						$changelog = ($temp[$remoteBundle['latest']]);
						$status['update']['number'] += 1;
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
?>
