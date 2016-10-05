<?php
/**
 * 
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 * Camera factory
 */
 
class Camera {
	
	protected $CI; //code igniter instance
	protected $version; // camera version
	protected $camera_params; // camera parameters
	protected $arguments; // raspistill arguments
	protected $output_path; // output directory
	protected $base_filename;
	protected $filename;
	
	/**
	 * class constructor
	 */
	function __construct($params = array()) 
	{
		foreach($params as $key => $value) //init class attributes (if present)
		{
			if(property_exists($this, $key))
				$this->$key = $value;
		}
		
		$this->CI =& get_instance(); //init ci instance
		$this->CI->load->helper('file');
		$this->CI->config->load('fabtotum');
		$this->CI->load->helper('fabtotum_helper');
		$this->version = $this->CI->config->item('camera_version');
		
		$cameras_path = $this->CI->config->item('cameras');
		$this->camera_params = json_decode(file_get_contents( $cameras_path.'/'.$this->version . '_params.json'), true);
		
		$this->output_path = $this->CI->config->item('temp_path');
		$this->arguments = $this->get_default_settings();
		$this->base_filename = 'preview.';
		$this->filename = $this->base_filename . $this->arguments['encoding'];
	}
	
	function getFilename()
	{
		return $this->output_path . $this->filename;
	}
	
	function getPermalink()
	{
		return $this->output_path . 'preview.permalink';
	}
	
	/**
	 * return camera version
	 */
	function getVersion()
	{
		return $this->version;
	}

	/**
	 * return mapping between 'low','medium','high' and numeric values for 
	 * width and height
	 */
	function getResolutionMapping()
	{
		return $this->camera_params['resolution'];
	}
	
	function takePhoto()
	{
		$args = '-n -t 1';
		
		$output = $this->getFilename();
		$permalink = $this->output_path . 'preview.permalink';
		$args .= ' --output '.$output;
		
		foreach($this->arguments as $arg => $value)
		{
			if ($arg == 'flip')
			{
				if($value == "vflip" || $value == "bflip")
				{
					$args .= ' --vflip';
				}
				if($value == "hflip" || $value == "bflip")
				{
					$args .= ' --hflip';
				}
			}
			else
			{
				$args .= ' --'.$arg . ' ' . $value;
			}
		}
		
		doCommandLine('raspistill ', $args);
		doCommandLine('ln -sf ', $output . ' ' . $permalink);
		
		return $output;
	}
	
	function setValue($key, $value)
	{
		if( array_key_exists($key, $this->arguments) )
		{
			if($key == 'encoding')
			{
				$this->filename = $this->base_filename . $value;
			}
			$this->arguments[$key] = $value;
			return true;
		}
		
		return false;
	}
	
	function getValue($key)
	{
		$value = null;
		if( array_key_exists($key, $this->arguments) )
		{
			$value = $this->arguments[$key];
		}
		
		return $value;
	}
	
	function getMimeType()
	{
		switch(  $this->arguments['encoding'] ) {
			case "bmp": 
				$ctype="image/bmp"; 
				break;
			case "gif": 
				$ctype="image/gif";
				break;
			case "png": 
				$ctype="image/png";
				break;
			case "jpeg":
			case "jpg": 
				$ctype="image/jpg";
				break;
			default:
				$ctype="application/octet-stream";
		}
		
		return $ctype;
	}
	
