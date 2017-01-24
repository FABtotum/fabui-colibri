<?php
/**
 * 
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */

/* variable initialization */

if( !isset($wizard_finish) ) $wizard_finish = end($steps)['number'];

?>
<script type="text/javascript">

	var wizard; //wizard object
	
	$(document).ready(function() {
		console.log('task_wizard_js: ready');
		initWizard();
	});
	
	//init wizard flow
	function initWizard()
	{
		wizard = $('.wizard').wizard();
		disableButton('.btn-prev');
		disableButton('.btn-next');
		
		$('.wizard').on('changed.fu.wizard', function (evt, data) {
			checkWizard();
		});
		$('.btn-prev').on('click', function() {
			console.log('prev');
			if(canWizardPrev()){
			}
		});
		$('.btn-next').on('click', function() {
			console.log('next');
			if(canWizardNext()){
			}
		});
		
		<?php if(isset($wizard_jump_to)): ?>
			$('.wizard').wizard('selectedItem', {
				step: <?php echo $wizard_jump_to?>
			});
			enableButton('.btn-prev');
		<?php endif; ?>
		
		checkWizard();
	}
	
	// check if i can move to previous step
	function canWizardPrev()
	{
		var step = $('.wizard').wizard('selectedItem').step;
		return false;
	}
	
	//check if i can move to next step
	function canWizardNext()
	{
		var step = $('.wizard').wizard('selectedItem').step;
		console.log('Can Wizard Next: ' + step);
		return false;
	}
	
	function gotoWizardStep(step)
	{
		$('.wizard').wizard('selectedItem', { step: step });
	}
	
	function gotoWizardFinish()
	{
		$('.wizard').wizard('selectedItem', { step: <?php echo $wizard_finish; ?> });
	}
	
</script>
