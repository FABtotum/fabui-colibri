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
		'title' => _("Dashboard"),
		'icon' => 'fa fa-dashboard',
		'url' => 'dashboard',
		'pos' => 10
	),
	'make' => array(
		'title' => _("Make"),
		'icon' => 'fabui-core',
		'pos' => 20,
		'sub' => array(
			'print' => array(
				'title' => _("Print"),
				'icon' => 'fabui-print',
				'url' => 'make/print',
				'pos' => 10
			),
			'mill' => array(
				'title' => _("Mill"),
				'icon' => 'fabui-subtractive',
				'url' => 'make/mill',
				'pos' => 20
			),
			'scan' => array(
				'title' => _("Scan"),
				'icon' => 'fabui-scan',
				'url' => 'scan',
				'pos' => 30,
			),
			'history' => array(
				'title' => _("History"),
				'icon' => 'fa-history',
				'url' => 'history',
				'pos' => 100
			)
		)
	),
	'jog' => array(
		'title' => _("Jog"),
		'icon' => 'fabui-jog',
		'url' => 'jog',
		'pos' => 30
	),
	'projectsmanager' => array(
		'title' => _("Project manager"),
		'icon' => 'fa-cubes',
		'url' => 'projectsmanager',
		'pos' => 40
	),
	'maintenance' => array(
		'title' => _("Maintenance"),
		'icon' => 'fa-wrench',
		'pos' => 50,
		'sub' => array(
			'head' => array(
				'title' => _("Head"),
				'icon' => 'fabui-head-2',
				'url' => 'maintenance/head',
				'pos' => 10
			),
			'spool' => array(
				'title' => _("Spool management"),
				'icon' => 'fabui-spool-front',
				'url' => 'maintenance/spool-management',
				'pos' => 20
			),
			'bedcalibration' => array(
				'title' => _("Bed calibration"),
				'icon' => 'fabui-bed',
				'url' => 'maintenance/bed-calibration',
				'pos' => 30
			),
			'nozzle' => array (
				'title' => _("Nozzle"),
				'icon' => 'fabui-nozzle',
				'pos' => 40,
				'sub' => array(
					'height' => array(
						'title' => _("Height calibration"),
						'icon' => 'fa-arrows-v',
						'url' => 'maintenance/nozzle-height-calibration',
						'pos' => 10,
					),
					'pidtune' => array(
						'title' => _("PID tune"),
						'icon' => 'fa-thermometer-three-quarters',
						'url' => 'maintenance/nozzle-pid-tune',
						'pos' => 20
					)
				)
			),
			'probecalibration' => array(
				'title' => _("Probe calibration"),
				'icon' => 'fa-level-down',
				'pos'  => 50,
				'sub'  => array(
					'angle' => array(
						'title' => _("Angle calibration"),
						'icon' => 'fa-angle-left',
						'url' => 'maintenance/probe-angle-calibration'
					)
				)
			),
			'feedercalibration' => array(
				'title' => _("Feeder"),
				'icon' => 'fabui-feeder',
				'pos' => 60,
				'sub'  => array(
					'profiles' => array(
						'title' => _("Profiles"),
						'icon' => 'fa-bars',
						'url' => 'maintenance/feeder-profiles',
						'pos' => 10
					),
					'length' => array(
						'title' => _("Step calibration"),
						'icon' => 'fabui-e-mode',
						'url' => 'maintenance/feeder-calibration',
						'pos' => 20,
					),
					'angle' => array(
						'title' => _("Engage"),
						'icon' => 'fa-hand-o-right',
						'url' => 'maintenance/feeder-engage',
						'pos' => 30
					)
				)
			),
			'4thaxis' => array(
				'title' => _("4th axis"),
				'icon' => 'fa-arrows-h',
				'url' => 'maintenance/4th-axis',
				'pos' => 70
			),
			'firstsetup' => array(
				'title' => _("First setup"),
				'icon' => 'fa-magic',
				'url' => 'maintenance/first-setup',
				'pos' => 80
			),
			'firmware' => array(
				'title' => _("Firmware"),
				'icon' => 'fa-microchip',
				'url' => 'maintenance/firmware',
				'pos' => 90,
			),
			'systeminfo' => array(
				'title' => _("System info"),
				'icon' => 'fa-info-circle',
				'url' => 'maintenance/system-info',
				'pos' => 100
			)
		)
	),
	'settings' => array (
		'title' => _("Settings"),
		'icon' => 'fa-cogs',
		'pos' => 60,
		'sub' => array(
			'hardware' => array(
				'title' => _("Hardware"),
				'icon' => 'fa-cog',
				'url' => 'settings/hardware',
				'pos' => 10
			),
			'network' => array(
				'title' => _("Network"),
				'icon' => 'fa-globe',
				'url' => 'settings/network',
				'pos' => 20
			),
			'raspicam' => array(
				'title' => _("Raspicam"),
				'icon' => 'fa-video-camera',
				'url' => 'settings/cam',
				'pos' => 30
			)
		)
	),
	'updates' => array(
 		'title' => _("Updates"),
 		'icon' => 'fa fa-refresh',
 		'url' => 'updates',
		'pos' => 70
	),
	'support' => array(
 		'title' => _("Support"),
 		'icon' => 'fa-life-ring',
 		'url' => 'support',
		'pos' => 80
	),
	'plugin' => array(
 		'title' => _("Plugins"),
 		'icon' => 'fa-plug',
 		'url' => 'plugin',
		'pos' => 90
	),
 );
 
$CI =& get_instance();
$CI->load->helper('plugin_helper');

extendMenuWithPlugins($config['menu']);

?>
