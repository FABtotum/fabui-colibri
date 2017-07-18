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
			return false;
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
				//~ disableButton('.button-next');
				enableButton('.button-next');
				$('.button-next').find('span').html(_("Print"));
				break;
			case 3: // Execution
				break;
			case 4:
				$('.button-next').find('span').html('');
		}
	}
	
	function startTask()
	{
		openWait('<i class="fa fa-spinner fa-spin "></i> ' + "<?php echo _('Preparing {0}');?>".format("<?php echo _(ucfirst($type)); ?>"), _("Checking safety measures...") );
		
		var calibration = $('input[name=calibration]:checked').val();
		var send_email = $("#email-switch").is(":checked");
		var auto_shutdown = $("#shutdown-switch").is(":checked");
		
		var data = {
			idFile:idFile,
			skipEngage:skipEngage,
			calibration:calibration,
			send_email:send_email,
			auto_shutdown:auto_shutdown,
			dropzone_file:fileFromDropzone
		};
			
		$.ajax({
			type: 'post',
			data: data,
			url: '<?php echo site_url($start_task_url); ?>',
			dataType: 'json'
		}).done(function(response) {
			
			if(response.start == false){
				gotoWizardStep(2);
				fabApp.showErrorAlert(response.message);
			}else{
				destroyDropZone();
				gotoWizardStep(3);
				idTask = response.id_task;
				updateFileInfo(response.file);
				fabApp.resetTemperaturesPlot(1);
				setTimeout(initGraph, 1000);
				updateZOverride(0);
				initRunningTaskPage();
				disableCompleteSteps();
				if (typeof ga !== 'undefined') {
					ga('send', 'event', 'print', 'start', 'print started');
				}
			}
			closeWait();
		})
	}
	
</script>
