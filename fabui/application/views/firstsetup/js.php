<?php
/**
 * 
 * @author Daniel Kesler
 * @version 1.0
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
/* variable initialization */
$this->load->helper('std_helper');
if( !isset($steps) ) $steps = array();
$steps = initializeSteps($steps);

if( !isset($runningTask) ) $runningTask = 0;
if( !isset($file_id) ) $file_id = '';
?>

<script type="text/javascript">

	var idFile <?php echo $file_id != '' ? ' = '.$file_id : ''; ?>; //file to create
	var idTask <?php echo $runningTask ? ' = '.$runningTask['id'] : ''; ?>;
	
	$(document).ready(function() {
		$('[data-toggle="tooltip"]').tooltip();
		
		//$('.do-bed-calibration').on('click', doBedCalibration);
	});
	
	function handleStep()
	{
		var step = getWizardStep();
		console.log('handleStep', step);
		
		switch(step)
		{
			case <?php echo getStepNumber($steps, 'head')?>:
				//set_head();
				//return false;
				break;
				
			case <?php echo getStepNumber($steps, 'bed')?>:
				break;
		}
		
		return true;
	}
	
	function checkWizard()
	{
		console.log('check Wizard');
		var step = $('.wizard').wizard('selectedItem').step;
		console.log(step);
		switch(step){
			case <?php echo getStepNumber($steps, 'head')?>: // Select file
				disableButton('.button-prev');
				enableButton('.button-next');
				$('.button-next').find('span').html("<?php echo _("Next"); ?>");
				break;
                
			case <?php echo getStepNumber($steps, 'bed')?>: // Get Ready
				enableButton('.button-prev');
				enableButton('.button-next');
				$('.button-next').find('span').html("<?php echo _("Skip"); ?>");
				break;
			
			case <?php echo getStepNumber($steps, 'nozzle')?>:
				enableButton('.button-next');
				$('.button-next').find('span').html("<?php echo _("Next"); ?>");
				break;
			
			case <?php echo getStepNumber($steps, 'spool')?>:
				enableButton('.button-next');
				$('.button-next').find('span').html("<?php echo _("Skip"); ?>");
				break;
				
			case <?php echo getStepNumber($steps, 'feeder')?>:
				enableButton('.button-next');
				$('.button-next').find('span').html("<?php echo _("Skip"); ?>");
				break;
				
			/*case 3: // Execution
				<?php if($runningTask): ?>;
				// do nothing
				<?php else: ?>
					// send zero axis
					startTask();
				<?php endif; ?>
				return false;
				break;
			case 4:
				
				$('.button-next').find('span').html('');*/
		}
	}
	
	function startTask()
	{

	}
	
	function set_head(){
	 	if($("#heads").val() == 'head_shape'){
	 		alert('Please select a Head');
	 		return false;
	 	}
	 	openWait('<i class="fa fa-circle-o-notch fa-spin"></i> <?php echo _("Installing head"); ?>', '<?php echo _("Please wait"); ?>...');
	 	$.ajax({
			type: "POST",
			url: "<?php echo site_url("head/setHead") ?>/"+ $("#heads").val(),
			dataType: 'json'
		}).done(function( data ) {
			$(".alerts-container").find('div:first-child').remove();
			$(".alerts-container").append('<div class="alert alert-success animated  fadeIn" role="alert"><i class="fa fa-check"></i> Well done! Now your <strong>FABtotum Personal Fabricator</strong> is set for the <strong>'+ data.name +'</strong>.</div>');			
			setTimeout(function(){
					//document.location.href =  '<?php echo site_url('head'); ?>?head_installed';
					
					if( data.capabilities.indexOf("print") != -1 )
					{
						document.location.href =  '<?php echo site_url('firstsetup/new_index'); ?>/bed';
					}
					else if( data.capabilities.indexOf("mill") != -1 )
					{
						document.location.href =  '<?php echo site_url('firstsetup/new_index'); ?>/test';
					}
					else if( data.capabilities.indexOf("laser") != -1 )
					{
						document.location.href =  '<?php echo site_url('firstsetup/new_index'); ?>/test';
					}
					
				}, 2000);
		});
	}
	
	
	
    /*var num_probes = 1;
    var skip_homing = 0;*/
	
/*    function doBedCalibration()
    {
        openWait('<i class="fa fa-circle-o-notch fa-spin"></i> Calibration in process');
        var now = jQuery.now();
        
        $.ajax({
            type: "POST",
            url: "<?php echo site_url("bed/calibrate") ?>/"
                + now + "/" 
                + num_probes + "/"
                + skip_homing,
            dataType: "json"
        }).done(function( data ) {
            
            num_probes++;
            skip_homing = 1;
            closeWait();
            
            if($(".step-1").is(":visible") ){
                $(".step-1").slideUp('fast', function(){
                    $(".step-2").slideDown('fast');
                });
            }
            
            $(".result-response").html(data.html);
            
        });
    }*/
	
</script>
