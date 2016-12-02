<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */

$config['menu'] = array(
	'dashboard' => array(
		'title' => 'Dashboard',
		'icon' => 'fa fa-dashboard',
		'url' => 'dashboard'
	),
	'make' => array(
		'title' => 'Make',
		'icon' => 'fa-play fa-rotate-90',
		'sub' => array(
			'print' => array(
				'title' => 'Print',
				'icon' => 'icon-fab-print',
				'url' => 'make/print'
			),
			'mill' => array(
				'title' => 'Mill',
				'icon' => 'icon-fab-mill',
				'url' => 'make/mill'
			),
			'scan' => array(
				'title' => 'Scan',
				'icon' => 'icon-fab-scan',
				'url' => 'scan'
			),
			'history' => array(
				'title' => 'History',
				'icon' => 'fa-history',
				'url' => 'history'
			)
		)
	),
	'jog' => array(
		'title' => 'Jog',
		'icon' => 'icon-fab-jog',
		'url' => 'jog'
	),
	'projectsmanager' => array(
		'title' => 'Projects Manager',
		'icon' => 'fa-cubes',
		'url' => 'projectsmanager'
	),
	'maintenance' => array(
		'title' => 'Maintenance',
		'icon' => 'fa-wrench',
		'sub' => array(
			'head' => array(
				'title' => 'Head Installation',
				'icon' => 'fa-toggle-down',
				'url' => 'maintenance/head/install'
			),
			'spool' => array(
				'title' => 'Spool Management',
				'icon' => 'fa-circle-o-notch',
				'url' => 'maintenance/spool-management'
			),
			'bedcalibration' => array(
				'title' => 'Bed Calibration',
				'icon' => 'fa-arrows-h',
				'url' => 'maintenance/bed-calibration'
			),
			'probecalibration' => array(
				'title' => 'Probe Calibration',
				'icon' => 'fa-level-down',
				'sub'  => array(
					'length' => array(
						'title' => 'Length Calibration',
						'icon' => 'fa-arrows-v',
						'url' => 'maintenance/probe-length-calibration'
					),
					'angle' => array(
						'title' => 'Angle Calibration',
						'icon' => 'fa-angle-left',
						'url' => 'maintenance/probe-angle-calibration'
					)
				)
			),
			'feedercalibration' => array(
				'title' => 'Feeder',
				'icon' => 'fa-level-down',
				'sub'  => array(
					'length' => array(
						'title' => 'Step Calibration',
						'icon' => 'fa-cog',
						'url' => 'maintenance/feeder-calibration'
					),
					'angle' => array(
						'title' => 'Engage',
						'icon' => 'fa-hand-o-right',
						'url' => 'maintenance/feeder-engage'
					)
				)
			),
			'4thaxis' => array(
				'title' => '4th Axis',
				'icon' => 'fa-arrows-h',
				'url' => 'maintenance/4th-axis'
			),
			'firstsetup' => array(
				'title' => 'First Setup',
				'icon' => 'fa-magic',
				'url' => 'maintenance/first-setup'
			),
			'firmware' => array(
				'title' => 'Firmware',
				'icon' => 'fa-microchip',
				'url' => 'maintenance/firmware'
			),
			'systeminfo' => array(
				'title' => 'System Info',
				'icon' => 'fa-info-circle',
				'url' => 'maintenance/system-info'
			)
		)
	),
	'settings' => array (
		'title' => 'Settings',
		'icon' => 'fa-cogs',
		'sub' => array(
			'hardware' => array(
				'title' => 'Hardware',
				'icon' => 'fa-cog',
				'url' => 'settings/hardware'
			),
			'network' => array(
				'title' => 'Network',
				'icon' => 'fa-globe',
				'url' => 'settings/network'
			),
			'raspicam' => array(
				'title' => 'Raspicam',
				'icon' => '',
				'url' => 'settings/cam'
			)
		)
	),
	'updates' => array(
 		'title' => 'Updates',
 		'icon' => 'fa fa-refresh',
 		'url' => 'updates'
	),
	'support' => array(
 		'title' => 'Support',
 		'icon' => 'fa-life-ring',
 		'url' => 'support'
	),
	'plugin' => array(
 		'title' => 'Plugins',
 		'icon' => 'fa-plug',
 		'url' => 'plugin'
	),
 );
 
$CI =& get_instance();
$CI->load->helper('plugin_helper');

extendMenuWithPlugins($config['menu']);
 
?>
