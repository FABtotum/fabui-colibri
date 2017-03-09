<?php
/**
 * 
 * @author FABteam
 * @version 1.0
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>

<script type="text/javascript">

	var temperaturesGraph;
	var mode = 'e';
	var showExtruderLines = true;

	var isRunning = <?php echo $runningTask ? 'true' : 'false' ?>;

	window.manageMonitor = function(data){
		handleTaskStatus(data.task.status);
		handleValues(data.pid_tune);
	};

	$(document).ready(function() {
		initGraph();
		if(isRunning) initRunningTaskPage();
		disableButton('#save');
		$("#autotune").on('click', handleAutotuneAction);
		$("#save").on('click', savePIDValues);
	});

	/** init temperatures graph */
	function initGraph()
	{
		temperaturesGraph = $.plot("#temperatures-chart", getPlotTemperatures(), {
			series : {
				lines : {
					show : true,
					lineWidth : 1,
					fill : true,
					fillColor : {
						colors : [{
							opacity : 0.1
						}, {
							opacity : 0.15
						}]
					}
				}
			},
			xaxis: {
				mode: "time",
				show: true,
				tickFormatter: function (val, axis) {
					var d = new Date(val);
					return d.getHours() + ":" + d.getMinutes();
				},
				 timeformat: "%Y/%m/%d",
				 zoomRange: [1,100]
			},
			yaxis: {
				min: 0,
				max: <?php echo ($installed_head['max_temp'] + 10) ?>,
				tickFormatter: function (v, axis) {
					return v + "&deg;C";
				},
			},
			tooltip : true,
			tooltipOpts : {
				content : "%s: %y &deg;C",
				defaultTheme : false
			},
			legend: {
				show : true
			},
			grid: {
				hoverable : true,
				clickable : true,
				borderWidth : 0,
				borderColor : "#efefef",
				tickColor :  "#efefef"
				
			},
			zoom:{
				interactive: false
			}
			
		});
		setInterval(updateGraph, 1000);
	}
	/**
	 * get plots for temperatures graph
	 */
	function getPlotTemperatures()
	{
		var seriesExtTemp   = [];
		var seriesExtTarget = [];
		var data            = new Array();
		$.each( temperaturesPlot.extruder.temp, function( key, plot ) {
  			seriesExtTemp.push([plot.time, plot.value]);
		});
		$.each( temperaturesPlot.extruder.target, function( key, plot ) {
  			seriesExtTarget.push([plot.time, plot.value]);
		});
		//extruder actual line
		if(showExtruderLines){
			data.push({
				data: seriesExtTemp,
				lines: { show: true, fill: true, lineWidth:0.5},
				label: "<?php echo _("Nozzle Temp");?>",
				color: "#FF0000",
				points: {"show" : false}
			});
			//extruder target line
			data.push({
				data: seriesExtTarget,
				lines: { show: true, fill: false, lineWidth:1 },
				label: "<?php echo _("Nozzle Target");?>",
				color: "#33ccff",
				points: {"show" : false}
			});
		}
		return data;
	}
	/**
	 * update graph
	 */
	function updateGraph()
	{
		var data = getPlotTemperatures();	
		if(typeof temperaturesGraph !== 'undefined' ){
			temperaturesGraph.setData(data);
			temperaturesGraph.draw();
			temperaturesGraph.setupGrid();
		}

		var temperature = temperaturesPlot.extruder.temp[temperaturesPlot.extruder.temp.length - 1];
		$(".spd-temperature").html(parseInt(temperature.value));
	}
	/**
	*
	**/
	function handleAutotuneAction()
	{
		var action = $(this).attr('data-action');
		if(action == 'start'){
			startPidTune();
		}else if(action == 'abort'){
			doAbort();
		}
	}
	/**
	*
	**/
	function startPidTune()
	{
		disableButton('#autotune');
		var data = {
			temperature : $("#temperature_target").val(),
			cycle       : $("#cycle").val(),
		}
		 $.ajax({
             type: "POST",
             url: "<?php echo site_url("nozzle/startPidTune") ?>",
             data: data,
             dataType: "json"
         }).done(function( data ) {
        	fabApp.resetTemperaturesPlot(1);
        	initRunningTaskPage();      	 
         });
	}
	/**
	* 
	**/
	function  initRunningTaskPage()
	{
		console.log("initRunningTaskPage");
		fabApp.freezeMenu('pidtune');
		fabApp.disableTopBarControls();
		disableInputs();
		getTaskMonitor();
		$("#autotune").html("<i class='fa fa-stop'></i> <?php echo _("Abort"); ?>");
		$("#autotune").attr("data-action", "abort");
		enableButton('#autotune');
	}
	/**
	*
	**/
	function handleTaskStatus(status)
	{
		switch(status)
		{
			case 'running':
				$("#autotune").html("<i class='fa fa-stop'></i> <?php echo _("Abort"); ?>");
				$("#autotune").attr("data-action", "abort");
				disableButton('#save');
				break;
			case 'completed':
				completedTask();
				break;
		}
	}
	/**
	*
	**/
	function getTaskMonitor()
	{
		$.get('/temp/task_monitor.json'+ '?' + jQuery.now(), function(data, status){
			manageMonitor(data);
		});
	}
	/**
	*
	**/
	function handleValues(object)
	{
		if(typeof object != "undefined"){
			$("#kp").val(object.P);
			$("#ki").val(object.I);
			$("#kd").val(object.D);
			$("#temperature_target").val(object.target);
		}
	}
	/**
	*
	**/
	function completedTask()
	{
		number_tasks = number_tasks - 1;
		fabApp.updateNotificationBadge();
		$("#autotune").html("<i class='fa fa-play'></i> <?php echo _("Start"); ?>");
		$("#autotune").attr("data-action", "start");
		enableButton('#save');
		fabApp.enableTopBarControls();
		enableInputs();
		fabApp.unFreezeMenu();
		fabApp.showInfoAlert("<?php echo _("Pid tune completed") ?>");
	}
	/**
	*
	**/
	function savePIDValues()
	{
		openWait('<i class="fa fa-spinner fa-spin "></i> <?php echo _("Saving and applying new values"); ?>', "<?php echo _("Please wait"); ?>..", false );
		var data = {
			i: $("#ki").val(),
			p: $("#kp").val(),
			d: $("#kd").val(),
		}
		$.ajax({
             type: "POST",
             url: "<?php echo site_url("nozzle/savePIDValues") ?>",
             data: data,
             dataType: "json"
         }).done(function( data ) {
			closeWait();
            console.log(data);
                	 
         });
	}
	/**
	*
	**/
	function doAbort()
	{
		$.ajax({
            type: "POST",
            url: "<?php echo site_url("control/taskAction/abort") ?>",
            dataType: "json"
        }).done(function( data ) {
           console.log(data);
        });
	}
	/**
	*
	**/
	function disableInputs()
	{
		$("#temperature_target").attr('readonly', 'readonly');
		$("#cycle").attr('readonly', 'readonly');
	}
	/**
	*
	**/
	function enableInputs()
	{
		$("#temperature_target").removeAttr('readonly');
		$("#cycle").removeAttr('readonly');
	}

</script>
