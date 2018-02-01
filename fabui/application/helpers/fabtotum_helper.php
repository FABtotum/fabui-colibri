<?php
/**
 * @file fabtotum_helper.php
 * @brief FABtotum helper function
 * 
 * @author Krios Mane <km@fabtotum.com>
 * @author Daniel Kesler <dk@fabtotum.com>
 * @version 0.1
 * @copyright https://opensource.org/licenses/GPL-3.0
 * 
 */

// Create /var/lib/fabui/settings/default_settings.json with default data
if ( !function_exists('createDefaultSettings'))
{

	function createDefaultSettings()
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->config->load('fabtotum');
		
		$settings = array(
			'color'         	 => array('r'=>255, 'g'=>255, 'b'=>255),
			'safety'        	 => array('door'=>1, 'collision_warning'=>1),
			'switch'        	 => 0,
			'feeder'        	 => array('disengage_offset'=> 2, 'available' => true, 'engage' => false),
			'milling'       	 => array('layer_offset' => 12),
			'customized_actions' => array('bothy' => 'none', 'bothz' => 'none'),
			'api'                => array('keys' => array()),
			'zprobe'        	 => array('enable'=> 0, 'zmax'=> 241.5),
			'z_max_offset'       => 241.5,
			'settings_type' 	 => 'default',
			'hardware'     	 	 => array(
				'head'   => 'printing_head', 
				'feeder' => 'built_in_feeder', 
				'camera' => array('version' => 'camera_v1', 'available' => false),
				'bed'    => array('enable'=> true)
			),
			'print'         	 => array('pre_heating' => array('nozzle' => 150, 'bed'=>50), 'calibration' => 'homing'),
			'stored_position'	 => array(),
			'custom'             => array(
				'overrides' => '',
				'invert_x_endstop_logic' =>false
			),
			'filament' 			 => array('type'=>'pla', 'inserted' => false),
			'wire_end'           => 0,
			'scan'               => array('available' => false),
			'probe'              => array('e' => 127, 'r' => 25, 'enable' => 0)
 		);
		write_file($CI->config->item('settings'), json_encode($settings, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK));
	}	
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('loadSettings'))
{
	/**
	 * 
	 * 
	 *  Load settings configuration
	 *  @return settings configuration
	 * 
	 */
	function loadSettings($factory = false)
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->config->load('fabtotum');
		
		if($factory){
			$factory_file = $CI->config->item('fabui_path').'settings/settings.json';
			if(file_exists($factory_file))
				$settings = json_decode(file_get_contents($factory_file), true);
			else
				$settings = json_decode(file_get_contents($CI->config->item('settings')), true);
		}else{
			$settings = json_decode(file_get_contents($CI->config->item('settings')), true);
		}
		return $settings;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('loadGCodeInfo'))
{
	/**
	 * 
	 * 
	 *  Load GCode descriptions
	 *  @return GCode info
	 * 
	 */
	function loadGCodeInfo()
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->config->load('fabtotum');
		$info = json_decode(file_get_contents($CI->config->item('settings_path') . 'gcodes.json'), true);
		//~ $info = json_decode(file_get_contents('/usr/share/fabui/settings/gcodes.json'), true);
		return $info;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('saveSettings'))
{
	/**
	 * 
	 * @param $data => data to save
	 * @param $type => wich settings to save
	 * 
	 * 
	 */
	function saveSettings($data)
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->config->load('fabtotum');
		$result = write_file($CI->config->item('settings'), json_encode($data, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK));
		xmlrpcReloadConfig();
		return $result;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('savePosition'))
{
	/**
	 * 
	 * @param $x, $y, $z => head position
	 * @param $task_type => task type to save the position to
	 * 
	 * 
	 */
	function savePosition($x, $y, $z, $task_type)
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$data = loadSettings();
		
		if( !array_key_exists('stored_position', $data) )
		{
			$data['stored_position'] = array();
		}
		else
		{
			if( !array_key_exists($task_type,$data['stored_position']) )
			{
				$old_x = $data['stored_position']['x'];
				$old_y = $data['stored_position']['y'];
				$old_z = $data['stored_position']['z'];
				
				// Preserve previous values if they have not been set
				if($x == 'undefined' and $old_x != 'x')
					$x = $old_x;
				if($y == 'undefined' and $old_y != 'y')
					$y = $old_y;
				if($z == 'undefined' and $old_z != 'z')
					$z = $old_z;
			}
			
		}
		
		$data['stored_position'][$task_type] = array('x' => $x, 'y' => $y, 'z' => $z);
		
		saveSettings($data);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('loadPosition'))
{
	function loadPosition($task_type = '')
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$data = loadSettings();
		
		if( !array_key_exists('stored_position', $data) )
			return array("x" => "undefined", "y" => "undefined", "z" => "undefined");
			
		if($task_type == '')
			return $data['stored_position'];
			
		if( array_key_exists($task_type, $data['stored_position']) )
			return $data['stored_position'][$task_type];
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getCameraVersion'))
{
	/**
	 * 
	 * 
	 *  Get Camera version
	 *  @return v1 or v2
	 * 
	 */
	function getCameraVersion()
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		return $CI->config->item('camera_version');
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('detectCamera'))
{
	/**
	 * 
	 * 
	 * 
	 */
	function detectCamera()
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		startPyScript('setCamera.py', '', false, true);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('isCameraPresent'))
{
	/**
	 * 
	 * Return true if camera was detected.
	 * 
	 */
	function isCameraPresent()
	{
		$CI =& get_instance();
		return isset($CI->config->config['camera_enabled']) ? $CI->config->config['camera_enabled'] : true;
	}
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('loadHeads'))
{
	/**
	 *
	 *
	 *  Load settings configuration
	 *  @return settings configuration
	 *
	 */
	function loadHeads()
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->config->load('fabtotum');
		#$settings = json_decode(file_get_contents($CI->config->item($type.'_settings')), true);
		$heads_dir = $CI->config->item('heads');
		
		$heads_files = array();
		foreach (glob($heads_dir.'/*.json') as $zip) {
			$info = get_file_info($zip);
			$heads_files [] = $info['name'];
		}
		$heads = array();
		$constants = get_defined_constants(true);
		$json_errors = array();
		foreach ($constants["json"] as $name => $value) {
			if (!strncmp($name, "JSON_ERROR_", 11)) {
				$json_errors[$value] = $name;
			}
		}
		foreach($heads_files as $head)
		{
			$head_file = $heads_dir . '/' . $head;
			$key = basename($head_file, '.json');
			$content = file_get_contents($head_file);
			// UTF-8 safety
			$content = iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($content));
			$heads[$key] = json_decode($content , true);
			$heads[$key]['filename'] = $key;
		}
		
		//sort heads
		uasort($heads, 'headsSort');
		
		return $heads;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('headsSort')){
    
    /*
     *
     */
    function headsSort($itemA, $itemB)
    {
        if(!isset($itemA['order']) || !isset($itemB['order'])) return true;
        
        if ($itemA['order'] == $itemB['order']) {
            return 0;
        }
        return ($itemA['order'] > $itemB['order']) ? 1 : -1;
    }
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('loadHead')){
	/**
	 * 
	 */
	function loadHead($head_name)
	{
		$heads = loadHeads();
		if(array_key_exists ($head_name, $heads)){
			return $heads[$head_name];
		}
		return false;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('loadLaserHeads'))
{
    /**
     * 
     */
    function loadLaserHeads()
    {
        $all_heads = loadHeads();
        $heads = array();
        foreach($all_heads as $idx => $h){   
            if(in_array('laser', $h['capabilities'])){
                $heads[$idx] = $h;
            }
        }
        return $heads;
    }
    
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('loadFeeders'))
{
	/**
	 *
	 *
	 *  Load settings configuration
	 *  @return settings configuration
	 *
	 */
	function loadFeeders()
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->config->load('fabtotum');
		#$settings = json_decode(file_get_contents($CI->config->item($type.'_settings')), true);
		$feeders_dir = $CI->config->item('feeders');
		
		$feeders_files = array();
		foreach (glob($feeders_dir.'/*.json') as $json) {
			$info = get_file_info($json);
			$feeders_files [] = $info['name'];
		}
		$feeders = array();
		$constants = get_defined_constants(true);
		$json_errors = array();
		foreach ($constants["json"] as $name => $value) {
			if (!strncmp($name, "JSON_ERROR_", 11)) {
				$json_errors[$value] = $name;
			}
		}
		foreach($feeders_files as $feeder)
		{
			$feeder_file = $feeders_dir . '/' . $feeder;
			$key = basename($feeder_file, '.json');
			$content = file_get_contents($feeder_file);
			// UTF-8 safety
			$content = iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($content));
			$info = json_decode($content , true);
			$info['is_4thaxis'] = false;
			$feeders[$key] = $info;
		}
		
		if( canHeadSupport('feeder') && !canHeadSupport('4thaxis'))
		{
			$head = getInstalledHeadInfo();
			$_data = loadSettings();
			$key = $_data['hardware']['head'];
			$info = $head['feeder'];
			$info['name'] = $head['name'];
			$info['description'] = $head['description'];
			$info['link'] = $head['link'];
			$info['is_4thaxis'] = false;
			$fw_id = (int)$head['fw_id'];
			if($fw_id){
				$info['factory'] = 1;
			}
			$feeders[$key] = $info;
		}
		
		if( canHeadSupport('4thaxis') && !canHeadSupport('feeder'))
		{
			$head = getInstalledHeadInfo();
			$_data = loadSettings();
			$key = $_data['hardware']['head'];
			$info = $head['4thaxis'];
			$info['name'] = $head['name'];
			$info['description'] = $head['description'];
			$info['link'] = $head['link'];
			$info['is_4thaxis'] = true;
			
			$fw_id = (int)$head['fw_id'];
			if($fw_id){
				$info['factory'] = 1;
			}
			$feeders[$key] = $info;
		}

		return $feeders;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('loadFeeder'))
{	
	/***
	 * 
	 */
	function loadFeeder($feeder_name)
	{
		$feeders = loadFeeders();
		if(array_key_exists ($feeder_name, $feeders)){
			return $feeders[$feeder_name];
		}
		return false;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('removeHeadInfo'))
{
	function removeHeadInfo($head_filename)
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$heads_dir = $CI->config->item('heads');
		
		$fn = $heads_dir.'/'.$head_filename.'.json';
		
		if( file_exists($fn) )
		{
			unlink($fn);
			return true;
		}
		
		return false;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('removeFeederInfo'))
{
	function removeFeederInfo($feeder_filename)
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$feeders_dir = $CI->config->item('feeders');
		
		$fn = $feeders_dir.'/'.$feeder_filename.'.json';
		
		if( file_exists($fn) )
		{
			unlink($fn);
			return true;
		}
		
		return false;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('saveHeadInfo'))
{
	function saveHeadInfo($info, $head_name, $restoreFactory = false)
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$CI->load->helper("file");
		$heads_dir = $CI->config->item('heads');
		
		$fn = $heads_dir.'/'.$head_name.'.json';
		if($restoreFactory == false){
			if(!file_exists($fn)){
				write_file($fn, "");
			}
			$currentInfo = json_decode(file_get_contents($fn), true);
			if(!is_array($currentInfo)) $currentInfo = array();
			$newInfo = array_replace_recursive($currentInfo, $info);
		}else{
			$newInfo = $info;
		}
		$content = json_encode($newInfo, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
		return file_put_contents($fn, $content) > 0;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('saveFeederInfo'))
{
	function saveFeederInfo($info, $feeder_name)
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$feeders_dir = $CI->config->item('feeders');
		$heads_dir = $CI->config->item('heads');
		
		$fn = $feeders_dir.'/'.$feeder_name.'.json';
		
		if( isFeederInHead($feeder_name) )
		{
			$heads = loadHeads();
			
			unset($info['name']);
			unset($info['description']);
			unset($info['link']);
			unset($info['is_4thaxis']);
			
			$heads[$feeder_name]['feeder'] = $info;
			
			saveHeadInfo($heads[$feeder_name], $feeder_name);
		}
		else if( is4thaxisInHead($feeder_name) )
		{
			$heads = loadHeads();
			
			unset($info['name']);
			unset($info['description']);
			unset($info['link']);
			unset($info['is_4thaxis']);
			// Remove feeder related attributes
			unset($info['steps_per_unit']);
			unset($info['retract_amount']);
			unset($info['retract_feedrate']);
			unset($info['retract_acceleration']);
			unset($info['tube_length']);
			
			$heads[$feeder_name]['4thaxis'] = $info;
			
			saveHeadInfo($heads[$feeder_name], $feeder_name);
		}
		else
		{
			unset($info['is_4thaxis']);
			
			if( file_exists($fn) )
			{
				$oldInfo = json_decode(file_get_contents($fn), true);
				$content = json_encode(array_replace_recursive($oldInfo, $info), JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
			}
			else
			{
				$content = json_encode($info, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
			}
			return file_put_contents($fn, $content) > 0;
		}
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('saveInfoToInstalledHead'))
{
	function saveInfoToInstalledHead($info)
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$heads_dir = $CI->config->item('heads');
		$_data = loadSettings();
		$head_name = $_data['hardware']['head'];
		return saveHeadInfo($info, $head_name);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('saveInfoToInstalledFeeder'))
{
	function saveInfoToInstalledFeeder($info)
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$_data = loadSettings();
		$feeder_name = $_data['hardware']['feeder'];
		return saveFeederInfo($info, $feeder_name);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getInstalledHeadInfo'))
{
	/**
	 * Load installed head information
	 */	
	function getInstalledHeadInfo()
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->config->load('fabtotum');
		$heads_dir = $CI->config->item('heads');
		
		$_data = loadSettings();
		
		$head_filename =  $heads_dir .'/'. $_data['hardware']['head'] . '.json';
		$info = false;
		if(file_exists($head_filename)){
			$info = json_decode(file_get_contents($head_filename), true);
			$fw_id = intval($info['fw_id']);
			if( $fw_id < 100 )
			{
				$info['image_src'] = '/assets/img/head/photo/' . $_data['hardware']['head'] . '.png';
			}
			else
			{
				// @TODO: support for custom images
				$info['image_src'] = '/assets/img/head/head_shape.png';
			}
			
			if(!isset($info['nozzle_offset']))
				$info['nozzle_offset'] = 0;
			
			$info['filename'] = basename($head_filename, '.json');
		}
		
		return $info;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getInstalledFeederInfo'))
{
	/**
	 * Load installed feeder information
	 */	
	function getInstalledFeederInfo()
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->config->load('fabtotum');
		
		$_data = loadSettings();
		$feeder_name = $_data['hardware']['feeder'];
		
		$feeders = loadFeeders();
		return $feeders[$feeder_name];
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('isLaserProHead'))
{
	/**
	 * Check whether if laser head is pro
	 * @param array $head head info (same as getInstalledHeadInfo() )
	 * @return true | false
	 */
	function isLaserProHead($head = array())
	{
		if(empty($head)){
			$head = getInstalledHeadInfo();
		}
		
		$pro_heads = array(7);
		
		return in_array(intval($head['fw_id']),  $pro_heads);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('restoreHeadFactorySettings'))
{
	/**
	 * restore factory settings for head
	 */
	function restoreHeadFactorySettings($head_file_name)
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->config->load('fabtotum');
		$heads_dir        = $CI->config->item('heads');
		$factory_dir      = $CI->config->item('fabui_path').'heads/';
		if(file_exists($factory_dir.$head_file_name.'.json')){
			$factory_settings = json_decode(file_get_contents($factory_dir.$head_file_name.'.json'), true);
			return saveHeadInfo($factory_settings, $head_file_name, true);
		}
		return false;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('restoreFeederFactorySettings'))
{
	/**
	 * restore factory settings for head
	 */
	function restoreFeederFactorySettings($feeder_file_name)
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->config->load('fabtotum');
		$heads_dir        = $CI->config->item('feeders');
		$factory_dir      = $CI->config->item('fabui_path').'feeders/';
		if(file_exists($factory_dir.$feeder_file_name.'.json')){
			$factory_settings = json_decode(file_get_contents($factory_dir.$feeder_file_name.'.json'), true);
			return saveFeederInfo($factory_settings, $feeder_file_name);
		}
		return false;
	}
}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('safetyCheck'))
{
	/**
	 * Check whether the installed head supports a specific feature and bed is flipped to heater side
	 * @param feature Feature to look for (print, mill, laser...)
	 * @param heated_bed true|false|'any'
	 */
	function safetyCheck($feature, $heated_bed = 'any')
	{
		
		$head_in_place = isHeadInPlace();
		$bed_enabled   = isBedEnabled();
		
		$result = array(
			'head_is_ok'    => false,
			'head_info'     => getInstalledHeadInfo(),
			'head_in_place' => $head_in_place,
			'bed_enabled'   => $bed_enabled
		);
		
		$result['head_is_ok'] = canHeadSupport($feature);
		$result['all_is_ok'] = $result['head_is_ok']  && $head_in_place;
		
		if($feature == 'mill'){
			if($result['head_is_ok'] == true && $head_in_place == false)
				$result['all_is_ok'] = true;
		}
		
		$bed_in_place_str = "no";
		$result['bed_in_place'] = false;
		
		if($bed_enabled){
			$bed_in_place  = isBedInPlace();
			$bed_in_place_str = $bed_in_place?"yes":"no";
			$result['bed_in_place'] = $bed_in_place;
			$result['bed_is_ok'] = ($heated_bed == $bed_in_place_str) || ($heated_bed == 'any');
		}
		
		$result['bed_is_ok'] = ($heated_bed == $bed_in_place_str) || ($heated_bed == 'any');
		$result['all_is_ok'] = $result['head_is_ok'] && $result['bed_is_ok'];
		
		return $result;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('canHeadSupport'))
{
	/**
	 * Check whether the installed head supports a specific feature
	 * @param feature Feature to look for (print, mill, laser...)
	 */
	function canHeadSupport($feature)
	{
		$data = getInstalledHeadInfo();
		
		if(isset($data['capabilities']))
		{
			return in_array($feature, $data['capabilities']);
		}
		
		return false;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('isFeederInHead'))
{
	/**
	 * Check whether the feeder is part of the head.
	 */
	function isFeederInHead($feeder_name)
	{
		$heads = loadHeads();
		if( array_key_exists($feeder_name, $heads) )
		{
			$info = loadHead($feeder_name);
			if(isset($info['capabilities']))
			{
				return in_array('feeder', $info['capabilities']);
			}
			
		}
		return false;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('is4thaxisInHead'))
{
	/**
	 * Check whether the 4th-axis is part of the head.
	 */
	function is4thaxisInHead($fourthaxis_name)
	{
		$heads = loadHeads();
		if( array_key_exists($fourthaxis_name, $heads) )
		{
			$info = loadHead($fourthaxis_name);
			if(isset($info['capabilities']))
			{
				return in_array('4thaxis', $info['capabilities']);
			}
			
		}
		
		return false;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('isHeadInPlace'))
{
	function isHeadInPlace()
	{
		/**
		 * check if head in properly inserted
		 * @return boolean
		 * _0 is for check timestamp with index 0
		 * more details JogFactory -> buildCommands 
		 */
		$timestamp = time().rand();
		$reply = doGCode(array('M745'), $timestamp);
		if( isset($reply['commands'][$timestamp.'_0']))
		{
			/*
			foreach($reply['commands'] as $value)
			{
				//~ $join = join('-', $value['reply']);
				//~ return $join == "TRIGGERED-ok";
				return $value['reply'][0] == "TRIGGERED";
			}*/
			return $reply['commands'][$timestamp.'_0']['reply'][0] == "TRIGGERED";
		}
		return false;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('isBedinPlace'))
{
	/**
	 * check if bed is inserted
	 * @return boolean
	 * _0 is for check timestamp with index 0
	 * more details JogFactory -> buildCommands
	 */
	function isBedInPlace()
	{
		$timestamp = time().rand();
		$reply     = doGCode(array('M744'), $timestamp);
		
		if( isset($reply['commands'][$timestamp.'_0']))
		{
			/*
			foreach($reply['commands'] as $value)
			{
				//~ $join = join('-', $value['reply']);
				//~ return $join == "TRIGGERED-ok";
				return $value['reply'][0] == "TRIGGERED";
			}*/
			return $reply['commands'][$timestamp.'_0']['reply'][0] == "TRIGGERED";
		}
		return false;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('isBedEnabled'))
{
	/***
	 * return if the bed is enabled
	 * default is true and always it will be
	 */
	function isBedEnabled()
	{
		$data = loadSettings();
		if(!isset($data['hardware']['bed'])) return true;
		return $data['hardware']['bed']['enable'];
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('canFeederBeEngaged'))
{
	function canFeederBeEngaged()
	{
		$_data = loadSettings();
		$settings_type = $_data['settings_type'];
		if (isset($_data['settings_type']) && $_data['settings_type'] == 'custom') {
			$_data = loadSettings( "custom" );
		}
		return $_data['feeder']['show'];
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('doCommandLine'))
{
	/**
	 * Execute script from command line
	 * @param bin Script filename
	 * @param scriptPath Directory the script is located in
	 * @param args Arguments
	 * @param background Run script in the background an return control
	 */
	function doCommandLine($bin, $scriptPath, $args = '', $background = false, $log_file = 'doCommandLine.log')
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$CI->load->helper('utility_helper');
		
		$command = $bin.' '.$scriptPath.' ';
		
		
		
		if(is_array($args) && $args != ''){
			if(is_array($args)){
				foreach($args as $key => $value){
					// if key exists and is not an array's index	
					if(array_key_exists($key, $args) && $key != '' && !is_int($key)){
						$command .= $key.' ';
					}
					if($value != '') {
						if(is_int($value)) $command .= ' '.$value.' ';
						else $command .= ' "'.$value.'" ';
					}
				}
			}else{
				foreach($args as $arg){
					$command .= $arg.' ';
				}
			}
		}
		else
		{
			$command .= $args;
		}
		
		shell_exec("echo '".$command."' > /tmp/fabui/doCommandLine");
		
		/* Note to myself: DO NOT PLAY WITH THESE COMMANDS !!!!! */
		if($background) 
			$command .= ' &> /tmp/fabui/'.$log_file.' 2>&1 &';
		else
			$command .= ' | tee /tmp/fabui/'.$log_file;
		/* Note to myself: DO NOT PLAY WITH THESE COMMANDS !!!!! */
		
		log_message('debug', $command);
		return shell_exec($command);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('sendToXmlrpcServer'))
{
	function sendToXmlrpcServer($method, $data = array())
	{
		$CI =& get_instance(); //init ci instance
		$CI->config->load('fabtotum');
		$CI->load->library('xmlrpc');
		
		$CI->xmlrpc->server('127.0.0.1/FABUI', $CI->config->item('xmlrpc_port'));
		$CI->xmlrpc->method($method);
		$CI->xmlrpc->timeout(120*5);
		
		if(!is_array($data)) $data = array($data);
		
		$CI->xmlrpc->request( $data );
		
		$response = false;
		$reply    = '';
		$message = '';
		
		$trace = '';
		
		if ( !$CI->xmlrpc->send_request())
		{
			$reply    = $CI->xmlrpc->display_error();
			$response = False;
			$message    = 'request had an error: '.$CI->xmlrpc->display_error();
		}
		else
		{	
			if(is_array($CI->xmlrpc->display_response())){
				$reply = $CI->xmlrpc->display_response();
				$response = True;
			}else {
				$tmp = json_decode( $CI->xmlrpc->display_response(), true );
				$trace = $CI->xmlrpc->display_response();
				
				if(json_last_error()){
					$reply = $CI->xmlrpc->display_response();
					$response = 'error';
					$message = json_last_error_msg();
				}else{
					if($tmp['response'] == 'success')
					{
						$response = true;
					}
					$response = $tmp['response'];
					$reply    = $tmp['reply'];
					$message  = $tmp['message'];
				}
			}
		}
		return array('response' => $response, 'reply' => $reply, 'message' => $message);
		
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('doMacro'))
{
	function doMacro($macroName, $traceFile = '', $extrArgs = '')
	{	
		$CI =& get_instance(); //init ci instance
		
		if($traceFile == '' or $traceFile == null){
			
			$CI->config->load('fabtotum');
			$traceFile = $CI->config->item('trace');
		}
		
		if( !is_array($extrArgs) )
		{
			$extrArgs = array($extrArgs);
		}
		
		$CI->load->helper('language_helper');
		$language_code = getCurrentLanguage().'.UTF-8';
		
		$data = array( array($macroName, 'string'),
				array($extrArgs, 'array'),
				array(true, 'boolean'),
				array($language_code, 'string')
		);
		
		log_message('debug', "do_macro: ".$macroName);
		$serverResponse = sendToXmlrpcServer('do_macro', $data);
		
		$serverResponse['lang'] = $language_code ;
		
		if($serverResponse['response'] != 'success'){
			$serverResponse['trace'] = $serverResponse['message'];
		}else{
			$serverResponse['trace'] = file_get_contents($traceFile);
		}
		return $serverResponse;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('doGCode'))
{
	/**
	 * @param $gcodes GCode command or an array of GCode commands
	 * Send gcode commands using JogFactory
	 */
	function doGCode($gcodes, $timestamp = '')
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->load->library('JogFactory', '', 'jogFactory');
		$CI->jogFactory->sendCommands( $gcodes, $timestamp);
		return $CI->jogFactory->response();
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('resetController'))
{
	/**
	 * Reset controller board
	 */
	function resetController()
	{
		return sendToXmlrpcServer('do_reset');
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('emergency'))
{
	/**
	 * Stop all running tasks and scripts and restart main scripts
	 */
	function emergency()
	{
		doCommandLine('sudo /etc/init.d/fabui', 'emergency');
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('restart'))
{
	/**
	 * Restart main daemon's scripts
	 */
	function restart()
	{
		doCommandLine('sudo /etc/init.d/fabui', 'restart');
	}
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('stopServices'))
{
	/**
	 * Stop main daemons
	 */
	function stopServices()
	{
		doCommandLine('sudo /etc/init.d/fabui', 'stop');
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('startServices'))
{
	/**
	 * Start main daemons
	 */
	function startServices()
	{
		doCommandLine('sudo /etc/init.d/fabui', 'start');
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('readInitialTemperatures')) 
{
	/**
	 * @param $file
	 * @param $numLines
	 * get the initial temperatures of an additive file
	 */
	function readInitialTemperatures($file, $numLines = 500){
		
		if(file_exists($file)){
			//$re = "\"M(\d+)\sS([+|-]*[0-9]*.[0-9]*)\""; //regular expression to catch temperatures
			$extruderGCodes = array('M109');
			$bedGCodes      = array('M190');
			$extruderTemp = 1;
			$bedTemp      = 1;
			//read first $numLines lines of the file
			$lines = explode(PHP_EOL, doCommandLine('head', '"'.$file.'"', array('-n' => $numLines)));
			foreach($lines as $line){
				$tags = explode(' ', $line);
				if( in_array($tags[0], $extruderGCodes) )
				{
					foreach($tags as $tag)
					{
						if($tag[0] == 'S')
						{
							$extruderTemp = intval(substr($tag,1));
							break;
						}
					}
				}
				if( in_array($tags[0], $bedGCodes) )
				{
					foreach($tags as $tag)
					{
						if($tag[0] == 'S')
						{
							$bedTemp = intval(substr($tag,1));
							break;
						}
					}
				}
				if($bedTemp > 1 && $extruderTemp > 1) break;
			}
			return array('extruder' => intval($extruderTemp), 'bed' => intval($bedTemp));
		}else 
			return false;
	}
	
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('checkManufactoring'))
{
	/**
	 * @param (string) $file_path
	 * @param (int) $numLines, number of lines to read
	 * retunr what type of manufactoring file is
	 */
	function checkManufactoring($filePath, $numLines = 500)
	{
		$args = array(
			'-f' => $filePath,
			'-n' => $numLines
		);
		
		$manufactoring = trim(startPyScript('check_manufactoring.py', $args, false, true));
		
		if($manufactoring == '')
		{
			$manufactoring = checkPluginManufacturing($filePath, $numLines);
		}
		
		return $manufactoring;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('writeToCommandFile'))
{
	/**
	 * write to command to command file
	 */
	function writeToCommandFile($command)
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$CI->load->helper('file');
		write_file($CI->config->item('command'), $command);
		log_message('debug', "Command: ".$command);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('abort'))
{
	/**
	 * abort task
	 */
	function abort()
	{
		return sendToXmlrpcServer('do_abort');
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('trigger'))
{
	/**
	 * trigger task notification
	 */
	function trigger($name, $data)
	{
		$args = array($name, $data);
		
		if( is_array($data) )
		{
			$args = array( array($name, 'string'),
						   array($data, 'array')
			);
		}
		
		return sendToXmlrpcServer('do_trigger', $args );
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('xmlrpcReloadConfig'))
{
	/**
	 * Reload XML-RPC server configuration
	 */
	function xmlrpcReloadConfig()
	{
		return sendToXmlrpcServer('reload_config');
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('pause'))
{
	/**
	 * pause task
	 */
	function pause()
	{
		return sendToXmlrpcServer('do_pause');
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('resume'))
{
	/**
	 * resume task
	 */
	function resume()
	{
		return sendToXmlrpcServer('do_resume');
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('flowRate'))
{
	/**
	 * resume task
	 */
	function flowRate($value)
	{
		return sendToXmlrpcServer('set_flow_rate', $value);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('speed'))
{
	/**
	 * set speed override
	 */
	function speed($value)
	{
		return sendToXmlrpcServer('set_speed', array($value));
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('sendEmail'))
{
	/**
	 * 
	 */
	function sendEmail($value)
	{
		return sendToXmlrpcServer('set_send_email', $value);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('autoShutdown'))
{
	/**
	 * 
	 */
	function autoShutdown($value)
	{
		return sendToXmlrpcServer('set_auto_shutdown', $value);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('zHeight'))
{
	/**
	 * set z height override
	 */
	function zHeight($value)
	{
		return sendToXmlrpcServer('set_z_modify', $value);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('fan'))
{
	/**
	 * set fan override
	 */
	function fan($value, $percent = true)
	{
		if($percent){
			$value = (($value/100)*255);
		}
		return sendToXmlrpcServer('set_fan', $value);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('rpm'))
{
	/**
	 * set rpm override
	 */
	function rpm($value)
	{
		return sendToXmlrpcServer('set_rpm', array($value));
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('pwm_laser'))
{
	/**
	 * set pwm override
	 */
	function pwm_laser($value)
	{
		return sendToXmlrpcServer('set_laser', array($value));
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('pwm'))
{
	/**
	 * set pwm override
	 */
	function pwm($value)
	{
		return sendToXmlrpcServer('set_pwm', array($value));
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('clearJogResponse'))
{
	/**
	 * send command to clear jog response
	 */
	function clearJogResponse()
	{
		//writeToCommandFile('!jog_clear');
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('startPyScript'))
{
	/**
	 * start python task
	 */
	function startPyScript($script, $params = '', $background = true, $sudo = false, $log_file = 'doCommandLine.log')
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$extPath = $CI->config->item('ext_path');
		// TODO: check trailing /
		$cmd = 'python';
		if($sudo)
			$cmd = 'sudo ' . $cmd;
		return doCommandLine($cmd, $extPath.'py/'.$script, $params, $background, $log_file);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('startBashScript'))
{
	/**
	 * start bash script
	 */
	function startBashScript($script, $params = '', $background = true, $sudo = false, $log_file = 'doCommandLine.log')
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$extPath = $CI->config->item('ext_path');
		// TODO: check trailing /
		$cmd = 'sh';
		if($sudo)
			$cmd = 'sudo ' . $cmd;
		return doCommandLine($cmd, $extPath.'bash/'.$script, $params, $background, $log_file);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('resetTaskMonitor'))
{
	/**
	 * reset task monitor values
	 */
	function resetTaskMonitor($resetArray = array())
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->config->load('fabtotum');
		
		//it must exists, but if not
		if(!file_exists($CI->config->item('task_monitor'))) write_file($CI->config->item('task_monitor'), json_encode(array()));
		
		$monitor = json_decode(file_get_contents($CI->config->item('task_monitor')), true);
		unset($monitor['gpusher']);
		unset($monitor['print']);
		unset($monitor['update']);
		if(!is_array($monitor)) $monitor = array();
		
		//override keys value
		$default_monitor['override']['z_override'] = 0;
		$default_monitor['override']['laser']      = 0;
		$default_monitor['override']['speed']      = 100;
		$default_monitor['override']['flow_rate']  = 100;
		$default_monitor['override']['fan']        = 0;
		$default_monitor['override']['rpm']        = 0;
		//task keys value
		$default_monitor['task']['status']         = '';
		$default_monitor['task']['pid']            = '';
		$default_monitor['task']['completed_time'] = 0;
		$default_monitor['task']['estimated_time'] = 0;
		$default_monitor['task']['started_time']   = 0;
		$default_monitor['task']['duration']       = 0;
		$default_monitor['task']['controller']     = '';
		$default_monitor['task']['id']             = '';
		$default_monitor['task']['type']           = '';
		$default_monitor['task']['percent']        = 0;
		
		
		$monitor = array_replace_recursive ($monitor, $default_monitor, $resetArray);
		write_file($CI->config->item('task_monitor'), json_encode($monitor));
	} 
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('resetTrace'))
{
	/**
	 * reset trace file
	 */
	function resetTrace($content = '')
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->config->load('fabtotum');
		
		write_file($CI->config->item('trace'), $content);
	} 
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('setRecovery'))
{
	function setRecovery($mode)
	{
		return startBashScript('recovery.sh', $mode, false, true);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('firmwareInfo'))
{
	function firmwareInfo()
	{
		$tmp   = doMacro('version');
		if($tmp['response'] == true){
			return $tmp['reply'];
		}
		return false;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('bootFilesInfo'))
{
	function bootFilesInfo()
	{
		$version = 'v20170223';
		
		if(file_exists('/mnt/live/mnt/boot/version'))
		{
			$version = trim(file_get_contents('/mnt/live/mnt/boot/version'));
		}
		
		return $version;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('termsAndConditions'))
{
	/**
	 * 
	 */
	function termsAndConditions()
	{
		$CI =& get_instance();
		$CI->load->helper('language_helper');
		return $CI->load->view('layout/conditions/'.getCurrentLanguage(), null, true);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getSerialNumber'))
{
	/**
	 * return printer serial number
	 * you can find it in the back of the unit
	 * @todo
	 */
	function getSerialNumber()
	{ 
		$CI =& get_instance();
		$CI->load->model('Configuration', 'configuration');
		return $CI->configuration->load('serial_number');
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getUnitName'))
{
	/**
	 * return unit name
	 * you can find it in the back of the unit
	 * @todo
	 */
	function getUnitName()
	{
		$CI =& get_instance();
		$CI->load->model('Configuration', 'configuration');
		return $CI->configuration->load('unit_name');
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getUnitColor'))
{
	/**
	 * return unit color
	 *
	 * @todo
	 */
	function getUnitColor()
	{
		$CI =& get_instance();
		$CI->load->model('Configuration', 'configuration');
		return $CI->configuration->load('unit_color');
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getFilamentDescription'))
{
	/**
	 * 
	 * @todo
	 */
	function getFilamentDescription($filament)
	{
		$CI =& get_instance();
		$CI->load->helper('language_helper');
		return $CI->load->view('layout/filaments/'.$filament.'/'.getCurrentLanguage(), null, true);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getFilament'))
{
	/**
	 *
	 * @todo
	 */
	function getFilament($filament)
	{
		$CI =& get_instance();
		$CI->config->load('filaments');
		$filaments = $CI->config->item('filaments');
		return $filaments[$filament];
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('setFilament'))
{
	/**
	 *
	 * @todo
	 */
	function setFilament($filament, $inserted)
	{
		$settings = loadSettings();
		$temp = array(
			'type' => $filament,	
			'inserted' => $inserted
		);
		$settings['filament'] = $temp;
		saveSettings($settings);
		
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getTours'))
{
	/**
	 *
	 * @todo
	 */
	function getTours()
	{
		$tours = array();
		
		$dir    = '/var/www/assets/js/tours/';
		$tour_js_files = scandir($dir);

		foreach($tour_js_files as $tour_file)
		{
			$path_info = pathinfo($tour_file);
			if( $path_info['extension'] == 'js' )
			{
				//echo '<script type="text/javascript" src="/assets/js/tours/'.$tour_file.'?v='.FABUI_VERSION.'"></script>'.PHP_EOL;
				
				//var id = $(this).val().split("_").pop().join("_");
				
				$tour_id = $path_info['filename'];
				
				//$tour_id = preg_replace('/^[0-9]+_/', '', $tour_id);
				
				array_push($tours, $tour_id);
			}
		}
		
		return $tours;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('setSecure'))
{
	/**
	 * 
	 */
	function setSecure($mode = true){
		$CI =& get_instance();
		$CI->load->config('fabtotum');
		$CI->load->helper('file');
		doMacro('clear_errors');
		doMacro('set_ambient_color');
		$notify = json_decode( file_get_contents( $CI->config->item('notify_file') ), true);
		$notify['last_event']['seen'] = true;
		write_file($CI->config->item('notify_file'), json_encode($notify, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK));
		return true;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getAvailableColors'))
{
	/**
	 * 
	 */
	function getAvailableColors()
	{
		return array(
			'white' => _("White"),
			"red"   => _("Red"),
			"black" => _("Black")
		);
	}
	
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('colors_menu'))
{
	/**
	 * 
	 */
	function colors_menu($name = 'colors', $selected = '', $attributes = '')
	{
		$CI =& get_instance();
		$CI->load->helper('form');
		$colors = getAvailableColors();
		
		return form_dropdown($name, $colors, $selected, $attributes);
	}
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('send_via_noreply'))
{
	function send_via_noreply($email, $first_name, $last_name, $subject, $content)
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$CI->load->helper('os_helper');
		
		$url = $CI->config->item('fabtotum_noreply_url');
		
		$fields               = array();
		$fields['email']      = $email;
		$fields['subject']    = $subject;
		$fields['content']    = $content;
		$fields['first_name'] = $first_name;
		$fields['last_name']  = $last_name;
		$fields['from']       = getHostName().' - '._("Your FABtotum");
			
		$fields_string = '';

		foreach ($fields as $key => $value) {
			$fields_string .= $key . '=' . $value . '&';
		}
		
		rtrim($fields_string, '&');
		
		//return $url . ' ' . $fields_string;
		
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, count($fields));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		return curl_exec($ch) == "1";
	}
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('send_password_reset'))
{
    /**
     * @deprecated
     */
	function send_password_reset($email)
	{
		$CI =& get_instance();
		$CI->load->model('User', 'user');
		
		$user = $CI->user->getByEmail($email);
		
		if($user)
		{
			$uid        = $user['id'];
			$first_name = $user['first_name'];
			$last_name  = $user['last_name'];
			
			$token = md5($uid . '-' . $email . '-' . time());
			
			$user_settings = json_decode($user['settings'], true);
			$user_settings['token'] = $token;
		
			$data_update['settings'] = json_encode($user_settings);
			$CI->user->update( $uid, $data_update);
			$complete_url = site_url().'login/resetPassword/'.$token;
			
			$subject = _("Password Reset");
			$content = pyformat( _('Hi {0},<br><br>We\'ve generated a URL to reset your password. If you did not request to reset your password or if you\'ve changed your mind, simply ignore this email and nothing will happen.<br><br>You can reset your password by clicking the following URL:<br><a href="{1}">{1}</a><br><br>If clicking the URL above does not work, copy and paste the URL into a browser window. The URL will only be valid for a limited time and will expire.'), array($first_name, $complete_url) );
			
			return send_via_noreply($email, $first_name, $last_name, $subject, $content);
		}
		else
			return false;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getMakeTaskTypeList'))
{
	function getMakeTaskTypeList()
	{
		$CI =& get_instance();
		$CI->load->helper('plugin_helper');
		
		$defaultList = array(
				'print' => _("Print"),
				'mill'  => _("Mill"),
				'scan'  => _("Scan")
		);
		
		$plugins = getActivePlugins();
		$pluginLists = array();
		foreach($plugins as $plugin => $info){
			foreach($info['hooks'] as $hook)
			{
				if(isset($hook['printtypes'])){
					$pluginLists[$info['name']] = $hook['printtypes'];
				}
			}
		}	
		foreach($pluginLists as $label => $types){
			foreach($types as $type){
				if(!isset($defaultList[$type])){
					$defaultList[$type] = $label;
				}
			}
		}
		return $defaultList;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getState'))
{
	/**
	 * return the state of the printer: IDLE, BUSY, PAUSED, LOCKED
	 * 
	 */
	function getState()
	{
		//@TO DO
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getUnitType'))
{
	/**
	 * return unit type [UNKNWON, GENERAL, CORE, PRO, HYDRA]
	 */
	function getUnitType($hardware_version = '')
	{
		if($hardware_version == ''){
			$macroResponse = doMacro('version');
			if($macroResponse['response']){
				$versions = $macroResponse['reply'];
			}
			$hardware_version= isset($versions['production']['batch']) ? $versions['production']['batch'] : -1;
		}
		switch(true)
		{
			case ($hardware_version== -1):
				$type = 'UNKNOWN';
				break;
			case ($hardware_version >= 0 && $hardware_version < 1000):
				$type = 'GENERAL';
				break;
			case ($hardware_version >= 1000 && $hardware_version < 2000):
				$type = 'CORE';
				break;
			case ($hardware_version >= 2000 && $hardware_version < 3000):
				$type = 'PRO';
				break;
			case ($hardware_version >= 3000 && $hardware_version < 4000):
				$type= 'HYDRA';
				break;
			default:
				$type = 'UNKNOWN';
				break;
		}
		return $type;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getUnitTypeDescription'))
{
    /**
     * return unity type description
     * 
     */
    function getUnitTypeDescription()
    {
        $type = getUnitType();
        
        switch($type){
            case 'UNKNOWN':
            case 'GENERAL':
                return "FABtotum Personal Fabricator";
                break;
            case 'CORE':
                return "FABtotum Core";
                break;
            case 'PRO':
                return "FABtotum Core Pro";
                break;
            case 'HYDRA':
                return "FABtotum Hydra";
                break;
        }
    }
}
?>
