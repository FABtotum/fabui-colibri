<?php
/**
 * 
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<script type="text/javascript">
	var selected_feeder = "<?php echo $feeder?>";
	var feeders = <?php echo json_encode($feeders)?>;
    
	$(function () {
		
		$("#feeders").on('change', setFeederImg);
		$("#feeders").trigger('change');
		$("#set-feeder").on('click', setFeeder);
		
		$('.settings-action').on('click', buttonAction);
		$("#inputId").on('change', importFeederSettings);
		initFieldValidation();
        
        console.log('feeders', feeders);
		
	});

	function initFieldValidation()
	{
		$("#feeder-settings").validate({
			rules:{
				name:{
					required:true
				},
				'capability[]': {
					required: true,
					minlength: 1
				}
			},
			messages: {
				name:{
					required: _("Please enter head name")
				},
				'capability[]':  _("Please select at least one capability")
			},
			  submitHandler: function(form) {
				console.log("FORM SUBMIT");
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
		
		$("#feeder-name").inputmask("Regex");
	}
	/**
	*
	**/
	function setFeederImg(){
		selected_feeder = $(this).val();

		$(".jumbotron").html('');

		if(feeders.hasOwnProperty(selected_feeder))
		{
			$("#edit-button").show();
			$("#remove-button").show();
			var feeder = feeders[selected_feeder];
			if( parseInt(feeder.factory) == 1 )
				$("#remove-button").hide();
				
			$(".jumbotron").html(feeders[selected_feeder].description);
		}
		else
		{
			$("#edit-button").hide();
			$("#remove-button").hide();
		}
	 	
	 	//~ $("#feeder_img").parent().attr('href', 'javascript:void(0);');
	 	//~ $("#feeder_img").css('cursor', 'default');
	 	//~ $("#set-feeder").prop("disabled",false);
	 	
		//$("#head_img").attr('src', '/assets/img/head/' + $(this).val() + '.png');

	 }
	/**
	*
	**/
	function setFeeder(){
	 	openWait('<i class="fa fa-circle-o-notch fa-spin"></i> <?php echo _("Configuring feeder"); ?>', '<?php echo _("Please wait"); ?>...');
	 	$.ajax({
			type: "POST",
			url: "<?php echo site_url("feeder/setFeeder") ?>/"+ $("#feeders").val(),
			dataType: 'json'
		}).done(function( data ) {
			$(".alerts-container").find('div:first-child').remove();
			$(".alerts-container").append('<div class="alert alert-success animated  fadeIn" role="alert"><i class="fa fa-check"></i> ' + _("Well done! Now your <strong>FABtotum Personal Fabricator</strong> is configured for the <strong>{0}</strong>".format(data.name) + '</div>');			
			setTimeout(function(){
					document.location.href =  '<?php echo site_url('feeder'); ?>?feeder_installed';
					location.reload();
				}, 2000);
		});
	}
	/**
	*
	**/
	function buttonAction(){
		var action = $(this).attr('data-action');
		switch(action)
		{
			case "edit":
				if(feeders.hasOwnProperty(selected_feeder))
				{
					populateFeederSettings(feeders[selected_feeder]);
				}
				$('#settingsModal').modal('show');
				break;
			case "add":
				document.getElementById("feeder-settings").reset();
				showHideInputsForOfficialFeeders('show');
				$('#settingsModal').modal('show');
				break;
			case "remove":
				removeFeederSettings();
				break;
			case "save":
				if($("#feeder-settings").valid())
					saveFeederSettings();
				break;
			case "import":
				$("#inputId").trigger('click');
				break;
			case "export":
				if($("#feeder-settings").valid())
					exportFeederSettings();
				break;
			case "factory-reset":
				factoryReset(selected_feeder);
				break;
		}
		
		return false;
	}
	/**
	*
	**/
	function getFeederSettings()
	{	
		var settings = {};
		
		$("#feeder-settings :input").each(function (index, value) {
			var name = $(this).attr('name');
			var type = $(this).attr('type');
			if(name)
			{
				settings[name] = $(this).val();
				
				if(name == "custom_gcode")
					settings[name] = settings[name].toUpperCase();
			}
		});

		return settings;
	}
	/**
	*
	**/
	function populateFeederSettings(feeder)
	{
		document.getElementById("feeder-settings").reset();
		for (var key in feeder) {
			var value = feeder[key];
			var id = "#feeder-"+key;
			$(id).val(value);
			console.log('try to', id);
		}

		/**
		* only for fabtotums official heads
		*/
		if( parseInt(feeder.factory) == 1 ){
			showHideInputsForOfficialFeeders('hide');
		}else{
			showHideInputsForOfficialFeeders('show');
		}

	}
	/**
	*
	**/
	function saveFeederSettings()
	{
		var settings = getFeederSettings();	
		var filename = settings['name'].replace(/ /g, "_").replace(/-/g, "_").toLowerCase();
		$.ajax({
			type: 'post',
			url: '<?php echo site_url('feeder/saveFeeder'); ?>/' + filename,
			data : settings,
			dataType: 'json'
		}).done(function(response) {
			console.log(response);
			fabApp.showInfoAlert('<strong>{0}</strong> saved'.format(settings.name));
			setTimeout(function(){
				location.reload();
			}, 1000);
		});
	}
	/**
	*
	**/
	function exportFeederSettings()
	{
		var settings = getFeederSettings();
		var filename = settings['name'].replace(/ /g, "_").replace(/-/g, "_").toLowerCase() + ".json";
		var content = JSON.stringify(settings, null, 2)
		var blob = new Blob([content], {type: "text/plain"});
		saveAs(blob, filename);
	}
	/**
	*
	**/
	function importFeederSettings(event)
	{
		var input = event.target;
		var reader = new FileReader();
		reader.onload = function(){
			var text = reader.result;
			
			content = jQuery.parseJSON(text);
			populateFeederSettings(content);
		}
		reader.readAsText(input.files[0]);
		return false;
	}
	/**
	*
	**/
	function removeFeederSettings()
	{
		$.SmartMessageBox({
			title: "<?php echo _("Attention");?>!",
			content: "<?php echo _("Remove <strong>{0}</strong> settings?");?>".format(feeders[selected_feeder].name),
			buttons: '[<?php echo _("No")?>][<?php echo _("Yes")?>]'
		}, function(ButtonPressed) {
			if (ButtonPressed === "<?php echo _("Yes")?>")
			{
				$.ajax({
					type: 'post',
					url: '<?php echo site_url('feeder/removeFeeder'); ?>/' + selected_feeder,
					dataType: 'json'
				}).done(function(response) {
					console.log(response);
					fabApp.showInfoAlert('<strong>{0}</strong> removed'.format(feeders[selected_feeder].name));
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
	function showHideInputsForOfficialFeeders(action)
	{
		if(action == 'show'){
			$(".url-container").show();
			$(".description-container").show();
			$("#feeder-name").removeAttr("readonly")
			$("#feeder-fw_id").removeAttr("readonly");
			$(".factory-feeder-button").hide();
			$(".custom-feeder-button").show();
		}else if(action == 'hide'){
			$(".url-container").hide();
			$(".description-container").hide();
			$("#feeder-name").attr("readonly", "readonly");
			$("#feeder-fw_id").attr("readonly", "readonly");
			$(".factory-feeder-button").show();
			$(".custom-feeder-button").hide();
		}
	}
	/**
	*
	**/
	function factoryReset()
	{
		$.SmartMessageBox({
			title: "<?php echo _("Attention");?>!",
			content: "<?php echo _("Restore factory settings for <strong>{0}</strong> ?");?>".format(feeders[selected_feeder].name),
			buttons: '[<?php echo _("No")?>][<?php echo _("Yes")?>]'
		}, function(ButtonPressed) {
			if (ButtonPressed === "<?php echo _("Yes")?>")
			{
				$.ajax({
					type: 'post',
					url: '<?php echo site_url('feeder/factoryReset'); ?>/' + selected_feeder,
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
</script>
