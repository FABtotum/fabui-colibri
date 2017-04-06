<script type="text/javascript">

	var task_monitor_interval;
	var temperatures_interval;
	var notify_interval;
	var trace_interval;

	var traceData = '';

	 var settings_json = restore_settings = <?php echo $settings; ?>;
	 var opt = { 
        change: function(data) {settings_json = data;},
        propertyclick: function(path) { /* called when a property is clicked with the JS path to that property */ }
	 };
	    /* opt.propertyElement = '<textarea>'; */ // element of the property field, <input> is default
	    /* opt.valueElement = '<textarea>'; */  // element of the value field, <input> is default
	 
	$(document).ready(function(){

		$('#settings-json').jsonEditor(settings_json, opt);
		getTaskMonitor();

		$("#save-json-settings").on('click', askSettingsJson);
		$("#restore-json-settings").on('click', restoreSettingsJson);

		$(".json-rpc").on("click", jsonRPC);
		
		task_monitor_interval = setInterval(getTaskMonitor, 1000);
		
		$(".nav-tabs > li > a").on('click', function(){ 

			clearAllIntervals();
			switch($(this).attr('href')){
				case '#monitor-tab':
					task_monitor_interval = setInterval(getTaskMonitor, 1000);
					break;
				case '#temperatures-tab':
					getTemperatures();
					temperatures_interval = setInterval(getTemperatures, 5000);
					break;
				case '#notify-tab':
					notify_interval = setInterval(getNotify, 1000);
					break;
				case '#trace-tab':
					getTrace();
					trace_interval = setInterval(getTrace, 1000);
					break;
			}
		});
	});

	function getJson(url, element){
		$.get(url + '?' + jQuery.now(), function(data, status){
			$(element).JSONView(data);
		});
	}
	function getTaskMonitor()
	{
		getJson('/temp/task_monitor.json', '#task_monitor');
	}
	function getTemperatures()
	{
		getJson('/temp/temperature.json', '#temperatures');
	}
	function getNotify()
	{
		getJson('/temp/notify.json', '#notify');
	}
	function getTrace()
	{
		$.get('/temp/trace?' + jQuery.now(), function(data, status){
			if(traceData != data){
				var new_content = data.replace(traceData, '');
				$("#trace").append('<hr><i class="fa fa-plus"></i> ' + new_content);
				$("#trace").scrollTop(1E10);
				traceData = data;			
			}
		});
	}
	
	function clearAllIntervals()
	{
		clearInterval(task_monitor_interval);
		clearInterval(temperatures_interval);
		clearInterval(notify_interval);
		clearInterval(trace_interval);
	}
	/**
	*
	**/
	function jsonRPC()
	{
		var method = $(this).attr('data-action');
		$("#json-rpc-result").html("");
		$.ajax({
			  type: "POST",
			  url: "<?php echo site_url('debug/jsonrpc/'); ?>/" + method,
			  dataType: 'json',
		}).done(function( response ) {
			$("#json-rpc-result").JSONView(response);
		});
	}
	/**
	*
	**/
	function askSettingsJson()
	{	
		$.SmartMessageBox({
			title: "",
			content : "Are you sure?",
			buttons: "[Yes][Cancel]",
		}, function(ButtonPressed, Option) {
			if(ButtonPressed == "Cancel"){ //cancel
				return;
			}
			if (ButtonPressed == "Yes") { //logout
				
				saveSettingsJson(settings_json);
			}
		});
		
	}
	/**
	*
	**/
	function saveSettingsJson(json)
	{
		openWait("<i class='fa fa-spin fa-spinner'></i> <?php echo _("Applying new settings"); ?>", "<?php echo _("Please wait"); ?>...", false );
		$.ajax({
			  type: "POST",
			  url: "<?php echo site_url('debug/saveSettingsJson/'); ?>",
			  data: {json: json},
			  dataType: 'json',
		}).done(function( response ) {
			closeWait();
		});
	}
	/**
	*
	**/
	function restoreSettingsJson()
	{
		settings_json = restore_settings;
		saveSettingsJson(restore_settings);
	}
</script>
