<script type="text/javascript">




	var address = new Array();
	var intervals = new Array();
	<?php $count = 1; ?>
	<?php foreach($units as $unit):?>
	 address[<?php echo $count ?>] = '<?php echo $unit; ?>';
	<?php $count++;?>
	<?php endforeach;?>
	var intervalTimer = 2000;
	
	$(document).ready(function(){

		var document_height = $( document ).height();
		console.log(document_height);
		
		$(".tab-content").height((document_height-200));
		$(".google_maps").height((document_height-200));
		checkUnits();
		$(".view-unit").on('click', viewUnit);
		$(".abort-unit").on('click', abortUnit);
		$(".play-resume-unit").on('click', playResume);
		
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
		$.get('http://' + address[id] + '/temp/task_monitor.json?' + jQuery.now(), function(data){ 
			handleDataMonitor(id, data);
		});
		
	}
	/**
	*
	**/
	function handleDataMonitor(id, data)
	{
		var html = "";

		switch(data.task.status)
		{
			case 'running':
				var status_label = '<p>Task running <span class="pull-right">' + parseInt(data.task.percent) + '%</span></p>' ;
				var show_progress = true;
				$("#play-resume-unit-" + id).html('<i class="fa fa-pause"></i> Pause')
				$("#play-resume-unit-" + id).attr("data-action", "pause");
				enableButton($("#play-resume-unit-" + id));
				
				break;
			case 'paused':
				var status_label = '<p>Task paused <span class="pull-right">' + parseInt(data.task.percent) + '%</span></p>' ;
				var show_progress = true;
				$("#play-resume-unit-" + id).html('<i class="fa fa-play"></i> Resume');
				$("#play-resume-unit-" + id).attr("data-action", "resume");
				enableButton($("#play-resume-unit-" + id));
				break;
			case 'aborting':
				var status_label = '<p>Aborting task </p>' ;
				var show_progress = false;
				break;
			default:
				disableButton($("#play-resume-unit-" + id));
				
		}
		html += status_label;
		if(show_progress){
			html += ''+
			'<div class="progress progress-sm progress-striped active">' + 
				'<div class="progress-bar " role="progressbar" style="width: ' + parseInt(data.task.percent) +'%"></div>' +
			'</div>';
		}
		
		$("#unit-" + id + "-content").html(html);
	}
	/**
	*
	*/
	function viewUnit()
	{
		var unit = $(this).attr("data-unit");
		$("#unit-" + unit + "-tab-link").trigger("click");
	}
	/**
	*
	*/
	function abortUnit()
	{
		var unit = $(this).attr("data-unit");
		$.get('http://' + address[unit] + '/fabui/control/taskAction/abort', function(data){ 
			
		});
	}
	/**
	*
	**/
	function playResume()
	{
		var unit = $(this).attr("data-unit");
		var action = $(this).attr("data-action");

		$.get('http://' + address[unit] + '/fabui/control/taskAction/' + action, function(data){ 
			
		});
	}
	
</script>
