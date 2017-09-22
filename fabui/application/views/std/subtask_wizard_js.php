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

if( !isset($wizard_finish) ) $wizard_finish = end($steps)['number'];

?>
<script type="text/javascript">

	var wizard; //wizard object
	
	$(document).ready(function() {
		initWizard();
	});

	function gotoSubWizardStep(name, step)
	{
		//$('.wizard').wizard('selectedItem', { step: step });
	}
	
	function gotoSubWizardFinish(name)
	{
		//$('.wizard').wizard('selectedItem', { step: <?php echo $wizard_finish; ?> });
	}
	
	function getSubWizardStep(name)
	{
		//return $('.wizard').wizard('selectedItem').step;
	}

</script>
