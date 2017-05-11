<?php 

use MatthiasMullie\Minify;

require_once 'autoload.php';

class Minifier {
	
	
	protected $js_minifier;
	protected $css_minifier;
	
	/***
	 * 
	 */
	function __construct()
	{
		$this->js_minifer = new Minify\JS();
		$this->css_minifier = new Minify\CSS();
	}
	/***
	 * 
	 */
	public function addCSS($css)
	{
		$this->css_minifier->add($css);
	}
	/***
	 * 
	 */
	public function addJS($js)
	{
		$this->js_minifer->add($js);
	}
	/**
	 * 
	 */
	public function minifyJS($path='')
	{
		if($path != '')  $this->js_minifer->minify($path);
		else return $this->js_minifer->minify();
	}
	/**
	 *
	 */
	public function minifyCSS($path='')
	{
		if($path != '')  $this->css_minifier->minify($path);
		else return $this->css_minifier->minify();
	}
}



?>