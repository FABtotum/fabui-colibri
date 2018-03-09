<script type="text/javascript">

	/*
	var HYBRID_WORKING_MODE = <?php echo array_search('Hybrid', $working_modes); ?>;
	var FFF_WORKING_MODE    = <?php echo array_search('FFF',    $working_modes); ?>;
	var LASER_WORKING_MODE  = <?php echo array_search('Laser',  $working_modes); ?>;
	var CNC_WORKING_MODE    = <?php echo array_search('CNC',    $working_modes); ?>;
	var SCAN_WORKING_MODE   = <?php echo array_search('Scan',   $working_modes); ?>;
	var SLA_WORKING_MODE    = <?php echo array_search('SLA',    $working_modes); ?>;
	*/
	var HYBRID_WORKING_MODE = <?php echo $working_modes['Hybrid']?>;
	var FFF_WORKING_MODE    = <?php echo $working_modes['FFF']?>;
	var LASER_WORKING_MODE  = <?php echo $working_modes['Laser']?>;
	var CNC_WORKING_MODE    = <?php echo $working_modes['CNC']?>;
	var SCAN_WORKING_MODE   = <?php echo $working_modes['Scan']?>;
	var SLA_WORKING_MODE    = <?php echo $working_modes['SLA']?>;
	var PRISM_MODULE_ID     = 8;

	var max_prism_connection_attempts     = 3;
	var prism_connection_attemtps_counter = 0;

	var selected_head = "<?php echo $installed_head['filename']?>";
	heads = <?php echo json_encode($heads)?>;
	var official_heads_id_limit = 100;
	var owl;
	
	$(document).ready(function() {
		initCarousel();
		initFieldValidation();

		$('.settings-action').on('click', buttonAction);
		$('.capability').on('change', capability_change);
		$("#inputId").on('change', importHeadSettings);
		$("#advanced_settings_switch").on('click', clickShowHideSettings);

	});
	/**
	*
	**/
	function initCarousel()
	{
		owl = $('#heads-carousel').owlCarousel({
        	loop: true,
        	center: false,
        	startPosition: parseInt($(".installed").attr('data-position')),
         	margin: 1,
         	navText : ["<i class='fa fw-lg fa-chevron-left'></i>","<i class='fa fw-lg fa-chevron-right'></i>"],
         	dots: false,
         	onInitialize: fixNavBars,
         	onInitialized: fixNavBars,
         	onChange : fixNavBars,
         	onResized : fixNavBars,
            responsiveClass: true,
            	responsive: {
                	0: {
                    	items: 1,
                    	nav: true
                  	},
                  	600: {
                    	items: 3,
                    	nav: true
                  	},
                  	1000: {
                    	items: 5,
                    	nav: true,
                    	loop: false,
                    	margin: 20
                  	}
                }
		});

		$('.filters-button').on('click', function(e) {
			var filter_data = $(this).data('filter');
						
			/* return if current */
			if($(this).hasClass('btn-info')) return;

			/* active current */
			$(this).addClass('btn-info').siblings().removeClass('btn-info');

			/* animate filter */
			var owlAnimateFilter = function(even) {
				$(this)
				.addClass('__loading')
				.delay(70 * $(this).parent().index())
				.queue(function() {
					$(this).dequeue().removeClass('__loading')
				})
			}

			/* Filter 
			owl.owlFilter(filter_data);*/
			owl.owlFilter(filter_data, function(_owl) {
				$(_owl).find('.item').each(owlAnimateFilter); 
				$('.settings-action').on('click', buttonAction);
				fixNavBars();
			});
		});
	}
	
	function fixNavBars(event)
	{

		var mainContentHeight = $("#content").height();
		//center arrows
		var carouselHeight = $("#heads-carousel  .owl-stage-outer").height();
		var prevHeight = $("#heads-carousel .owl-prev").height();
		var nextHeight = $("#heads-carousel .owl-next").height();
		$("#heads-carousel .owl-prev").css("top", (carouselHeight-prevHeight)/2);
		$("#heads-carousel .owl-next").css("top", (carouselHeight-prevHeight)/2);
		
		if(carouselHeight > mainContentHeight) fixNavBars();
		
		
	}
	/**
	*
	**/
	function initFieldValidation()
	{
		$("#head-settings").validate({
			rules:{
				name:{
					required:true
				},
				'capability[]': {
					required: true,
					minlength: 1
				},
				fw_id: {
					required: true
				}
			},
			messages: {
				name:{
					required: _("Please enter head name")
				},
				'capability[]':  _("Please select at least one capability")
			},
			  submitHandler: function(form) {
			},
			errorPlacement : function(error, element) {
				if(element[0].name == "capability[]")
				{
					error.insertAfter( $("#capabilities-container") );
				}
				else
					error.insertAfter(element.parent());
			}
		});
		$("#head-name").inputmask("Regex");
	}
	/**
	*
	**/
	function buttonAction()
	{
		var button = $(this);
		var action = button.attr('data-action');
		switch(action)
		{
			case "edit":
				selected_head = button.attr('data-head');
				populateHeadSettings(heads[selected_head]);
				$("#advanced_settings_switch").prop('checked', false);
				showHideSettings(false);
				$('#settingsModal').modal('show');
				break;
			case "add":
				$("#advanced_settings_switch").prop('checked', true);
				showHideSettings(true);
				document.getElementById("head-settings").reset();
				showHideInputsForOfficialHeads('show');
				$("#head-fw_id").attr("min", official_heads_id_limit);
				$('#settingsModal').modal('show');
				break;
			case "remove":
				removeHeadSettings();
				break;
			case "save":
				if($("#head-settings").valid())
					saveHeadSettings(true);
				break;
			case "import":
				$("#inputId").trigger('click');
				break;
			case "export":
				if($("#head-settings").valid())
					exportHeadSettings();
				break;
			case "factory-reset":
				factoryReset(selected_head);
				break;
			case "save-install":
				if($("#head-settings").valid())
					saveHeadSettings(set_head);
				break;
			case "install":
				selected_head = button.attr('data-head');
				set_head();
				break;
			case 'info':
				selected_head = button.attr('data-head');
				showDescription();
				break;
				
		}
		return false;
	}
	/**
	*
	**/
	function showHideSettings(bool)
	{
		if (bool) {
			$(".advanced-settings").removeClass('advanced-settings').addClass("all-settings");
		} else {
			$(".all-settings").removeClass('all-settings').addClass("advanced-settings");
		}
	}
	/**
	*
	**/
	function populateHeadSettings(head, isImport)
	{
		isImport = isImport || false ;
		
		document.getElementById("head-settings").reset();
		for (var key in head) {
			var value = head[key];
			// now you can use key as the key, value as the... you guessed right, value
			if(isArray(value))
			{
				if(key == "capabilities")
				{
					for(var i=0; i<value.length; i++)
					{
						var id = "#cap-" + value[i];
						$(id).prop('checked', true);
					}
				}
				if(key == "plugins")
				{
					$("#plugins").val(value.toString());
				}
			}
			else if(isObject(value))
			{
				if(key == "feeder")
				{
					for (var fkey in value)
					{
						var fvalue = value[fkey];
						var id = "#feeder-"+fkey;
						$(id).val(fvalue);
					}
				}

				if(key == "offset")
				{
					for (var oKey in value)
					{
						var oValue = value[oKey];

						if(isObject(oValue)){
							
							for(var vKey in oValue)
							{
								var vValue = oValue[vKey];
								var id = "#offset-"+oKey+'-'+vKey;
								$(id).val(vValue);
							}
						}
					}
				}
			}
			else
			{
				var id = "#head-"+key;
				$(id).val(value);
			}
		}
		capability_change(false);
		/**
		* only for fabtotums official heads
		*/
		if(!isImport){
			$("#head-fw_id").attr("min", 1);
			if(head.fw_id < official_heads_id_limit){
				showHideInputsForOfficialHeads('hide');
			}else{
				showHideInputsForOfficialHeads('show');
				$("#head-fw_id").attr("min", official_heads_id_limit);
			}
		}else{
			$("#head-fw_id").attr("min", official_heads_id_limit);
		}

		showHideLaserProExtraSettings(head.fw_id);

	}
	/**
	*
	**/
	function showHideInputsForOfficialHeads(action)
	{
		if(action == 'show'){
			$(".url-container").show();
			$(".description-container").show();
			$("#head-name").removeAttr("readonly")
			$("#head-fw_id").removeAttr("readonly");
			$(".factory-head-button").hide();
			$(".custom-head-button").show();
			$(".laser-pro").hide();
		}else if(action == 'hide'){
			$(".url-container").hide();
			$(".description-container").hide();
			$("#head-name").attr("readonly", "readonly");
			$("#head-fw_id").attr("readonly", "readonly");
			$(".factory-head-button").show();
			$(".custom-head-button").hide();
			$(".laser-pro").show();
		}
	}
	/**
	*
	**/
	function isArray(val)
	{
		return Array.isArray(val);
	}
	/**
	*
	**/
	function isObject(val) 
	{
		if (val === null) { return false;}
		return ( (typeof val === 'function') || (typeof val === 'object') );
	}
	/**
	*
	**/
	function capability_change(update_working_mode)
	{
		update_working_mode = update_working_mode || true;
		var capabilities = [];
		var print        = false;
		var mill         = false;
		var laser        = false;
		var scan         = false;
		var feeder       = false;
		var fourthaxis   = false;
		var sla          = false;
		
		$(".capability").each(function (index, value) {

			var element = $(this);
			if(element.is(":checked"))
			{
				capabilities.push(element.attr('data-attr'));
			}
		});

		
		var working_mode = CNC_WORKING_MODE;
		
		if(capabilities.indexOf("print") > -1)
		{
			$(".print-settings").slideDown();
			working_mode = FFF_WORKING_MODE;
			print = true;
		}
		else
			$(".print-settings").slideUp();
			
		if(capabilities.indexOf("mill") > -1)
		{
			$(".mill-settings").slideDown();
			mill = true;
			if(working_mode == FFF_WORKING_MODE)
				working_mode = HYBRID_WORKING_MODE;
			else
				working_mode = CNC_WORKING_MODE;
		}
		else
			$(".mill-settings").slideUp();
			
		if(capabilities.indexOf("feeder") > -1 ){
			$(".feeder-settings").slideDown();
			feeder = true;
		}
		else{
			$(".feeder-settings").slideUp();
			feeder = false;
		}
		
		if(capabilities.indexOf("4thaxis") > -1){
			$(".4thaxis-settings").slideDown();
			fourthaxis = true;
		}
		else{
			$(".4thaxis-settings").slideUp();
			fourthaxis = false;
		}
		
		if(capabilities.indexOf("laser") > -1)
		{
			working_mode = LASER_WORKING_MODE;
			laser = true;
			$(".laser-settings").slideDown();
			
		}else{
			$(".laser-settings").slideUp();
		}
			
		if(capabilities.indexOf("scan") > -1)
		{
			working_mode = SCAN_WORKING_MODE;
			scan = false;
		}

		if(capabilities.indexOf("sla") > -1){
			sla = true;
			working_mode = SLA_WORKING_MODE;
		}
		
		if(update_working_mode)
			$("#head-working_mode").val(working_mode);

		updateTool(working_mode, feeder, fourthaxis);


		if( $(this).is("input") )
		{
			
			var state = $(this).is(":checked");
			var tab_name =  $(this).attr('data-attr');

			
			if(state)
			{
				$("#"+tab_name+"-tab-button").trigger('click');
				if(capabilities.length == 1)
					$("#"+tab_name+"-tab").addClass("active");
			}
			else
			{
				$("#"+tab_name+"-tab").removeClass("active");
				if(capabilities.length > 0)
				{
					var last_idx = capabilities.length -1;
					$("#"+capabilities[last_idx]+"-tab-button").trigger('click');
					if(capabilities.length == 1)
					{
						$("#"+capabilities[last_idx]+"-tab").addClass("active");
					}
				}
			}
		}
		else // first time show scenario
		{
			var available_tabs = ['print', 'mill', 'feeder', '4thaxis', 'laser'];

			$(".tab-pane").removeClass('active');
			
			for(var i=0; i<capabilities.length; i++)
			{
				var capability = capabilities[i];
				if(available_tabs.indexOf(capability) > -1)
				{
					$("#"+capability+"-tab-button").trigger('click');
					$("#"+capability+"-tab").addClass("active");
					break;
				}
			}
		}
	}
	/**
	*
	**/
	function updateTool(working_mode, hasFeeder, hasFourthAxis)
	{
		var tool = '';
		switch(working_mode){
			case HYBRID_WORKING_MODE: //hybrid
			case FFF_WORKING_MODE: //FFF
				tool = 'M563 P0 D0';
				break;
			case LASER_WORKING_MODE: //laser
			case CNC_WORKING_MODE: //CNC
				tool = 'M563 P0 D-1';
				break;
			case SCAN_WORKING_MODE: // Scan
				break;
			case SLA_WORKING_MODE:
				tool = 'M563 P0 H-1 D3';
				break;
		}
		if(hasFeeder){
			tool = 'M563 P2 D0';
		}else if(hasFourthAxis && working_mode == 4){
			tool = 'M563 P0 D3';
		}
		$("#tool").val(tool);
	}
	/**
	*
	**/
	function importHeadSettings(event)
	{
		var input = event.target;
		var reader = new FileReader();
		reader.onload = function(){
			var text = reader.result;
			
			content = jQuery.parseJSON(text);
			populateHeadSettings(content, true);
		}
		reader.readAsText(input.files[0]);
		return false;
	}
	/**
	*
	**/
	function clickShowHideSettings()
	{
		showHideSettings($(this).prop('checked'));
	}
	/**
	*
	**/
	function factoryReset()
	{
		$.SmartMessageBox({
			title: "<?php echo _("Attention");?>!",
			content: "<?php echo _("Restore factory settings for <strong>{0}</strong> ?");?>".format(heads[selected_head].name),
			buttons: '[<?php echo _("No")?>][<?php echo _("Yes")?>]'
		}, function(ButtonPressed) {
			if (ButtonPressed === "<?php echo _("Yes")?>")
			{
				$.ajax({
					type: 'post',
					url: '<?php echo site_url('head/factoryReset'); ?>/' + selected_head,
					dataType: 'json'
				}).done(function(response) {
					fabApp.showInfoAlert('<?php echo _("Factory settings restored") ?>');
					setTimeout(function(){
						location.reload();
					}, 1000);
				});
			}
			if (ButtonPressed === "<?php echo _("No")?>")
			{
			}
		});
	}
	/**
	*
	**/
	function exportHeadSettings()
	{
		var settings = getHeadSettings();
		var filename = settings['name'].replace(/ /g, "_").replace(/-/g, "_").toLowerCase() + ".json";
		var content = JSON.stringify(settings, null, 2)
		var blob = new Blob([content], {type: "text/plain"});
		saveAs(blob, filename);
	}
	/**
	*
	**/
	function getHeadSettings()
	{
		var capabilities = [];
		var plugins = [];
		
		var settings = {};
		
		$("#head-settings :input").each(function (index, value) {

			var name   = $(this).attr('name');
			var id     = $(this).attr('id');
			var type   = $(this).prop('tagName').toLowerCase();
			var feeder = id.startsWith("feeder-");
			var offset = id.startsWith("offset-");
			var fourthaxis = id.startsWith("4thaxis-");
			
			if( !settings.hasOwnProperty('feeder'))
			{
				settings['feeder'] = {};
			}
			if( !settings.hasOwnProperty('4thaxis'))
			{
				settings['4thaxis'] = {};
			}
			if(!settings.hasOwnProperty('offset'))
			{
				settings['offset'] = {};
			}
			
			if(name)
			{
				if($(this).is(':checkbox'))
				{
					
					if($(this).is(":checked"))
					{
						capabilities.push( $(this).attr('data-attr') );
					}
				}
				else if($(this).is('select')){
					settings[name] = $(this).val();
				}
				else
				{
					if(feeder) {
						settings['feeder'][name] = $(this).val();
					} else if(fourthaxis) {
						settings['4thaxis'][name] = $(this).val();
					} else if(offset){
						var tmp = name.split("-");
						if(!settings['offset'].hasOwnProperty(tmp[0])) settings['offset'][tmp[0]] = {};
						settings['offset'][tmp[0]][tmp[1]] = $(this).val();
					} 
					else {
						settings[name] = $(this).val();
					}
				}
				
				if(name == "custom_gcode")
				{
					if(feeder)
						settings['feeder'][name] = settings['feeder'][name].toUpperCase();
					else
						settings[name] = settings[name].toUpperCase();
				}
				if(name=="plugins")
				{
					if($(this).val() == ""){
						settings['plugins'] = new Array();
					}else{
						settings['plugins'] = $(this).val().split(",");
					}
				}
				
			}
		});
		
		settings['capabilities'] = capabilities;
		
		if( capabilities.indexOf("feeder") == -1 )
		{
			settings['feeder'] = {};
		}
		
		if( capabilities.indexOf("4thaxis") == -1 )
		{
			settings['4thaxis'] = {};
		}

		if( capabilities.indexOf("laser") > -1 )
		{
			settings['thermistor_index'] = 3;
		}

		delete settings.capability;
		return settings;
	}
	/**
	*
	**/
	function saveHeadSettings(callback)
	{
		openWait('<i class="fa fa-save"></i> <?php echo _("Saving head settings"); ?>', '<?php echo _("Please wait"); ?>...');
		var settings = getHeadSettings();		
		var filename = settings['name'].replace(/ /g, "_").replace(/-/g, "_").toLowerCase();
		$.ajax({
			type: 'post',
			url: '<?php echo site_url('head/saveHead'); ?>/' + filename,
			data : settings,
			dataType: 'json'
		}).done(function(response) {
			fabApp.showInfoAlert('<strong>{0}</strong> saved'.format(settings.name));

			setTimeout(function(){
				if($.isFunction(callback)){
					callback(filename);
				}else{
					location.reload();
				}				
			}, 1000);
		});
	}
	/**
	*
	**/
	function set_head(headToInstall){		
		
		headToInstall = headToInstall || selected_head;
		
	 	if($("#heads").val() == 'head_shape'){
	 		alert( _("Please select a Head") );
	 		return false;
	 	}

	 	var title = '<?php echo _("Installing head"); ?>';
	 	if(headToInstall == 'prism_module'){
	 		title = '<?php echo _("Installing module"); ?>';
	 	}
	 	
	 	openWait('<i class="fa fa-cog fa-spin"></i> ' + title, '<?php echo _("Please wait"); ?>...');
	 	$.ajax({
			type: "POST",
			url: "<?php echo site_url("head/setHead") ?>/"+ headToInstall,
			dataType: 'json'
		}).done(function( data ) {
			
			if(parseInt(data.fw_id) != PRISM_MODULE_ID){
				openWait('<i class="fa fa-check"></i> <?php echo _("Head installed"); ?>', '<?php echo _("Reloading page"); ?>', false);
    			setTimeout(function(){
    				location.reload();
    			}, 2000);
			}else{
				openWait('<i class="fa fa-cog fa-spin"></i> <?php echo _("Connecting to PRISM module"); ?>', '<?php echo _("Please wait"); ?>');
				autoConnectToPrism();
			}
			
		});
	}

	/**
	*
	**/
	function autoConnectToPrism()
	{
		if(prism_connection_attemtps_counter < max_prism_connection_attempts){
    		prism_connection_attemtps_counter++;
    		waitContent("Prism connection attempt: " + prism_connection_attemtps_counter);
    		$.ajax({
    			type: 'get',
    			url: '<?php echo site_url('plugin/fab_prism/autoconnect'); ?>',
    			dataType: 'json'
    		}).done(function(response) {			
    			if(response.bluetooth_status.paired.connected == false){
    				autoConnectToPrism();
    			}else{
    				openWait('<i class="fa fa-check"></i> <?php echo _("Prism Module connected"); ?>', '<?php echo _("Loading page"); ?>');
    				setTimeout(function(){
    					location.reload();
    				}, 2000);
    			}
    		}).fail(function(jqXHR, textStatus){
    			autoConnectToPrism();
			});
		}else{
			
			openWait('<i class="fa fa-exclamation-triangle"></i> <?php echo _("Couldn\'t connect to PRSIM"); ?>', '<?php echo _("Redirect to settings page"); ?>', false);
			setTimeout(function(){
				closeWait();
	    		//document.location.href = '<?php echo site_url('#plugin/fab_prism/settings'); ?>';
	    		document.location.href = '/fabui/#plugin/fab_prism/settings';
	    	}, 2000);
			
		}
	}
	
	/**
	*
	**/
	function showDescription()
	{
		$(".heads-description").addClass("hidden");
		$("#"+selected_head+"_description").removeClass("hidden");
		$("#descriptionModalTitle").html(heads[selected_head].name);
		$("#head-more-details").attr("href",heads[selected_head].link );
		$('#descriptionModal').modal('show');
	}
	/**
	*
	**/
	function removeHeadSettings()
	{
		$.SmartMessageBox({
			title: "<?php echo _("Attention");?>!",
			content: "<?php echo _("Remove <strong>{0}</strong> settings?");?>".format(heads[selected_head].name),
			buttons: '[<?php echo _("No")?>][<?php echo _("Yes")?>]'
		}, function(ButtonPressed) {
			if (ButtonPressed === "<?php echo _("Yes")?>")
			{
				$.ajax({
					type: 'post',
					url: '<?php echo site_url('head/removeHead'); ?>/' + selected_head,
					dataType: 'json'
				}).done(function(response) {
					fabApp.showInfoAlert('<strong>{0}</strong> removed'.format(heads[selected_head].name));
					setTimeout(function(){
						location.reload();
					}, 1000);
				});
			}
			if (ButtonPressed === "<?php echo _("No")?>")
			{
			}
		});
	}
	/**
	*
	**/
	function showHideLaserProExtraSettings(id)
	{
		var pro_laser_heads = [7];

		if(pro_laser_heads.indexOf(id) > -1){
			$(".laser-pro").show();
		}else{
			$(".laser-pro").hide();
		}
	}
</script>