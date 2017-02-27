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
	$(document).ready(function() {
		<?php if($runningTask): ?>
		resumeTask();
		<?php else: ?>
		checkUpdateStatus();
		<?php endif; ?>
	});
	/**
	* ============================================================
	**/
	/**
	*
	**/
	function checkUpdateStatus()
	{
		$(".button-container").html('');
		$('.fabtotum-icon .badge').html('<i class="fa fa-refresh fa-spin"></i>');
		$(".status").html('<i class="fa fa-spinner fa-spin"></i> <?php echo _("Checking for updates") ?>');
		$.ajax({
			type: "POST",
			url: "<?php echo site_url('updates/updateStatus') ?>",
			dataType: 'json'
		}).done(function(response) {
			if(response.remote_connection == false){
				noInternetAvailable();
			}else{
				handleAvailableUpdates(response);
			}
			fabApp.getUpdates();
		});
	}
	/**
	* 
	**/
	function noInternetAvailable()
	{
		$(".status").html('<i class="fa fa-exclamation-circle"></i><br><?php echo _(" No internet connection found") ?><br><?php echo _("Check your connection or try again") ?>');
		$('.fabtotum-icon .badge').find('i').removeClass('fa-spin fa-refresh').addClass('fa-exclamation-circle');

		buttons = '<button class="btn btn-default  action-buttons" id="check-again"> <?php echo _("Check again") ?></button> ';
		$(".button-container").html(buttons);
		$("#check-again").on('click', checkAgain);
	}
	/**
	*
	**/
	function checkAgain()
	{
		checkUpdateStatus();
	}
	/**
	*
	**/
	function handleAvailableUpdates(object) {
		
		$('.fabtotum-icon').parent().addClass('tada animated');
		var buttons = '';
		
		if(object.update.available){
			$(".status").html('<i class="fa fa-exclamation-circle"></i> <?php echo _(" New important software updates are now available") ?>');
			$('.fabtotum-icon .badge').find('i').removeClass('fa-spin fa-refresh').addClass('fa-exclamation-circle');
		}else{
			$(".status").html('<?php echo _("Great! Your FABtotum Personal Fabricator is up to date") ?>');
			$('.fabtotum-icon .badge').find('i').removeClass('fa-spin fa-refresh').addClass('fa-check');
		}

		createBundlesTable(object);
		createFirmwareTable(object);
		createBootFilesTable(object);
		buttons += '<button class="btn btn-default  action-buttons" id="do-update"><i class="fa fa-refresh"></i> <?php echo _("Update") ?></button> ';
		buttons += '<button class="btn btn-default  action-buttons" id="bundle-details"><i class="fa fa-reorder"></i> <?php echo _("View details"); ?></button> ';
		$(".button-container").html(buttons);
		$("#bundle-details").on('click', showHideBundlesDetails);
		$("#do-update").on('click', doUpdate);
	}


	function createBundlesTable(data, show_check)
	{
		
		var html = '<table id="bundles-table" class="table  table-forum">' + 
		 				'<thead>' +
							'<tr>' +
								'<th colspan="2">Bundle</th>' +
								/*'<th class="text-center" style="width:150px">Installed version</th>' + */
								'<th class="text-center" style="width:150px"><?php echo _("Remote version") ?></th>' +
								'<th class="text-center" style="width:40px"><div class="checkbox" style="margin-top:0px; margin-bottom:0px"><label><input id="select-all-bundles" value="all_bundles" type="checkbox" class="checkbox"><span></span></label></div></th>' + 
							'</tr>' + 
						'</thead>' + 
						'<tbody>';

		console.log("BUNDLES", data.bundles);

		$.each(data.bundles, function(bundle_name, object) {
			if(object.need_update){
				var tr_class = 'warning';
				var icon = 'fa fa-exclamation-circle text-muted';
				var checked = 'checked="checked"';

				html += '<tr id="tr-' + bundle_name + '" class="' + tr_class + '">' +
		        	'<td  class="text-center" style="width:40px;"><i id="icon-'+ bundle_name +'" class="'+ icon + '"></i></td>' +
		        	'<td><h4><a href="javascript:void(0)">' + bundle_name.capitalize() + '</a> <small></small>' + 
		        	'<small id="small-'+ bundle_name +'"><?php echo _("Installed version") ?>: ' + object.local +' | <?php echo _("Build date") ?>: ' + object.info.build_date + '</small>' +
		        	'</h4></td>' + 
		        	/*'<td class="text-center">' + object.local + '</td>'+*/
		        	'<td class="text-center">' + object.latest + ' </td>' +
		        	'<td class="text-center" style="width:40px"><div class="checkbox" style="margin-top:0px;"><label><input value="'+bundle_name +'" type="checkbox" '+checked +' class="checkbox"><span></span></label></div></td>' + 
		        '</tr>';
			}
		});

		$.each(data.bundles, function(bundle_name, object) {
			if(!object.need_update){
				var tr_class = '';
				var icon = 'fa fa-check text-muted';
				var checked = '';

				html += '<tr id="tr-' + bundle_name + '" class="' + tr_class + '">' +
		        	'<td  class="text-center" style="width:40px;"><i id="icon-'+ bundle_name +'" class="'+ icon + '"></i></td>' +
		        	'<td><h4><a href="javascript:void(0)">' + bundle_name.capitalize() + '</a> <small></small>' + 
		        	'<small id="small-'+ bundle_name +'"><?php echo _("Installed version") ?>: ' + object.local +' | <?php echo _("Build date") ?>: ' + object.info.build_date + '</small>' +
		        	'</h4></td>' + 
		        	/*'<td class="text-center">' + object.local + '</td>'+*/
		        	'<td class="text-center">' + object.latest + ' </td>' +
		        	'<td class="text-center" style="width:40px"><div class="checkbox" style="margin-top:0px;"><label><input value="'+bundle_name +'" type="checkbox" '+checked +' class="checkbox"><span></span></label></div></td>' + 
		        '</tr>';
			}
		});
		
		html +=    		'<tbdoy>' + 
					'</table>';
		if(data.update.bundles > 0) {
			$("#bundles-badge").html(data.update.bundles).addClass('animated fadeIn');
		}
		$("#bundles_tab").html(html);
		$("#select-all-bundles").on('click', function(){
			var that = this;
			$(this).closest("table").find("tr > td input:checkbox").each(function() {
				this.checked = that.checked;
			});
		});
	}
	/**
	*
	**/
	function createFirmwareTable(object)
	{
		var icon = "fa fa-check text-muted";
		var tr_class = "";
		var checked = '';
		if(object.firmware.need_update){
			$("#firmware-badge").html('!');
			icon = "fa fa-exclamation-circle text-muted";
			tr_class = "warning";
			checked = 'checked="checked"';
		}
		var html = '<table id="firmware-table" class="table  table-forum">' +
					'<thead>'+
						'<tr>'+
							'<th colspan="2"></th>' +
							'<th class="text-center" style="width:150px"><?php echo _("Remote version") ?></th>' +
							'<th class="text-center" style="width:40px;"></th>' +
						'</tr>' +
					'</thead>';
		html += '<tbody>' +
					'<tr class="' + tr_class + '">' +
						'<td style="width:40px;"><i class="' + icon + '"></i></td>'+
						'<td>'+
							'<h4>'+
								'<a href="javascript:void(0);"> Fablin </a>' + 
								'<small><?php echo _("Installed version") ?>: ' + object.firmware.installed  + '</small>' +
							'</h4>'+
						'</td>'+
						'<td class="text-center">'+ object.firmware.remote.version  +'</td>'+
						'<td class="text-center" style="width:40px"><div class="checkbox" style="margin-top:0px;"><label><input value="firmware" type="checkbox" '+checked +' class="checkbox"><span></span></label></div></td>' + 
					'</tr>' + 
					'<tr><td></td><td></td><td></td><td></td></tr>' +
				'</tbody>';	
		html += '</table>';


		$("#firwmare_tab").html(html);
		
	}
	
	/**
	*
	**/
	function createBootFilesTable(object)
	{
		var icon = "fa fa-check text-muted";
		var tr_class = "";
		var checked = '';
		if(object.boot.need_update){
			$("#boot-badge").html('!');
			icon = "fa fa-exclamation-circle text-muted";
			tr_class = "warning";
			checked = 'checked="checked"';
		}
		
		var html = '<table id="boot-table" class="table  table-forum">' +
					'<thead>'+
						'<tr>'+
							'<th colspan="2"></th>' +
							'<th class="text-center" style="width:150px"><?php echo _("Remote version") ?></th>' +
							'<th class="text-center" style="width:40px;"></th>' +
						'</tr>' +
					'</thead>';
		html += '<tbody>' +
					'<tr class="' + tr_class + '">' +
						'<td style="width:40px;"><i class="' + icon + '"></i></td>'+
						'<td>'+
							'<h4>'+
								'<a href="javascript:void(0);"> Boot files </a>' + 
								'<small><?php echo _("Installed version") ?>: ' + object.boot.installed  + '</small>' +
							'</h4>'+
						'</td>'+
						'<td class="text-center">'+ object.boot.remote.version  +'</td>'+
						'<td class="text-center" style="width:40px"><div class="checkbox" style="margin-top:0px;"><label><input value="firmware" type="checkbox" '+checked +' class="checkbox"><span></span></label></div></td>' + 
					'</tr>' + 
					'<tr><td></td><td></td><td></td><td></td></tr>' +
				'</tbody>';	
		html += '</table>';
		$("#boot_tab").html(html);
	}
	
	/**
	*
	**/
	function showHideBundlesDetails()
	{
		var button = $(this);
		
		if($('.fabtotum-icon').is(":visible")){
			
			$(".fabtotum-icon").slideUp(function(){
				$(".tabs-container").slideDown(function(){
					button.html("<i class='fa fa-reorder'></i> <?php echo _("Hide details") ?>");
				});
			});
		}else{
			$(".tabs-container").slideUp(function(){
				$(".fabtotum-icon").slideDown(function(){
					$(".fabtotum-icon").css( "display", "inline" );
					button.html("<i class='fa fa-reorder'></i> <?php echo _("View details") ?>");
				});
				
			});
		}
	}
	/**
	*
	**/
	function doUpdate()
	{
		disableButton('.action-buttons');
		var bundles_to_update = [];
		var firmware = false;
		var boot = false;
		
		$("#bundles-table").find("tr > td input:checkbox").each(function () {
			if($(this).is(':checked')){
				bundles_to_update.push($(this).val());
			}
		});
		$("#firmware-table").find("tr > td input:checkbox").each(function () {
			if($(this).is(':checked')){
				firmware = true;
			}
		});
		$("#boot-table").find("tr > td input:checkbox").each(function () {
			if($(this).is(':checked')){
				boot = true;
			}
		});

		if(bundles_to_update.length > 0 || firmware || boot){
			startUpdate(bundles_to_update, firmware, boot);
		}else{
			enableButton('.action-buttons');
			$.smallBox({
				title : "<?php echo _('Warning')?>",
				content : '<?php echo _('Please select at least 1 bundle firmware or boot update')?>',
				color : "#5384AF",
				timeout: 3000,
				icon : "fa fa-warning"
			});
		}
	
		//startUpdate(bundles_to_update, firmware, boot);
	}
	/**
	*
	**/
	function startUpdate(bundles, firmware, boot)
	{
		$(".status").html('<?php echo _("Connecting to update server") ?>...');
		$.ajax({
			type: "POST",
			data: {'bundles': bundles, 'firmware' : firmware, 'boot' : boot},
			url: "<?php echo site_url('updates/startUpdate') ?>",
			dataType: 'json'
		}).done(function(response) {
			initTask();
		});
	}
	/*
	*
	**/
	function showHideUpdateDetails()
	{
		var button = $(this);

		if($(".update-details").is(":visible")){
			$(".update-details").slideUp(function(){
				button.html("<i class='fa fa-reorder'></i> <?php echo _("View details") ?>");
			});
		}else{
			$(".update-details").slideDown(function(){
				button.html("<i class='fa fa-reorder'></i> <?php echo _("Hide details") ?>");
			});
		}
	}
	/**
	*
	**/
	if(typeof manageMonitor != 'function'){
		window.manageMonitor = function(data){
			console.log("=======================");
			console.log("UPDATE - MANAGEMONITOR");
			handleTask(data);
			
		}
	}
	/**
	*
	**/
	function handleTask(data)
	{
		handleUpdate(data.update);
		handleCurrent(data.update.current);
		handleTaskStatus(data.task.status);
	}
	/**
	*
	**/
	function handleTaskStatus(status)
	{
		
		switch(status){ 
			case 'preparing':
				$(".status").html('<?php echo _("Connecting to update server") ?>...');
				break;
			case 'runnning':
				break;
			case 'completed':
				$(".status").html('<i class="fa fa-check"></i> Update completed');
				$('.fabtotum-icon .badge').addClass('check').find('i').removeClass('fa-spin fa-refresh').addClass('fa-check');
				$("#do-abort").remove();
				fabApp.unFreezeMenu();
				$(".small").html('<?php echo _("A reboot is needed to apply new features") ?>');
				if($("#do-reboot").length == 0) $(".button-container").append('<button class="btn btn-default  action-buttons" id="do-reboot"> <?php echo _("Reboot now") ?></button>')
				$('.fabtotum-icon').parent().removeClass().addClass('tada animated');
				$("#do-reboot").on('click', fabApp.reboot);
				break;
		}

	}
	/**
	*
	**/
	function handleCurrent(current)
	{
		if(current.status != ''){
			switch(current.status){
				case 'downloading' :
					$(".status").html('<i class="fa fa-download"></i> <?php echo _("Downloading") ?> ' + current.type + ' (' + current.name.capitalize() +')');
					break;
				case 'installing' :
					$(".status").html('<i class="fa fa-gear fa-spin"></i> <?php echo _("Installing") ?> ' + current.type + '  (' + current.name.capitalize() +')');
					break;
			}
		}
	}
	/**
	*
	**/
	function handleUpdate(object)
	{
		
		
		var table = '<table class="table  table-forum"><thead><tr></tr></thead><tbody>';		
		$.each(object.tasks, function(i, task) {
			
			var tr_class = task.status == 'error' ? 'warning' : '';
			
			table += '<tr class="'+ tr_class +'">';
			table += '<td width="20" class="text-center"></td>';
			table += '<td><h4><a href="javascript:void(0);">' + task.name.capitalize() + '</a>';
			
			switch(task.status){
				case 'downloading':
					label = '<p><i class="fa fa-download"></i> <?php echo _("Downloading") ?> (' + humanFileSize(task.files.main_file.size)  + ') <span class="pull-right">'+ parseInt(task.files.main_file.progress)  +'%</span></p>'+
						'<div class="progress progress-xs"> '+
							'<div class="progress-bar bg-color-blue" style="width: '+ parseInt(task.files.main_file.progress) +'%;"></div> '+
						'</div>';
					break;
				case 'downloaded':
					label = '<i class="fa fa-check"></i> <?php echo _("Downloaded") ?>';
					break;
				case 'installing':
					label = '<i class="fa fa-gear fa-spin"></i> <?php echo _("Installing") ?> ';
					break;
				case 'installed':
					label = '<i class="fa fa-check"></i> <?php echo _("Installed") ?>';
					break;
				case 'error':
					label = '<p><i class="fa fa-times"></i> <?php echo _("Error") ?></p>' +
							'<p>' + task.message.replaceAll('\n', '<br>') + '</p>';
					break;
				default:
					label = task.status;
					break;
			}
			table += '<small>' + label + '</small>';
			table += '</h4></td>';
			table += '</tr>';
		});
		
		table += '</tbody></table>';
		
		$(".update-details").html(table);
	}
	/**
	*
	**/
	function resumeTask()
	{
		initTask();
	}
	/**
	*
	**/
	function initTask()
	{
		fabApp.freezeMenu('updates');
		$(".small").html("<?php echo _("Please don't turn off the printer until the operation is completed") ?>");
		
		$(".tabs-container").slideUp(function() {
			$(".fabtotum-icon").slideDown(function(){
				$(".fabtotum-icon").css( "display", "inline" );
				$('.fabtotum-icon .badge').find('i').removeClass('fa-exclamation-circle').addClass('fa-spin fa-refresh');

				var buttons = '';
				buttons += '<button class="btn btn-default  action-buttons" id="update-details"><i class="fa fa-reorder"></i> <?php echo _("View details") ?></button> ';

				$(".button-container").html(buttons);
				$("#update-details").on('click', showHideUpdateDetails);
				
			});
		});
	}
</script>
