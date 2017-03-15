<script type="text/javascript">

	var address = new Array();
	var intervals = new Array();
	<?php $count = 1; ?>
	<?php foreach($units as $unit):?>
	 address[<?php echo $count ?>] = '<?php echo $unit; ?>';
	<?php $count++;?>
	<?php endforeach;?>
	var intervalTimer = 5000;
	
	$(document).ready(function(){
		
		$(".tab-content").height($( document ).height());
		$(".google_maps").height(($( document ).height()-200));
		checkUnits();
		
	});
	/**
	*
	**/
	function checkUnits()
	{
		var iframes =  $(".unit-container");
		iframes.each(function(){
			var iframe = $(this);
			var src = iframe.attr('src');
			$.get(src, function(data){
				showIframe(iframe);
			}).fail(function() {
				hideIframe(iframe);
			});
		});
	}
	/**
	*
	**/
	function showIframe(element)
	{
		var id = element.attr('id');
		element.removeClass("hidden");
		$("#" + id +"-tab-link").find('i').removeClass("fa-ban").addClass("fa-border fa-play fa-rotate-90");
		monitor(id.replace("unit-", ""));
		startIntervalCheck(id.replace("unit-", ""));
	}
	/**
	*
	**/
	function hideIframe(element)
	{
		var id = element.attr('id');
		$("#" + id +"-tab-link").find('i').removeClass("fa-border fa-play fa-rotate-90").addClass("fa-ban");
		element.addClass("hidden");
	}
	/**
	*
	**/
	function startIntervalCheck(id)
	{
		console.log(id);
		
		intervals[id] = setInterval(function() {
			monitor(id);
		}, intervalTimer);
	}
	/**
	*
	**/
	function monitor(id)
	{
		console.log('http://' + address[id] + '/temp/task_monitor.json?' + jQuery.now());
		$.get('http://' + address[id] + '/temp/task_monitor.json?' + jQuery.now(), function(data){ 
			handleDataMonitor(id, data);
		});
		
	}
	/**
	*
	**/
	function handleDataMonitor(id, data)
	{
		console.log("UNIT " + id);
		console.log(data);
		$("#json-unit-" + id).JSONView(data);
	}
</script>
