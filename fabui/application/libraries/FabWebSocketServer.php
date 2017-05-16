<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

require_once APPPATH . 'third_party/vendor/autoload.php';
require_once 'JogFactory.php';
class FabWebSocketServer implements MessageComponentInterface {
	protected $clients;
	
	/**
	 */
	public function __construct() {
		$this->clients = new \SplObjectStorage ();
	}
	/**
	 */
	public function onOpen(ConnectionInterface $conn) {
		// Store the new connection to send messages to later
		$this->clients->attach ( $conn );
	}
	/**
	 */
	public function onMessage(ConnectionInterface $from, $msg) {
		$decoded_message = json_decode ( $msg, true );
		$reply_message = $msg;
		if (json_last_error () == JSON_ERROR_NONE) {
			if (isset ( $decoded_message ['function'] )) {
				$method_name = $decoded_message ['function'];
				if (method_exists ( $this, $method_name )) {
					if (isset ( $decoded_message ['params'] ))
						$reply_message = $this->$method_name ( $decoded_message ['params'] );
				}
			}
		}
		
		foreach ( $this->clients as $client ) {
			$client->send ( $reply_message );
		}
	}
	/**
	 */
	public function onClose(ConnectionInterface $conn) {
		// The connection is closed, remove it, as we can no longer send it messages
		$this->clients->detach ( $conn );
	}
	/**
	 */
	public function onError(ConnectionInterface $conn, \Exception $e) {
		echo "An error has occurred: {$e->getMessage()}\n";
		$conn->close ();
	}
	/**
	 */
	public function buildResponse($messageType, $messageData, $format = 'json') {
		return json_encode ( array (
				'type' => $messageType,
				'data' => $messageData 
		) );
	}
	/**
	 */
	public function serial($data) {
		$CI = & get_instance ();
		
		$method = $data ['method'];
		$methodStamp = $data ['stamp'];
		$methodParam = $data ['value'];
		
		unset ( $data ['method'] );
		unset ( $data ['value'] );
		unset ( $data ['stamp'] );
		unset ( $data ['action'] );
		
		$jogFactory = new JogFactory ( $data );
		
		if (method_exists ( $jogFactory, $method )) { // if method exists than do it
			$messageData = $jogFactory->$method ( $methodParam, $methodStamp );
			$messageType = $jogFactory->getResponseType ();
			unset ( $jogFactory );
			return $this->buildResponse ( $messageType, $messageData );
		}
	}
	/**
	 * return if there is an usb disk inserted
	 */
	public function usbInserted() {
		// load CI instance for configs and helpers
		$CI = & get_instance ();
		$CI->load->helper ( 'os_helper' );
		$messageData = array (
				'status' => getUsbStatus (),
				'alert' => false 
		);
		return $this->buildResponse ( 'usb', $messageData );
	}
}

?>