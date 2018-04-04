<?php
/**
 * 
 * @author Krios Mane
 * @author Development Team
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<script type="text/javascript">
	var wizard; //wizard object
	var scanQualites = new Array();
	var probingQualities = new Array();
	<?php if(isset($scanQualities)): ?>
	<?php foreach($scanQualities as $quality):?>
	scanQualites.push(<?php echo json_encode($quality);?>);
	<?php endforeach;?>
	<?php endif; ?>
	<?php if(isset($probingQualities)): ?>
	<?php foreach($probingQualities as $quality):?>
	probingQualities.push(<?php echo json_encode($quality);?>);
	<?php endforeach;?>
	<?php endif; ?>
	var scanMode = 0;
	var scanModeInstructions = 0;
	var elapsedTime = 0;
	var objectMode = 'new';
	var isRunning = false;
	var isCompleting = false;
	var isCompleted = false;
	var isAborting = false;
	var isAborted = false;
	var objectID = 0;
	var fileID = 0;
	
	function checkConnection(obj){
		
		$("#connection_test_button").addClass("disabled");
		$("#connection_test_button").html( _("Checking connection...") );
		
		$('#btn-next').addClass('disabled');
		$("#connection-note").html("");
		
		var data = {ip: $("#pc-host-address").val(), port:$("#pc-host-port").val()};

		$.ajax({
			  type: "POST",
			  url: "<?php echo site_url('scan/checkConnection'); ?>",
			  data: data,
			  dataType: 'json',
		}).done(function( response ) {

			if(response.connection == 'failed'){
				
				$("#connection_test_button").removeClass("disabled btn-primary btn-success").addClass("btn-warning");
				$("#connection_test_button").html('<i class="fa fa-exclamation-triangle"></i> ' + _("No connection.") );
				$("#connection-note").html( _("Please check the desktop server or that your firewall is not blocking the port and try again.") );
				$('#btn-next').removeClass('disabled').addClass('disabled');
				
						
			}else{
				
				$("#connection_test_button").html('<i class="fa fa-check"></i> ' + _("Connection success!") );
				$("#connection_test_button").removeClass("disabled btn-primary btn-warning").addClass("btn-success");
				$("#connection-note").html("");
				$('#btn-next').removeClass('disabled');
				
			}
		   
		});	
	}

	/**
	* override default manage monitor for scan controller
	*/
	/*if(typeof manageMonitor != 'function'){*/
		window.manageMonitor = function(data){
			updateTaskProgress(data.task.percent);
			if(data.scan.hasOwnProperty('postprocessing_percent')){
				$(".postprocessing").show();
				updatePostprocessingProgressBar(data.scan.postprocessing_percent);
			}
			
			updateSlices(data.scan.scan_total, data.scan.scan_current);
			if(data.scan.hasOwnProperty('point_count'))
			{
				$(".pointcloudinfo").show();
				updateClouds(data.scan.point_count, data.scan.cloud_size);
			}
			
			if(data.scan.type != 'probe')
			{
				$(".imageinfo").show();
			}
			
			updateResolution(data.scan.width, data.scan.height);
			updateIso(data.scan.iso);
			handleTaskStatus(data.task.status);
			objectID = data.scan.object_id;
			fileID = data.scan.file_id;
			scanMode = data.scan.type;
		};
	/*}*/
	
	$("#restart").on('click', function(){
		location.reload();
	});
</script>  
