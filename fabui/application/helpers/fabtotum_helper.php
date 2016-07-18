<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
if ( !function_exists('createDefaultSettings'))
{
	/**
	 * 
	 * Create ./settings/default_settings.json with default data
	 * 
	 * 
	 */
	function createDefaultSettings()
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->config->load('fabtotum');
		
		$dafault_settings = array(
			'color'         	 => array('r'=>255, 'g'=>255, 'b'=>255),
			'safety'        	 => array('door'=>1, 'collision_warning'=>1),
			'switch'        	 => 0,
			'feeder'        	 => array('disengage-offset'=> 2, 'show' => true),
			'milling'       	 => array('layer_offset' => 12),
			'e'             	 => 3048.1593,
			'a'             	 => 177.777778,
			'customized_actions' => array('bothy' => 'none', 'bothz' => 'none'),
			'api'                => array('keys' => array()),
			'zprobe'        	 => array('enable'=>0, 'zmax'=>206),
			'settings_type' 	 => 'default',
			'hardware'     	 	 => array('head' => $CI->config->item('heads').'/hybrid_head.json'),
			'print'         	 => array('pre_heating' => array('nozzle' => 150, 'bed'=>50)),
			'invert_x_endstop_logic' => false
		);
		write_file($CI->config->item('default_settings'), json_encode($dafault_settings));
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
	function loadSettings($type = 'default')
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->config->load('fabtotum');
		$settings = json_decode(file_get_contents($CI->config->item($type.'_settings')), true);
		return $settings;
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
	function saveSettings($data, $type = 'default')
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->config->load('fabtotum');
		
		return write_file($CI->config->item($type.'_settings'), json_encode($data));
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('loadHead'))
{
	/**
	 * Load installed head
	 */	
	function loadHead($type = 'default')
	{
		$settings = loadSettings();
		return json_decode(file_get_contents($settings['hardware']['head']), true);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('doCommandLine'))
{
	/**
	 * @param $script name
	 * @param args
	 * doCL => do Command Line
	 * exec script from command line
	 */
	function doCommandLine($bin, $scriptPath, $args = '')
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$CI->load->helper('utility_helper');
		
		$command = $bin.' '.$scriptPath.' ';
		
		if(is_array($args) || $args != ''){
			if(is_assoc($args)){
				foreach($args as $key => $value){
					// if key exists and is not an array's index	
					if(array_key_exists($key, $args) && $key != '' && !is_int($key)){
						$command .= $key.' ';
					}
					if($value != '') $command .= ' '.$value.' ';
				}
			}else{
				foreach($args as $arg){
					$command .= $arg.' ';
				}
			}
		}
		log_message('debug', $command);
		return shell_exec($command);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('doMacro'))
{
	/**
	 * @param $macroName
	 * @param $traceFile
	 * @param $responseFile
	 * @param $extrArgs
	 * Exec macro operation
	 * 
	 */ 
	function doMacro($macroName, $traceFile = '', $responseFile = '', $extrArgs = '')
	{
		if($macroName == '') return;
		//load CI instancem, helpers, config
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->config->load('fabtotum');
		
		$extPath = $CI->config->item('ext_path');
		if($traceFile == '' or $traceFile == null)        $traceFile    = $CI->config->item('trace');
		if($responseFile == '' or $responseFile == null ) $responseFile = $CI->config->item('macro_response');
		
		doCommandLine('python', $extPath.'py/gmacro.py', is_array($extrArgs) ? array_merge(array($macroName, $traceFile, $responseFile), $extrArgs) : array($macroName, $traceFile, $responseFile, $extrArgs));
		//if response is false means that macro failed
		$response = str_replace('<br>', '', trim(file_get_contents($responseFile))) == 'true' ? true : false;
		return array('response' => $response, 'trace' => file_get_contents($traceFile));
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
			$re = "\"M(\d+)\sS([+|-]*[0-9]*.[0-9]*)\""; //regular expression to catch temperatures
			$extruderGCodes = array(109);
			$bedGCodes      = array(190);
			$extruderTemp = 1;
			$bedTemp      = 1;
			//read first $numLines lines of the file
			$lines = explode(PHP_EOL, doCommandLine('head', '"'.$file.'"', array('-n' => $numLines)));
			foreach($lines as $line){
				preg_match($re, $line, $matches);
				if(count($matches) > 0){
					if(in_array($matches[1], $extruderGCodes)) $extruderTemp = $matches[2];
					if(in_array($matches[1], $bedGCodes))      $bedTemp      = $matches[2];
				}
				if($bedTemp > 1 && $extruderTemp > 1) break;
			}
			return array('extruder' => intval($extruderTemp), 'bed' => intval($bedTemp));
		}else 
			return false;
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
		writeToCommandFile('!reset');
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
		doCommandLine('/etc/init.d/fabui', 'restart');
		resetController();
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
	function checkManufactoring($filePath, $numLines = 100)
	{
		$subtractiveRe = "/(M3\\s)|(M5\\s)|(M4\\s)|(M03\\s)/"; 
		$lines = explode(PHP_EOL, doCommandLine('head', '"'.$filePath.'"', array('-n' => $numLines)));
		foreach($lines as $line){
			if(substr( $line, 0, 1 ) !== ';'){
				preg_match($subtractiveRe, $line, $matches);
				if(count($matches) > 0){
					return 'subtractive';
				}
			}
		}
		return 'additive';
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('startPrint'))
{
	/**
	 * 
	 */
	function startPrint($gcodeFilePath, $taskID = 0)
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum'); 
		$extPath = $CI->config->item('ext_path');
		doCommandLine('python', $extPath.'/py/print.py '.$taskID.' "'.$gcodeFilePath.'" > /dev/null & echo $! > /run/task_create.pid');	
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
		writeToCommandFile('!abort');
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
		writeToCommandFile('!pause');
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
		writeToCommandFile('!resume');
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
		writeToCommandFile('!flow_rate:'.$value);
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
		writeToCommandFile('!speed:'.$value);
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
		$sign = substr($value, 0,1);
		$value = str_replace($sign, '' , $value);
		$command = $sign == '-' ? '!z_minus' : '!z_plus';
		writeToCommandFile($command.':'.$value);
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
		writeToCommandFile('!fan:'.$value);
	}
}
?>