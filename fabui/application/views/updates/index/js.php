<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<script type="text/javascript">

	var bandleStatus;
	var bundlesToUpdate = new Array();
	
	$(document).ready(function() {
		checkBundleStatus();
		$("#details-button").on('click', showHideDetails);
		$("#update-button").on('click', showHideDetails);
		$("#check-again").on('click', checkBundleStatus);
		$("#update").on('click', update);
	});	
	function checkBundleStatus()
	{
		$(".fabtotum-badge").removeClass('bg-color-green').removeClass('bg-color-orange').addClass('bg-color-blue');
		$("#badge-icon").html('<i class="fa  fa-spin fa-spinner txt-color-black"></i>');
		$("#status").html("Check for updates");
		$(".details").slideUp(function(){
			$("#details-button").html('show details <i class="fa fa-angle-double-down"></i>');
		});
		
		$.ajax({
			type: "POST",
			url: "<?php echo site_url('updates/bundleStatus') ?>",
			dataType: 'json'
		}).done(function(response) {
			bandleStatus = response;
			var needUpdate = response.update;
			var badgeBgColor = 'green';
			var icon = 'check';
			var message = 'Great! Your FABtotum Personal Fabricator is up to date';
			
			if(needUpdate){
				badgeBgColor = 'orange';
				icon = 'warning';
				message = 'New important software updates are now available';
			}

			
			$("#status").html("Check complete!");
			$(".fabtotum-badge").removeClass('bg-color-blue').addClass('bg-color-' + badgeBgColor);
			$("#badge-icon").html('<i class="fa  fa fa-'+ icon +' txt-color-black"></i>');

			$("#response").html(message);

			var html = '<table class="table table-striped table-forum">';
			$.each(response.bundles, function(i, item) {
				
				var sign = item.update == true ? 'fa-times' : 'fa-check';
				var text_color = item.update == true ? 'text-danger' : 'text-success';
				html += '<tr>';
				html += '<td width="20"><i class="fa '+ sign +' '+ text_color +'"></i></td>';
				html += '<td><h4><a href="javascript:void(0)">' + i + '</a>' ;
				if(item.update == true){
					html += ' <small>You have version <b>'+ item.local +'</b> installed. Update to <b>' + item.latest + '</b>. <a class="changelog" data-attribute="' + i + '" href="javascript:void(0)">View details</a> </small>';
				}
				html += ' </td>';
				
				html += '</tr>';
				
			    
			});
			html += '</table>';

			$(".details").html(html);
			$(".changelog").click(showChangeLog);


		});
	}
	/**
	*
	*/
	function showHideDetails()
	{
		var button = $(this);
		if($('.details').is(":visible")){
			$(".details").slideUp(function(){
				button.html('show details <i class="fa fa-angle-double-down"></i>');
			});
			
		}else{
			$(".details").slideDown(function(){
				button.html('hide details <i class="fa fa-angle-double-up"></i>');
			});
			
		}
	}
	/**
	*
	*/
	function showChangeLog()
	{
		var button = $(this);
		var bundle = button.attr('data-attribute');

		$("#changelog-modal-title").html(bundle + ' ' + bandleStatus['bundles'][bundle]['latest']);
		$('#changelog-modal-body').html(bandleStatus['bundles'][bundle]['changelog']);
		$('#changelog-modal').modal('show');
	}
	/**
	*
	*/
	function update()
	{
		$.each(bandleStatus.bundles, function(i, item) {
			if(item.update == true){
				bundlesToUpdate.push(i);
			}
		});
		$.ajax({
			type: "POST",
			data: {'bundles': bundlesToUpdate},
			url: "<?php echo site_url('updates/startUpdate') ?>",
			dataType: 'json'
		}).done(function(response) {
			if(response.start == false){
			}else{
				initTask();
				
			}
			
		});
	}
	/**
	*
	*/
	function initTask()
	{
		
		$("#pre-update-button-container").slideUp(function(){
			$("#update-button-container").slideDown(function() {


				var html = '<div class="row"><div class="col-sm-12 show-stats"><div class="row">';

				/*
				$.each(bundlesToUpdate, function(i, item) {
					
					html += '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12"><span class="text">' + item + '</span>';
					html += '<div class="progress"><div class="progress-bar" id="'+item+'-progress-bar"></div></div>'
					html += '</div>';

					
				});

				html += '</div></div></div>';
				
				$(".details").html(html);*/
				$("#response").html('Please don\'t turn off the printer until the operation is completed');
				fabApp.freezeMenu('updates');
				$(".fabtotum-badge").removeClass('bg-color-green').removeClass('bg-color-orange').addClass('bg-color-blue');
				$("#badge-icon").html('<i class="fa  fa-spin fa-spinner txt-color-black"></i>');
				$("#status").html("Updating");
			});
		});
		
	}
	/**
	*
	*/
	function completeTask()
	{	
		$(".fabtotum-badge").removeClass('bg-color-blue').addClass('bg-color-green');
		$("#badge-icon").html('<i class="fa  fa fa-check txt-color-black"></i>');
		$("#status").html("Update complete!");
		fabApp.unFreezeMenu();
		$("#response").html('Great! Your FABtotum Personal Fabricator is up to date');
		
	}
	/**
	*
	*/
	if(typeof manageMonitor != 'function'){
		window.manageMonitor = function(data){
			handleStatuses(data.task.status, data.update.current.status)
			handleUpdate(data.update)
		};
	}
	/**
	 *  monitor interval if websocket is not available
	 */
	function jsonMonitor()
	{
		if(!socket_connected) getTaskMonitor();
	}
	/**
	 * get task monitor json
	 */
	function getTaskMonitor()
	{
		$.get('/temp/task_monitor.json'+ '?' + jQuery.now(), function(data, status){
			manageMonitor(data);
		});
	}
	/**
	*
	*/
	function handleStatuses(task_status, update_status)
	{
		switch(task_status){
			case 'preparing':
				$("#status").html('Connecting to update server...');
				break;
			case 'running':
				var label = '';
				switch(update_status){
					case 'downloading':
						label = 'Downloading bundles..'
						break;
					case 'installing':
						label = 'Installing bundles..'
						break;	
				}
				$("#status").html(label);
				
				break;
			case 'completed':
				completeTask();
				break;
		}
	}
	/**
	*
	*/
	function handleUpdate(update)
	{
		var current_bundle = update.current.bundle;
		var current_file_type = update.current.file_type;


		var html = '<div class="well"><ul class="list-unstyled">';
		
		
		$.each(update.bundles, function(i, item) {

			var icon = current_bundle == i ? 'fa fa-cog fa-spin fa-fw' : 'fa fa-puzzle-piece fa-fw';

			html += '<li><p> <i class="' + icon +'"></i> <strong>' + i + '</strong> <span class="pull-right">';

			if(item.status == 'downloading'){
				html += ' downloading (' + parseInt(item.files.bundle.progress) + ' %) ';
			}
			if(item.status == 'downloaded'){
				html += ' downloaded ';
			}

			if(item.status == 'installing'){
				html += ' installing ';
			}
			
			html += '</span></p></li>';

			
			/*$("#" + i + "-progress-bar").attr("style", "width:" +item.files.bundle.progress +"%;");*/
		});

		html += '</ul></div>';
		$(".details").html(html);
	}
	
</script>
