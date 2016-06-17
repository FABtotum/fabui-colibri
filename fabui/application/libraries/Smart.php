<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
 require_once('smartui/SmartUtil.php');
 require_once('smartui/SmartUI.php');
 require_once('smartui/Widget.php');
 require_once('smartui/Form.php');
 require_once('smartui/Button.php');
 
 class Smart {
 	
	public function __construct() {
		
		SmartUI::register('widget', 'Widget');
		SmartUI::register('button', 'Button');
		SmartUI::register('smartform', 'Form');
	}
	
	public function create_widget($options = array(), $contents = array()) {
		return new Widget($options, $contents);
	}
	
	public function create_form($fields = array(), $options = array()){
		return new Form($fields, $options);
	}
	
	public function create_button($content = '', $type = 'default', $options = array()){
		return new Button($content, $type, $options);
	}
	
 }
?>