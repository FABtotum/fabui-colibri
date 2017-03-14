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
	$(document).ready(function() {
		initWizard();
		$(".mode-choise").on('click', setMode);
		$("#filament").on('change', setFilamentDescription);
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
			$('#myWizard').wizard('next');
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
				doSpoolAction();
				disableButton('.button-prev');
				disableButton('.button-next');
				break;
				
		}
	}
	/**
	*
	**/
	function setMode()
	{
		var action = $(this).attr('data-action');
		mode = action;
		switch(action)
		{
			case 'load':
				goToStep2();
				break;
			case 'unload':
				goToStep3();
				break;
		}
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
	function setFilamentDescription()
	{
		var filament = $(this).val();
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
            url: "<?php echo site_url("spool") ?>/" + mode,
            dataType: 'json'
      }).done(function( response ) {
    	  goToStep(4); 
      });
	}
</script>
