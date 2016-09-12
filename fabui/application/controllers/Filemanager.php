<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
 
 class Filemanager extends FAB_Controller {
 	
	public function index()
	{
		//load libraries, helpers, model, config
		$this->load->library('smart');
		$this->load->helper('layout');
		$data['alert'] = $this->session->flashdata('alert'); //show message if is present
		
		//main page widget
		$widgetOptions = array(
			'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
			'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
		);
		
		$headerToolbar = '<div class="widget-toolbar" role="menu"><a class="btn btn-default" href="'.site_url('filemanager/new-object').'"><i class="fa fa-plus"></i> Add new object </a></div>';
		
		$widget = $this->smart->create_widget($widgetOptions);
		$widget->id = 'file-manager-widget';
		$widget->header = array('icon' => 'fa-folder-open', "title" => "<h2>Objects</h2>", 'toolbar'=>$headerToolbar);
		$widget->body   = array('content' => $this->load->view('filemanager/index/widget', $data, true ), 'class'=>'no-padding');
		$this->content  = $widget->print_html(true);
		
		
		//add needed scripts
		$this->addJSFile('/assets/js/plugin/datatables/jquery.dataTables.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/datatables/dataTables.colVis.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/datatables/dataTables.tableTools.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/datatables/dataTables.bootstrap.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/datatable-responsive/datatables.responsive.min.js'); //datatable */
		$this->addJsInLine($this->load->view('filemanager/index/js','', true));
		$this->view();
	}

	/**
	 * @return get all objects for table view
	 */
	public function getUserObjects()
	{
		//load db model
		$this->load->model('Objects', 'objects');
		//retrieve objetcs
		$objects = $this->objects->userObjects($this->session->user['id']);
		//crate response for datatable
		$aaData = array();
		foreach($objects as $object){
			$temp = array();
			$temp[] = '<label class="checkbox-inline"><input type="checkbox" id="check_'.$object['id'].'" name="checkbox-inline" class="checkbox"><span></span> </label>';
			$temp[] = '<i class="fa fa-folder-open"></i> <a href="'.site_url('filemanager/object/'.$object['id']).'">'.$object['name'].'</a>';
			$temp[] = $object['description'];
			$temp[] = $object['num_files'];
			$aaData[] = $temp;
		}
		$this->output->set_content_type('application/json')->set_output(json_encode(array('aaData' => $aaData)));
	}
	
	/**
	 * add new object and files page
	 */
	public function newObject()
	{
		//TODO
		//load libraries, helpers, model, config
		$this->load->library('smart');
		
		$widgetOptions = array(
			'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
			'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
		);
		
		$widgeFooterButtons = $this->smart->create_button('Save', 'primary')->attr(array('id' => 'save'))->attr('data-action', 'exec')->icon('fa-save')->print_html(true);
		
		$widget = $this->smart->create_widget($widgetOptions);
		$widget->id = 'file-manager-add-object-widget';
		$widget->header = array('icon' => 'fa-folder-open', "title" => "<h2>Add new object</h2>", 'toolbar'=>'');
		$widget->body   = array('content' => $this->load->view('filemanager/add/widget', '', true ), 'class'=>'', 'footer'=>$widgeFooterButtons);
		$this->content  = $widget->print_html(true);
		
		
		//add needed scripts
		$this->addJSFile('/assets/js/plugin/dropzone/dropzone.min.js'); //dropzpone
		$this->addJSFile('/assets/js/plugin/jquery-validate/jquery.validate.min.js'); //validator
		$this->addJsInLine($this->load->view('filemanager/add/js','', true));
		
		$this->view();
	}
	
	/**
	 * 
	 */
	public function saveObject()
	{
		//TODO
		$data = $this->input->post();
		$files = explode(',', $data['filesID']); //explode files id
		unset($data['filesID']);
		//load db model
		$this->load->model('Objects', 'objects');
		$data['user'] = $this->session->user['id'];
		$data['date_insert'] = date('Y-m-d H:i:s');
		$data['date_update'] = date('Y-m-d H:i:s');
		
		//add object record
		$objectID = $this->objects->add($data);
		if(count($files) > 0){ //if files are presents add to object
			foreach ($files as $fileID) {
				$this->objects->addFile($objectID, $fileID);
			}
		}
		$this->session->set_flashdata('alert', array('type' => 'alert-success', 'message'=> '<i class="fa fa-fw fa-check"></i> Object has been added' ));
		redirect('filemanager');
	}
	
	/**
	 * upload file
	 */
	public function uploadFile()
	{
		//TODO
		//load helpers
		$this->load->helper('file');
		$this->load->helper('fabtotum');
		//get file extension to save the file in the correct directory
		$fileExtension = get_file_extension($_FILES['file']['name']);
		//load configs
		$this->config->load('upload');
		//preaprea configs for upload library
		//crate folder extension if doesn't exist
		if(!file_exists($this->config->item('upload_path').$fileExtension)) create_folder($this->config->item('upload_path').$fileExtension);
		//load upload library
		$config['upload_path']      = $this->config->item('upload_path').$fileExtension;
		$config['allowed_types']    = $this->config->item('allowed_types');
		$config['file_ext_tolower'] = true ; 
		$config['remove_spaces']    = true ;
		$config['encrypt_name']     = true;
		$this->load->library('upload', $config);
		if($this->upload->do_upload('file')) { //do upload
			//load db model
			$this->load->model('Files', 'files');
			$data = $this->upload->data();
			$data['insert_date'] = date('Y-m-d H:i:s');
			$data['update_date'] = date('Y-m-d H:i:s');
			$data['note'] = '';
			$data['attributes'] = '{}';
			$data['print_type'] = checkManufactoring($data['full_path']);
			$fileId = $this->files->add($data);
			$response['upload'] = true;
			$response['fileId'] = $fileId;
		}else{
			$response['upload'] = false;
			$response['message'] = $this -> upload -> display_errors();
		}
		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}
	
	/**
	 * show object page with info and files
	 */
	public function object($objectId)
	{
		if($objectId == '') redirect('filemanager');
		
		//load libraries, helpers, model, config
		$this->load->library('smart');
		//load db model
		$this->load->model('Objects', 'objects');
		$data['object'] = $this->objects->get($objectId, 1);
		
		$widgetOptions = array(
			'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
			'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
		);
		
		$widgeFooterButtons = $this->smart->create_button('Save', 'primary')->attr(array('id' => 'save'))->attr('data-action', 'exec')->icon('fa-save')->print_html(true);
		
		$widget = $this->smart->create_widget($widgetOptions);
		$widget->id = 'file-manager-edit-object-widget';
		$widget->header = array('icon' => 'fa-folder-open', "title" => "<h2>Edit object</h2>", 'toolbar'=>'');
		$widget->body   = array('content' => $this->load->view('filemanager/edit/widget', $data, true ), 'class'=>'', 'footer'=>$widgeFooterButtons);
		$this->content  = $widget->print_html(true);
		
		//add needed scripts
		$this->addJSFile('/assets/js/plugin/datatables/jquery.dataTables.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/datatables/dataTables.colVis.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/datatables/dataTables.tableTools.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/datatables/dataTables.bootstrap.min.js'); //datatable
		$this->addJSFile('/assets/js/plugin/datatable-responsive/datatables.responsive.min.js'); //datatable */
		$this->addJsInLine($this->load->view('filemanager/edit/js',$data['object'], true));
		
		$this->view();
	}
	
	/**
	 * @param (int) object id
	 * @return all files associated to the object
	 */
	public function getFiles($objectID)
	{
		//load db model
		$this->load->model('Files', 'files');
		//retrieve objetcs
		$files = $this->files->getByObject($objectID);
		//crate response for datatable
		$aaData = array();
		foreach($files as $file){
			$temp = array();
			$temp[] = '<label class="checkbox-inline"><input type="checkbox" id="check_'.$file['id'].'" name="checkbox-inline" class="checkbox"><span></span> </label>';
			$temp[] = '<a href="'.site_url('filemanager/file/'.$file['id']).'">'.str_replace($file['file_ext'], '', $file['client_name']).'</a>';
			$temp[] = $file['print_type'];
			//$temp[] = $object['num_files'];
			$aaData[] = $temp;
		}
		$this->output->set_content_type('application/json')->set_output(json_encode(array('aaData' => $aaData)));
	}
	
	public function file($fileId)
	{
		//load db model
		$this->load->model('Files', 'files');
		$file = $this->files->get($fileId, 1);
	}
 }
 
?>