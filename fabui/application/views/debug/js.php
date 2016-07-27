<script type="text/javascript">

	var task_monitor_interval;
	var temperatures_interval;
	var notify_interval;
	var trace_interval;

	var traceData = '';
	
	$(document).ready(function(){

		getTaskMonitor();
		
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
</script>