<?php
/**
 * 
 * @author Daniel Kesler
 * @version 1.0
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>

<script type="text/javascript">

	var idFile <?php echo $file_id != '' ? ' = '.$file_id : ''; ?>; //file to create
	var idTask <?php echo $runningTask ? ' = '.$runningTask['id'] : ''; ?>;
	
	
	$(document).ready(function() {
		$('[data-toggle="tooltip"]').tooltip();
	});
	
	function handleStep()
	{
		var step = $('.wizard').wizard('selectedItem').step;
		console.log('handleStep', step);
		
		if(step == 2)
		{
			<?php if($runningTask): ?>;
			// do nothing
			<?php else: ?>
				// send zero axis
				startTask();
				gotoWizardStep(3);
			<?php endif; ?>
		}
		
		return true;
	}
	
	function checkWizard()
	{
		console.log('check Wizard');
		var step = $('.wizard').wizard('selectedItem').step;
		console.log(step);
		switch(step){
			case 1: // Select file
				disableButton('.button-prev');
				if(idFile)
					enableButton('.button-next');
				else
					disableButton('.button-next');
				$('.button-next').find('span').html("<?php echo _("Next"); ?>");
				
				break;
			case 2: // Get Ready
				enableButton('.button-prev');
				disableButton('.button-next');
				$('.button-next').find('span').html("<?php echo _("Mill"); ?>");
				break;
				
			case 3: // Execution
				break;
			case 4:
				$('.button-next').find('span').html('');
		}
	}
	
	function jogSetAsZero()
	{
		console.log('set as zero');
		enableButton('.button-next');
		return false;
	}
	
	function startTask()
	{
		console.log('Starting task');
		openWait('<i class="fa fa-spinner fa-spin "></i>' + "<?php echo _('Preparing {0}');?>".format("<?php echo _(ucfirst($type)); ?>"), _("Checking safety measures...") );
		
		var data = {
			idFile:idFile
			};
			
		$.ajax({
			type: 'post',
			data: data,
			url: '<?php echo site_url($start_task_url); ?>',
			dataType: 'json'
		}).done(function(response) {
			if(response.start == false){
				$('.wizard').wizard('selectedItem', { step: 2 });
				fabApp.showErrorAlert(response.message);
			}else{
				
				idTask = response.id_task;
				
				initRunningTaskPage();
				updateZOverride(0);

				ga('send', 'event', 'mill', 'start', 'mill started');
			}
			closeWait();
		})
	}
	
</script>
