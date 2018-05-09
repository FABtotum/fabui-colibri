<?php
/**
 *
 * @author FABteam
 * @version 0.0.1
 * @license https://opensource.org/licenses/GPL-3.0
 *
 */
?>

<script type="text/javascript">
	
	var camDropZone;
	var camApplications = <?php echo json_encode($cam['apps']); ?>;
	var camApp = null;
	var camAcceptedFiles = [];
	var camApplicationConfigs = [];
	var camTask = null;
	
	$(document).ready(function() {
		initLaserDropZone();
		
		disableButton(".action-button");
		$(".action-button").on('click', doAction);
		$("#upload-new-file").on('click', doUploadNewFile);
		
		<?php if(!$internet): ?>
		showNoInternet();
		handleDropzone(camDropZone, 'disable');
		<?php elseif(!$isFabid):?>
		showNoFABIDModal();
		handleDropzone(camDropZone, 'disable');
		<?php elseif(!$subscription_exists): ?>
		handleDropzone(camDropZone, 'disable');
		showSubscriptionModal();
		<?php elseif($internet):?>
		//~ loadRecentImagesUploaded();
		<?php endif; ?>
		
		//console.log('apps', camApplications);
		
		//doShowGroups();
		startApplication(5);
		initSettingsForm();
	});
	
	/**
	*
	**/
	function initLaserDropZone()
	{
		disableButton("#cam-upload");
		$("div#cam-dropzone").dropzone({
			url: "<?php echo site_url('cam2/upload') ?>",
			addRemoveLinks : true, 
			dictDefaultMessage: '<span class="text-center"><span class="font-lg visible-lg-block visible-md-block visible-sm-block visible-xs-block dictDefaultMessage"><span class="font-lg"><i class="fa fa-caret-right text-danger"></i> <?php echo _("Drop files to upload") ?> </span> <span>&nbsp&nbsp<h4 class="display-inline"> (<?php echo _("or click") ?>)</h4></span>',
			//acceptedFiles: "*",
			
			accept: function(file, done) {
				if(camAcceptedFiles)
				{
					var fileExt =  '.' + file.name.split('.').pop().toLowerCase();
					var fileSize = file.size / 1024;
					var isSizeOk = (fileSize <= camApp.max_filesize);
					var isFileTypeOk = ($.inArray(fileExt, camAcceptedFiles) >= 0);
					
					if(!isSizeOk)
					{
						var maxsize = camApp.max_filesize / 1024;
						fabApp.showErrorAlert("<?php echo _("File error"); ?>", 
							"File is too big ("+maxsize+"MiB). Max filesize: "+maxsize+"MiB." );
					}
					
					if(!isFileTypeOk)
					{
						fabApp.showErrorAlert("<?php echo _("File error"); ?>", 
							"You can't upload files of this type." );
					}
					
					if(isSizeOk && isFileTypeOk)
					{
						done();
					}
					else
					{
						this.removeFile(file);
					}
				}
				else
				{
					this.removeFile(file);
				}
			},
			autoProcessQueue: false,
			//maxFilesize : <?php /*echo ($max_upload_file_size/1024);*/ ?>,
			maxFiles: 1,
			dictRemoveFile: "<?php echo _("Remove file");?>",
			dictMaxFilesExceeded: "<?php echo  _("You can upload just {{maxFiles}} file at time"); ?>", 
			init: function()
			{
				camDropZone = this;
				/**
				*
				**/
				this.on("processing", function(file) {
					this.options.url = "<?php echo site_url('cam2/upload') ?>/" + camApp.id;
				});
				/**
				*
				**/
				this.on("addedfile", function(file){
					enableButton("#cam-upload");
				});
				/**
				*
				**/
				this.on("error", function(file, errorMessage){
					fabApp.showErrorAlert("<?php echo _("File error"); ?>", errorMessage);
					disableButton("#cam-upload");
					camDropZone.removeFile(file);
				});
				/**
				*
				**/
				this.on("maxfilesexceeded", function(file){
					
				});
				/**
				*
				**/
				this.on("removedfile", function(file){
					

					
					if(camDropZone.files.length ==  1){
						if(camDropZone.files[0].status == "queued"){
							enableButton("#cam-upload");
						}
					}else{
						disableButton("#cam-upload");
					}
					
					
				});
				/**
				*
				**/
				this.on("thumbnail", function(file, dataUrl){
					
				});
				/**
				*
				**/
				this.on("success", function(file) {
					
					if(file.hasOwnProperty("xhr")){
						var response = jQuery.parseJSON(file.xhr.response);
						processUpload(response);
					}
				});
				/**
				*
				**/
				this.on("complete", function (file) {
					
				});
				/**
				*
				**/
				this.on("uploadprogress", function(file, progress) {
					
					$(".dropzone-progress-bar").attr("style", "width:"+parseInt(progress)+"%");
					if(progress < 100){
						$(".dropzone-file-upload-percent").html(parseInt(progress) + " %");
					}
				});
				/**
				*
				**/
				this.on("totaluploadprogress", function(uploadProgress , totalBytes , totalBytesSent){
					if(uploadProgress == 100)
					{
						var html = '<span><i class="fa fa-check"></i> <?php echo _("Uploaded"); ?></span><br>';
						html += '<span class="fant-md"><i class="fa fa-cog fa-spin"></i> <?php echo _("Processing file"); ?></span>';
						$(".dropzone-upload-label").html(html);
						$(".dropzone-file-upload-percent").html('');
						$(".dropzone-progress-bar").parent().remove();
					}
				});
			}
		});
	}
	
	function processUpload(response)
	{
		if(response.upload == true){
			uploadedFile = response;

			setTimeout(function(){
				$(".dropzone-upload-label").html("<?php echo _("Completed"); ?>");
				$(".dropzone-file-upload-percent").html('<i class="fa fa-check"></i>');
	 			hideDropzoneModal();
	 			initSettingsForm();
	 			closeWait();
			},2000);
		}else{
			fabApp.showErrorAlert("<?php echo _("Upload failed"); ?>", response.error);
			hideDropzoneModal();
			closeWait();
		}
		
	}
	
	/**
	*
	**/
	function doAction()
	{
		var button = $(this);
		action = button.attr('data-action');
		type   = button.attr('data-type');

		switch(action)
		{
			case 'upload':
				doUpload(camDropZone);
				break;
			case 'generate-gcode':
				//if(type == 'laser') generateLaserGCode();
				generateGCode();
				break;
			case 'download-gcode':
				//if(type == 'laser') downloadLaserGcode(button.attr('data-href'));
				break;
			case 'open-save-modal':
				//openDownloadDialog();
				break;
			case 'save-gcode':
				//saveGCode(type, button.attr('data-id'));
				break;
			case 'active-subscription':
				activeSubscription(type);
				break;
			case 'engrave-gcode':
				//engraveGcode(button.attr('data-id'));
				break;
		}
	}
	
	function doUploadNewFile()
	{
		startApplication(camApp.id);
	}
	
	/**
	*
	**/
	function showNoFABIDModal()
	{
		$('#fabidModal').modal({
			keyboard: false,
			backdrop: 'static'
		});
	}
	/**
	*
	**/
	function showNoInternet()
	{
		
		$('#noInternetModal').modal({
			keyboard: false,
			backdrop: 'static'
		});
	}
	
	/**
	*
	**/
	function handleDropzone(dropzone, action)
	{	
		switch(action)
		{
			case 'enable':
				$(dropzone.element).css("opacity", 1);
				$(dropzone.element).find(".dictDefaultMessage").html('<span class="font-lg"><i class="fa fa-caret-right text-danger"></i> <?php echo _("Drop files to upload"); ?></span><span>&nbsp;&nbsp;<h4 class="display-inline"> (<?php echo _("or click"); ?>)</h4></span>');
				dropzone.enable();
				break;
			case 'disable':
				dropzone.disable();
				$(dropzone.element).find(".dictDefaultMessage").html('<span class="font-lg"><i class="fa fa-exclamation-triangle text-danger"></i> <?php echo !$internet ? _("No internet connection found").'<br>'._("Check network settings and try again") :  _("You must enter a valid subscription code <br> in order to use CAM toolbox"); ?></span>');
				$(dropzone.element).css("opacity", 0.4);
				break;
		}
	}
	
	/**
	*
	**/
	function doUpload(dropzone)
	{
		if(dropzone.getQueuedFiles().length > 0){
			showDropzoneModal(dropzone);
			//start upload
			dropzone.processQueue();
		}
	}
	
	/**
	*
	**/
	function showDropzoneModal(dropzone)
	{
		var files = dropzone.getQueuedFiles();
		if(files.length > 0){

			var file = files[0];
			$(".dropzone-file-name").html('<strong>' + file.name + '</strong> <small>(' + humanFileSize(file.size) + ')</small> ');
			$(".dropzone-progress-bar").attr("style", "width:0%");
			$(".dropzone-upload-label").html("<i class='fa fa-upload'></i> <?php echo _("Uploading"); ?>");
			$('#progressModal').modal({
				keyboard: false,
				backdrop: 'static'
			});
		}
	}
	
	/***
	*
	**/
	function hideDropzoneModal()
	{
		$('#progressModal').modal('hide');
	}
	
	/**
	* Show app groups
	**/
	function doShowGroups()
	{
		var content = '<div class="row">';
		
		var groups = [
			{
				'name' : 'Laser',
				'group': 'laser',
				'img': '/assets/img/head/photo/laser_head_pro.png'
			},
			{
				'name' : 'Milling',
				'group': 'milling',
				'img': '/assets/img/head/photo/milling_head.png'
			},
			{
				'name' : 'Prism',
				'group': 'prism',
				'img': '/assets/img/head/photo/prism_module.png'
			},
			{
				'name' : 'Printing',
				'group': 'printing',
				'img': '/assets/img/head/photo/printing_head_pro.png'
			}
		];
		
		camAcceptedFiles = [];
		camTask = null;
		
		for(i in groups) {
			g = groups[i];
			content += '\
			<div class="col-sm-3">\
				<div class="panel panel-default">\
					<div class="panel-body status">\
						<div class="image padding-10">\
							<a data-group="'+g.group+'" href="#" class="app-group-view">\
								<img src="'+g.img+'">\
							</a>\
						</div>\
						<div style="padding:5px;">\
							<p class="text-center">'+g.name+'</p>\
						</div>\
					</div>\
				</div>\
			</div>'
		}
		
		content += '</div>';
		
		$('#cam-apps-view').html(content);
		$('.app-group-view').on('click', doShowApps );
		
		return false;
	}
	
	function doShowApps()
	{
		var link = $(this);
		group = link.attr('data-group');
		showApps(group);
		return false;
	}
	
	function showApps(group)
	{
		camAcceptedFiles = [];
		
		content = '\
		<div class="row margin-bottom-10">\
			<div class="col-sm-12">\
				<button class="btn btn-default pull-left app-groups"><i class="fa fa-arrow-left"></i> Back</button>\
			</div>\
		</div>'
		
		content += '<div class="row">';
		
		var rowCount = 0;
		for(i in camApplications)
		{
			app = camApplications[i];
			
			if(app.group == group)
			{
				content += '\
				<div class="col-sm-3">\
					<div class="panel panel-default">\
						<div class="panel-body status">\
							<div style="padding:5px;">\
								<a data-app="'+app.id+'" href="#" class="go-to-app">\
									<p class="text-center">'+app.name+'</p>\
								</a>\
							</div>\
						</div>\
					</div>\
				</div>'
				
				rowCount += 1;
				
				if(rowCount >= 4)
				{
					content += '</div><div class="row">';
					rowCount = 0;
				}
			}
		}
		
		content += '</div>';
		
		$("#cam-dropzone-view").addClass("hidden");
		$("#cam-apps-view").html(content);
		$(".go-to-app").on('click', doStartApplication );
		$(".app-groups").on('click', doShowGroups );
	}
	
	function doStartApplication()
	{
		var link = $(this);
		var appId = link.attr('data-app');
		camTask = null;
		
		startApplication(appId);
		$("#cam-app-config-view").html('loading...');
		
		return false;
	}
	
	function startApplication(appId)
	{
		for(i in camApplications)
		{
			var a = camApplications[i];
			if(a.id == appId)
			{
				camApp = a;
				camAcceptedFiles = a.accepts.split('|');
				break;
			}
		}
		
		setProfileList(camApp.id);
		camDropZone.removeAllFiles();
		
		var content = '<div class="row">';
		content = '</div>';
		
		$.get("<?php echo site_url('cam2/ui') ?>/" + appId)
			.done(function( data ) {
				console.log(data);
				$("#cam-app-config-view").html(data);
				
				changeProfile();
			});
		
		$("#cam-dropzone-view").removeClass("hidden");
		$("#cam-settings-view").addClass("hidden");
		$("#cam-apps-view").removeClass("hidden");
		// TODO: make translation friendly string
		var note = 'Note: Accepts (*';
		note += camAcceptedFiles.join(', *');
		note += ') files of up to '+ (camApp.max_filesize / 1024) +' MB.';
		
		$("#note-dropzone-maxsize").html(note);
		$("#cam-apps-view").html(content);
		$("#back-to-apps").attr('data-group', camApp.group);
		$("#back-to-apps").on('click', doShowApps );
		//initSettingsForm();
	}
	
	function setProfileList(appId)
	{
		var profileId = 0;
		$("#cam-profile").empty();
		$.each(camApp.config, function (i, item) {
			console.log('cfg', item);
			$('#cam-profile').append($('<option>', { 
				value: i,
				text : item.name 
			}));
		});
		$("#cam-profile").on('change', changeProfile);
	}
	
	function changeProfile()
	{
		var id = $("#cam-profile").val();
		var cfg = camApp.config[id];
		console.log('profile change', cfg);
		$.each(cfg.data, function (key, value) {
			var fieldId = 'camfield-' + key.replace('.', '-');
			console.log('field', key, value);
			$("#" + fieldId).val(value);
		});
	}
	
	function initSettingsForm()
	{
		$("#cam-dropzone-view").addClass("hidden");
		$("#cam-apps-view").addClass("hidden");
		$("#cam-settings-view").removeClass("hidden");
		enableButton("#cam-generate-gcode");
	}
	
	function unflatten(obj, path, value, datatype)
	{
		if(path.length == 1)
		{
			switch(datatype)
			{
				case "string":
					obj[ path[0] ] = value;
					break;
				case "number":
					obj[ path[0] ] = Number(value);
					break;
				case "boolean":
					obj[ path[0] ] = value.toLowerCase() == "true";
					break;
			}
		}
		else
		{
			var key = path[0];
			var new_path = path.splice(0, 1); 
			
			if(!(key in obj))
				obj[key] = {}
			
			unflatten( obj[ key ], path, value, datatype);
		}
	}
	
	function generateGCode()
	{
		var config = {};
		
		console.log('== generate ==');
		$("#cam-config-form :input").each(function (index, value) {
			var name = $(this).attr('name').replace('camfield-', '');
			var type = $(this).attr('type');
			var datatype = $(this).attr('data-type');
			
			console.log(name, $(this).val(), datatype);
			
			unflatten(config, name.split('-'), $(this).val(), datatype);
		});
		
		var data = {
			config: JSON.stringify(config),
			app_name: camApp.name
		}
		
		$("#cam-generate-gcode").find('i').addClass("fa-spin");
		disableButton("#cam-generate-gcode");
		disableButton("#cam-save-gcode");
		disableButton("#cam-make-gcode");
		disableButton("#cam-download-gcode");
		
		$.ajax({
			type: "POST",
			url: "<?php echo site_url('cam2/generate') ?>/" +  camApp.id,
			dataType: 'json',
			data : data
		}).done(function( response ) {
			console.log(response);
			camTask = {
				id: response.taskId,
				status: 'WAITING'
			}
			
			checkStatus();
		});
	}
	
	function taskFinish(task)
	{
		camTask = task;
		
		$("#cam-generate-gcode").find('i').removeClass("fa-spin");
		enableButton("#cam-generate-gcode");
		
		if(task.status == "FINISHED")
		{
			enableButton("#cam-save-gcode");
			enableButton("#cam-make-gcode");
			enableButton("#cam-download-gcode");
		}
	}
	
	function checkStatus()
	{
		$.get("<?php echo site_url('cam2/status') ?>/" +  camTask.id,
		).done(function( response ) {
			console.log(response);
			
			if( response.status == "FINISHED" ||
				response.status == "FAILED" ||
				response.status == "ABORTED")
			{
				taskFinish(response);
			}
			else
			{
				setTimeout(checkStatus, 1000);
			}
		});
	}
	
</script>
