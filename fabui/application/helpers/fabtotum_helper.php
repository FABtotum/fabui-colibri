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
	 * Create /var/lib/fabui/settings/default_settings.json with default data
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
			'hardware'     	 	 => array('head' => $CI->config->item('heads').'/hybrid_head.json', 'camera' => $CI->config->item('cameras').'/camera_v1.json'),
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
		$heads_files = array_diff(scandir($heads_dir), array('..', '.'));

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
		}

		return $heads;
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
	function doCommandLine($bin, $scriptPath, $args = '', $background = false)
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
		if($background) $command .= ' &> /tmp/fabui/doCommandLine.log &';
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
		$CI->xmlrpc->timeout(120);
		$CI->xmlrpc->request( $data );
		
		$response = false;
		$reply    = '';
		$message = '';
		
		if ( !$CI->xmlrpc->send_request())
		{
			$reply    = $CI->xmlrpc->display_error();
			$response = False;
			$message    = 'request had an error: '.$CI->xmlrpc->display_error();
		}
		else
		{
			$tmp = json_decode( $CI->xmlrpc->display_response(), true ); 
			print_r($tmp);
			if($tmp['response'] == 'success')
			{
				$response = True;
			}
			$reply   = $tmp['reply'];
			$message = $tmp['message'];
		}
		return array('response' => $response, 'reply' => $reply, 'message' => $message);
		
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('doMacro'))
{
	function doMacro($macroName, $traceFile = '', $extrArgs = '')
	{		
		if($traceFile == '' or $traceFile == null){
			$CI =& get_instance(); //init ci instance
			$CI->config->load('fabtotum');
			$traceFile = $CI->config->item('trace');
		}
		
		if( !is_array($extrArgs) )
		{
			$extrArgs = array($extrArgs);
		}
		
		$data = array( array($macroName, 'string'),
				array($extrArgs, 'array'),
				array(true, 'boolean')
		);
		
		$serverResponse = sendToXmlrpcServer('do_macro', $data);
		
		if($serverResponse['response'] == false){
			$serverResponse['trace'] = $serverResponse['message'];
		}else{
			$serverResponse['trace'] = file_get_contents($traceFile);
		}
		return $serverResponse;
	}
}
/*
if(!function_exists('doMacro'))
{
	function doMacro($macroName, $traceFile = '', $extrArgs = '')
	{
		$CI =& get_instance(); //init ci instance
		$CI->config->load('fabtotum');
		$CI->load->library('xmlrpc');
		
		$CI->xmlrpc->server('127.0.0.1/FABUI', $CI->config->item('xmlrpc_port'));
		$CI->xmlrpc->method('do_macro');

		if( !is_array($extrArgs) )
		{
			$extrArgs = array($extrArgs);
		}
		
		$CI->xmlrpc->timeout(120);
		
		$data = array( array($macroName, 'string'),
					   array($extrArgs, 'array'),
					   array(true, 'boolean')
				);
		
		$CI->xmlrpc->request( $data );
		
		if($traceFile == '' or $traceFile == null)
			$traceFile = $CI->config->item('trace');

		$_reply = '';
		$_message = '';
		$_response = False;
		
		if ( !$CI->xmlrpc->send_request())
		{
			$_reply = $CI->xmlrpc->display_error();
			$_response = False;
			$trace = 'request had an error: '.$CI->xmlrpc->display_error();
		}
		else
		{
			$tmp = json_decode( $CI->xmlrpc->display_response(), true );
			if($tmp['response'] == 'success')
			{
				$_response = True;
			}
			$_reply   = $tmp['reply'];
			$_message = $tmp['message'];
			$trace	= file_get_contents($traceFile);
		}
		
		return array('reply' => $_reply, 'response' => $_response, 'message' => $_message, 'trace' => $trace);
	}
}
*/
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('doGCode'))
{
	/**
	 * @param $gcodes GCode command or an array of GCode commands
	 * Send gcode commands using JogFactory
	 */
	function doGCode($gcodes)
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->load->library('JogFactory', '', 'jogFactory');
		$CI->jogFactory->sendCommands( $gcodes );
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
		doCommandLine('/etc/init.d/fabui', 'emergency');
		//~ resetController();
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
		//writeToCommandFile('!abort');
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
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('clearJogResponse'))
{
	/**
	 * send command to clear jog response
	 */
	function clearJogResponse()
	{
		writeToCommandFile('!jog_clear');
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('startScript'))
{
	/**
	 * start python task
	 */
	function startScript($script, $params = '', $background = true)
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$extPath = $CI->config->item('ext_path');
		// TODO: check trailing /
		return doCommandLine('python', $extPath.$script, $params, $background);
	}
}
?>
