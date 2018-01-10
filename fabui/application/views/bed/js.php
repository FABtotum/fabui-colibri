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

	var numProbes = 1;
	var skipHoming = 0;
	
	$(document).ready(function() {
		init();
	});
	
	/**
	*
	**/
	function handleStep()
	{
		var step = $('#myWizard').wizard('selectedItem').step;
		switch(step){
			case 1:
			case 2:
				doBedCalibration();
				break;
		}
	}
	/**
	*
	**/
	function checkWizard()
	{	
	}
	/**
	*
	*/
	function init()
	{
		enableButton('.button-next');
	}
	/**
	*
	**/
	function doBedCalibration()
	{
		openWait('<i class="fa fa-cog fa-spin"></i> <?php echo _("Calibration in progress");?>');
		$.ajax({
            type: "POST",
            url: "<?php echo site_url("bed/calibrate") ?>/"
                + numProbes + "/"
                + skipHoming,
            dataType: "json"
        }).done(function( data ) {
			if(data.response == 'success')
			{
				processResponse(data);
			}else{
				fabApp.showErrorAlert(data.message);
				closeWait();
			}
        });
	}
	/**
	*
	**/
	function processResponse(data)
	{
		numProbes++;
		skipHoming = 1;
		$("#calbration-result").html(data.html);
		closeWait();
		gotoWizardStep(2);
		$(".button-next").html("<span><?php echo _("Calibrate again");?></span>");
	}
</script>
