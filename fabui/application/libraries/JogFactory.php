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
	protected $serialCommands = array();
	
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
		$this->CI->load->helper('file_helper');
  	}
	
	/***
	 * send gcode command to serial
	 */
	function sendCommand($commands)
	{
		if(!is_array($commands) && $commands != '')
		{
			array_push($this->serialCommands, $commands);
		}else{
			$this->serialCommands = $commands;
		}
		
		$commandToWrite = '';
		foreach($this->serialCommands as $key => $command){
			$commandToWrite .= '!jog:'.time().$key.','.$command.PHP_EOL;
		}
		write_file($this->CI->config->item('command'), $commandToWrite);
	}
	
	/**
	 * @return Array data response
	 */
	function response()
	{
		return array('type'=> $this->responseType, 'commands' => $this->serialCommands, 'response' => $this->serialReply);
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
	 * @return array status of the printer (temperatures, fan, floware, speed, etc)
	 * get Nozzle and Bed temperatures
	 */
	public function getStatus()
	{
		$this->setResponseType('status');
		$this->serialReply = json_decode(file_get_contents($this->CI->config->item('status')), true);
		return $this->response();
	}
	
	/***
	 * @param int $temperature
	 * set heated bed temperature
	 */
	public function setBedTemp($temperature)
	{
		$this->sendCommand('M140 S'.$temperature);
		return $this->response();
	}
	
	/**
	 * @param int $temperature
	 * set nozzle temperature
	 */
	public function setNozzleTemp($temperature)
	{
		$this->sendCommand('M104 S'.$temperature);
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
		
		$this->sendCommand(array('G91', sprintf($directions[$direction], $this->step['xy'], $this->feedrate['xyz']),'G90'));
		return $this->response();
	}
	
	/**
	 * @param string $direction
	 * move Z axis
	 */
	public function moveZ($direction)
	{
		$sign = $direction == 'up' ? '-' : '+';
		$this->sendCommand(array('G91', 'GO Z'.$sign.$this->step['z'].' F'.$this->feedrate['xyz']));
		return $this->response();
	}
	
	/**
	 * zero all 
	 */
	public function zeroAll()
	{
		$this->sendCommand('G92 X0 Y0 Z0 E0');
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
		
		$this->sendCommand($commands);
		return $this->response();
	}
	
	/**
	 * 
	 */
	function extrude($sign)
	{
		//TODO
		$this->sendCommand(array('G91', 'G0 E'.$sign.$this->step['extruder'].' F'.$this->feedrate['extruder']));
		return $this->response();
	}
}
 
?>