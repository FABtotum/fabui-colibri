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
	protected $serial; //serial class
	protected $feedrate = array();
	protected $step = array();
	protected $responseType = 'serial'; //type of response (temperature, gcode, serial)
	protected $responseData;
	protected $serialReply = array(); //serial reply
	protected $useXmlrpc = true;
	protected $xmlrpc = null;
	protected $commands = array();
	protected $result;
	protected $xmlRpcResponse = array();
	
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
		
		if(!$this->useXmlrpc){
			$this->CI->load->helper('file_helper');
		}else{
			/*
			$this->CI->load->library('xmlrpc');
			$this->CI->xmlrpc->server('127.0.0.1/FABUI', $this->CI->config->item('xmlrpc_port'));
			$this->CI->xmlrpc->timeout(120*5);
			$this->CI->xmlrpc->method('send');
			*/
			$this->CI->load->helper('fabtotum_helper');
		}
		
  	}
  	/**
  	 * build list commands
  	 * @param array commands or string
  	 */
  	function buildCommands($commands)
  	{	
  		if(!is_array($commands) && $commands != '')
  		{
  			array_push($this->commands, $commands);
  		}else{
  			$this->commands = $commands;
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
	function sendCommands($commands)
	{	
		$this->buildCommands($commands);
		
		if(!$this->useXmlrpc){
			$commandToWrite = '';
			foreach($this->commands as $key => $command){
				$commandToWrite .= '!jog:'.time().$key.','.$command.PHP_EOL;
			}
			write_file($this->CI->config->item('command'), $commandToWrite);
		}else{
			
			$this->xmlRpcResponse =  sendToXmlrpcServer('send', array($this->getCommands('text')));
			$this->serialReply = $this->xmlRpcResponse['reply'];
			$this->result = $this->xmlRpcResponse['response'];
			/*
			$request = array($this->getCommands('text'));
			$this->CI->xmlrpc->request($request);
			
			$this->result = False;
			$this->serialReply = '';
			 
			if (!$this->CI->xmlrpc->send_request())
			{
				$this->serialReply .= $this->CI->xmlrpc->display_error();
			}
			else
			{
				$this->serialReply .= implode('<br>', $this->CI->xmlrpc->display_response()) ;
				$this->result = True;
			}
			*/
			
		}
	}
	/**
	 * @return Array data response
	 */
	function response()
	{
		return array('type'=> $this->responseType, 'commands' => $this->commands, 'reply' => $this->serialReply, 'result' => $this->result);
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
	public function getTemperatures()
	{
		$this->setResponseType('temperatures');
		$this->serialReply = json_decode(file_get_contents($this->CI->config->item('temperature')), true);
		return $this->response();
	}
	/***
	 * @param int $temperature
	 * set heated bed temperature
	 */
	public function setBedTemp($temperature)
	{
		$this->sendCommands('M140 S'.$temperature);
		return $this->response();
	}
	/**
	 * @param int $temperature
	 * set nozzle temperature
	 */
	public function setExtruderTemp($temperature)
	{
		$this->sendCommands('M104 S'.$temperature);
		return $this->response();
	}
	/**
	 * @param string $direction 
	 * move head on X Y axis
	 */
	public function moveXY($direction)
	{
		$directions['up']         = 'G0 Y+%.2f F%.2f';
		$directions['up-right']   = 'GO Y+%1$.2f X+%1$.2f F%2$.2f';
		$directions['up-left']    = 'G0 Y+%1$.2f X-%1$.2f F%2$.2f';
		$directions['down']       = 'G0 Y-%.2f F%.2f';
		$directions['down-right'] = 'G0 Y-%1$.2f X+%1$.2f F%2$.2f';
		$directions['down-left']  = 'GO Y-%1$.2f X-%1$.2f F%2$.2f';
		$directions['left']       = 'GO X-%.2f F%.2f';
		$directions['right']      = 'GO X+%.2f F%.2f';
		
		$this->sendCommands(array('G91', sprintf($directions[$direction], $this->step['xy'], $this->feedrate['xyz']),'G90'));
		return $this->response();
		
	}
	/**
	 * @param string $direction
	 * move Z axis
	 */
	public function moveZ($direction)
	{
		$sign = $direction == 'up' ? '-' : '+';
		$this->sendCommands(array('G91', 'GO Z'.$sign.$this->step['z'].' F'.$this->feedrate['xyz']));
		return $this->response();
	}
	/**
	 * zero all 
	 */
	public function zeroAll()
	{
		$this->sendCommands('G92 X0 Y0 Z0 E0');
		return $this->response();
	}
	/***
	 * 
	 */
	public function emergency($mode)
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
	function extrude($sign)
	{
		//TODO
		$this->sendCommands(array('G91', 'G0 E'.$sign.$this->step['extruder'].' F'.$this->feedrate['extruder']));
		return $this->response();
	}
	/**
	 * Manual Data Input (MDI)
	 */
	function manualDataInput($inputCommands)
	{
		//TODO
		$commandsToSend = array();
		$list = explode(PHP_EOL, $inputCommands);
		foreach($list as $command){
			$cleanCommand = trim(strtoupper($command));
			if($cleanCommand != '') $commandsToSend[] = $cleanCommand;
			
		}
		$this->sendCommands($commandsToSend);
		return $this->response();
	}
}
 
?>
