<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 * Serial factory
 */
 
 class JogFactory {
	
	protected $CI; //code igniter instance
	//protected $serial; //serial class
	protected $feedrate     = 0;
	protected $step         = 0;
	protected $waitforfinish = false;
	protected $responseType = 'jog'; //type of response (temperature, gcode, serial)
	protected $responseData;
	protected $temperatures = array();
	protected $xmlrpc       = null;
	protected $commands     = array();
	
	/**
	 * class constructor
	 */
  	function __construct($params = array()) 
  	{
  		foreach($params as $key => $value){ //init class attributes (if present)
			if(property_exists($this, $key))
				$this->$key = $value;
		}
		$this->CI =& get_instance(); //init ci instance
		$this->CI->config->load('fabtotum');
		
		$this->CI->load->helper('fabtotum_helper');
		
  	}
  	/**
  	 * build list commands
  	 * @param array commands or string
  	 */
  	function buildCommands($commands, $id_stamp='')
  	{	
		if($id_stamp == '')
		{
			$id_stamp = time();
		}
		
  		if(!is_array($commands) && $commands != '')
  		{
  			$this->commands[$id_stamp] = array('code' =>  $commands);
  		}
  		else
  		{
  			foreach ($commands as $index => $command)
  			{
  				$this->commands[$id_stamp.'_'.$index] = array('code' =>  $command);
  			}
  		}
  	}
  	/**
  	 * @param string $type ['array', 'text']
  	 * return commands lists
  	 */
  	function getCommands($type = 'array')
  	{	
  		if($type == 'array')
  			return $this->commands;
  		else if ($type == 'text'){
  			$text = '';
  			foreach($this->commands as $key => $command){
  				$text .= $command.PHP_EOL;
  			}
  			return $text;
  		}
  	}
	/***
	 * send gcode
	 * @param array|string $commands
	 */
	function sendCommands($commands, $id_stamp='')
	{	
		$this->buildCommands($commands, $id_stamp);
		
		foreach($this->commands as $timestamp => $data){
			
			$responseTemp = sendToXmlrpcServer('send', $data['code']);
			//print_r($responseTemp);
			
			$data['response'] = $responseTemp['response'];
			$data['message']  = $responseTemp['message'];
			$data['reply']    = implode(PHP_EOL,$responseTemp['reply']);
			$this->commands[$timestamp] = $data;
		}
	}
	/**
	 * @return Array data response
	 */
	function response()
	{
		if ($this->responseType == 'jog')
			return array('commands' => $this->commands);
		
		if($this->responseType == 'temperatures')
			return array('temperatures' => $this->temperatures);
	}
	/**
	 * @param $type (serial, temperature, etc..)
	 * set response type
	 */
	public function setResponseType($type)
	{
		$this->responseType = $type;
	}
	/**
	 * @return response type
	 */
	public function getResponseType()
	{
		return $this->responseType;
	}
	/***
	 * @return array status of the printer (temperatures)
	 * get Nozzle and Bed temperatures
	 */
	public function getTemperatures($id_stamp)
	{
		$this->setResponseType('temperatures');
		$this->temperatures = json_decode(file_get_contents($this->CI->config->item('temperature')), true);
		return $this->response('temperatures');
	}
	/***
	 * @param int $temperature
	 * set heated bed temperature
	 */
	public function setBedTemp($temperature, $id_stamp)
	{
		$this->sendCommands('M140 S'.$temperature, $id_stamp);
		return $this->response();
	}
	/**
	 * @param int $temperature
	 * set nozzle temperature
	 */
	public function setExtruderTemp($temperature, $id_stamp)
	{
		$this->sendCommands('M104 S'.$temperature);
		return $this->response();
	}
	/**
	 * @param string $action 
	 * execute movement command
	 */
	public function move($action, $id_stamp)
	{
		if($action == '')
			return array();
		
		$actions['up']         = 'G0 Y+%.2f F%.2f';
		$actions['up-right']   = 'GO Y+%1$.2f X+%1$.2f F%2$.2f';
		$actions['up-left']    = 'G0 Y+%1$.2f X-%1$.2f F%2$.2f';
		$actions['down']       = 'G0 Y-%.2f F%.2f';
		$actions['down-right'] = 'G0 Y-%1$.2f X+%1$.2f F%2$.2f';
		$actions['down-left']  = 'GO Y-%1$.2f X-%1$.2f F%2$.2f';
		$actions['left']       = 'GO X-%.2f F%.2f';
		$actions['right']      = 'GO X+%.2f F%.2f';
		
		$actions['z-up']      = 'GO Z-%.2f F%.2f';
		$actions['z-down']      = 'GO Z+%.2f F%.2f';
		
		if( !array_key_exists($action, $actions) )
			return array();
		
		if($this->waitforfinish)
			$this->sendCommands(array('G91', sprintf($actions[$action], $this->step, $this->feedrate), 'M400', 'G90'), $id_stamp);
		else
			$this->sendCommands(array('G91', sprintf($actions[$action], $this->step, $this->feedrate), 'G90'), $id_stamp);
		return $this->response();
	}
	
	public function home($action, $id_stamp)
	{
		$actions['home-xy']      = 'G28 X Y';
		$actions['home-xyz']     = 'G28';
		$actions['home-xyz-min'] = 'G27';
		$actions['home-z']       = 'G28 Z';
		$actions['home-z-min']   = 'G27 Z';
		
		if( !array_key_exists($action, $actions) )
			return array();
			
		$this->sendCommands(array($actions[$action]), $id_stamp);
		
		return $this->response();
	}
	
	/**
	 * zero all 
	 */
	public function zeroAll($empty, $id_stamp)
	{
		$this->sendCommands(array('G92 X0 Y0 Z0 E0'), $id_stamp);
		return $this->response();
	}
	
	public function getPosition($empty, $id_stamp)
	{
		$this->sendCommands(array('M114'), $id_stamp);
		return $this->response();
	}
	
	/***
	 * @tag: to_be_removed
	 */
	public function emergency($mode, $id_stamp)
	{
		//$commands[] = 'M730';
		//if($mode == 'false') $commands[] = 'M731';
		$commands[] = 'M999';
		$commands[] = 'M728';
		
		$this->sendCommands($commands);
		return $this->response();
	}
	/**
	 * 
	 */
	function extrude($sign, $id_stamp)
	{
		//TODO
		$this->sendCommands(array('G91', 'G0 E'.$sign.$this->step.' F'.$this->feedrate), $id_stamp);
		return $this->response();
	}
	/**
	 * set extruder mode
	 * @mode extruder|4thaxis
	 */
	function setExtruderMode($mode, $id_stamp)
	{
		
		$settings = loadSettings();
		if($settings['settings_type'] == 'custom')
		{
			$settings = loadSettings('custom');
		}
		
		if($mode == "extruder")
		{
			
			$this->sendCommands(array('M92 E'.$settings['e'], 'G92 E0'), $id_stamp);
		}
		else if($mode == "4thaxis")
		{
			$this->sendCommands(array('M92 E'.$settings['a'], 'G92 E0'), $id_stamp);
		}
		return $this->response();
	}
	/**
	 * Manual Data Input (MDI)
	 */
	function manualDataInput($inputCommands, $id_stamp)
	{
		//TODO
		$commandsToSend = array();
		$list = explode(PHP_EOL, $inputCommands);
		foreach($list as $command){
			$cleanCommand = trim(strtoupper($command));
			if($cleanCommand != '') $commandsToSend[] = $cleanCommand;
			
		}
		$this->sendCommands($commandsToSend, $id_stamp);
		return $this->response();
	}
}
 
?>
