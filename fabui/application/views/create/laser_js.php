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
		$('#understandSafety').on('click', understandSafety);
		$('[data-toggle="tooltip"]').tooltip();
	});
	
	function checkWizard()
	{
		console.log('check Wizard');
		var step = $('.wizard').wizard('selectedItem').step;
		console.log(step);
		switch(step){
			case 1: // Select file
				disableButton('.btn-prev');
				if(idFile)
					enableButton('.btn-next');
				else
					disableButton('.btn-next');
				$('.btn-next').find('span').html('Next');
				
				cmd = 'M62';
				fabApp.jogMdi(cmd);
				
				break;
			case 2: // Safety
				enableButton('.btn-prev');
				disableButton('.btn-next');
				$('.btn-next').find('span').html('Next');
				
				cmd = 'M62';
				fabApp.jogMdi(cmd);
				
				break;
			case 3: // Calibration
				enableButton('.btn-prev');
				disableButton('.btn-next');
				$('.btn-next').find('span').html('Engrave');
				
				cmd = 'M60 S10\nM300\n';
				fabApp.jogMdi(cmd);
				
				break;
			case 4: // Execution
				<?php if($runningTask): ?>;
				// do nothing
				<?php else: ?>
					cmd = 'M62';
					fabApp.jogMdi(cmd);
					startTask();
				<?php endif; ?>
				return false;
				break;
			case 5:
				
				$('.btn-next').find('span').html('');
		}
	}
	
	function jogSetAsZero()
	{
		console.log('set as zero');
		enableButton('.btn-next');
		return false;
	}
	
	function understandSafety()
	{
		enableButton('.btn-next');
		return false;
	}
	
	function startTask()
	{
		console.log('Starting task');
		openWait('<i class="fa fa-spinner fa-spin "></i>' + "<?php echo _('Preparing {0}');?>".format("<?php echo _(ucfirst($type)); ?>"), "<?php echo _('Please wait');?>");
		
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

				ga('send', 'event', 'laser', 'start', 'laser started');
			}
			closeWait();
		})
	}
	
	function setLaserPWM(action, value)
	{
		console.log(action, value);
		message="Laser PWM set to: " + value;
		showActionAlert(message);
	}
	
</script>
