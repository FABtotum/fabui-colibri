<?php
/**
 * 
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */

defined('BASEPATH') OR exit('No direct script access allowed');
 
class Firmware extends FAB_Controller {
	/**
	 *
	 */
	function __construct()
	{
		parent::__construct();
		session_write_close(); //avoid freezing page
	}
	public function test()
	{
		$this->load->helper('fabtotum_helper');
		$this->load->helper('os_helper');
		$tmp = doMacro('version');
		$reply = $tmp['reply'];
		
		$this->output->set_content_type('application/json')->set_output(json_encode($reply));
	}
	/**
	 * 
	 */
	public function index()
	{
		$this->load->library('smart');
		$this->load->helper('form');
		$this->load->helper('fabtotum_helper');
		$this->load->helper('os_helper');
		
		$this->config->load('fabtotum');
		
		//init
		$data            = array();
		$fw_versions_url = $this->config->item('firmware_endpoint') . 'fablin/atmega1280/version.json';
		$content         = getRemoteFile($fw_versions_url);
		
		$data['firmwareInfo'] = firmwareInfo();

		
		$data['content'] = $content;

		$fw_versions = array();
		$fw_versions['factory'] = 'Factory default';
		
		if($content)
		{
			$tmp = json_decode($content, true)['firmware'];
			foreach($tmp as $key => $value)
			{
				if($key != 'latest')
				{
					$fw_versions[$key] = $key . ' (download)';
				}
			}
		}
		
		$fw_versions['upload'] = 'Upload custom';
		$data['fw_versions'] = $fw_versions;
		
		$widgetOptions = array(
			'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
			'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
		);
		
		$widgeFooterButtons = '';//$this->smart->create_button('Flash firmware', 'primary')->attr(array('id' => 'flashButton'))->attr('data-action', 'exec')->icon('fa-save')->print_html(true);
		
		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'main-widget-firmware';
		$widget->header = array('icon' => 'fa-microchip', "title" => "<h2>"._("Firmware")."</h2>");
		$widget->body   = array('content' => $this->load->view('firmware/main_widget', $data, true ), 'class'=>'fuelux', 'footer'=>$widgeFooterButtons);

		$this->addJSFile('/assets/js/plugin/bootstrap-progressbar/bootstrap-progressbar.min.js'); //datatable
		$this->addJsInLine($this->load->view('firmware/main_js', $data, true)); 
		$this->content = $widget->print_html(true);
		$this->view();
	}
	/**
	 * 
	 */
	public function doFlashFirmware($version)
	{
		//load helpers
		$this->load->helper('fabtotum');
		$this->load->helper('update_helper');
		
		$data = array();
		$data['result'] = '';
		
		if($version == 'factory')
		{
			stopServices();
			$result = flashFirmware('factory');
			startServices();
		}
		else
		{
			stopServices();
			$result = flashFirmware('remote', $version);
			startServices();
		}
		
		$data['result'] = $result;
		
		$this->output->set_content_type('application/json')->set_output(json_encode($data));
	}
	/**
	 * 
	 */
	public function doUploadFirmware()
	{
		//load helpers
		$this->load->helper('file');
		$this->load->helper('fabtotum');
		$this->load->helper('update_helper');
		
		$upload_config['upload_path']   = '/tmp/fabui/';
		$upload_config['allowed_types'] = 'hex';
		
		$this->load->library('upload', $upload_config);
		
		if($this->upload->do_upload('hex-file')){ //do upload
			$upload_data = $this->upload->data();
			$result = false;
			
			stopServices();
			flashFirmware('custom', $upload_data['full_path']);
			startServices();
			
			$data['result'] = $result;
		}else{
			$data['error'] = strip_tags($this->upload->display_errors());
		}
		
		$this->output->set_content_type('application/json')->set_output(json_encode($data));
	}

}
 
?>
