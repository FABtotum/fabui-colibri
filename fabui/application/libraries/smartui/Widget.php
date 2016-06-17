<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
 class Widget extends SmartUI {

	private $_options_map = array(
		"editbutton" => true,
		"colorbutton" => true,
		"editbutton" => true,
		"togglebutton" => true,
		"deletebutton" => true,
		"fullscreenbutton" => true,
		"custombutton" => true,
		"collapsed" => false,
		"sortable" => true,
		"refreshbutton" => false
	);

	private $_structure = array(
		"class" => "",
		"color" => "",
		"id" => "",
		"attr" => array(),
		"options" => array(),
		"header" => array(),
		"body" => array()
	);

	public function __construct($options=array(), $contents = array()) {
		$this->_init_structure($options, $contents);
	}

	private function _init_structure($user_options, $user_contents) {
		$this->_structure = parent::array_to_object($this->_structure);

		$user_contents_map = array("header" => array(), "body" => "", "color" => "");
		$new_user_contents = parent::set_array_prop_def($user_contents_map, $user_contents);

		$this->_structure->options = parent::set_array_prop_def($this->_options_map, $user_options);

		$body_structure = array(
			"editbox" => "",
			"content" => "",
			"class" => "",
			"toolbar" => null,
			"footer" => null
		);
		$this->_structure->body = parent::set_array_prop_def($body_structure, $new_user_contents["body"], "content");

		$header_structure = array(
			"icon" => null,
			"class" => "",
			"title" => "",
			"toolbar" => array()
		);
		$this->_structure->header = parent::set_array_prop_def($header_structure, $new_user_contents["header"], "title");

		$this->_structure->color = $new_user_contents["color"];
		$this->_structure->id = parent::create_id(true);
	}

	public function __set($name, $value) {
		if (isset($this->_structure->{$name})) {
            $this->_structure->{$name} = $value;
            return;
        }
		SmartUI::err('Undefined structure property: '.$name);
	}

	public function __call($name, $args) {
		return parent::_call($this, $this->_structure, $name, $args);
	}

	public function __get($name) {
		if (isset($this->_structure->{$name})) {
            return $this->_structure->{$name};
        }
        SmartUI::err('Undefined structure property: '.$name);
        return null;
	}

	public function print_html($return = false) {
		$get_property_value = parent::_get_property_value_func();

		$that = $this;
		$structure = $this->_structure;

		$attr = $get_property_value(
			$structure->attr,
			array(
				"if_closure" => function($attr) use ($that) { return SmartUtil::run_callback($attr, array($that)); }, //if user passes a closure, pass those optional parameters that they can use
				"if_other" => function($attr) { return $attr; }, //just directly return the string for this type of structure item
				"if_array" => function($attr) {
					$props = array_map(function($attr, $attr_value) { //build attribute values from passed array
						return $attr.' = "'.$attr_value.'"';
					}, array_keys($attr), $attr);

					return implode(' ', $props);
				}
			)
		);

		$options_map = $this->_options_map;

		$options = $get_property_value(
			$structure->options,
			array(
				"if_closure" => function($options) use ($that) { return SmartUtil::run_callback($options, array($that)); },
				"if_other" => function($options) { return $options; },
				"if_array" => function($options) use ($that, $options_map) {
					$props = array_map(function($option, $value) use ($that, $options_map) {
						if (is_bool($value)) {
							$str_val = var_export($value, true);
							if (isset($options_map[$option])) {
								if ($value !== $options_map[$option]) {
									return 'data-widget-'.$option.'="'.$str_val.'"';
								} else return '';
							} else return 'data-widget-'.$option.'="'.$str_val.'"';
						}
						return 'data-widget-'.$option.'="'.$value.'"';
					}, array_keys($options), $options);

					return implode(' ', $props);
				}
			)
		);

		$body = $get_property_value(
			$structure->body,
			array(
				"if_closure" => function($body) use ($that) { return SmartUtil::run_callback($body, array($that)); },
				"if_other" => function($body) {
					return '<div class="widget-body">'.$body.'</div>';
				},
				"if_array" => function($body) use ($that) {
					$editbox = '';
					if (isset($body["editbox"])) {
						$editbox = '<div class="jarviswidget-editbox">';
						$editbox .= $body["editbox"];
						$editbox .= '</div>';
					}

					$content = '';
					if (isset($body['content'])) {
						if (SmartUtil::is_closure($body['content'])) {
							$content = SmartUtil::run_callback($body['content'], array($that));
						} else {
							$content = $body['content'];
						}
					}


					$class = 'widget-body';
					if (isset($body["class"])) {
						if (is_array($body["class"])) {
							$class .= ' '.implode(' ', $body["class"]);
						} else {
							$class .= ' '.$body["class"];
						}
					}

					$toolbar = '';
					if (isset($body["toolbar"])) {
						$toolbar = '<div class="widget-body-toolbar">';
						$toolbar .= $body["toolbar"];
						$toolbar .= '</div>';
					}

					$footer = '';
					if (isset($body['footer'])) {
						$footer = '<div class="widget-footer">';
						$footer .= $body['footer'];
						$footer .= '</div>';
					}

					$result = $editbox;
					$result .= '<div class="'.$class.'">';
					$result .= $toolbar;
					$result .= $content;
					$result .= $footer;
					$result .= '</div>';

					return $result;
				}
			)
		);

		$header = $get_property_value(
			$structure->header,
			array(
				"if_closure" => function($header) use ($that) { return SmartUtil::run_callback($body, array($that)); },
				"if_other" => function($body) { return $body; },
				"if_array" => function($body) use ($get_property_value, $that) {
					$toolbar_htm = '';

					if (isset($body["icon"])) {
						$toolbar_htm .= '<span class="widget-icon"> <i class="'.SmartUI::$icon_source.' '.$body["icon"].'"></i> </span>';
					}

					if (isset($body["toolbar"])) {
						$toolbar_htm .= $get_property_value($body["toolbar"], array(
							"if_closure" => function($toolbar) use ($that) { return SmartUtil::run_callback($toolbar, array($that, $toolbar)); },
							"if_other" => function($toolbar) { return $toolbar; },
							"if_array" => function($toolbar) {
								$toolbar_props_htm = array();
								foreach ($toolbar as $toolbar_prop) {
									$id = '';
									$class = 'widget-toolbar';
									$attrs = array();
									$content = '';
									if (is_string($toolbar_prop)) {
										$content = $toolbar_prop;
									} else if (is_array($toolbar_prop)) {
										$id = isset($toolbar_prop["id"]) ? $toolbar_prop["id"] : '';
										$class .= isset($toolbar_prop["class"]) ? ' '.$toolbar_prop["class"] : '';
										if (isset($toolbar_prop["attr"])) {
											if (is_array($toolbar_prop["attr"])) {
												foreach ($toolbar_prop["attr"] as $attr => $value) {
													$attrs[] = $attr.'="'.$value.'"';
												}

											} else {
												$attrs[] = $toolbar_prop["attr"];
											}
										}
										$content = isset($toolbar_prop["content"]) ? $toolbar_prop["content"] : '';
									}

									$htm = '<div class="'.trim($class).'" id="'.$id.'" '.implode(' ', $attrs).'>';
									$htm .= $content;
									$htm .= '</div>';

									$toolbar_props_htm[] = $htm;
								}

								return implode(' ', $toolbar_props_htm);
							}
						));
					}

					if (isset($body["title"])) {
						$toolbar_htm .= $body["title"];
					} else
						$toolbar_htm .= '<h2><code>SmartUI::Widget->header[content] not defined</code></h2>';

					return $toolbar_htm;
				}
			)
		);

		$class = $get_property_value($structure->class, array(
			"if_closure" => function($class) use ($that) { return SmartUtil::run_callback($class, array($that)); },
			"if_array" => function($class) {
				return implode(' ', $class);
			}
		));

		$color = $get_property_value(
			$structure->color,
			array(
				"if_closure" => function($color) use ($that) { return SmartUtil::run_callback($color, array($that)); },
				"if_other" => function($color) { return $color ? 'jarviswidget-color-'.$color : ''; },
				"if_array" => function($color) {
					SmartUI::err('SmartUI::Widget::color requires string');
				}
			)
		);

		$id = $get_property_value(
			$structure->id,
			array(
				"if_closure" => function($id) use ($that) { return SmartUtil::run_callback($id, array($that)); },
				"if_array" => function($id) {
					SmartUI::err('SmartUI::Widget::id requires string.');
					return '';
				}
			)
		);

		$id = $id ? 'id="'.$id.'"' : '';
		$main_classes = array('jarviswidget', $color, $class);
		$main_attributes = array('class="'.trim(implode(' ', $main_classes)).'"', $id, $options, $attr);

		$result = '<div '.trim(implode(' ', $main_attributes)).'>';
		$result .= '<header>'.$header.'</header>';
		$result .= '<div>'.$body.'</div>';
		$result .= '</div>';

		if ($return) return $result;
		else echo $result;
	}
}
?>