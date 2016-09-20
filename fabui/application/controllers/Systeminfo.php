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
 
function seconds_to_time($seconds) {
	$dtF = new DateTime("@0");
	$dtT = new DateTime("@$seconds");
	return $dtF -> diff($dtT) -> format('%ad %hh %im');
}
 
class SystemInfo extends FAB_Controller {

    public function index()
    {
		$this->load->library('smart');
		$this->load->helper('fabtotum_helper');

		// ==== MEMORY
		$output_memory = shell_exec('cat /proc/meminfo');
		$table_rows    = preg_split('/$\R?^/m', $output_memory);
		$row_mem_total = $table_rows[0];
		$row_mem_free  = $table_rows[1];
		$data['mem_total'] = explode(' ', $row_mem_total);
		$data['mem_total'] = $data['mem_total'][count($data['mem_total']) - 2];
		$data['mem_free']  = explode(' ', $row_mem_free);
		$data['mem_free']  = $data['mem_free'][count($data['mem_free']) - 2];
		$data['mem_used_percentage'] = floor((($data['mem_total'] - $data['mem_free']) / $data['mem_total']) * 100);
		
		// === BOARD TEMPERATURE
		$output       = shell_exec('cat /sys/class/thermal/thermal_zone0/temp');
		$data['temp'] = intval($output) / 1000;
		// === BOARD TIME ALIVE
		$output             = shell_exec('echo "$(</proc/uptime awk \'{print $1}\')"');
		$data['time_alive'] = seconds_to_time(intval($output));
		
		// vcgencmd get_camera => supported=1 detected=1
		
		$data['rpi_version'] = shell_exec('</proc/cpuinfo grep Hardware | awk \'{print $3}\'');
		// === NETWORK
		//~ $output        = shell_exec('sh /var/www/fabui/script/bash/transfer_rate.sh eth0');
        //~ $data['eth_rates'] = explode(' ', $output);
		
		//~ $output        = shell_exec('sh /var/www/fabui/script/bash/transfer_rate.sh wlan0');
        //~ $data['wlan_rates'] = explode(' ', $output);
		
		// == STORAGE
		$output               = shell_exec('df -Ph');
		$table_rows   = preg_split('/$\R?^/m', $output);
		$table_header = explode(' ', $table_rows[0]);
		$table_rows   = array_splice($table_rows, 1);
		$data['table_header'] = array_splice($table_header, 0, count($table_header) - 1);
		
		$tmp = array();
		$visible_partitions = array('/tmp', '/mnt/bigtemp', '/mnt/userdata', '/mnt/live/mnt/changes', '/mnt/live/mnt/bundles', '/mnt/live/mnt/boot');
		foreach($table_rows as $row)
		{
			$row = preg_replace('/\s+/', ' ',$row);
			$items = explode(' ', $row);
			$mount_point = $items[5];
			if( in_array($mount_point, $visible_partitions) )
			{
				$tmp[] = $row;
			}
		}
		$data['table_rows'] = $tmp;
		
		//== OS INFO
		$data['os_info'] = shell_exec('uname -a');
		
		// == FABTOTUM INFO
		$data['fabtotum_info'] = array('fw' => '0.96', 'hw' => 'v1.0');
		
		$_units = loadSettings();
		$settings_type = $_units['settings_type'];
		if (isset($_units['settings_type']) && $_units['settings_type'] == 'custom') {
			$_units = loadSettings( $_units['settings_type'] );
		}
		
		$data['unit_configs']  = $_units;
		
		$widgetOptions = array(
			'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
			'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
		);
		
		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'main-widget-systeminfo';
		$widget->header = array('icon' => 'fa-info-circle', "title" => "<h2>System Info</h2>");
		$widget->body   = array('content' => $this->load->view('systeminfo/widget', $data, true ), 'class'=>'fuelux');

		
		$this->addJSFile('/assets/js/plugin/bootstrap-progressbar/bootstrap-progressbar.min.js'); //datatable
		$this->addJsInLine($this->load->view('systeminfo/js', $data, true)); 
		$this->content = $widget->print_html(true);
		$this->view();
    }

}
 
?>
