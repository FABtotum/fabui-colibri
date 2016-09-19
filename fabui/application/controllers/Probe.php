<?php
/**
 * 
 * @author Krios Mane
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */

defined('BASEPATH') OR exit('No direct script access allowed');
 
class Probe extends FAB_Controller {

	public function index($type = 'length')
	{
		switch($type){
			case 'length':
				$this->doLengthCalibration();
				break;
			case 'angle':
				$this->doAngleCalibration();
				break;
		}
	}

	public function doLengthCalibration()
	{
		//load libraries, helpers, model
		$this->load->library('smart');
		$this->load->helper('form');
			
		//data
		$data = array();
		
		$this->view();
	}

	public function doAngleCalibration()
	{
		$this->view();
	}

}
 
?>
