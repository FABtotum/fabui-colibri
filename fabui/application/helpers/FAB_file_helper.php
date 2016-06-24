<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */

if ( !function_exists('get_file_extension'))
{
	/**
	 * @param $fileName (string) - file name
	 * @return file extension (string)
	 */	
	function get_file_extension($fileName) 
	{
		return substr(strrchr($fileName,'.'),1);
	}
}

if(!function_exists('create_folder'))
{
	/**
	 * @param $folder_path (string)
	 * create folder
	 * @return TRUE on success or FALSE on failure.
	 */
	function create_folder($folder_path)
	{
		return mkdir($folder_path, 0755);
	}
} 
 
 
?>