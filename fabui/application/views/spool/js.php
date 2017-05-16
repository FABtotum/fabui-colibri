<?php
/**
 * 
 * @author Krios Mane
 * @author Fabtotum Development Team
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>
<script type="text/javascript">
	var mode;
	var filament;
	
	$(document).ready(function() {
		setFilamentDescription('<?php echo isset($settings['filament']['type']) ? $settings['filament']['type'] : 'pla' ?>');
		$(".mode-choise").on('click', clickSetMode);
		$(".filament").on('click', filamentButtonClick);
		$("#restart-button").on('click', restartAction);
	});
	
	/**
	*
	**/
	function handleStep()
	{
		var step = $('.wizard').wizard('selectedItem').step;
		console.log('handleStep', step);
		
		if(step == 3)
		{
			doSpoolAction();
			return false;
		}
		
		return true;
	}
	
	/**
	*
	**/
	function checkWizard()
	{
		var step = $('.wizard').wizard('selectedItem').step;
		console.log('Check', step);
		switch(step){
			case 1: // Choose mode
				disableButton('.button-prev');
				disableButton('.button-next');
				break;
			case 2: // Filament
				enableButton('.button-prev');
				enableButton('.button-next');
				break;
			case 3: // Get ready
				enableButton('.button-prev');
				enableButton('.button-next');
				break;
			case 4: // Finish
				disableButton('.button-prev');
				disableButton('.button-next');
				break;
		}
	}
	
	/**
	*
	**/
	function clickSetMode()
	{
		var action = $(this).attr('data-action');
		setMode(action);
	}
	/**
	*
	**/
	function setMode(action)
	{	
		mode = action;
		$(".get-ready-row").hide();
		$("#"+mode+"-row").show();
		
		switch(action)
		{
			case 'load':
				$("#filament-title").html('<?php echo _('Select filament to load')?>');
				break;
			case 'unload':
				$("#filament-title").html('<?php echo _('What filament are you going to unload?')?>');
				break;
		}
		gotoWizardStep(2);
	}
	/**
	*
	**/
	function  filamentButtonClick()
	{
		var type = $(this).attr("data-type");
		setFilamentDescription(type);
	}
	/**
	*
	**/
	function setFilamentDescription(type)
	{	
		filament = type;
		$(".filament").addClass('btn-default').removeClass('bg-color-blueLight txt-color-white').find('span').html('');
		$("." + filament).addClass('bg-color-blueLight txt-color-white').removeClass('btn-default').find('span').html('<i class="fa fa-check"></i>');
		var html = $("#"+ filament +"_description").html();
		$("#filament-description").html(html);
	}
	/**
	*
	**/
	function doSpoolAction()
	{
		openWait("<i class='fa fa-circle-o-notch fa-spin'></i> <?php echo _("Please wait");?>");
		$.ajax({
			type: "POST",
			url: "<?php echo site_url("spool") ?>/" + mode + '/' + filament,
			dataType: 'json'
		}).done(function( response ) {
		closeWait();
		if(response.response == 'success'){
			if(mode == 'unload'){
				$("#restart-button").removeClass('hidden');
			}
			gotoWizardFinish();
		}else{
			fabApp.showErrorAlert(response.message);
		}
	  });
	}
	/**
	*
	*/
	function restartAction()
	{
		setMode('load')
	}
</script>