	/**
	 * return parameter list with available values 
	 */
	function getParameterList()
	{
		// -- type
		$params['encoding']['jpg'] = '.jpg';
		$params['encoding']['bmp'] = '.bmp';
		$params['encoding']['png'] = '.png';
		$params['encoding']['gif'] = '.gif';
		// -- size (depends on camera version)
		$params['size'] = array();
		foreach($this->camera_params['resolution']['all'] as $size )
		{
			$params['size'][$size] = $size;
		}
		// -- iso
		$params['ISO']['100'] = 100;
		$params['ISO']['200'] = 200;
		$params['ISO']['300'] = 300;
		$params['ISO']['400'] = 400;
		$params['ISO']['500'] = 500;
		$params['ISO']['600'] = 600;
		$params['ISO']['700'] = 700;
		$params['ISO']['800'] = 800;
		// -- quality list
		$params['quality']['10']  = 10;
		$params['quality']['20']  = 20;
		$params['quality']['30']  = 30;
		$params['quality']['40']  = 40;
		$params['quality']['50']  = 50;
		$params['quality']['60']  = 60;
		$params['quality']['70']  = 70;
		$params['quality']['80']  = 80;
		$params['quality']['90']  = 90;
		$params['quality']['100'] = 100;
		// -- imxfxs list
		$params['imxfx']['none']          = 'None';
		$params['imxfx']['negative']      = 'Negative';
		$params['imxfx']['solarise']      = 'Solarise';
		$params['imxfx']['polarise']      = 'Polarise';
		$params['imxfx']['whiteboard']    = 'Whiteboard';
		$params['imxfx']['blackboard']    = 'Blackboard';
		$params['imxfx']['sketch']        = 'Sketch';
		$params['imxfx']['denoise']       = 'Denoise';
		$params['imxfx']['emboss']        = 'Emboss';
		$params['imxfx']['oilpaint']      = 'Oilpaint';
		$params['imxfx']['hatch']         = 'Hatch';
		$params['imxfx']['gpen']          = 'Gpen';
		$params['imxfx']['pastel']        = 'Pastel';
		$params['imxfx']['watercolour']   = 'Watercolour';
		$params['imxfx']['film']          = 'Film';
		$params['imxfx']['blur']          = 'Blur';
		$params['imxfx']['saturation']    = 'Saturation';
		$params['imxfx']['colourswap']    = 'Colourswap';
		$params['imxfx']['whashedout']    = 'Whashedout';
		$params['imxfx']['posterise']     = 'Posterise';
		$params['imxfx']['colourpoint']   = 'Colourpoint';
		$params['imxfx']['colourbalance'] = 'Colourbalance';
		$params['imxfx']['cartoon']       = 'Cartoon';
		// -- brightness list
		$params['brightness']['100'] = "Max";
		$params['brightness']['88']  = "Very High";
		$params['brightness']['64']  = "High";
		$params['brightness']['50']  = "Default";
		$params['brightness']['32']  = "Low";
		$params['brightness']['18']  = "Very Low";
		$params['brightness']['0']   = "Min";
		// -- contrast list
		$params['contrast']['100']  = "Max";
		$params['contrast']['64']   = "Very High";
		$params['contrast']['32']   = "High";
		$params['contrast']['0']    = "Default";
		$params['contrast']['-32']  = "Low";
		$params['contrast']['-64']  = "Very Low";
		$params['contrast']['-100'] = "Min";
		// -- awb list
		$params['awb']['off']          = 'Off';
		$params['awb']['auto']         = 'Auto';
		$params['awb']['sun']          = 'Sun';
		$params['awb']['cloud']        = 'Cloud';
		$params['awb']['shade']        = 'Shade';
		$params['awb']['tungstun'] 	  = 'Tungstun';
		$params['awb']['incandescent'] = 'Incandescent';
		$params['awb']['flash']        = 'Flash';
		$params['awb']['horizon']      = 'Horizon';
		// -- ev comp list
		$params['ev_comp']['10']  = "Max";
		$params['ev_comp']['8']   = "Very High";
		$params['ev_comp']['6']   = "High";
		$params['ev_comp']['5']   = "Default";
		$params['ev_comp']['-6']  = "Low";
		$params['ev_comp']['-8']  = "Very Low";
		$params['ev_comp']['-10'] = "Min";
		// -- exposure list
		$params['exposure']['off']          = "Off";
		$params['exposure']['auto']         = "Auto";
		$params['exposure']['night']        = "Night";
		$params['exposure']['nightpreview'] = "NightPreview";
		$params['exposure']['backlight']    = "Backlight";
		$params['exposure']['spotlight']    = "Spotlight";
		$params['exposure']['sports']       = "Sports";
		$params['exposure']['snow']         = "Snow";
		$params['exposure']['beach']        = "Beach";
		$params['exposure']['verylong']     = "Very Long";	
		$params['exposure']['fixedfps']     = "Fixed Fps";
		$params['exposure']['antishake']    = "Anti Shake";
		$params['exposure']['fireworks']    = "Fireworks";
		// -- rotation list
		$params['rotation']['0']   = 0;
		$params['rotation']['90']  = 90;
		$params['rotation']['180'] = 180;
		$params['rotation']['270'] = 270;
		// -- metering list
		$params['metering']['average'] = 'Average';
		$params['metering']['spot']    = 'Spot';
		$params['metering']['backlit'] = 'Backlit';
		$params['metering']['matrix']  = 'Matrix';
		// -- Flip
		$params['flip']['no']    = 'No Flip';
		$params['flip']['vflip'] = 'Vertical Flip';
		$params['flip']['hflip'] = 'Horizontal Flip';
		$params['flip']['bflip'] = 'Flip Both';
		
		return $params;
	}
	
	function get_default_settings()
	{
		return array(
			'encoding' => 'jpg',
			'width' => '640',
			'height' => '480',
			'ISO' => '800',
			'quality' => '100',
			'brightness' => '50',
			'imxfx' => 'none',
			'contrast' => '0',
			'sharpness' => '0',
			'saturation' => '0',
			'awb' => 'auto',
			'ev' => '5',
			'exposure' => 'auto',
			'rotation' => '90',
			'metering' => 'average',
			'flip' => 'bflip'
		);	
	}
}
 
?>
