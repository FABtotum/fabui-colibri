<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 * Serial factory
 */
 
 require_once('Serial.php'); //serial class
 
 class JogFactory {
	
	protected $CI; //code igniter instance
	protected $serial; //serial class
	protected $feedrate = array();
	protected $step = array();
	protected $responseType = 'serial'; //type of response (temperature, gcode, serial)
	protected $responseData;
	protected $serialReply; //serial reply
	
	
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
		//init serial class
		$this->serial = new Serial();		
		$this->serial->deviceSet($this->CI->config->item('serial_port'));
		$this->serial->confBaudRate($this->CI->config->item('serial_baud'));
		$this->serial->confParity("none");
		$this->serial->confCharacterLength(8);
		$this->serial->confStopBits(1);
		$this->serial->confFlowControl("none");
  	}
	
	/***
	 * send gcode command to serial
	 */
	function sendCommand($command)
	{			
		$this->serial->deviceOpen();
		$read = '';
		if(is_array($command)){
			foreach($command as $comm){
				$this->serial->sendMessage($comm.PHP_EOL);
				$read .= $this->serial->readPort();
			}
		}elseif($command != ''){
			$this->serial->sendMessage($command.PHP_EOL);
			$read .= $this->serial->readPort();
		}
		$this->serial->deviceClose();
		$this->serialReply = $read;
	}
	
	/**
	 * @return Array data response
	 */
	function response()
	{
		return array('type'=> $this->responseType, 'response' => $this->serialReply);
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
	 * @return $string temperatures (ex: ok T:102.0 /102.0 B:66.1 /66.0 T0:102.0 /102.0 @:26 B@:0\n)
	 * get Nozzle and Bed temperatures
	 */
	public function getTemperatures()
	{
		$this->setResponseType('temperature');
		$this->sendCommand('M105');
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
}
 
?>