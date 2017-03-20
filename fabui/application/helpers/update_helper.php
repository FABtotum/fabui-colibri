<?php
/**
 *
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 *
 */

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
		$remoteContent = getRemoteFile($endpoint, false);
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
		$CI->load->helper('plugin_helper');
		$CI->load->helper('os_helper');
		//init
		$remoteMeta      = false;
		$remoteBundles   = false;
		$firmwareRemote  = false;
		$remotePlugins   = false;
		$isInternet      = isInternetAvaialable();
		$bundlesEndpoint = $CI->config->item('colibri_endpoint').getArchitecture();
		$fwEndpoint      = $CI->config->item('firmware_endpoint').'fablin/atmega1280/';
		//get local info
		$localBundles      = getLocalBundles();
		$installedFirmware = firmwareInfo();
		$installedBootfiles = bootFilesInfo();
		$installedPlugins   = getInstalledPlugins();
		
		$firmware['installed']   = $installedFirmware['firmware']['version'];
		$firmware['need_update'] = false;
		
		$bootfiles['installed']   = $installedBootfiles;
		$bootfiles['need_update'] = false;
		
		$plugins['installed'] = getInstalledPlugins();
		$plugins['need_update'] = false;
		
		if($isInternet){ //check only if internet is available
			$remoteMeta        = getSystemRemoteVersions();
			$remoteBundles     = $remoteMeta['bundles'];
			$remoteBootfiles   = $remoteMeta['boot'];
			$remoteBundles     = getRemoteBundles();
			$firmwareRemote    = getRemoteFwVersions();
			$remotePlugins     = getOnlinePlugins();
			
			//retrieve remote firmware info
			$latestFirmwareRemote            = $firmwareRemote['firmware']['latest'];
			$firmware['need_update']         = version_compare($installedFirmware['firmware']['version'], $firmwareRemote['firmware']['latest']) == -1 ? true : false;
			$firmware['remote']              = $firmwareRemote['firmware'][$firmwareRemote['firmware']['latest']];
			$firmware['remote']['changelog'] = getRemoteFile($fwEndpoint.'/latest/changelog.txt', false);

			//retrieve remote bootfiles info
			$bootfiles['need_update']        = version_compare($installedBootfiles, $remoteBootfiles['latest']) == -1 ? true : false;
			$bootfiles['remote']             = array();
			$bootfiles['remote']['version']  = $remoteBootfiles['latest'];
		}
		
		$status = array(
			'bundles'    => array(),
			'boot' => $bootfiles,
			'images' => array(),
			'firmware'   => $firmware,
			'plugins' => array(),
			'update' => array(
				'available' => false,
				'bundles'   => 0,
				'plugins'   => 0,
				'firmware'  => false,
				'boot'      => $bootfiles['need_update'],
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
			if($remoteBundles){ //retrieve remote bundle info
				$remoteBundle = $remoteBundles[$bundleName];
				$latestVersion = str_replace('v', '', $remoteBundle['latest']);
				$needUpdate = version_compare($localBundleData['version'], $latestVersion) == -1 ? true : false;
				$changelog = '';
				if($needUpdate) {
					$remoteContent = getRemoteFile($bundlesEndpoint.'/bundles/'.$bundleName.'/changelog.json', false);
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
		
		foreach($installedPlugins as $pluginSlug => $pluginData)
		{
			$needUpdate = false;
			$latestVersion = 'unknown';
			$changelog = 'unknown';
			$onlineData = 'unknown';
			
			if($remotePlugins)
			{
				// check if there is an online version of this plugin
				if( array_key_exists($pluginSlug, $remotePlugins) )
				{
					$onlineData = $remotePlugins[$pluginSlug];
					$latestVersion = $onlineData['latest'];
					
					$needUpdate = version_compare($pluginData['version'], $onlineData['latest']) == -1 ? true : false;
					$changelog = '';
					if($needUpdate)
					{
						$status['update']['plugins'] += 1;
					}
				}
			}
			
			$status['plugins'][$pluginSlug] = array(
				'latest'      => $latestVersion,
				'need_update' => $needUpdate,
				'changelog'   => $changelog,
				'info'        => $pluginData
			);
		}
		
		$status['update']['available'] = $status['update']['bundles'] > 0 || $status['update']['plugins'] > 0 || $firmware['need_update'] || $bootfiles['need_update'] ? true : false;
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
		$remoteContent = getRemoteFile($endpoint, false);
		if($remoteContent != false) return json_decode($remoteContent, true);
		else false;
	}
}

?>
