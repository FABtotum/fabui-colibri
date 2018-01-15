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

	//global, generic
	var uploadedFile;
	var gcodeID = '';
	var today   = moment("<?php echo date("Y-m-d H:i") ?>");
	var laser_pro_heads = [7];
	
	//laser stuff
	var laserDropZone;
	var laser_profiles         = jQuery.parseJSON('<?php echo json_encode($laser_profiles);?>');
	var laser_gcode_generated  = false;
	var selected_laser_profile = '';
	var laser_file_type        = 'RASTER';
	var laser_speed_settings   = 'none';
	var laser_pwm_settings     = 'none';
	var laser_skip_settings    = 'none';
	
	$(document).ready(function() {
		initLaserDropZone();
		disableButton(".action-button");
		$(".action-button").on('click', doAction);
		$("#head").on('change', setLaserHead);
		$("#laser-profile").on('change', setLaserProfile);
		$("#laser-speed-mode").on('change', onLaserSpeedModeChange);
		$("#laser-pwm-mode").on('change', onLaserPWMmodeChange);
		$("#laser-skip-mode").on('change', onLaserSkipModeChange);
		$(".laser-monitor-change").on('change', onLaserValueChange);
		$("#project-save-mode-choose").on('change', setSaveProjectMode);
		$("#add-subscription-button").on('click', showSubscriptionModal);
		$("#remove-subscription-button").on('click', removeSubscription);
		$("#upload-new-file").on('click', function(){
			location.reload();
		});

		$("#grey-levels-slider").ionRangeSlider({
	        min: 1,
	        from: 1,
	        max: 10,
	        type: 'single',
	        step: 1,
	        postfix: " ",
	        prettify: false,
	        onChange: function (data) {
	            $("#grey-levels-slider-value").html(data.from);
	            onLaserValueChange();
	        }
	    });

	    loadRecentImagesUploaded();
		loadHelpDescriptions();
		populateProjectsList();
		<?php if(!$internet): ?>
		showNoInternet();
		handleDropzone(laserDropZone, 'disable');
		<?php elseif(!$isFabid):?>
		showNoFABIDModal();
		handleDropzone(laserDropZone, 'disable');
		<?php elseif(!$subscription_exists): ?>
		handleDropzone(laserDropZone, 'disable');
		showSubscriptionModal();
		<?php endif; ?>

		initCodeVisibilityHandler();
			
	});
	/**
	*
	**/
	function initLaserDropZone()
	{
		disableButton("#laser-upload");
		$("div#laser-dropzone").dropzone({
			url: "<?php echo site_url('cam/upload/laser') ?>",
			addRemoveLinks : true, 
			dictDefaultMessage: '<span class="text-center"><span class="font-lg visible-lg-block visible-md-block visible-sm-block visible-xs-block dictDefaultMessage"><span class="font-lg"><i class="fa fa-caret-right text-danger"></i> <?php echo _("Drop files to upload") ?> </span> <span>&nbsp&nbsp<h4 class="display-inline"> (<?php echo _("or click") ?>)</h4></span>',
			acceptedFiles: "<?php echo $accepted_files["laser"]?>",
			autoProcessQueue: false,
			maxFilesize : <?php echo ($max_upload_file_size/1024); ?>,
			maxFiles: 1,
			dictRemoveFile: "<?php echo _("Remove file");?>",
			dictMaxFilesExceeded: "<?php echo  _("You can upload just {{maxFiles}} file at time"); ?>", 
			init: function()
			{
				laserDropZone = this;
				/**
				*
				**/
				this.on("addedfile", function(file){
					enableButton("#laser-upload");
				});
				/**
				*
				**/
				this.on("error", function(file, errorMessage){
					fabApp.showErrorAlert("<?php echo _("File error"); ?>", errorMessage);
					disableButton("#laser-upload");
					laserDropZone.removeFile(file);
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
					

					
					if(laserDropZone.files.length ==  1){
						if(laserDropZone.files[0].status == "queued"){
							enableButton("#laser-upload");
						}
					}else{
						disableButton("#laser-upload");
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
						html += '<span class="fant-md"><i class="fa fa-gear fa-spin"></i> <?php echo _("Processing file"); ?></span>';
						$(".dropzone-upload-label").html(html);
						$(".dropzone-file-upload-percent").html('');
						$(".dropzone-progress-bar").parent().remove();
					}
				});
			}
		});
	}
	/**
	*
	**/

	function processUpload(response)
	{
		if(response.upload == true){
			uploadedFile = response;
			laser_file_type = response.info.type;
			var content = '<ol class="sortable-layers list-unstyled">';
			//var content = '<div class="dd" id="nestable"><ol class="dd-list">';
			var palette = ['red', 'rgb(0,128,0)', 'blue', 'violet', 'gold', 'black', 'gray'];
			var color_idx = 0;
			if(laser_file_type == 'VECTOR'){
				$(".dimensions-container").slideUp();
				$("#target_width").val(0);
				$(".raster-settings").slideUp();
				for(i=0; i<response.info.layers.length; i++){
					var lyr = response.info.layers[i];
					var lyr_name = lyr.name;
					if(lyr.elements_count > 0){
						content += '<li><div class="row">\
							<section class=""><label class="label layer-label"><i></i> layer: <strong>"'+lyr.description+'"</strong></label></section>\
							<section class="col col-1">\
								<input type="color" id="'+lyr_name+'-color" name="layer-'+lyr_name+'-color" class="color-palette" data-color="'+palette[color_idx]+'">\
							</section>\
							<section class="col col-5">\
								<label class="input">\
									<span class="icon-prepend"><?php echo _("PWM") ?></span>\
									<input name="layer-'+lyr_name+'-pwm" id="layer-'+lyr_name+'-pwm" class="laser-monitor-change" type="number" value="255" min="0" max="255">\
								</label>\
							</section>\
							<section class="col col-5">\
								<label class="input">\
									<span class="icon-prepend"><?php echo _("Feed"); ?></span>\
									<input name="layer-'+lyr_name+'-burn" id="layer-'+lyr_name+'-burn" class="laser-monitor-change" type="number" value="1000" min="200" max="10000">\
								</label>\
							</section>\
							<section class="col col-1 laser-pro-settings-vector">\
								<label class="checkbox">\
									<input class="layer-cut" data-name="'+lyr_name+'" id="'+lyr_name+'-cut"  name="layer-'+lyr_name+'-cut" type="checkbox"><i></i> <?php echo _("Cut"); ?>\
								</label>\
							</section>\
							</div></li>';
						color_idx += 1;
						if(color_idx >= palette.length)
							color_idx = palette.length-1;
					}
				}
				content += '</ol>';
				$("#laser-image-source").parent().css("min-height",   300);
				$("#laser-preview-source").parent().css("min-height", 300);
				$("#no-gcode-alert").css('top', 150);
				$("#laser-image-source").remove();
				$("#engraving-note").remove();

				$(".layer-settings").html(content);
				$(".laser-monitor-change").on('change', onLaserValueChange);
				$(".color-palette").spectrum({
					showPaletteOnly: true,
					showPalette:true,
					hideAfterPaletteSelect:true,
					palette: [palette],
					change: function(color) {
						onLaserValueChange();
					}
				});

				$('ol.sortable-layers').sortable({});
				$('ol.sortable-layers').sortable('disable');
				
				$(".layer-settings").slideDown();

				$(".layer-cut").on('click', function(){
					var count = 0;
					$(".layer-cut").each(function (index, value) {
						if($(this).is(":checked")){
							count++;
						}
					});

					if(count > 0){
						$(".laser-cut-z-settings").slideDown();
						$('ol.sortable-layers').sortable('enable');
						$(".layer-label").addClass('cursor-move');
						$(".layer-label").find('i').addClass('fa fa-arrows');
					}else{
						$(".laser-cut-z-settings").slideUp();
						$('ol.sortable-layers').sortable('disable');
						$(".layer-label").removeClass('cursor-move');
						$(".layer-label").find('i').removeClass('fa fa-arrows');
					}
				});
			}else{
				$("#no-preview").remove();
			}
			
			setTimeout(function(){
				$(".dropzone-upload-label").html("<?php echo _("Completed"); ?>");
				$(".dropzone-file-upload-percent").html('<i class="fa fa-check"></i>');
	 			hideDropzoneModal();
	 			initLaserSlicerForm();
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
				if(type == 'laser') doUpload(laserDropZone);
				break;
			case 'generate-gcode':
				if(type == 'laser') generateLaserGCode();
				break;
			case 'download-gcode':
				if(type == 'laser') downloadLaserGcode(button.attr('data-href'));
				break;
			case 'open-save-modal':
				openDownloadDialog();
				break;
			case 'save-gcode':
				saveGCode(type, button.attr('data-id'));
				break;
			case 'active-subscription':
				activeSubscription(type);
				break;
			case 'engrave-gcode':
				engraveGcode(button.attr('data-id'));
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
	/**
	*
	**/
	function initLaserSlicerForm()
	{
		populateLaserProfilesOptions();
		setLaserProfile();
		enableButton("#laser-generate-gcode");
		$("#laser-image-source").attr('src', uploadedFile.url);
		initPreviewCarousel();
		

		$("#laser-upload-container").slideUp(function(){
			$("#laser-slice-settings-container").removeClass("hidden");
			$("#laser-image-container").removeClass("hidden");
			
			if($("#laser-image-source").length > 0){
    			var height = $("#laser-image-source").parent().height();
    			$("#laser-preview-source").parent().css("min-height", height);
    			$("#no-gcode-alert").css('top', (height/2));
			}			
		});
	}
	/**
	*
	**/
	function setLaserProfile()
	{
		selected_laser_profile = laser_profiles[$("#laser-profile").val()];

		if(typeof selected_laser_profile != "undefined"){
			$("#laser-profile-description").html(selected_laser_profile["info"]["description"]);
			loadSlicerProfile(selected_laser_profile);
			//override fan value for laser pro heads
			if(isLaserProHead()){
				$("[name='fan']").prop('checked', true);
			}
		}else{
			fabApp.showWarningAlert("<?php echo _("No profiles available for this head");  ?>");
		}
	}
	/**
	*
	**/
	function setLaserHead()
	{
		populateLaserProfilesOptions();
		$("#laser-profile").trigger('change');
	}
	/***
	*
	**/
	function hideDropzoneModal()
	{
		$('#progressModal').modal('hide');
	}
	/**
	*
	**/
	function loadSlicerProfile(profile)
	{
		//general
		$("[name='general-dot_size']").val(profile.general.dot_size);
		$("[name='fan']").prop('checked', profile.general.fan);

		//speed
		$("#laser-speed-mode").val(profile.speed.type).trigger('change');
		switch(profile.speed.type)
		{
			case "const":
				$("[name='speed-burn']").val(profile.speed.burn);
				$("[name='speed-travel']").val(profile.speed.travel);
				break;
			case "linear":
				$("[name='speed-in_min']").val(profile.speed["in_max"]);
				$("[name='speed-in_max']").val(profile.speed["in_min"]);
				$("[name='speed-out_min']").val(profile.speed["out_min"]);
				$("[name='speed-out_max']").val(profile.speed["out_max"]);
				break;
		}

		//pwm
		$("#laser-pwm-mode").val(profile.pwm.type).trigger('change');
		switch(profile.pwm.type)
		{
			case "const":
				$("[name='pwm-value']").val(profile.pwm.value);
				break;
			case "linear":
				$("[name='pwm-in_min']").val(profile.pwm["in_min"]);
				$("[name='pwm-in_max']").val(profile.pwm["in_max"]);
				$("[name='pwm-out_min']").val(profile.pwm["out_min"]);
				$("[name='pwm-out_max']").val(profile.pwm["out_max"]);
				break;
		}

		//skip line
		switch(profile.skip.type)
		{
			case "modulo":
				$("[name='skip-mod']").val(profile.skip["mod"]);
				var on_list = profile.skip["on"];
				var val = "";
				var is_first = true;
				for(i=0; i<on_list.length; i++)
				{
					if(is_first)
					{
						is_first = false;
					}
					else
						val += ", ";
					val += on_list[i]
				}
				$("[name='skip-on']").val(val);
				break;
		}
		$("#skip-mode").val(profile.skip.type).trigger('change');

		if( profile.hasOwnProperty("layer") )
		{
			for (var key in profile.layer)
			{
				l = profile.layer[key];
				$("[name='layer-"+key+"-pwm']").val(l.pwm);
				$("[name='layer-"+key+"-burn']").val(l.burn);
				
				if( l.hasOwnProperty("color") )
				{
					//color = $("#"+key+"-color").spectrum("set", 'rgb('+l.color.r+','+l.color.g+','+l.color.b+');')
				}
			}
		}
	}
	/**
	*
	**/
	function onLaserSpeedModeChange()
	{
		var mode = $(this).val();
		
		if(laser_speed_settings != mode)
		{
			$('.laser-speed-settings').slideUp();
			$('#laser-speed-'+mode).slideDown();
		}
		laser_speed_settings = mode;
		return false;
	}
	/**
	*
	**/
	function onLaserPWMmodeChange()
	{
		var mode = $(this).val();
		if(laser_pwm_settings != mode)
		{
			$('.laser-pwm-settings').slideUp();
			$('#laser-pwm-'+mode).slideDown();
		}
		laser_pwm_settings = mode;
		return false;
	}
	/**
	*
	**/
	function onLaserSkipModeChange()
	{
		var mode = $(this).val();
		if(laser_skip_settings != mode)
		{
			$('.laser-skip-settings').slideUp();
			$('#skip-'+mode).slideDown();
		}
		laser_skip_settings = mode;
		return false;
	}
	/**
	*
	**/
	function populateLaserProfilesOptions()
	{
		var head = $("#head").val();
		var options = '';
	
		$.each(laser_profiles, function(i, profile) {
	
			if(jQuery.inArray( parseInt(head), profile.general.head ) >= 0 ){
			
    			if(laser_file_type == 'VECTOR' && profile.pwm.type == "layer" && profile.speed.type == "layer")
    			{
    				options += '<option value="'+i+'">'+profile.info.name+' ['+profile.info.material+']</option>';
    			}
    			else if(laser_file_type != 'VECTOR' && profile.pwm.type != "layer" && profile.speed.type != "layer")
    			{
    				options += '<option value="'+i+'">'+profile.info.name+' ['+profile.info.material+']</option>';
    			}
			}
		});

		$("#laser-profile").html(options);

		//if is laser head pro
		if(jQuery.inArray( parseInt(head), laser_pro_heads ) >= 0 && laser_file_type == 'VECTOR'){
			$(".laser-pro-settings-vector").slideDown();
		}else{
			$(".laser-pro-settings-vector").slideUp();
		}
	}
	/**
	*
	**/
	function onLaserValueChange(e)
	{
		if(laser_gcode_generated){
			$("#laser-generate-gcode").html('<i class="fa fa-gear"></i> <?php echo _("Regenerate GCode"); ?>').attr("data-regenerate", "true");
			enableButton("#laser-generate-gcode");	
		}
	}
	/**
	*
	**/
	function generateLaserGCode()
	{
		populateProjectsList();
		var data = {preset:{}};

		data['target_width']  = $("#target_width").val();
		data['target_height'] = $("#target_height").val();
		data['invert']        = $("#invert").is(":checked")?"yes":"no";
		data['filename']      = uploadedFile.file_name;
		data['file']          = uploadedFile.full_path;
		data['z_depth']       = $("#z-depth").val();
		data['z_steps']       = $("#z-steps").val();
		data['cut_layer']     = '';
		
		data.preset = getCurrentLaserSettings();

		/*
		* if is head pro selected
		* check if layers cut available
		*/
		if(jQuery.inArray( parseInt($("#head").val()), laser_pro_heads ) >= 0){
			var layer_cut = [];
			$(".layer-cut").each(function (index, value) {
				if($(this).is(":checked")){
					layer_cut.push($(this).attr("data-name"));
				}
			});
			data['cut_layer'] = layer_cut.join();	
		}
	
		disableButton("#laser-generate-gcode");
		disableButton("#laser-save-gcode");
		disableButton("#download-button");

		$("#laser-generate-gcode").find('i').addClass("fa-spin");
		$("#laser-preview-image-tab").css("opacity", 0.3);
		$(".laser-status").html("<?php echo _("Please wait"); ?>");
		//$("#laser-save-gcode").html("<?php echo _("Please wait"); ?>").removeClass("btn-success").addClass("btn-default");
		//$("#laser-save-gcode").removeClass("btn-success").addClass("btn-default");
		//$("#download-button").removeClass("btn-info").addClass("btn-default");

		$.ajax({
			type: "POST",
			url: "<?php echo site_url('cam/generate/laser') ?>/" +  gcodeID,
			dataType: 'json',
			data : data
		}).done(function( response ) {

			if(response.status == true){
				laser_gcode_generated = true;
				var now = new Date();
				var project_name_suffix = now.getDate() + '/' + (now.getMonth()+1) + '/' + now.getFullYear() + ' ' + now.getHours() + ':'+now.getMinutes();
				$("#engraving-note").removeClass('hidden');
				$("#laser-preview-source").attr("src", "<?php echo site_url('cam/preview/laser/') ?>" + response.id);
				$(".owl-next").trigger('click');
				
				//$("#laser-save-gcode").html("<i class='fa fa-check'></i> <?php echo _("GCode ready"); ?>").removeClass("btn-default").addClass("btn-success");
				$(".laser-status").html("<i class='fa fa-check'></i> <?php echo _("GCode ready"); ?>");
				//$("#laser-save-gcode").removeClass("btn-default").addClass("btn-success");
				$("#download-button").attr("data-href", "<?php echo site_url('cam/download/laser/') ?>/" + response.id + "/" + uploadedFile.raw_name).attr("data-type", "laser");
				$("#new-file-name").val(uploadedFile.raw_name);
				$("#new-project-name").val("<?php echo _("New laser project"); ?> " + project_name_suffix);
				$("#save-gcode").attr("data-type", "laser").attr("data-id", response.id);
				$("#no-gcode-alert").remove();
				enableButton("#laser-save-gcode");
				enableButton("#download-button");
				enableButton("#save-gcode");
				gcodeID = response.id;
				$("#laser-preview-image-tab").css("opacity", 1);
				fabApp.showInfoAlert("<?php echo _("Gcode ready!"); ?>");
			}else{
				laser_gcode_generated = false;
				enableButton("#laser-generate-gcode");
				fabApp.showErrorAlert("<?php echo _("Generation failed"); ?>", response.message);
			}
			$("#laser-generate-gcode").find('i').removeClass("fa-spin");
		});

	}
	/**
	*
	**/
	function getCurrentLaserSettings()
	{
		var preset = {};

		$(".laser-slicing-profile :input").each(function (index, value) {
			var name = $(this).attr('name');
			var type = $(this).attr('type');
			if(name)
			{
				if(type == "color")
				{
					color = $(this).spectrum("get");
					preset[$(this).attr('name')+'-r'] = color._r;
					preset[$(this).attr('name')+'-g'] = color._g;
					preset[$(this).attr('name')+'-b'] = color._b;
				}
				else if(name == "skip-on")
				{
					preset[$(this).attr('name')] = $(this).val().split(',');
				}
				else if(name != "laser-profile")
				{
					preset[$(this).attr('name')] = $(this).val();
				}
			}			
		});

		preset['pwm-off_during_travel'] = $("#off-during-travel").is(":checked")?true:false;
		preset['info-name'] = selected_laser_profile["info"]["name"];
		preset['info-material'] = selected_laser_profile["info"]["material"];
		preset['info-description'] = selected_laser_profile["info"]["description"];
		preset['general-levels'] = $("#grey-levels-slider-value").html();
		preset['fan'] = $("#fan").is(':checked');

		if(laser_file_type == 'VECTOR')
		{
			preset['pwm-type']   = 'layer';
			preset['speed-type'] = 'layer';
		}

		return preset;
	}
	/**
	*
	**/
	function downloadLaserGcode(endpoint)
	{
		window.location.href=endpoint;
	}
	/**
	* 
	**/
	function openDownloadDialog()
	{
		$('#downloadGcodeModal').modal({
			keyboard: false,
			//backdrop: 'static'
		});
	}
	/**
	*
	**/
	function populateProjectsList()
	{
		$.ajax({
			type: "POST",
			url: "<?php echo site_url('cam/getUserObjects') ?>",
			dataType: 'json',
		}).done(function( response ) {
			var options = '';

			if(response.aaData.length > 0){
				$("#project-save-mode-choose").removeAttr("disabled");  
				$(response.aaData).each(function (index, value){
					
					var description = value[2] != null ? ' - ' + value[2] : '';
					options += '<option value="'+value[0]+'">'+value[1] + description +'</option>';
				});
				$("#projects-list").html(options);
				$("#project-save-mode-choose").trigger("change");
			}else{
				$("#project-save-mode-choose").trigger("change");
				$("#project-save-mode-choose").attr("disabled", "disabled");  
			}
		});
	}
	/**
	*
	**/
	function setSaveProjectMode()
	{
		var mode = $(this).val();
		$(".project-mode").slideUp();
		$("."+mode+"-project").slideDown();
	}
	/**
	*
	**/
	function saveGCode(type, id)
	{
		var data = {};
		data["mode"]         = $("#project-save-mode-choose").val();
		data["filename"]     = $("#new-file-name").val();
		data["project_id"]   = $("#projects-list").val();
		data["project_name"] = $("#new-project-name").val();
		disableButton("#save-gcode");
		$("#save-gcode").html("<i class='fa fa-gear fa-spin'></i> <?php echo _("Saving"); ?>");
		$.ajax({
			type: "POST",
			data: data,
			url: "<?php echo site_url('cam/saveGCode') ?>/" + type+'/'+id,
			dataType: 'json',
		}).done(function( response ) {
			if(response.success == true){
				fabApp.showInfoAlert("<?php echo _("Gcode saved"); ?>");

				if(type == 'laser'){
					$(".laser-status").html("<i class='fa fa-check'></i> <?php echo _("Gcode saved"); ?>");
					$("#laser-engrave-gcode").attr("data-id", response.file_id);
					disableButton('#laser-save-gcode');
					enableButton("#laser-engrave-gcode");
				}
				
			}else{

				if(type == 'laser'){
					disableButton("#laser-engrave-gcode");
				}
			}
			$("#save-gcode").html("<i class='fa fa-save'></i> <?php echo _("Save"); ?>");
			$('#downloadGcodeModal').modal('hide');
		});
	}
	/**
	*
	**/
	function loadHelpDescriptions()
	{
		$(":input").each(function (index, value) {
			
			var field = $(this);
			var name = field.attr('name');
			
			if(typeof name !== "undefined"){
				if(fields_descriptions.hasOwnProperty(name)){
					var info = fields_descriptions[name];

					if(field.attr('type') == 'checkbox'){
						field.parent().find('span').attr("rel", "popover-hover").attr("data-placement", "top").attr("data-html", "true").attr("data-content",info.description).attr("data-original-title", info.title).css("cursor", "help !important");
					}else{
						field.prev().attr("rel", "popover-hover").attr("data-placement", "top").attr("data-html", "true").attr("data-content",info.description).attr("data-original-title", info.title);
					}
				}
			}
		});
		$("[rel=popover-hover], [data-rel=popover-hover]").popover({
			trigger : "hover"
		});
	}
	/**
	*
	**/
	function showSubscriptionModal()
	{
		enableButton("#modal-active-subscription");
		initModalSubscriptionFormValidator();
		$('#subscriptionModal').modal({
			keyboard: false,
			backdrop: 'static'
		});
	}
	/**
	*
	**/
	function initModalSubscriptionFormValidator()
	{
		$("#modal-subscription-form").validate({
			// Rules for form validation
			rules : {
				modal_subscription : {
					required : true
				}
			},
			// Messages for form validation
			messages : {
				modal_subscription : {
					required : "<?php echo _("Please enter subscription code")?>"
				}
			},
			// Do not change code below
			errorPlacement : function(error, element) {
				error.insertAfter(element.parent());
			}
		});
	}
	/**
	*
	**/
	function activeSubscription(type)
	{
		if($("#"+type+"-subscription-form").valid()){

			var code = $("#"+type+"-subscription").val().trim();
			disableButton("#"+type+"-active-subscription");
			$.ajax({
				type: "POST",
				url: "<?php echo site_url('cam/subscription/active') ?>/"+code,
				dataType: 'json',
			}).done(function( response ) {
				
				if(response.status==false){
					fabApp.showErrorAlert("<?php echo _("Activation failed"); ?>", response.message);
					enableButton("#"+type+"-active-subscription");
					handleDropzone(laserDropZone, 'disable');
				}else{
					fabApp.showInfoAlert(response.message);
					$('#subscriptionModal').modal('hide');
					handleDropzone(laserDropZone, 'enable');
					createSubscriptionCodesTable(response.subscription);
					$("#settings-code-container").removeClass("hidden");
   	   				$("#settings-add-new-code").addClass("hidden");
   	   				handleDropzone(laserDropZone, 'enable');

					
				}
			});

		}
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
				$(dropzone.element).find(".dictDefaultMessage").html('<span class="font-lg"><i class="fa fa-warning text-danger"></i> <?php echo !$internet ? _("No internet connection found").'<br>'._("Check network settings and try again") :  _("You must enter a valid subscription code <br> in order to use CAM toolbox"); ?></span>');
				$(dropzone.element).css("opacity", 0.4);
				break;
		}
	}
	/**
	*
	**/
	function removeSubscription()
	{
		$.SmartMessageBox({
            title: "<i class='fa fa-trash'></i> <span class='txt-color-orangeDark'><strong><?php echo _("Warning");?></strong></span> ",
            content: "<span class='font-md'><?php echo _("You need a valid subscription code to use CAM Toolbox");?><br><?php echo _("Are you sure you want remove it?")?></span>",
            buttons: "[<?php echo _("No");?>][<?php echo _("Yes");?>]"
        }, function(ButtonPressed) {
           if(ButtonPressed == "<?php echo _("Yes");?>"){
        	   $.ajax({
   				type: "POST",
   				url: "<?php echo site_url('cam/subscription/remove') ?>",
   				dataType: 'json',
   			}).done(function( response ) {
   				if(response.status == true){
   	   				$("#settings-code-container").addClass("hidden");
   	   				$("#settings-add-new-code").removeClass("hidden");
   	   				handleDropzone(laserDropZone, 'disable');
   				}else{
   	   				
   				}
   			});
           }
       });
	}
	/**
	*
	**/
	function createSubscriptionCodesTable(subscription)
	{
		//subscription-codes-table
		var info = jQuery.parseJSON(subscription.target);
		var statusClass = info.status == 'active' ? 'success' : 'danger';
		var expirationDate = moment(subscription.exp_date);
		var remainingDays = expirationDate.diff(today, 'days');
		var password_symbol = "*";
		
		var html = '<thead>\
						<tr>\
							<th><?php echo _("Code");?></th>\
							<th><?php echo _("Status");?></th>\
							<th><span class="hidden-xs"><?php echo _("Expiration date");?></span><span class="visible-xs"><?php echo _("Exp. date"); ?></span></th>\
							<th width="20"></th>\
						<tr>\
					<thed>\
					<tbody>\
						<tr>\
							<td width="300"><span class="visible-code hidden"><strong>'+subscription.link+'</strong></span> <span class="hidden-code">'+password_symbol.repeat(subscription.link.length)+'</span>  <span class="pull-right"><i style="cursor:pointer;" class="fa fa-eye code-visible-button"></i></span></td>\
							<td><span class="center-block padding-5 label label-'+statusClass+'">'+info.status+'</span></td>\
							<td>'+expirationDate.format("DD/MM/YYYY")+' ('+_("{0} remaining days").replace("{0}", remainingDays)+')</td>\
							<td class="text-center"><button title="<?php echo _("Remove"); ?>" id="remove-subscription-button" class="btn btn-danger"><i class="fa fa-trash"></i></button></td>\
						</tr>\
					</tbody>';
		$("#subscription-codes-table").html(html);
		$("#remove-subscription-button").on('click', removeSubscription);
		initCodeVisibilityHandler();
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
	function initPreviewCarousel()
	{
		 $('#laser-preview-carousel').owlCarousel({
	        loop: true,
	        margin: 1,
	        navText : ["<?php echo _("Source image");?>","<?php echo _("Laser engraving preview");?>"],
	        dots: false,
	        responsiveClass: true,
	        items: 1,
	        nav: true,
	        loop:false,
	        margin:10
		});
	}
	/**
	*
	**/
	function engraveGcode(id)
	{
		document.location.href = '/fabui/#plugin/fab_laser/make/' + id;
	}
	/**
	*
	**/
	function initCodeVisibilityHandler()
	{
		$(".code-visible-button").mouseup(function() {
			$(".visible-code").addClass('hidden');
			$(".hidden-code").removeClass('hidden');
			
		}).mousedown(function() {
			$(".visible-code").removeClass('hidden');
			$(".hidden-code").addClass('hidden');
		});
	}
	/**
	*
	**/
	function isLaserProHead()
	{
		return jQuery.inArray( parseInt($("#head").val()), laser_pro_heads ) >= 0;
	}
	/**
	*
	**/
	function loadRecentImagesUploaded()
	{
		$.ajax({
			type: "POST",
			url: "<?php echo site_url('cam/laserUploadedImages') ?>/",
			dataType: 'json',
		}).done(function( response ) {
			if(response.images.length > 0){
				var content = '';
				$.each(response.images, function (index, image) {

					var image_view = '';
					if(image.extension == "dxf"){
						image_view = '<i class="fa fa-file" style="font-size:90px;color:#D6D6D6"></i>';
					}else{
						image_view = '<img style="vertical-align: middle;" src="'+image.url+'">';
					}
					content += '<div class="well well-sm text-center" style="width:120px!important;overflow: hidden;">\
									<span style="display: block; height:100px; text-align:center;">'+image_view+'</span>\
									<br>\
									<strong title="'+image.name+'">'+image.name+'</strong><br>\
									<?php if($subscription_exists): ?>
									<a class="uploaded-image" data-name="'+image.name+'" href="javascript:void(0)"><?php echo _("Use again");?></a>\
									<?php endif; ?>
								</div>';
				});
				content += '';
				$("#uplaoded-images").html(content);
				$("#laser-recent-files-title").show();
				$(".uploaded-image").on('click', useUploadedImage);

				$('#uplaoded-images').owlCarousel({
		         	dots: false,
		         	navText : ["<i class='fa fw-lg fa-chevron-left'></i>","<i class='fa fw-lg fa-chevron-right'></i>"],
		            responsiveClass: true,
		            autoWidth: true,
		            responsive:{
		                0:{
		                    items:2,
		                    loop:true,
		                    margin:10
		                },
		                500:{
		                    items:4,
		                    loop:false,
		                    margin:5
		                },
		                1000:{
		                    items:10,
		                    loop:false,
		                    margin:10
		                }
		            }
				});
			}
		});
	}
	/**
	*
	**/
	function useUploadedImage()
	{
		var name = $(this).attr('data-name');
		openWait("<i class='fa fa-cog fa-spin'></i> <?php echo _("Processing file"); ?>", "<?php echo _("Please wait");?>", false);
		$.ajax({
			type: "POST",
			url: "<?php echo site_url('cam/reUseUploadedImage') ?>/"+name,
			dataType: 'json',
		}).done(function( response ) {
			processUpload(response);
		});
	}
</script>