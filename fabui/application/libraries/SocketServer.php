<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
 require_once('websocket/WebSocketServer.php');
 require_once('JogFactory.php');
 
 class SocketServer extends WebSocketServer {
 	
	
	protected $messageType;
	protected $messageData;
	
	function __construct($params)
	{
		parent::__construct($params['address'], $params['port']);
	}
 	
	/**
	 *  get request from the UI or the fabtotum and do what is needed to do
	 *  
	 */
	protected function process ($user, $request)
	{
		
		$requestData = json_decode($request, true);
		$message     = $request;
		
		if(isset($requestData['function'])){
			$function       = $requestData['function'];
			$functionParams = isset($requestData['params']) ? $requestData['params'] : '';
			if(method_exists($this, $function)){ //if function exists, do it
				$message = $this->$function($functionParams);
			}
		}
		//send message to all connected user
		foreach($this->users as $connectedUser){
			$this->send($connectedUser, $message);
		}
		
  	}
	
	/**
	 * 
	 */
	protected function connected ($user) 
	{
	    // Do nothing: This is just an echo server, there's no need to track the user.
	    // However, if we did care about the users, we would probably have a cookie to
	    // parse at this step, would be looking them up in permanent storage, etc.
	    
	    //print_r($user);
	
  	}
	
	/**
	 * 
	 */
	protected function closed ($user) 
	{
	    // Do nothing: This is where cleanup would go, in case the user had any sort of
	    // open files or other objects associated with them.  This runs after the socket 
	    // has been closed, so there is no need to clean up the socket itself here.
  	}
	
	/**
	 * prepare response message
	 */
	public function buildResponse()
	{
		return json_encode(array('type' => $this->messageType, 'data' =>$this->messageData));
	}
	
	/**
	 * return if there is an usb disk inserted
	 */
	public function usbInserted()
	{
		//load CI instance for configs and helpers
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$this->messageType = 'usb';
		//set true if usb file exists
		$this->messageData = array('status' => file_exists($CI->config->item('usb_file')), 'alert' => false);
		return $this->buildResponse();
	}
	
	/**
	 *  exec gcode commands
	 */
	public function serial($data)
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		
		$method      = $data['method'];
		$methodParam = $data['value'];
		
		unset($data['method']);
		unset($data['value']);
		
		$jogFactory = new JogFactory($data);
		if(method_exists($jogFactory, $method)){ //if method exists than do it
			$this->messageData = $jogFactory->$method($methodParam);
			$this->messageType = $jogFactory->getResponseType();
			unset($jogFactory);
			return $this->buildResponse();
		}
		
	}
	
	/**
	 * return busy message
	 */
	public function fabBusy()
	{
		$this->messageData = array('message' => 'FABtotum is busy');
		$this->messageType = 'alert';
		return $this->buildResponse();
	}
	
 }
 
?>