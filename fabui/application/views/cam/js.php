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
		$("#laser-head").on('change', setLaserHead);
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
						if(response.upload == true){
							uploadedFile = response;
							laser_file_type = response.info.type;
							var content = '';
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
										content += '<section><label clas="label">Layer '+lyr.description+'</label></section>';
										content += '<div class="row">\
											<section class="col col-2">\
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
											</div>';
										color_idx += 1;
										if(color_idx >= palette.length)
											color_idx = palette.length-1;
									}
								}
							}
							
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
							$(".layer-settings").slideDown();
							setTimeout(function(){
								$(".dropzone-upload-label").html("<?php echo _("Completed"); ?>");
								$(".dropzone-file-upload-percent").html('<i class="fa fa-check"></i>');
					 			hideDropzoneModal();
					 			initLaserSlicerForm();
							},2000);
						}else{
							fabApp.showErrorAlert("<?php echo _("Upload failed"); ?>", response.error);
							hideDropzoneModal();
						}
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
		$("#laser-image-source").attr('src', uploadedFile.url );
		$("#laser-upload-container").slideUp(function(){
			$("#laser-slice-settings-container").removeClass("hidden");
			$("#laser-image-container").removeClass("hidden");
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
		var head = $("#laser-head").val();
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
		/*
		
		
		if(generated)
		{
			$("#generate-gcode").html('Regenerate GCode');
		}
		//$("#download-gcode").addClass('disabled');
		disableButton("#download-gcode"); */
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
		data['fan']           = $("#fan").is(":checked")?"yes":"no";
		
		data.preset = getCurrentLaserSettings();
		disableButton("#laser-generate-gcode");
		disableButton("#laser-save-gcode");
		disableButton("#download-button");

		$("#laser-generate-gcode").find('i').addClass("fa-spin");
		$("#laser-preview-image-tab").css("opacity", 0.3);
		$("#laser-save-gcode").html("<?php echo _("Please wait"); ?>").removeClass("btn-success").addClass("btn-default");
		$("#download-button").removeClass("btn-success").addClass("btn-default");

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
				$("#laser-preview-source").attr("src", "<?php echo site_url('cam/preview/laser/') ?>" + response.id);
				$("#laser-save-gcode").html("<i class='fa fa-check'></i> <?php echo _("GCode ready"); ?>").removeClass("btn-default").addClass("btn-success");
				$("#download-button").attr("data-href", "<?php echo site_url('cam/download/laser/') ?>/" + response.id + "/" + uploadedFile.raw_name).attr("data-type", "laser").removeClass("btn-default").addClass("btn-success");
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
		//window.open(endpoint, 'Download');
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

			var code = $("#"+type+"-subscription").val();
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
							<td><strong>'+subscription.link+'</strong></td>\
							<td><span class="center-block padding-5 label label-'+statusClass+'">'+info.status+'</span></td>\
							<td>'+expirationDate.format("DD/MM/YYYY")+' ('+_("{0} remaining days").replace("{0}", remainingDays)+')</td>\
							<td class="text-center"><button title="<?php echo _("Remove"); ?>" id="remove-subscription-button" class="btn btn-danger"><i class="fa fa-trash"></i></button></td>\
						</tr>\
					</tbody>';
		$("#subscription-codes-table").html(html);
		$("#remove-subscription-button").on('click', removeSubscription);
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
</script>