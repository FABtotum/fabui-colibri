<script type="text/javascript">

	$(document).ready(function(){

		getTaskMonitor();
		getTemperatures();
		getNotify();
		
		setInterval(getTaskMonitor, 1000);
		setInterval(getTemperatures, 5000);
		setInterval(getNotify, 1000);
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
</script>
