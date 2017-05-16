<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

require_once 'FabWebSocketServer.php';
require_once APPPATH.'/third_party/vendor/autoload.php';

class WebSocketServer {
	
	protected $port = '9003';
	protected $server;
	
	/**
	 * 
	 */
	function __construct($params = array()) {
		
		foreach($params as $key => $value){ //init class attributes (if present)
			if(property_exists($this, $key))
				$this->$key = $value;
		}
		$this->server = IoServer::factory ( new HttpServer ( new WsServer ( new FabWebSocketServer) ), $this->port );
	}
	/**
	 * 
	 */
	public function run()
	{
		$this->server->run();	
	}
	
}