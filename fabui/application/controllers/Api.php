<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
 
 require APPPATH . '/libraries/REST_Controller.php';

 class Api extends REST_Controller {
 	
 	function __construct()
 	{
 		// Construct the parent class
 		parent::__construct();
 	}
 	/**
 	 * 
 	 * 
 	 */
 	public function gcode_get()
 	{
 		$gcode = $this->input->get('v');
 		$status = false;
 		if($gcode != '' ) {
 			$message = $this->send_gcode($gcode);
 			$http_code = REST_Controller::HTTP_OK;
 			$status = true;
 		}else{
 			$message = _("Missing argument");
 			$http_code = REST_Controller::HTTP_BAD_REQUEST;
 		}
 		$this->set_response(array(
 			'status' => $status,
 			'data' => $message,
 		), $http_code);
 		
 	}
 	/**
 	 *
 	 *
 	 */
 	public function gcode_post()
 	{
 		$gcode = $this->input->post('v');
 		$status = false;
 		if($gcode != '' ) {
 			$message = $this->send_gcode($gcode);
 			$http_code = REST_Controller::HTTP_OK;
 			$status = true;
 		}else{
 			$message = _("Missing argument");
 			$http_code = REST_Controller::HTTP_BAD_REQUEST;
 		}
 		$this->set_response(array(
 				'status' => $status,
 				'data' => $message,
 		), $http_code);
 	}
 	/**
 	 * 
 	 */
 	public function discover_get()
 	{
 		$this->load->helpers('os_helper');
 		$this->load->helpers('fabtotum_helper');
 		
 		$message['serialno']   = getSerialNumber();
 		$message['mac']        = getMACAddres();
 		$message['apiversion'] = 1;
 		$message['state']      = getState();
 		
 		$this->set_response(array(
 				'status' => true,
 				'data' => $message,
 		), REST_Controller::HTTP_OK);
 		
 	}
 	/**
 	 * 
 	 */
 	private function send_gcode($gcode)
 	{
 		$this->load->helper('fabtotum_helper');
 		$commands = explode(',', strtoupper($gcode));
 		$response = array();
 		foreach($commands as $gc){
 			$responseTemp = sendToXmlrpcServer('send', $gc);
 			$response[$gc] = array(
 				'reply' => implode(PHP_EOL,$responseTemp['reply'])
 			);
 		};
 		return $response;
 	}
 }
?>
