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
		'icon' => 'fa fa-lg fa-fw fa-tachometer-alt',
		'url' => 'dashboard',
		'pos' => 10
	),
	'cam' => array(
		'title' => _("CAM Toolbox"),
		'icon' => 'fa-lg fa-fw fabui-edit-file',
		'url' => 'cam-toolbox',
		'pos' => 11
	),
	'make' => array(
		'title' => _("Make"),
		'icon' => 'fa-lg fa-fw fabui-core',
		'pos' => 20,
		'sub' => array(
			'print' => array(
				'title' => _("Print"),
				'icon' => 'fa-lg fa-fw fabui-print',
				'url' => 'make/print',
				'pos' => 10
			),
			'mill' => array(
				'title' => _("Mill"),
				'icon' => 'fa-lg fa-fw fabui-subtractive',
				'url' => 'make/mill',
				'pos' => 20
			),
			'scan' => array(
				'title' => _("Scan"),
				'icon' => 'fa-lg fa-fw fabui-3d-scanner',
				'url' => 'scan',
				'pos' => 30,
			),
			'history' => array(
				'title' => _("History"),
				'icon' => 'fa fa-lg fa-fw fa-history',
				'url' => 'history',
				'pos' => 100
			)
		)
	),
	'jog' => array(
		'title' => _("Jog"),
		'icon' => 'fa-lg fa-fw fabui-jog',
		'url' => 'jog',
		'pos' => 30
	),
	'projectsmanager' => array(
		'title' => _("Project manager"),
		'icon' => 'fa fa-lg fa-fw fa-cubes',
		'url' => 'projectsmanager',
		'pos' => 40
	),
	'maintenance' => array(
		'title' => _("Maintenance"),
		'icon' => 'fa fa-lg fa-fw fa-wrench',
		'pos' => 50,
		'sub' => array(
			'head' => array(
				'title' => _("Heads & Modules"),
				'icon' => 'fa-lg fa-fw fabui-head-2',
				'url' => 'maintenance/heads-modules',
				'pos' => 10
			),
			'spool' => array(
				'title' => _("Spool management"),
				'icon' => 'fa-lg fa-fw fabui-spool-front',
				'url' => 'maintenance/spool-management',
				'pos' => 20
			),
			'bedcalibration' => array(
				'title' => _("Bed calibration"),
				'icon' => 'fa-lg fa-fw fabui-bed',
				'url' => 'maintenance/bed-calibration',
				'pos' => 30
			),
			'nozzle' => array (
				'title' => _("Nozzle"),
				'icon' => 'fa-lg fa-fw fabui-nozzle',
				'pos' => 40,
				'sub' => array(
					'height' => array(
						'title' => _("Height calibration"),
						'icon' => 'fa fa-lg fa-fw fa-arrows-alt-v',
						'url' => 'maintenance/nozzle-height-calibration',
						'pos' => 10,
					),
					'pidtune' => array(
						'title' => _("PID tune"),
						'icon' => 'fa fa-lg fa-fw fa-thermometer-three-quarters',
						'url' => 'maintenance/nozzle-pid-tune',
						'pos' => 20
					)
				)
			),
			'probecalibration' => array(
				'title' => _("Probe calibration"),
				'icon' => 'fa fa-lg fa-fw fa-level-down-alt',
				'pos'  => 50,
				'sub'  => array(
					'angle' => array(
						'title' => _("Angle calibration"),
						'icon' => 'fa fa-lg fa-fw fa-angle-left',
						'url' => 'maintenance/probe-angle-calibration'
					)
				)
			),
			'feedercalibration' => array(
				'title' => _("Feeder"),
				'icon' => 'fa-lg fa-fw fabui-feeder',
				'pos' => 60,
				'sub'  => array(
					'profiles' => array(
						'title' => _("Profiles"),
						'icon' => 'fa fa-lg fa-fw fa-bars',
						'url' => 'maintenance/feeder-profiles',
						'pos' => 10
					),
					'length' => array(
						'title' => _("Step calibration"),
						'icon' => 'fa-lg fa-fw fabui-e-mode',
						'url' => 'maintenance/feeder-calibration',
						'pos' => 20,
					),
					'angle' => array(
						'title' => _("Engage"),
						'icon' => 'fa fa-lg fa-fw fa-hand-o-right',
						'url' => 'maintenance/feeder-engage',
						'pos' => 30
					)
				)
			),
			'4thaxis' => array(
				'title' => _("4th axis"),
				'icon' => 'fa fa-lg fa-fw fa-arrows-h',
				'url' => 'maintenance/4th-axis',
				'pos' => 70
			),
			'firstsetup' => array(
				'title' => _("First setup"),
				'icon' => 'fa fa-lg fa-fw fa-magic',
				'url' => 'maintenance/first-setup',
				'pos' => 80
			),
			'firmware' => array(
				'title' => _("Firmware"),
				'icon' => 'fa fa-lg fa-fw fa-microchip',
				'url' => 'maintenance/firmware',
				'pos' => 90,
			),
			'systeminfo' => array(
				'title' => _("System info"),
				'icon' => 'fa fa-lg fa-fw fa-info-circle',
				'url' => 'maintenance/system-info',
				'pos' => 100
			),
		    /*'backup' => array(
		        'title' => _("Backup & restore"),
		        'icon' => 'icon-electronics-089',
		        'url' => 'maintenance/backup-restore',
		        'pos' => 110
		    )*/
		)
	),
	'settings' => array (
		'title' => _("Settings"),
		'icon' => 'fa fa-lg fa-fw fa-cogs',
		'pos' => 60,
		'sub' => array(
			'hardware' => array(
				'title' => _("Hardware"),
				'icon' => 'fa fa-lg fa-fw fa-cog',
				'url' => 'settings/hardware',
				'pos' => 10
			),
			'network' => array(
				'title' => _("Network"),
				'icon' => 'fa fa-lg fa-fw fa-globe',
				'url' => 'settings/network',
				'pos' => 20
			),
			'raspicam' => array(
				'title' => _("Raspicam"),
				'icon' => 'fa fa-lg fa-fw fa-video',
				'url' => 'settings/cam',
				'pos' => 30
			),
		    'users' => array(
		      'title' => _("Users"),
		      'icon' => 'fa fa-lg fa-fw fa-users',
		      'url'  => 'settings/users',
		      'pos'  => 40
		    ),
		)
	),
	'updates' => array(
 		'title' => _("Updates"),
 		'icon' => 'fa fa-lg fa-fw  fa-sync-alt',
 		'url' => 'updates',
		'pos' => 70
	),
	'support' => array(
 		'title' => _("Support"),
 		'icon' => 'fa fa-lg fa-fw fa-life-ring',
 		'url' => 'support',
		'pos' => 80
	),
	'plugin' => array(
 		'title' => _("Plugins"),
 		'icon' => 'fa fa-lg fa-fw fa-plug',
 		'url' => 'plugin',
		'pos' => 90
	),
 );
 
$CI =& get_instance();
$CI->load->helper('plugin_helper');

extendMenuWithPlugins($config['menu']);

?>
