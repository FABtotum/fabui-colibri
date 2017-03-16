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
	var wizard;
	var mode;
	var filament;
	$(document).ready(function() {
		initWizard();
		setFilamentDescription('<?php echo isset($settings['filament']['type']) ? $settings['filament']['type'] : 'pla' ?>');
		$(".mode-choise").on('click', clickSetMode);
		$(".filament").on('click', filamentButtonClick);
		$("#restart-button").on('click', restartAction);
	});
	/**
	*
	**/
	function initWizard()
	{
		wizard = $('.wizard').wizard();

		disableButton('.button-prev');
		disableButton('.button-next');
		
		$('.wizard').on('changed.fu.wizard', function (evt, data) {
			handleStep();
		});
		
		$('#myWizard').on('clicked.fu.wizard', function (evt, data) {
		});
		
		$('.button-prev').on('click', function(e) {
			$('#myWizard').wizard('previous');
		});
		
		$('.button-next').on('click', function(e) {
			var step = $('.wizard').wizard('selectedItem').step;
			if(step == 3){
				doSpoolAction();
				return;
			}else{
				$('#myWizard').wizard('next');
			}
		});
	}
	/**
	*
	**/
	function handleStep()
	{
		var step = $('.wizard').wizard('selectedItem').step;
		console.log(step);
		switch(step)
		{
			case 1:
				disableButton('.button-prev');
				disableButton('.button-next');
				break;
			case 2:
				enableButton('.button-prev');
				enableButton('.button-next');
				break;
			case 3:
				enableButton('.button-prev');
				enableButton('.button-next');
				break;
			case 4:
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
		goToStep2();
		
	}
	/**
	*
	**/
	function goToStep(step)
	{
		$('.wizard').wizard('selectedItem', { step: step });
	}
	/**
	*
	**/
	function goToStep2()
	{
		console.log("gotostep2");
		goToStep(2);
		
	}
	/**
	*
	**/
	function goToStep3()
	{
		goToStep(3);
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
        	  goToStep(4);
          }else{
        	  showErrorAlert('<?php echo _("Error") ?>', response.message);
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
