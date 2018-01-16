<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 * 
 */
?>
<script type="text/javascript">
	var mode;
	var interval;
	var z_max_offset;
	var z_probe_offset;
	/**
	*
	**/
	$(document).ready(function() {
		fabApp.checkSafety('print', 'yes', '#main-widget-nozzl-height-calibration');
		init();
	});
	/**
	*
	**/
	function init()
	{
		$(".mode-choise").on('click', clickSetMode);
		$(".z-action").on('click', moveZAxis);
		$('.change-over').on('mousedown', mouseDown);
		$('.change-over').on('mouseup', mouseUp);
		$("#save-override").on('click', saveOverride);
		$("#calibrate-again").on('click', calibrateAgain);
		$("#calibrate-height").on('click', doHeightCalibration);
	}
	/**
	*
	**/
	function handleStep()
	{
		
	}
	/**
	*
	**/
	function checkWizard()
	{
		var step = $('.wizard').wizard('selectedItem').step;
		switch(step)
		{
			case 1:
				disableButton('.button-prev');
				disableButton('.button-next');
				break;
			case 2:
				enableButton('.button-prev');
				disableButton('.button-next');
				break;
			case 3:
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
		$(".calibration-row").addClass("hidden");
		switch(mode)
		{
			case 'assisted':
				doAssistedCalibration();
				break;
			case 'fine':
				doFineCalibration();
				break;
		}
	}
	/**
	*
	**/
	function doAssistedCalibration()
	{
		openWait("<i class='fa fa-circle-o-notch fa-spin'></i> <?php echo _("Please wait");?>");
		$.ajax({
			type: "POST",
			url: "<?php echo site_url("nozzle") ?>/prepare",
			dataType: 'json'
		}).done(function( response ) {
			closeWait();
			if(response.response == 'success'){
				z_max_offset = response.reply.z_max_offset;
				z_probe_offset = response.reply.z_probe_offset;
				$("#assisted-row").removeClass("hidden");
				gotoWizardStep(2);
			}else{
				fabApp.showErrorAlert(response.message);
			}
		});
	}
	/**
	*
	**/
	function doFineCalibration()
	{
		openWait("<i class='fa fa-circle-o-notch fa-spin'></i> <?php echo _("Please wait");?>");
		$.ajax({
			type: "POST",
			url: "<?php echo site_url("nozzle") ?>/getOffset",
			dataType: 'json'
		}).done(function( response ) {
			$("#fine-row").removeClass("hidden");
			$("#nozzle-offset").html(response.nozzle_offset);
			$("#over").val(0);
			closeWait();
			gotoWizardStep(2);
		});
	}
	/**
	*
	**/
	function moveZAxis()
	{
		var sign  = $(this).attr('data-action');
		var step  = parseFloat($("#step").val());
		var gcode = 'G91\nG0 Z' + sign + step + ' F1000';
		fabApp.jogMdi(gcode);
	}
	/**
	*
	**/
	function mouseDown()
	{
		var over = parseFloat($("#over").val());
		if(over >= -2 && over <=  2){
			var action = $(this).attr("data-action");
			over = eval(parseFloat(over) + action + '0.01');
			$("#over").val(over.toFixed(2));

			interval = window.setInterval(function(){
            	if(over >= -2 && over <=  2){
            		over = eval(parseFloat(over) + action + '0.01');
                   	$("#over").val(over.toFixed(2));
               	}
           }, 100);
		}
	}
	/**
	*
	**/
	function mouseUp()
	{
		window.clearInterval(interval);
	}
	/**
	*
	**/
	function doHeightCalibration()
	{
		openWait("<i class='fa fa-circle-o-notch fa-spin'></i> <?php echo _("Please wait");?>");
		var over = parseFloat($("#over").val());
		$.ajax({
			type: "POST",
			url: "<?php echo site_url("nozzle") ?>/calibrateHeight/",
			dataType: 'json'
		}).done(function( response ) {
			closeWait();
			gotoWizardStep(3);
			processCalibrationResponse(response);
		});	
	}
	function processCalibrationResponse(data)
	{
		var html = '';

		html += '<table class="table">' +
					'<tbody>' +
						'<tr>' + 
							'<td><?php echo _('New probe length'); ?></td>' +
							'<td> <strong>' + z_probe_offset  + ' (mm)</strong></td>' +
						'</tr>'+
						'<tr>'+
							'<td><?php echo _('New z max offset'); ?></td>'+
							'<td><strong>' + z_max_offset  + ' (mm)</strong></td>'+
						'</tr>'+
						'<tr>'+
							'<td><?php echo _('New nozzle offset'); ?></td>'+
							'<td><strong>' + data.nozzle_z_offset  + ' (mm)</strong></td>'+
						'</tr>'+
					'</tbody>' + 
				'</table>';
		$(".calibration-result").html(html);
		
	}
	/**
	*
	**/
	function saveOverride()
	{
		openWait("<i class='fa fa-circle-o-notch fa-spin'></i> <?php echo _("Please wait");?>");
		var over = parseFloat($("#over").val());
		$.ajax({
			type: "POST",
			url: "<?php echo site_url("nozzle") ?>/overrideOffset/" + over,
			dataType: 'json'
		}).done(function( response ) {
			closeWait();
			gotoWizardStep(3);
			processOverrideResponse(response);
		});	
	}
	/**
	*
	**/
	function processOverrideResponse(data)
	{
		var html = '';

		html += '<table class="table">' +
					'<tbody>' +
						'<tr>' + 
							'<td><?php echo _('New nozzle offset'); ?></td>' +
							'<td> <strong>' + data.nozzle_offset  + ' (mm)</strong></td>' +
						'</tr>'+
						'<tr>'+
							'<td><?php echo _('Old nozzle offset'); ?></td>'+
							'<td><strong>' + data.old_nozzle_offset  + ' (mm)</strong></td>'+
						'</tr>'+
						'<tr>'+
							'<td><?php echo _('Override'); ?></td>'+
							'<td><strong>' + data.over  + ' (mm)</strong></td>'+
						'</tr>'+
					'</tbody>' + 
				'</table>';
		$(".calibration-result").html(html);
	}
	/**
	*
	**/
	function calibrateAgain()
	{
		setMode(mode);
	}
</script>
