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
	var local_data_copy = {};
	var checkInterval;
	
	var selected_by = {};
	var requires = {};
	
	$(document).ready(function() {
		<?php if($runningTask): ?>
		resumeTask();
		<?php elseif($internet): ?>
		checkUpdateStatus();
		<?php else: ?>
		noInternetAvailable();
		<?php endif; ?>
	});
	
	/**
	* ============================================================
	**/
	/**
	*
	**/
	function checkUpdateStatus(bool)
	{
		var task_completed = bool || false;
		$(".button-container").html('');
		/*$('.fabtotum-icon .badge').html('<i class="fa fa-refresh fa-spin"></i>');*/
		if(!task_completed) $(".status").html('<i class="fa fa-spinner fa-spin"></i> <?php echo _("Checking for updates") ?>');
		$.ajax({
			type: "POST",
			url: "<?php echo site_url('updates/updateStatus') ?>",
			dataType: 'json'
		}).done(function(response) {
			if(response.remote_connection == false){
				if(!task_completed) noInternetAvailable();
			}else{
				if(!task_completed) handleAvailableUpdates(response);
				fabApp.handleUpdatesData(response);
			}
			//fabApp.doFunctionOverWS('getUpdates');
		});
	}
	/**
	* 
	**/
	function noInternetAvailable()
	{
		$(".status").html('<i class="fa fa-exclamation-circle"></i><br><?php echo _("No internet connection found") ?><br><?php echo _("Check network settings and try again") ?>');
		/*$('.fabtotum-icon .badge').find('i').removeClass('fa-spin fa-refresh').addClass('fa-exclamation-circle');*/

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
			$(".status").html('<i class="fa fa-exclamation-circle"></i> <?php echo _("New important software updates are now available") ?>');
			/*$('.fabtotum-icon .badge').find('i').removeClass('fa-spin fa-refresh').addClass('fa-exclamation-circle');*/
		}else{
			$(".status").html('<?php echo _("Great! Your FABtotum Personal Fabricator is up to date") ?>');
			/*$('.fabtotum-icon .badge').find('i').removeClass('fa-spin fa-refresh').addClass('fa-check');*/

		}

		local_data_copy = object;
		
		createBundlesTable(object);
		createFirmwareTable(object);
		createBootFilesTable(object);
		createPluginsTable(object);
		
		$(".show-changelog").on('click', findChangelog);
		
		buttons += '<button class="btn btn-default  action-buttons" id="do-update"><i class="fa fa-refresh"></i> <?php echo _("Update") ?></button> ';
		buttons += '<button class="btn btn-default  action-buttons" id="bundle-details"><i class="fa fa-reorder"></i> <?php echo _("View details"); ?></button> ';
		$(".button-container").html(buttons);
		$("#bundle-details").on('click', showHideBundlesDetails);
		$("#do-update").on('click', doUpdate);
	}
	
	function findChangelog()
	{
		var subtype = $(this).attr('data-type');
		var id = $(this).attr('data-attr');
		var latest = $(this).attr('data-latest');
		
		var changelog = "";
		var title = "Changelog";
		
		if(local_data_copy)
		{
			switch(subtype)
			{
				case "bundle":
					changelog = "";
					if(latest)
						showChangelog(title, changelog, "<?php echo site_url('updates/getChangelog')?>/" + subtype + "/" + id + "/v" + latest);
					break;
				case "boot":
					changelog = "";
					if(latest)
						showChangelog(title, changelog, "<?php echo site_url('updates/getChangelog')?>/" + subtype + "/bootfiles/" + latest);
					break;
				case "firmware":
					changelog = local_data_copy.firmware.remote.changelog;
					showChangelog(title, changelog);
					break;
			}
		}
		
	}
	
	function showChangelog(title, changelog, url)
	{
		$("#changelog-title").html(title);
		$("#changelog-content").html('Loading...');
		
		if(url)
		{
			
			$("#changelogModal").modal('show');
			
			$.get(url, function(data){
				var converter = new showdown.Converter(),
				html = converter.makeHtml(data);
				$("#changelog-content").html(html);
				
			});
		}
		else
		{
			var converter = new showdown.Converter(),
			html = converter.makeHtml(changelog);
			$("#changelog-content").html(html);
			$("#changelogModal").modal('show');
		}
	}

	function createBundlesTable(data, show_check)
	{
		
		var have_priority_updates = data.update.priority.length > 0;
		
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

		// separated into two section so the updates go on the top
		$.each(data.bundles, function(bundle_name, object) {
			if( ((!have_priority_updates && object.need_update) || (have_priority_updates && object.is_priority)) && object.online == true ){
				var tr_class = 'warning';
				var icon = object.is_priority?'fa fa-exclamation-circle text-danger fa-2x':'fa fa-exclamation-circle';
				var checked = 'checked="checked"';
				var tooltip = object.is_priority ? 'Critical Update' : '';

				html += '<tr id="tr-' + bundle_name + '" class="' + tr_class + '">' +
		        	'<td  class="text-center" style="width:40px;"><i id="icon-'+ bundle_name +'" class="'+ icon + '" title="' + tooltip + '"></i></td>' +
		        	'<td><h4><a href="javascript:void(0)" class="show-changelog" data-type="bundle" data-attr="'+bundle_name+'" data-latest="'+object.latest+'">' + bundle_name.capitalize() + '</a> <small></small>' + 
		        	'<small id="small-'+ bundle_name +'"><?php echo _("Installed version") ?>: ' + object.local +' | <?php echo _("Build date") ?>: ' + object.info.build_date + '</small>' +
		        	'</h4></td>' + 
		        	/*'<td class="text-center">' + object.local + '</td>'+*/
		        	'<td class="text-center">' + object.latest + ' </td>' +
		        	'<td class="text-center" style="width:40px"><div class="checkbox" style="margin-top:0px;"><label><input id="checkbox-bundle-'+bundle_name+'" value="'+bundle_name +'" type="checkbox" class="checkbox checkbox-action"><span></span></label></div></td>' + 
		        '</tr>';
			}
		});

		$.each(data.bundles, function(bundle_name, object) {
			if( ((!have_priority_updates && !object.need_update) || (have_priority_updates && !object.is_priority)) && object.online == true  ){
				var tr_class = '';
				var icon = 'fa fa-check text-muted';
				var checked = '';

				html += '<tr id="tr-' + bundle_name + '" class="' + tr_class + '">' +
		        	'<td  class="text-center" style="width:40px;"><i id="icon-'+ bundle_name +'" class="'+ icon + '"></i></td>' +
		        	'<td><h4><a href="javascript:void(0)" class="show-changelog" data-type="bundle" data-attr="'+bundle_name+'" data-latest="'+object.latest+'">' + bundle_name.capitalize() + '</a> <small></small>' + 
		        	'<small id="small-'+ bundle_name +'"><?php echo _("Installed version") ?>: ' + object.local +' | <?php echo _("Build date") ?>: ' + object.info.build_date + '</small>' +
		        	'</h4></td>' + 
		        	/*'<td class="text-center">' + object.local + '</td>'+*/
		        	'<td class="text-center">' + object.latest + ' </td>' +
		        	'<td class="text-center" style="width:40px"><div class="checkbox" style="margin-top:0px;"><label><input id="checkbox-bundle-'+bundle_name+'" value="'+bundle_name +'" type="checkbox" '+checked +' class="checkbox checkbox-action"><span></span></label></div></td>' + 
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
		
		$(".checkbox-action").off('change');
		$(".checkbox-action").on('change', checkboxChanged);
		
		$.each(data.bundles, function(bundle_name, object) {
			if(object.need_update){
				// select the updates
				var cb_id = "checkbox-bundle-" + bundle_name;
				$("#" + cb_id).prop("checked", true );
				$("#" + cb_id).trigger('change');
			}
		});
		
		prioritySelect(data.update.priority);
	}
	
	function prioritySelect(priority_bundles)
	{
		if(priority_bundles.length == 0)
			return
			
		$(".checkbox-action").attr("disabled", true);
		$(".checkbox-action").attr("checked", false);
			
		$.each(priority_bundles, function(index, bundle_name) {
			var cb_id = "checkbox-bundle-" + bundle_name;
			$("#" + cb_id).prop("checked", true );
		});
		
		//~ $("#" + cb_id).attr("disabled", true);
	}
	
	function checkboxChanged()
	{
		var bundle_name = $(this).val();
		var is_selected = $(this).is(":checked");
		var bundle_info = local_data_copy.bundles[bundle_name];

		
		
		if(bundle_info.requires.hasOwnProperty("bundle"))
		{
			$.each(bundle_info.requires.bundle, function(index, object) {

				var localBundle = local_data_copy.bundles[object.name];

				if(typeof localBundle == 'undefined'){
				    
				}else
				if( versionCompare(localBundle.local, object.min_version ) == -1 )
				{
					var cb_id = "checkbox-bundle-" + object.name;
					$("#" + cb_id).prop("checked", is_selected );
					$("#" + cb_id).attr("disabled", !is_selected);
					//$("#" + cb_id).attr("title", "Requires by " + bundle_name);
					$("#" + cb_id).trigger('change');
				}
			});
		}
		

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
								'<a href="javascript:void(0);" class="show-changelog" data-type="firmware" data-attr="fablin"> Fablin </a>' + 
								'<small><?php echo _("Installed version") ?>: ' + object.firmware.installed  + '</small>' +
							'</h4>'+
						'</td>'+
						'<td class="text-center">'+ object.firmware.remote.version  +'</td>'+
						'<td class="text-center" style="width:40px"><div class="checkbox" style="margin-top:0px;"><label><input  id="checkbox-firmware-fablin" value="fablin" type="checkbox" '+checked +' class="checkbox"><span></span></label></div></td>' + 
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
								'<a href="javascript:void(0);" class="show-changelog" data-type="boot" data-attr="bootfiles" data-latest="'+object.boot.remote.version+'"> Boot files </a>' + 
								'<small><?php echo _("Installed version") ?>: ' + object.boot.installed  + '</small>' +
							'</h4>'+
						'</td>'+
						'<td class="text-center">'+ object.boot.remote.version  +'</td>'+
						'<td class="text-center" style="width:40px"><div class="checkbox" style="margin-top:0px;"><label><input  id="checkbox-bool" value="boot" type="checkbox" '+checked +' class="checkbox"><span></span></label></div></td>' + 
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
		var plugins_to_update = [];
		
		$("#bundles-table").find("tr > td input:checkbox").each(function () {
			if($(this).is(':checked')){
				bundles_to_update.push($(this).val());
			}
		});

		$("#plugins-table").find("tr > td input:checkbox").each(function () {
			if($(this).is(':checked')){
				plugins_to_update.push($(this).val());
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

		if(bundles_to_update.length > 0 || firmware || boot || plugins_to_update.length>0){
			startUpdate(bundles_to_update, firmware, boot, plugins_to_update);
		}else{
			enableButton('.action-buttons');
			$.smallBox({
				title : "<?php echo _('Warning')?>",
				content : '<?php echo _('Please select at least 1 bundle, firmware, boot or plugin update')?>',
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
	function startUpdate(bundles, firmware, boot, plugins)
	{	
		$(".status").html('<?php echo _("Connecting to update server") ?>...');
		$.ajax({
			type: "POST",
			data: {'bundles': bundles, 'firmware' : firmware, 'boot' : boot, 'plugins': plugins},
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
			handleTask(data);
		}
	}
	/**
	*
	**/
	function handleTask(data)
	{
		handleUpdate(data);
		handleCurrent(data.update.current);
		handleTaskStatus(data.task.status, data);
	}
	/**
	*
	**/
	function handleTaskStatus(status, data)
	{
		
		switch(status){ 
			case 'preparing':
				$(".status").html('<?php echo _("Connecting to update server") ?>...');
				break;
			case 'runnning':
				break;
			case 'completed':


				if(data.update.current.status == "error"){
					$(".status").html('<i class="fa fa-warning"></i> ' + data.update.current.message).addClass('margin-bottom-20');
					$(".lead").hide();
					$(".update-details").hide();
				}else{
					$(".status").html('<i class="fa fa-check"></i> Update completed');
					$("#do-abort").remove();
					$(".small").html('<?php echo _("A reboot is needed to apply new features") ?>');
					if($("#do-reboot").length == 0) $(".button-container").append('<button class="btn btn-default  action-buttons" id="do-reboot"> <?php echo _("Reboot now") ?></button>')
					$('.fabtotum-icon').parent().removeClass().addClass('tada animated');
					$("#do-reboot").on('click', fabApp.reboot);
				}
				/*$('.fabtotum-icon .badge').addClass('check').find('i').removeClass('fa-spin fa-refresh').addClass('fa-check');*/
				fabApp.unFreezeMenu();
				checkUpdateStatus(true);
				clearInterval(checkInterval);
				number_tasks -= 1;
				fabApp.updateNotificationBadge();
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
	
		$.each(object.update.tasks, function(i, task) {

			var tr_class = '';
			if(object.update.current.task == task.name) tr_class = 'warning';
			if(object.task.status == 'completed') tr_class = '';
			if(task.status == 'error') tr_class = 'error';
			var icon = '';
			switch(task.type){
				case 'plugin':
					icon = 'fa-plug';
					break;
				case 'bundle':
					icon = 'fa-puzzle-piece';
					break;
				case 'firmware':
					icon = 'fa-microchip';
					break;
				case 'boot':
					icon = 'fa-rocket';
					break;
			}
			
			table += '<tr class="'+ tr_class +'">';
			table += '<td width="20" class="text-center"></td>';
			table += '<td><h4><a href="javascript:void(0);"><i class="fa '+icon+' "></i> ' + task.name.capitalize() + '</a>';
			
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

		checkInterval = setInterval(jsonMonitor, 1000);
		
		fabApp.freezeMenu('updates');
		$(".small").html("<?php echo _("Please don't turn off the printer until the operation is completed") ?>");
		
		$(".tabs-container").slideUp(function() {
			$(".fabtotum-icon").slideDown(function(){
				$(".fabtotum-icon").css( "display", "inline" );
				/*$('.fabtotum-icon .badge').find('i').removeClass('fa-exclamation-circle').addClass('fa-spin fa-refresh');*/

				var buttons = '';
				buttons += '<button class="btn btn-default  action-buttons" id="update-details"><i class="fa fa-reorder"></i> <?php echo _("View details") ?></button> ';

				$(".button-container").html(buttons);
				$("#update-details").on('click', showHideUpdateDetails);
				
			});
		});
	}
	/**
	*
	**/
	function jsonMonitor()
	{
		if(!socket_connected || socket.fallback) getTaskMonitor();
	}
	/**
	*
	**/
	function getTaskMonitor()
	{
		$.get('/temp/task_monitor.json'+ '?' + jQuery.now(), function(data, status){
			handleTask(data);
		});
	}
	
	/**
	 * Simply compares two string version values.
	 * 
	 * Example:
	 * versionCompare('1.1', '1.2') => -1
	 * versionCompare('1.1', '1.1') =>  0
	 * versionCompare('1.2', '1.1') =>  1
	 * versionCompare('2.23.3', '2.22.3') => 1
	 * 
	 * Returns:
	 * -1 = left is LOWER than right
	 *  0 = they are equal
	 *  1 = left is GREATER = right is LOWER
	 *  And FALSE if one of input versions are not valid
	 *
	 * @function
	 * @param {String} left  Version #1
	 * @param {String} right Version #2
	 * @return {Integer|Boolean}
	 * @author Alexey Bass (albass)
	 * @since 2011-07-14
	 */
	function versionCompare(left, right) {
		if (typeof left + typeof right != 'stringstring')
			return false;
		
		var a = left.split('.')
		,   b = right.split('.')
		,   i = 0, len = Math.max(a.length, b.length);
			
		for (; i < len; i++) {
			if ((a[i] && !b[i] && parseInt(a[i]) > 0) || (parseInt(a[i]) > parseInt(b[i]))) {
				return 1;
			} else if ((b[i] && !a[i] && parseInt(b[i]) > 0) || (parseInt(a[i]) < parseInt(b[i]))) {
				return -1;
			}
		}
		
		return 0;
	}

	/**
	*
	*/
	function createPluginsTable(object)
	{
		var html = '<table id="plugins-table" class="table  table-forum">' + 
			'<thead>' +
			'<tr>' +
				'<th colspan="2">Plugin</th>' +
				'<th class="text-center" style="width:150px"><?php echo _("Remote version") ?></th>' +
				'<th class="text-center" style="width:40px"><div class="checkbox" style="margin-top:0px; margin-bottom:0px"><label><input id="select-all-plugins" value="all_plugins" type="checkbox" class="checkbox"><span></span></label></div></th>' + 
			'</tr>' + 
		'</thead>' + 
		'<tbody>';

		$.each(object.plugins, function(plugin_name, plugin) {

			var tr_class = plugin.need_update ? 'warning' : '';
			var icon = plugin.need_update ? 'fa-exclamation-circle text-danger': 'fa-check text-muted';
			var checked = plugin.need_update ? 'checked="checked"' : '';
			
			html += '<tr class="'+tr_class+'">'+
				'<td class="text-center" style="width:40px;"><i class="fa '+icon+'"></i></td>'+
				'<td><h4>'+
					'<a>'+plugin.info.name+'</a>' +
					'<small>'+plugin.info.description+' | <?php echo _("Installed version") ?>: '+plugin.info.version+' </small>' + 
				'</h4></td>'+
				'<td class="text-center">'+plugin.latest+'</td>'+
				'<td class="text-center" style="width:40px"><div class="checkbox" style="margin-top:0px;"><label><input id="checkbox-plugin-'+plugin_name+'" value="'+plugin_name +'" type="checkbox" '+checked+' class="checkbox checkbox-action"><span></span></label></div></td>' + 
			'</tr>';

		});
		
		html += '</tbody></table>';

		$("#plugins_tab").html(html);
		if(object.update.plugins > 0){
			$("#plugins-badge").html(object.update.plugins);
		}

		$("#select-all-plugins").on('click', function(){
			var that = this;
			$(this).closest("table").find("tr > td input:checkbox").each(function() {
				this.checked = that.checked;
			});
		});
	}
	
</script>
