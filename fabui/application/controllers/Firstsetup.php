<?php
/**
 * 
 * @author Krios Mane
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */

defined('BASEPATH') OR exit('No direct script access allowed');
 
class FirstSetup extends FAB_Controller {

    public function index()
    {
		//load libraries, helpers, model
		$this->load->library('smart');
		$this->load->helper('form');

		$widgetOptions = array(
			'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
			'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
		);
		
		$data = array();
		
		$widgeFooterButtons = '';

		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'main-widget-firstsetup';
		$widget->header = array('icon' => 'icon-fab-print', "title" => "<h2>"._("First setup")."</h2>");
		$widget->body   = array('content' => $this->load->view('firstsetup/widget', $data, true ), 'class'=>'fuelux');
		
		$this->addJsInLine($this->load->view( 'firstsetup/js', $data, true));
		
		$this->content = $widget->print_html(true);
		$this->view();
    }

	public function new_index($step = 'head')
	{
		$this->load->library('smart');
		$this->load->helper('form');
		$this->load->helper('fabtotum_helper');
		
		$data = array();
		
		$data['head_in_place'] = isHeadInPlace();
		$data['head_info'] = getInstalledHeadInfo();
		
		$_units = loadSettings();
		$heads  = loadHeads();
		$data['units'] = $_units;
		$data['settings'] = loadSettings();
		$data['heads'] = $heads;
		$data['filamentsOptions'] = array('pla' => 'PLA', 'abs' => 'ABS', 'nylon' => 'Nylon');
		
		$heads_list = array();

		foreach($heads as $head => $val)
		{
			$heads_list[$head] = $val['name'];
		}
		
		$data['heads_list'] = $heads_list;
		$data['head'] = 'printing_head_v2';
		
		/*$bed_subtask_data = array(
			'name'  => 'bed',
			'steps' => array(
					array(
					 'title'   => _("Intro"),
					 'name'    => 'intro',
					 'content' => $this->load->view( 'firstsetup/wizard/bed/intro', $data, true ),
					 'active'  => true
					),
					array(
					 'title'   => _("Bed"),
					 'name'    => 'bed',
					 'content' => $this->load->view( 'firstsetup/wizard/bed/results', $data, true )
					)
			)
		);*/
		
		$data['steps'] = array(
				array(
					'title'   => _("Head"),
					'name'    => 'head',
					'content' => $this->load->view( 'firstsetup/wizard/head', $data, true ),
					'active'  => ($step == 'head')
			    ),
			    array(
					'title'   => _("Bed"),
					'name'    => 'bed',
					'content' => $this->load->view( 'bed/calibration_widget', $data, true ),
					'active'  => ($step == 'bed')
				),

				array(
					'title'   => _("Nozzle"),
					'name'    => 'nozzle',
					//'content' => $this->load->view( 'std/subtask_wizard', $data, true ),
					'active'  => ($step == 'nozzle'),
					'steps' => array(
							array(
							 'title'   => _("Get ready"),
							 'name'    => 'get_ready',
							 'content' => $this->load->view( 'firstsetup/wizard/nozzle/get_ready', $data, true ),
							 'active'  => false
							),
							array(
							 'title'   => _("Jog"),
							 'name'    => 'jog',
							 'content' => $this->load->view( 'firstsetup/wizard/nozzle/jog', $data, true ),
							 'active'  => true
							)
					)
			    ),
				array(
					'title'   => _("Spool"),
					'name'    => 'spool',
					//'content' => $this->load->view( 'firstsetup/wizard/empty', $data, true ),
					'active'  => ($step == 'spool'),
					'steps' => array(
							array(
							 'title'   => _("Choose"),
							 'name'    => 'get_ready',
							 'content' => $this->load->view( 'spool/wizard/step2', $data, true ),
							 'active'  => true
							),
							array(
							 'title'   => _("Load"),
							 'name'    => 'load',
							 'content' => $this->load->view( 'firstsetup/wizard/empty', $data, true ),
							 'active'  => false
							),
							array(
							 'title'   => _("Finish"),
							 'name'    => 'finish',
							 'content' => $this->load->view( 'firstsetup/wizard/empty', $data, true ),
							 'active'  => false
							)
					)
			    ),
				array(
					'title'   => _("Feeder"),
					'name'    => 'feeder',
					'content' => $this->load->view( 'firstsetup/wizard/empty', $data, true ),
					'active'  => ($step == 'feeder')
			    ),
				array(
					'title'   => _("Test"),
					'name'    => 'test',
					'content' => $this->load->view( 'firstsetup/wizard/empty', $data, true ),
					'active'  => ($step == 'test')
			    ),
				array(
				'title'   => _("Finish"),
				'name'    => 'finish',
				'content' => $this->load->view( 'firstsetup/wizard/empty', $data, true ),
				'active'  => ($step == 'finish')
			    )
			);
		
		$widgetOptions = array(
			'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
			'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
		);
		
		$widgeFooterButtons = '';

		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'main-widget-firstsetup';
		$widget->header = array('icon' => 'icon-fab-print', "title" => "<h2>"._("First setup")."</h2>");
		$widget->body   = array('content' => $this->load->view('std/task_wizard', $data, true ), 'class'=>'fuelux');

		$this->addJSFile('/assets/js/plugin/fuelux/wizard/wizard.min.old.js'); //wizard
		$this->addJsInLine($this->load->view( 'std/task_wizard_js',   $data, true));
		//~ $this->addJsInLine($this->load->view( 'std/subtask_wizard_js',   $data, true));
		
		$this->addJsInLine($this->load->view( 'firstsetup/js', $data, true));
		$this->addJsInLine($this->load->view( 'firstsetup/wizard/head_js', $data, true));
		$this->addJsInLine($this->load->view( 'bed/calibration_js', $data, true));


		$this->content = $widget->print_html(true);
		$this->view();
	}
}
 
?>
