<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'dashboard';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
//install
$route['install/do'] = 'install/doInstall';
$route['install/restore'] = 'install/doRestore';
//login
$route['login/new-account']    = 'login/newAccount';
$route['login/reset-password'] = 'login/resetPassword';
$route['login/do']             = 'login/doLogin';
// make
$route['make/print/([a-z]+)/(\d+)'] = 'create/index/print/$1/$2';
$route['make/mill/([a-z]+)/(\d+)']  = 'create/index/mill/$1/$2';
$route['make/print'] = 'create/index/print';
$route['make/mill']  = 'create/index/mill';
$route['make/scan']  = 'scan';
//file manager
$route['projectsmanager/add-project']  = 'projectsmanager/newProject';
$route['projectsmanager/add-file/(:num)']  = 'projectsmanager/newFile/$1';
//settings
$route['settings/cam'] = 'cam';
//maintenance
$route['maintenance/head/install']              = 'head/index/install';
$route['maintenance/bed-calibration']           = 'bed';
$route['maintenance/spool-management']          = 'spool';
$route['maintenance/probe-length-calibration']  = 'probe/index/length';
$route['maintenance/probe-angle-calibration']   = 'probe/index/angle';
$route['maintenance/feeder-calibration']        = 'feeder/index/calibrate';
$route['maintenance/feeder-engage']             = 'feeder/index/engage';
$route['maintenance/4th-axis']                  = 'fourthaxis';
$route['maintenance/first-setup']               = 'firstsetup';
$route['maintenance/firmware']                  = 'firmware';
$route['maintenance/system-info']               = 'systeminfo';
$route['maintenance/nozzle-height-calibration'] = 'nozzle';

$route['plugin/add'] = 'plugin/add';
$route['plugin/upload'] = 'plugin/upload';
$route['plugin/doUpload'] = 'plugin/doUpload';
$route['plugin/remove/(:any)'] = 'plugin/manage/remove/$1';
$route['plugin/activate/(:any)'] = 'plugin/manage/activate/$1';
$route['plugin/deactivate/(:any)'] = 'plugin/manage/deactivate/$1';

$route['plugin/(:any)'] = 'plugin_$1';
$route['plugin/(:any)/(:any)'] = 'plugin_$1/$2';
# 1 arg
$route['plugin/(:any)/(:any)/(:any)'] = 'plugin_$1/$2/$3';
# 2
$route['plugin/(:any)/(:any)/(:any)/(:any)'] = 'plugin_$1/$2/$3/$4';
# 3
$route['plugin/(:any)/(:any)/(:any)/(:any)/(:any)'] = 'plugin_$1/$2/$3/$5';
# 4
$route['plugin/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)'] = 'plugin_$1/$2/$3/$6';
# 5
$route['plugin/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)'] = 'plugin_$1/$2/$3/$6/$7';
