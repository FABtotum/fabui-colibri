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
	
	
	/**
	 * show objects page
	 **/
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
		
		$headerToolbar = '<div class="widget-toolbar" role="menu">
		<a class="btn btn-success" href="'.site_url('filemanager/newObject').'"><i class="fa fa-plus"></i> Add New Object </a>
		<button class="btn btn-danger bulk-button" data-action="delete"><i class="fa fa-trash"></i> Delete </button>
		<button class="btn btn-info bulk-button" data-action="download"><i class="fa fa-download"></i> Download </button>
		</div>';
		
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
		
		if($data['object']) // if object existss
		{
			$widgetOptions = array(
				'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
				'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
			);
			
			$widgeFooterButtons = $this->smart->create_button('Save', 'primary')->attr(array('id' => 'save-object'))->attr('data-action', 'exec')->icon('fa-save')->print_html(true);
			
			$headerToolbar = '<div class="widget-toolbar" role="menu">
			<a class="btn btn-default" href="'.site_url('filemanager').'"><i class="fa fa-arrow-left"></i> Back </a>
			<a class="btn btn-success" href="'.site_url('filemanager/newFile').'/'.$objectId.'"><i class="fa fa-plus"></i> Add Files </a>
			<button class="btn btn-danger bulk-button" data-action="delete"><i class="fa fa-trash"></i> Delete </button>
			<button class="btn btn-info bulk-button" data-action="download"><i class="fa fa-download"></i> Download </button>
			</div>';
			
			$widget = $this->smart->create_widget($widgetOptions);
			$widget->id = 'file-manager-edit-object-widget';
			$widget->header = array('icon' => 'fa-folder-open', "title" => "<h2>Edit object</h2>", 'toolbar'=>$headerToolbar);
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
		else
		{
			redirect('filemanager');
		}
	}
	
	/**
	 * show file actions view
	 */
	public function file($fileId)
	{
		if($fileId == '') redirect('filemanager');
		
		//load libraries, helpers, model, config
		$this->load->library('smart');
		//load db model
		$this->load->model('Files', 'files');
		$data['file'] = $this->files->get($fileId, 1);
		
		if($data['file']) // if file existss
		{
			$widgetOptions = array(
				'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
				'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
			);
			
			$widgeFooterButtons = $this->smart->create_button('Save', 'primary')->attr(array('id' => 'save-object'))->attr('data-action', 'exec')->icon('fa-save')->print_html(true);
			
			$headerToolbar = ''; /*'<div class="widget-toolbar" role="menu">
			<a class="btn btn-default" href="'.site_url('filemanager').'"><i class="fa fa-arrow-left"></i> Back </a>
			<a class="btn btn-success" href="'.site_url('filemanager/newFile').'/'.$objectId.'"><i class="fa fa-plus"></i> Add Files </a>
			<button class="btn btn-danger bulk-button" data-action="delete"><i class="fa fa-trash"></i> Delete </button>
			<button class="btn btn-info bulk-button" data-action="download"><i class="fa fa-download"></i> Download </button>
			</div>';*/
			
			$widget = $this->smart->create_widget($widgetOptions);
			$widget->id = 'file-manager-edit-object-widget';
			$widget->header = array('icon' => 'fa-folder-open', "title" => "<h2>File view</h2>", 'toolbar'=>$headerToolbar);
			$widget->body   = array('content' => $this->load->view('filemanager/file/view/widget', $data, true ), 'class'=>'', 'footer'=>$widgeFooterButtons);
			$this->content  = $widget->print_html(true);
			
			//add needed scripts
			$this->addJSFile('/assets/js/plugin/datatables/jquery.dataTables.min.js'); //datatable
			$this->addJSFile('/assets/js/plugin/datatables/dataTables.colVis.min.js'); //datatable
			$this->addJSFile('/assets/js/plugin/datatables/dataTables.tableTools.min.js'); //datatable
			$this->addJSFile('/assets/js/plugin/datatables/dataTables.bootstrap.min.js'); //datatable
			$this->addJSFile('/assets/js/plugin/datatable-responsive/datatables.responsive.min.js'); //datatable */
			$this->addJsInLine($this->load->view('filemanager/file/view/js',$data['file'], true));
			
			$this->view();
		}
		else
		{
			redirect('filemanager');
		}
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
	 * add files to object page
	 */
	public function newFile($objectID)
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
		$widget->header = array('icon' => 'fa-folder-open', "title" => "<h2>Add new file</h2>", 'toolbar'=>'');
		$widget->body   = array('content' => $this->load->view('filemanager/file/add/widget', '', true ), 'class'=>'', 'footer'=>$widgeFooterButtons);
		$this->content  = $widget->print_html(true);
		
		
		//add needed scripts
		$this->addJSFile('/assets/js/plugin/dropzone/dropzone.min.js'); //dropzpone
		$this->addJSFile('/assets/js/plugin/jquery-validate/jquery.validate.min.js'); //validator
		$this->addJsInLine($this->load->view('filemanager/file/add/js','', true));
		
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
		$objects = $this->objects->getUserObjects($this->session->user['id']);
		//crate response for datatable
		$aaData = array();
		foreach($objects as $object){
			$temp = array();
			$temp[] = '<label class="checkbox-inline"><input type="checkbox" id="check_'.$object['id'].'" name="checkbox-inline" class="checkbox"><span></span> </label>';
			$temp[] = '<i class="fa fa-folder-open"></i> <a href="'.site_url('filemanager/object/'.$object['id']).'">'.$object['name'].'</a>';
			$temp[] = $object['description'];
			
			$date_inserted = date('d/m/Y', strtotime($object['date_insert']));
			
			$temp[] = $date_inserted;
			$temp[] = $object['num_files'];
			$aaData[] = $temp;
		}
		$this->output->set_content_type('application/json')->set_output(json_encode(array('aaData' => $aaData)));
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
		//~ $data['date_insert'] = date('Y-m-d H:i:s');
		$data['date_update'] = date('Y-m-d H:i:s');
		
		//add object record
		$objectID = $this->objects->add($data);
		if(count($files) > 0)
		{ //if files are presents add to object
			$this->objects->addFiles($objectID, $files);
		}
		$this->session->set_flashdata('alert', array('type' => 'alert-success', 'message'=> '<i class="fa fa-fw fa-check"></i> Object has been added' ));
		redirect('filemanager');
	}
	
	/**
	 * update object attributes 
	 */
	public function updateObject()
	{
		$response['success'] = true;
		
		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}
	
	/*
	 * delete object and all associated files
	 */
	public function deleteObjects()
	{
		// TODO: error handling
		$this->load->model('Objects', 'objects');
		$this->load->model('Files', 'files');
		
		$response['success'] = true;
		$response['message'] = '';
		
		$ids = $this->input->post("ids");
		foreach($ids as $objectID)
		{
			$files = $this->files->getByObject($objectID);
			
			$fileIDs = array();
			foreach($files as $file)
			{
				$fileID = $file['id'];
				$fileIDs[] = $fileID;
				$this->files->delete( $fileID );
			}
			
			$this->objects->deleteFiles($objectID, $fileIDs);
			$this->objects->delete( $objectID );
		}
		
		$this->output->set_content_type('application/json')->set_output(json_encode( $response ));
	}
	
	/*
	 * delete file
	 */
	public function deleteFiles()
	{
		// TODO: error handling
		$this->load->model('Objects', 'objects');
		$this->load->model('Files', 'files');
		
		$response['success'] = true;
		$response['message'] = '';
		
		$ids = $this->input->post("ids");
		foreach($ids as $fileID)
		{
			$objectID = $this->files->getObject($fileID)['id'];
			$this->objects->deleteFiles($objectID, $fileID);
			$this->files->delete($fileID);
		}
		$this->output->set_content_type('application/json')->set_output(json_encode( $response ));
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
			$temp[] = $file['note'];
			
			$date_inserted = date('d/m/Y', strtotime($file['insert_date']));
			
			$temp[] = $date_inserted;
			$temp[] = '';
			//$temp[] = $object['num_files'];
			$aaData[] = $temp;
		}
		$this->output->set_content_type('application/json')->set_output(json_encode(array('aaData' => $aaData)));
	}
	
	public function download($type, $list)
	{
		/** LOAD HELPER */
		$this->load->model('Files', 'files');
		$this->load->model('Objects', 'objects');
		$this->load->helper('download');
		$this->load->library('zip');
		$this->config->load('fabtotum');
		
		if ($type == 'file')
		{
			$files = explode('-', $list);
			if (count($files) > 0)
			{
				if (count($files) == 1)
				{
					
					$file = $this->files->get($files[0], 1);
					$data = file_get_contents($file['full_path']);
					force_download($file['client_name'], $data);
				} 
				else
				{
					foreach($files as $file_id) 
					{
						$file = $this->files->get($file_id, 1);
						$this->zip->read_file($file['full_path'], $file['client_name'] );
					}
					$this->zip->download('fabtotum_files.zip');
				}
			}
		} 
		else if ($type == 'object')
		{
			$objects = explode('-', $list);
			if (count($objects) > 0) 
			{
				foreach ($objects as $obj_id)
				{
					$obj = $this->objects->get($obj_id, 1);
					// Replace unwanted characters in filename
					$obj_folder = str_replace('&', 'and',str_replace(' ', '_', $obj['name']));
					$obj_folder = str_replace('(', '_', $obj_folder);
					$obj_folder = str_replace(')', '_', $obj_folder);
					
					// Create object directory
					$this->zip->add_dir($obj_folder);
					
					// Add object files in the object directory
					$files = $this->files->getByObject($obj_id);
					foreach ($files as $file)
					{
						// Create virtual file path for zip file
						$file_path = $obj_folder . '/' . $file['client_name'];
						$this->zip->read_file($file['full_path'], $file_path );
					}
				}
				
				$this->zip->download('fabtotum_objects.zip');
			}
		}

	}
	
 }
 
?>
