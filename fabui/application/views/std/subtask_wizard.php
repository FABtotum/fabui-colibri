<?php
/**
 * 
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */

/* variable initialization */
$this->load->helper('std_helper');
if( !isset($steps) ) $steps = array();
$steps = initializeSteps($steps);

if( !isset($runningTask) ) $runningTask = 0;
if( !isset($warning) ) $warning = '';
//~ if( !isset($safety_check) ) $safety_check = array("all_is_ok" => true);
?>

