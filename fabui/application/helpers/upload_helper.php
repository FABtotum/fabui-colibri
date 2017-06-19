<?php
/**
 * 
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
if ( ! function_exists('allowedTypesToDropzoneAcceptedFiles'))
{
	/**
	 * Convert allowed_types string to dropzone acceptedFiles string.
	 */
	function allowedTypesToDropzoneAcceptedFiles($allowed_types) {
		$acceptedFiles = '';
		
		$types = explode('|', $allowed_types);
		$acceptedFiles = array();
		foreach($types as $type)
		{
			$acceptedFiles[] = '.'.strtolower($type);
			$acceptedFiles[] = '.'.strtoupper($type);
		}
		
		return join(', ', $acceptedFiles);
	}
}

if ( ! function_exists('uploadFromFileSystem'))
{
	/**
	 * Upload files from FileSystem
	 */
	function uploadFromFileSystem($file, $name = '', $note = '')
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->load->helper('file_helper');
		$CI->load->helper('fabtotum_helper');
		$CI->load->helper('upload_helper');
		$CI->load->model('Files', 'files');
		//load configs
		$CI->config->load('upload');
		
		//get file extension to save the file in the correct directory
		$file_extension = getFileExtension($file);
		$upload_path = $CI->config->item('upload_path');
		if( !file_exists($upload_path . $file_extension)) 
			createFolder($upload_path . $file_extension);
		
		if($name == ''){
			$name = basename($file);
		}
		//$client_name = basename($file);
		
		$file_information = get_file_info($file);
		$folder_destination = $upload_path . $file_extension . '/';
		
		$file_name = md5(uniqid(mt_rand())) . '.' . $file_extension;
		
		/** MOVE TO FINALLY FOLDER */
		$_command = 'sudo cp "' . $file . '" "' . $folder_destination . $file_name . '" ';
		shell_exec($_command);

		/** ADD PERMISSIONS */
		$_command = 'sudo chmod 644 "' . $folder_destination . $file_name . '" ';
		shell_exec($_command);
		$_command = 'sudo chown www-data.www-data "' . $folder_destination . $file_name . '" ';
		shell_exec($_command);
		
		$file_type = get_mime_by_extension($file_name);
		if(!$file_type)
			$file_type = 'application/octet-stream';
		
		/** INSERT RECORD TO DB */
		$data['file_name'] = $file_name;
		$data['file_path'] = $folder_destination;
		$data['full_path'] = $folder_destination . $file_name;
		$data['raw_name'] = str_replace('.'.$file_extension, '', $file_name);
		$data['orig_name'] = $name;
		$data['client_name'] = $name;
		$data['file_ext'] = '.' . $file_extension;
		$data['file_type'] = $file_type;
		$data['file_size'] = $file_information['size'];
		$data['insert_date'] = date('Y-m-d H:i:s');
		$data['update_date'] = date('Y-m-d H:i:s');
		$data['note'] = $note;
		$data['attributes'] = '{}';
		$data['print_type'] = checkManufactoring($data['full_path']);

		/** RETURN  */
		return $CI->files->add($data);
	}
}

?>
