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

?>
