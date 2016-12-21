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
		<?php if(!$runningTask): ?>
		checkBundleStatus();
		<?php else: ?>
		initRunningTask();
		<?php endif; ?>
		$("#check-again").on('click', checkBundleStatus);
		$("#do-update").on('click', doUpdate);
	});
	/***
	*
	**/
	function checkBundleStatus()
	{
		$(".status").html('<h5><i class="fa  fa-spin fa-spinner txt-color-black"></i> Check for updates</h5>');
		disableButton('.action-buttons');
		$.ajax({
			type: "POST",
			url: "<?php echo site_url('updates/bundleStatus') ?>",
			dataType: 'json'
		}).done(function(response) {
			enableButton('.action-buttons');
			if(response.remote_connection == false){
				showNoConnectionAvailable();
			}else{
				showUpdateAvailable(response.update);
			}
			disableButton("#do-abort");
			crateBundlesTable(response);
		});
	}
	/***
	*
	**/
	function initRunningTask()
	{
	}
	/***
	*
	**/
	function showNoConnectionAvailable()
	{
		var html = '<h5>No connection available</h5>';
		disableButton("#do-update");
		disableButton("#do-abort");
		$('.status').html(html);
	}
	/***
	*
	**/
	function crateBundlesTable(data)
	{
		var html = '<table id="bundles-table" class="table table-striped table-forum">' + 
		 				'<thead>' +
							'<tr>' +
								'<th colspan="2">Bundle</th>' +
								/*'<th class="text-center" style="width:150px">Installed version</th>' + */
								'<th class="text-center" style="width:150px">Remote version</th>' +
								'<th class="text-center" style="width:40px"><div class="checkbox" style="margin-top:0px; margin-bottom:0px"><label><input id="select-all-bundles" value="all_bundles" type="checkbox" class="checkbox"><span></span></label></div></th>' + 
							'</tr>' + 
						'</thead>' + 
						'<tbody>';

		$.each(data.bundles, function(bundle_name, object) {

			var tr_class = '';
			var icon = '';
			var checked = '';

			if(data.remote_connection){
				tr_class = object.need_update ? 'warning' : '';
				icon = object.need_update ? 'fa fa-times text-muted' : 'fa fa-check text-muted';
				checked = object.need_update ? 'checked="checked"' : '';
			}
			
			html += '<tr id="tr-' + bundle_name + '" class="' + tr_class + '">' +
			        	'<td  class="text-center" style="width:40px;"><i id="icon-'+ bundle_name +'" class="'+ icon + '"></i></td>' +
			        	'<td><h4><a href="javascript:void(0)">' + bundle_name.capitalize() + '</a> <small></small>' + 
			        	'<small id="small-'+ bundle_name +'">Installed version: ' + object.local +' | Build date: ' + object.info.build_date + '</small>' +
			        	'</h4></td>' + 
			        	/*'<td class="text-center">' + object.local + '</td>'+*/
			        	'<td class="text-center">' + object.latest + ' </td>' +
			        	'<td class="text-center" style="width:40px"><div class="checkbox" style="margin-top:0px;"><label><input value="'+bundle_name +'" type="checkbox" '+checked +' class="checkbox"><span></span></label></div></td>' + 
			        '</tr>';
		});
		html +=    		'<tbdoy>' + 
					'</table>';
		if(data.update.number > 0) {
			$("#bundles-badge").html(data.update.number).addClass('animated fadeIn');
		}
		$("#bundles_tab").html(html);
		$("#select-all-bundles").on('click', function(){
			var that = this;
			$(this).closest("table").find("tr > td input:checkbox").each(function() {
				this.checked = that.checked;
			});
		});
	}
	/***
	*
	**/
	function showUpdateAvailable(update)
	{
		var label = 'Great! Your FABtotum Personal Fabricator is up to date';
		if(update.available){
			label = '<i class="fa fa-refresh"></i> New important software updates are now available';
		}
		$('.status').html('<h5>' + label + '</h5>');
	}
	/***
	*
	**/
	function doUpdate()
	{
		var bundles_to_update = [];
		$("#bundles-table").find("tr > td input:checkbox").each(function () {
			if($(this).is(':checked')){
				bundles_to_update.push($(this).val());
			}
		});
		$("#bundles-table > tbody > tr").each(function(){
			$(this).removeClass('warning');
		});
		
		
		startUpdate(bundles_to_update);
	}
	/**
	*
	**/
	function startUpdate(bundles)
	{
		$.ajax({
			type: "POST",
			data: {'bundles': bundles},
			url: "<?php echo site_url('updates/startUpdate') ?>",
			dataType: 'json'
		}).done(function(response) {
		});
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
		console.log("HANDLE TASK");
		var task    = data.task;
		var bundles = data.update.bundles;
		var current = data.update.current;

		var status_label = '';

		
		
		$('.status').html('<h5><i class="fa fa-refresh fa-spin"></i> ' + current.status.capitalize() + ' : '+ current.bundle.capitalize() +'</h5>');
		
		$.each(bundles, function(i, item) {

			if(current.bundle == i){
				$("#icon-" + i).removeClass().addClass('fa fa-cog fa-spin fa-fw');
				$("#tr-" + i).removeClass().addClass('warning');

				if(item.status == 'downloading'){
					$("#small-"+i).html('<i class="fa fa-download"></i> Downloading (' +  parseInt(item.files.bundle.progress) + ' %)');
				}else if(item.status == 'intsalling'){
					$("#small-"+i).html('<i class="fa fa-check"></i> Installing');
				}
			}else{
				$("#icon-" + i).removeClass();
				$("#tr-" + i).removeClass();
				if(item.status == 'downloaded'){
					$("#small-"+i).html('<i class="fa fa-check"></i> Downloaded');
				}else if(item.status == 'installed'){
					$("#small-"+i).html('<i class="fa fa-check"></i> installed');
				}else {
					$("#small-"+i).html(item.status);
				}
				
				
			}
			
		});
		
	}
</script>