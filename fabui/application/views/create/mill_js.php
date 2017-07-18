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

	<?php if($runningTask): ?>
	var idFile = <?php echo $runningTask['id_file']; ?>;
	<?php else: ?>
	var idFile <?php echo $file_id != '' ? ' = '.$file_id : ''; ?>; //file to create
	<?php endif; ?>
	var idTask <?php echo $runningTask ? ' = '.$runningTask['id'] : ''; ?>;
	var fileFromDropzone = false;
	
	$(document).ready(function() {
		$('[data-toggle="tooltip"]').tooltip();
	});
	
	function handleStep()
	{
		var step = $('.wizard').wizard('selectedItem').step;
		
		if(step == 2)
		{
			<?php if($runningTask): ?>;
			// do nothing
			<?php else: ?>
				// send zero axis
				startTask();
				return false;
			<?php endif; ?>
		}
		return true;
	}
	
	function checkWizard()
	{
		var step = $('.wizard').wizard('selectedItem').step;
		switch(step){
			case 1: // Select file
				disableButton('.button-prev');
				if(idFile)
					enableButton('.button-next');
				else
					disableButton('.button-next');
				$('.button-next').find('span').html(_("Next"));
				
				break;
			case 2: // Get Ready
				enableButton('.button-prev');
				disableButton('.button-next');
				$('.button-next').find('span').html(_("Mill"));
				break;
				
			case 3: // Execution
				break;
			case 4:
				$('.button-next').find('span').html('');
		}
	}
	
	function jogSetAsZero()
	{
		enableButton('.button-next');
		return false;
	}
	
	function startTask()
	{
		openWait('<i class="fa fa-spinner fa-spin "></i> ' + "<?php echo _('Preparing {0}');?>".format("<?php echo _(ucfirst($type)); ?>"), _("Checking safety measures...") );	
		var data = {
			idFile:idFile,
			dropzone_file:fileFromDropzone
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
				gotoWizardStep(3);
				idTask = response.id_task;
				updateFileInfo(response.file);
				initRunningTaskPage();
				updateZOverride(0);
				disableCompleteSteps();
				if (typeof ga !== 'undefined') {
					ga('send', 'event', 'mill', 'start', 'mill started');
				}
			}
			closeWait();
		})
	}
	
</script>
