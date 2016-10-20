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
		<a class="btn btn-success" href="filemanager/add-object"><i class="fa fa-plus"></i> Add New Object </a>
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
			<a class="btn btn-default" href="filemanager"><i class="fa fa-arrow-left"></i> Back </a>
			<a class="btn btn-success" href="filemanager/add-file/'.$objectId.'"><i class="fa fa-plus"></i> Add Files </a>
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
		
		$data['is_editable'] = True;
		
		// additive
		$data['dimesions'] = '';
		$data['filament'] = '';
		$data['estimated_time'] = '';
		$data['number_of_layers'] = '';
		
		if($data['file']) // if file existss
		{
			$data['object'] = $this->files->getObject($fileId);
			$objectId = $data['object']['id'];
			
			$widgetOptions = array(
				'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
				'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
			);
			
			$widgeFooterButtons = 	
				'<label class="checkbox-inline" style="padding-top:0px;">
					 <input type="checkbox" class="checkbox" disabled="disabled" id="also-content">
					 <span>Save content also </span>
				</label>' .
				$this->smart->create_button('Save', 'primary')->attr(array('id' => 'save'))->attr('data-action', 'exec')->icon('fa-save')->print_html(true);
			
			$headerToolbar = '<div class="widget-toolbar" role="menu">
			<a class="btn btn-default" href="filemanager/object/'.$objectId.'"><i class="fa fa-arrow-left"></i> Back </a>
			<button class="btn btn-danger button-action" data-action="delete"><i class="fa fa-download"></i> Delete </a>
			<button class="btn btn-info button-action" data-action="download"><i class="fa fa-download"></i> Download </a>
			</div>';
			
			$widget = $this->smart->create_widget($widgetOptions);
			$widget->id = 'file-manager-edit-object-widget';
			$widget->header = array('icon' => 'fa-folder-open', "title" => "<h2>File view</h2>", 'toolbar'=>$headerToolbar);
			$widget->body   = array('content' => $this->load->view('filemanager/file/view/widget', $data, true ), 'class'=>'', 'footer'=>$widgeFooterButtons);
			$this->content  = $widget->print_html(true);
			
			//add css files
			$this->addCssFile('/assets/css/filemanager/style.css');
			//add needed scripts
			$this->addJSFile('/assets/js/plugin/ace/src-min/ace.js'); // editor
			$this->addJSFile('/assets/js/plugin/datatables/jquery.dataTables.min.js'); //datatable
			$this->addJSFile('/assets/js/plugin/datatables/dataTables.colVis.min.js'); //datatable
			$this->addJSFile('/assets/js/plugin/datatables/dataTables.tableTools.min.js'); //datatable
			$this->addJSFile('/assets/js/plugin/datatables/dataTables.bootstrap.min.js'); //datatable
			$this->addJSFile('/assets/js/plugin/datatable-responsive/datatables.responsive.min.js'); //datatable */
			$this->addJsInLine($this->load->view('filemanager/file/view/js', $data, true));
			
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
		$this->load->helper('upload_helper');
		
		$data = array();
		
		//load configs
		$this->config->load('upload');
		$data['accepted_files'] = allowedTypesToDropzoneAcceptedFiles( $this->config->item('allowed_types') );
		
		$widgetOptions = array(
			'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
			'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
		);
		
		$widgeFooterButtons = $this->smart->create_button('Save', 'primary')->attr(array('id' => 'save'))->attr('data-action', 'exec')->icon('fa-save')->print_html(true);
		
		$headerToolbar = '<div class="widget-toolbar" role="menu">
		<a class="btn btn-danger" href="filemanager"><i class="fa fa-arrow-left"></i> Cancel </a>
		</div>';
		
		$widget = $this->smart->create_widget($widgetOptions);
		$widget->id = 'file-manager-add-object-widget';
		$widget->header = array('icon' => 'fa-folder-open', "title" => "<h2>Add new object</h2>", 'toolbar'=>$headerToolbar);
		$widget->body   = array('content' => $this->load->view('filemanager/add/widget', $data, true ), 'class'=>'', 'footer'=>$widgeFooterButtons);
		$this->content  = $widget->print_html(true);
		
		//add needed scripts
		$this->addJSFile('/assets/js/plugin/dropzone/dropzone.min.js'); //dropzpone
		$this->addJSFile('/assets/js/plugin/jquery-validate/jquery.validate.min.js'); //validator
		$this->addJsInLine($this->load->view('filemanager/add/js',$data, true));
		
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
		
		$data = array('object_id' => $objectID);
		$this->config->load('upload');
		$this->load->helpers('upload_helper');
		$data['accepted_files'] = allowedTypesToDropzoneAcceptedFiles( $this->config->item('allowed_types') );
		//~ var_dump($data);
		
		$widgetOptions = array(
			'sortable' => false, 'fullscreenbutton' => true,'refreshbutton' => false,'togglebutton' => false,
			'deletebutton' => false, 'editbutton' => false, 'colorbutton' => false, 'collapsed' => false
		);
		
		$widgeFooterButtons = $this->smart->create_button('Save', 'primary')->attr(array('id' => 'save'))->attr('data-action', 'exec')->icon('fa-save')->print_html(true);
		
		$headerToolbar = '<div class="widget-toolbar" role="menu">
		<a class="btn btn-danger" href="filemanager/object/'.$objectID.'"><i class="fa fa-arrow-left"></i> Cancel </a>
		</div>';
		
		$widget = $this->smart->create_widget($widgetOptions);
		$widget->id = 'file-manager-add-object-widget';
		$widget->header = array('icon' => 'fa-folder-open', "title" => "<h2>Add new file</h2>", 'toolbar'=>$headerToolbar);
		$widget->body   = array('content' => $this->load->view('filemanager/file/add/widget', $data, true ), 'class'=>'', 'footer'=>$widgeFooterButtons);
		$this->content  = $widget->print_html(true);
		
		//add needed scripts
		$this->addJSFile('/assets/js/plugin/dropzone/dropzone.min.js'); //dropzpone
		$this->addJSFile('/assets/js/plugin/jquery-validate/jquery.validate.min.js'); //validator
		$this->addJsInLine($this->load->view('filemanager/file/add/js', $data, true));
		
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
			$temp[] = '<i class="fa fa-folder-open"></i> <a href="filemanager/object/'.$object['id'].'">'.$object['name'].'</a>';
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
	public function saveObject($objectID = '')
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
		
		$redirectTo = '#filemanager';
		//add object record
		if(!$objectID)
		{
			$data['date_insert'] = date('Y-m-d H:i:s');
			$objectID = $this->objects->add($data);
		}
		else
		{
			$redirectTo = '#filemanager/object/' . $objectID;
		}
		
		if(count($files) > 0)
		{ //if files are presents add to object
			$this->objects->addFiles($objectID, $files);
		}
		$this->session->set_flashdata('alert', array('type' => 'alert-success', 'message'=> '<i class="fa fa-fw fa-check"></i> Object has been added' ));
		
		redirect($redirectTo);
	}
	
	public function updateFile()
	{
		$response['success'] = true;
		$response['message'] = '';
		
		
		
		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}
	
	/**
	 * update object attributes 
	 */
	public function updateObject()
	{
		$data = $this->input->post();
		$this->load->model('Objects', 'objects');

		$objectID = $data['object_id'];
		
		$this->load->model('Objects', 'objects');
		$object = $this->objects->get($objectID, 1);
		
		if($object) // if object existss
		{
			$new_data = array(
				'name' 			=> $data['name'],
				'description'	=> $data['description'],
				'public' 		=> $data['public'],
				'date_update'	=> date('Y-m-d H:i:s')
			);
			
			$this->objects->update($objectID, $new_data);
			$response['success'] = true;
		}
		else
		{
			$response['success'] = false;
		}
		
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
			$file = $this->files->get($fileID, True);
			shell_exec('sudo rm '.$file['full_path']);
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
			$file = $this->files->get($fileID, True);
			shell_exec('sudo rm '.$file['full_path']);
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
	
	private function generateActionDropdown($default_action, $builtin_actions, $plugin_actions)
	{
		$html = '<div class="btn-group">
					<button data-action="'.site_url($default_action['url']).'"type="button" class="btn btn-xs btn-success file-action"><i class="fa '.$default_action['icon'].'"></i> '.$default_action['title'].' </button>';
		
		if( count($plugin_actions) > 0 || count($builtin_actions) > 0 )
		{
			$html .= '
			<button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<span class="caret"></span>
			<span class="sr-only">Toggle Dropdown</span>
			</button>
			<ul class="dropdown-menu">';
			
			foreach($builtin_actions as $a)
			{
				$html .= '<li><a class="file-action" data-action="'.$a['url'].'"><i class="fa '.$a['icon'].'"></i> '.$a['title'].' </a></li>';
			}
			
			if(count($plugin_actions) > 0)
			{
				$html .= '<li role="separator" class="divider"></li>';
				foreach($plugin_actions as $a)
				{
					$html .= '<li><a class="file-action" data-action="'.$a['url'].'"><i class="fa '.$a['icon'].'"></i> '.$a['title'].' </a></li>';
				}
			}
			
			$html .= '</ul>';
		}
		
		$html .= '</div>';
		
		return $html;
	}
	
	/**
	 * Return a menu formated action button with dropdown menu for more supported actions.
	 */
	private function getFileActionDropdown($fileID)
	{
		//load db model
		$this->load->helpers('plugin_helper');
		$this->load->model('Files', 'files');
		$file = $this->files->get($fileID, 1);
		$file_ext = ltrim($file['file_ext'], '.');
		
		$builtin_actions = array();
		
		if($file['print_type'] == 'additive')
		{
			$builtin_actions[] = array(
				"title" => "Print",
				"icon" => "fa-rotate-90 fa-play",
				"url" => "make/print/file/".$fileID
			);
			$default_action = $builtin_actions[0];
		}
		else if($file['print_type'] == 'substractive')
		{
			$builtin_actions[] = array(
				"title" => "Mill",
				"icon" => "fa-rotate-90 fa-play",
				"url" => "make/mill/file/".$fileID
			);
			$default_action = $builtin_actions[0];
		}
			
		$builtin_actions[] = array(
				"title" => "Download",
				"icon" => "fa-download",
				"url" => "filemanager/download/file/".$fileID
			);
			
		if( $file['print_type'] == 'additive' or $file['print_type'] == 'substractive' )
		{
			$builtin_actions[] = array(
				"title" => "Preview",
				"icon" => "fa-eye",
				"url" => ""
			);
			$builtin_actions[] = array(
				"title" => "Stats",
				"icon" => "fa-area-chart",
				"url" => ""
			);
		}
		
		$plugin_actions = array();
		
		$actions = getFileActionList($file_ext);
		foreach($actions as $action)
		{
			$plugin_actions[] = array(
				'title' => $action['title'],
				'icon' => $action['icon'],
				'url' => '#'.str_replace('$1', $fileID, $action['url'] )
			);
		}
		
		if( count($builtin_actions) == 1) // There are no builtin actions except "Download"
		{
			if( count($plugin_actions) > 0 ) // Plugins provide an actions
			{
				$default_action = $plugin_actions[0];
				
				unset($plugin_actions[0]);
			}
			else
			{
				$default_action = $builtin_actions[0];
				unset($builtin_actions[0]);
			}
		}
		else // There are some built
		{
			unset($builtin_actions[0]);
		}
		
		// Generate action buttons
		
		
		return $this->generateActionDropdown($default_action, $builtin_actions, $plugin_actions);
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
			$temp[] = '<a href="filemanager/file/'.$file['id'].'">'.str_replace($file['file_ext'], '', $file['client_name']).'</a>';
			$temp[] = $file['print_type'];
			$temp[] = $file['note'];
			
			$date_inserted = date('d/m/Y', strtotime($file['insert_date']));
			
			$temp[] = $date_inserted;
			$temp[] = $this->getFileActionDropdown($file['id']);
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
					force_download($file['orig_name'], $data);
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
