<?php
/**
 * 
 * @author Krios Mane
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */

/* variable initialization */

if(!isset($extruder_min)) 	$extruder_min = 0;
if(!isset($extruder_max)) 	$extruder_max = 250;
if(!isset($bed_min)) 		$bed_min = 10;
if(!isset($bed_max)) 		$bed_max = 100;

?>
<script type="text/javascript">
	
	var timerInterval;
	var elapsedTime = 0;
	var estimatedTime = 0;
	//sliders
	<?php if($type == "print"): ?>
	var extruderSlider;
	var bedSlider;
	var flowRateSlider;
	<?php endif; ?>
	<?php if($type == "mill"): ?>
	var rpmSlider;
	<?php endif; ?>
	var speedSlider;
	var fanSlider;
	var zOverride = 0;
	//graph
	var temperaturesGraph;
	var showExtActual = true;
	var showExtTarget = false;
	var showBedActual = true;
	var showBedTarget = false;
	//slider states
	var isSpeedSliderBusy       = false;
	var wasSpeedSliderMoved     = false;
	var isFlowRateSliderBusy    = false;
	var wasFlowRateSliderMoved  = false;
	var isFanSliderBusy         = false;
	var wasFanSliderMoved       = false;
	var isRpmSliderBusy         = false;
	var wasRpmSliderMoved       = false;
	//
	var soft_extruder_min  = 175;
	
	$(document).ready(function() {
		initSliders();
		<?php if($runningTask == true): ?>
		initRunningTaskPage();
		<?php endif; ?>
		$(".action").on('click', doAction);
		$(".graph-line-selector").on('click', setGraphLines);
	});
	
	/**
	 * init sliders
	 */
	function initSliders()
	{	
		<?php if($type == 'print'): ?>
		//extruder target
		if(typeof extruderSlider == "undefined")
			noUiSlider.create(document.getElementById('create-ext-target-slider'), {
				start: typeof (Storage) !== "undefined" ? localStorage.getItem("nozzle_temp_target") : <?php echo $extruder_min; ?>,
				connect: "lower",
				range: {'min': <?php echo isset($extruder_min) ? $extruder_min : 0; ?>, 'max' : <?php echo $extruder_max; ?>},
				pips: {
					mode: 'values',
					values: [0, 175, 250],
					density: 4,
					format: wNumb({
						postfix: '&deg;'
					})
				}
			});
		//bed target slider
		if(typeof bedSlider == "undefined")
			noUiSlider.create(document.getElementById('create-bed-target-slider'), {
				start: typeof (Storage) !== "undefined" ? localStorage.getItem("bed_temp_target") : 0,
				connect: "lower",
				range: {'min': <?php echo $bed_min; ?>, 'max' : <?php echo $bed_max; ?>},
				pips: {
					mode: 'positions',
					values: [0,25,50,75,100],
					density: 5,
					format: wNumb({
						postfix: '&deg;'
					})
				}
			});
		//flow-rate slider
		if(typeof flowRateSlider == "undefined")
			noUiSlider.create(document.getElementById('create-flow-rate-slider'), { 
				start: 100,
				connect: "lower",
				range: {'min': 0, 'max' : 500},
				pips: {
					mode: 'positions',
					values: [0,20,40,60,80,100],
					density: 10,
					format: wNumb({})
				}
			});
		//fan slider
		if(typeof fanSlider == "undefined")
			noUiSlider.create(document.getElementById('create-fan-slider'), {
				start: 0,
				connect: "lower",
				range: {'min': 0, 'max' : 100},
				pips: {
					mode: 'positions',
					values: [0,50,100],
					density: 10,
					format: wNumb({})
				}
			});
		
		extruderSlider = document.getElementById('create-ext-target-slider');
		bedSlider      = document.getElementById('create-bed-target-slider');
		flowRateSlider = document.getElementById('create-flow-rate-slider');
		fanSlider      = document.getElementById('create-fan-slider');
		
		//sliders events
		
		//extruder
		extruderSlider.noUiSlider.on('slide',  function(e){
			onSlide('extruder-target', e);
		});
		extruderSlider.noUiSlider.on('change', function(e){
			onChange('extruder-target', e);
		});
		//bed
		bedSlider.noUiSlider.on('slide',  function(e){
			onSlide('bed-target', e);
		});
		bedSlider.noUiSlider.on('change', function(e){
			onChange('bed-target', e);
		});
		//flow rate
		flowRateSlider.noUiSlider.on('change', function(e){
			onChange('flow-rate', e);
		});
		flowRateSlider.noUiSlider.on('slide', function(e){
			onSlide('flow-rate', e);
			wasFlowRateSliderMoved = true;
		});
		flowRateSlider.noUiSlider.on('end', function(e){
			isFlowRateSliderBusy = false;
		});
		flowRateSlider.noUiSlider.on('start', function(e){
			isFlowRateSliderBusy = true;
			wasFlowRateSliderMoved = true;
		});
		//fan
		fanSlider.noUiSlider.on('change', function(e){
			onChange('fan', e);
		});
		fanSlider.noUiSlider.on('slide', function(e){
			onSlide('fan', e);
			wasFanSliderMoved = true;
		});
		fanSlider.noUiSlider.on('end', function(e){
			isFanSliderBusy = false;
		});
		fanSlider.noUiSlider.on('start', function(e){
			isFanSliderBusy = true;
			wasFanSliderMoved = true;
		});
		<?php endif; ?>

		<?php if($type == "mill" || $type == "laser"): ?>
		if(typeof rpmSlider == "undefined")
		{
			noUiSlider.create(document.getElementById('create-rpm-slider'), {
				start: <?php echo isset($rpm_min) ? $rpm_min : 6000; ?>,
				connect: "lower",
				range: {'min': <?php echo isset($rpm_min) ? $rpm_min : 6000; ?>, 'max' : <?php echo isset($rpm_max) ? $rpm_max : 14000; ?>},
				pips: {
					mode: 'positions',
					values: [0,20,40,60,80,100],
					density: 10,
					format: wNumb({})
				}
			});
			rpmSlider = document.getElementById('create-rpm-slider');
		}
		
		rpmSlider.noUiSlider.on('change', function(e){
			onChange('rpm', e);
		});
		rpmSlider.noUiSlider.on('slide', function(e){
			onSlide('rpm', e);
			wasRpmSliderMoved = true;
		});
		rpmSlider.noUiSlider.on('end', function(e){
			isRpmSliderBusy = false;
		});
		rpmSlider.noUiSlider.on('start', function(e){
			isRpmSliderBusy = true;
			wasRpmSliderMoved = true;
		});
		<?php endif; ?>
		
		//speed slider
		if(typeof speedSlider == "undefined")
		{
			noUiSlider.create(document.getElementById('create-speed-slider'), {
				start: 100,
				connect: "lower",
				range: {'min': 0, 'max' : 500},
				pips: {
					mode: 'positions',
					values: [0,20,40,60,80,100],
					density: 10,
					format: wNumb({})
				}
			});
			speedSlider = document.getElementById('create-speed-slider');
		}
		//speed slider
		speedSlider.noUiSlider.on('change', function(e){
			onChange('speed', e);
		});
		speedSlider.noUiSlider.on('slide', function(e){
			onSlide('speed', e);
			wasSpeedSliderMoved = true;
		});
		speedSlider.noUiSlider.on('end', function(e){
			isSpeedSliderBusy = false;
		});
		speedSlider.noUiSlider.on('start', function(e){
			isSpeedSliderBusy = true;
			wasSpeedSliderMoved = true;
		});
	}
	
	/**
	 * event on slider slide
	 */
	function onSlide(element, value)
	{
		
		switch(element){
			case 'extruder-target':
				var extruder_target = parseInt(value);
				if(extruder_target < soft_extruder_min) extruder_target = soft_extruder_min;
				$(".slider-extruder-target").html(extruder_target);
				break;
			case 'bed-target':
				$(".slider-bed-target").html(parseInt(value));
				break;
			case 'flow-rate':
				$('.slider-task-flow-rate').html(parseInt(value));
				break;
			case 'fan':
				$('.slider-task-fan').html(parseInt(value));
				break;
			case 'speed':
				$('.slider-task-speed').html(parseInt(value));
				break;
			case 'rpm':
				$('.slider-task-rpm').html(parseInt(value));
				break;
			
		}
	} 
	/**
	 * event on slider set
	 */
	function onChange(element, value)
	{
		switch(element){
			case 'extruder-target':
				var extruder_target = parseInt(value[0]);
				if(extruder_target <= soft_extruder_min) {
					 extruder_target = soft_extruder_min;
					 extruderSlider.noUiSlider.set(soft_extruder_min);
				}
				fabApp.serial("setExtruderTemp",extruder_target);
				showActionAlert("Extruder temperature set to "+extruder_target+'&deg;');
				break;
			case 'bed-target':
				fabApp.serial("setBedTemp",parseInt(value[0]));
				showActionAlert("Bed temperature set to "+parseInt(value[0])+'&deg;');
				break;
			case 'flow-rate':
				sendActionRequest('flowRate', parseInt(value[0]));
				break;
			case 'fan':
				sendActionRequest('fan', parseInt(value[0]));
				break;
			case 'speed':
				sendActionRequest('speed', parseInt(value[0]));
				break;
			case 'rpm':
				<?php if( isset($set_rpm_function) ): ?>
					<?php echo $set_rpm_function ?>('rpm', parseInt(value[0]));
				<?php else: ?>
				sendActionRequest('rpm', parseInt(value[0]));
				<?php endif; ?>
				break;
		}
	}
	
	/**
	 * set initial target for temperatures sliders and temperatures labels
	 */
	function setTemperaturesSlidersValue(ext_temp_target = 0, bed_temp_target = 0)
	{	
		$.get(temperatures_file_url + '?' + jQuery.now(), function(data){

			/**
			* extruder
			*/
			if(data.ext_temp.constructor === Array){
				ext_temp = data.ext_temp[data.ext_temp.length - 1];
			}
			if(data.ext_temp_target.constructor === Array){
				if(!ext_temp_target)
					ext_temp_target = data.ext_temp_target[data.ext_temp_target.length - 1];
			}
			$(".extruder-temp").html(parseFloat(ext_temp).toFixed(0));
			$(".extruder-target").html(parseFloat(ext_temp_target).toFixed(0));
			/**
			* bed
			*/
			if(data.bed_temp_target.constructor === Array){
				if(!bed_temp_target)
					bed_temp_target = data.bed_temp_target[data.bed_temp_target.length - 1];
			}
			if(data.bed_temp.constructor === Array){
				bed_temp = data.bed_temp[data.bed_temp.length - 1];
			}
			$(".bed-temp").html(parseFloat(bed_temp).toFixed(0));
			$(".bed-target").html(parseFloat(bed_temp_target).toFixed(0));

			/***
			* init sliders values
			*/
			$(".slider-extruder-target").html(parseFloat(ext_temp_target).toFixed(0));
			$(".slider-bed-target").html(parseFloat(bed_temp_target).toFixed(0));

			if(typeof extruderSlider !== 'undefined'){
				extruderSlider.noUiSlider.set(ext_temp_target);
			}
			if(typeof bedSlider !== 'undefined'){
				bedSlider.noUiSlider.set(bed_temp_target);
			}
		});
	}
	
	/**
	 * init graph
	 */
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
				 timeformat: "%Y/%m/%d"
			},
			yaxis: { 
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
		});

		//init updateGraph interval
		setInterval(updateGraph, 1000);
	}
	
	/**
	*	show or hide lines on graph
	*/
	function setGraphLines(event)
	{	
		var name = $(this).attr('name');
		switch(name){
			case 'ext-actual':
				showExtActual = $(this).is(":checked");
				break;
			case 'ext-target':
				showExtTarget = $(this).is(":checked");
				break;
			case 'bed-actual':
				showBedActual = $(this).is(":checked");
				break;
			case 'bed-target':
				showBedTarget = $(this).is(":checked");
				break;
		}
		updateGraph();
		event.stopPropagation();  
	}
	
	/**
	 * get plots for temperatures graph
	 */
	function getPlotTemperatures()
	{
		var seriesExtTemp   = [];
		var seriesExtTarget = [];
		var seriesBedTemp   = [];
		var seriesBedTarget = [];
		var data            = new Array();
		
		$.each( temperaturesPlot.extruder.temp, function( key, plot ) {
  			seriesExtTemp.push([plot.time, plot.value]);
		});
		$.each( temperaturesPlot.extruder.target, function( key, plot ) {
  			seriesExtTarget.push([plot.time, plot.value]);
		});
		$.each( temperaturesPlot.bed.temp, function( key, plot ) {
  			seriesBedTemp.push([plot.time, plot.value]);
		});
		$.each( temperaturesPlot.bed.target, function( key, plot ) {
  			seriesBedTarget.push([plot.time, plot.value]);
		});
		
		//extruder actual line
		if(showExtActual)
			data.push({
				data: seriesExtTemp,
	      		lines: { show: true, fill: true },
	     	 	label: "Ext temp",
	     	 	color: "#FF0000",
	     	 	points: {"show" : false}
			});
			
		//extruder target line
		if(showExtTarget)
			data.push({
				data: seriesExtTarget,
	      		lines: { show: true, fill: false, lineWidth:1 },
	     	 	label: "Ext target",
	     	 	color: "#ff9933",
	     	 	points: {"show" : false}
			});
			
		//bed actual temp line
		if(showBedActual)
			data.push({
				data: seriesBedTemp,
	      		lines: { show: true, fill: true },
	     	 	label: "Bed temp",
	     	 	color: "#3276B1"
			});
			
		//bed target temp line
		if(showBedTarget)
			data.push({
				data: seriesBedTarget,
				lines: { show: true, fill: false, lineWidth:1 },
	     	 	label: "Bed target",
	     	 	color: "#33ccff"
			});
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
	}
	
	/**
	 * 
	 */
	function sendActionRequest(action, value)
	{
		value = value || '';
		var message;
		
		var taskType = "<?php echo isset($type_label) ? $type_label : ucfirst($type); ?>";
		
		$.ajax({
			type: 'post',
			url: '<?php echo site_url('control/taskAction/') ?>/' + action + '/' + value,
			dataType: 'json'
		}).done(function(response) {
			switch(action){
				case 'pause':
					message=taskType+" paused";
					break;
				case 'resume':
					message=taskType+" resumed";
					break;
				case 'speed':
					message="Speed override changed to: " + value;
					break;
				case 'flowRate':
					message="Flow Rate override changed to: " + value;
					break;
				case 'fan':
					message="Fan override changed to: " + value;
					break;
				case 'zHeight':
					if(value.charAt(0) == '+') message="Z height increased";
					else message="Z height decreased";
					break;
				case 'rpm':
					message="<?php echo isset($rpm_message) ? $rpm_message : "RPM speed set to:"; ?> " + value;
					break;
				default:
					message="Unknown action: "+ action;
			}
			showActionAlert(message);
		});
	}
	
	/**
	* show error message 
	*/
	function showErrorAlert(message)
	{
		$.smallBox({
			title : "Warning",
			content : message,
			color : "#C46A69",
			timeout: 10000,
			icon : "fa fa-warning"
		});
	}
	
	/**
	*
	*/
	function showActionAlert(message)
	{
		$.smallBox({
			title : "Info",
			content : message,
			color : "#5384AF",
			timeout: 3000,
			icon : "fa fa-check bounce animated"
		});
	}
	
	/**
	 * exec action 
	 */
	function doAction(e)
	{
		var element = $(this);
		action = element.attr('data-action');
		switch(action){
			case 'abort':
				abort();
				break;
			case 'pause':
			case 'resume':
				pauseResume(action, element);
				break;
			case 'zHeight':
				var sign = element.attr('data-attribute');
				var overrideToAdd = parseFloat($("#zHeight").val()).toFixed(2);
				var actualOverride = parseFloat($(".z-height").html()).toFixed(2);
				var operation = '(' + actualOverride +')' + sign + '(' + overrideToAdd + ')';
				$(".z-height").html(parseFloat(eval(operation)).toFixed(2));
				sendActionRequest('zHeight', sign+$("#zHeight").val());
				break;
		}
	}
	/**
	 * 
	 */
	function abort()
	{
		openWait('<i class="fa fa-spinner fa-spin "></i> Aborting print', 'Please wait..', false);
		$.ajax({
			type: 'post',
			url: '<?php echo site_url('control/taskAction/abort') ?>',
			dataType: 'json'
		}).done(function(response) {
		});
	}
	/**
	* handle called when task is aborted
	*/
	function aborted()
	{
		openWait('<i class="fa fa-check "></i> Print aborted', 'Reloading page...', false);
		setTimeout(function(){
			location.reload();
		}, 5000);
	}
	
	/**
	 * 
	 */
	function pauseResume(action, element)
	{
		var taskType = "<?php echo isset($type_label) ? $type_label : ucfirst($type); ?>";
		if(action == 'pause') {
			element.attr('data-action', 'resume');
			element.html('<i class="fa fa-play"></i> Resume '+taskType);
		}else if(action == 'resume'){
			element.attr('data-action', 'pause');
			element.html('<i class="fa fa-pause"></i> Pause '+taskType);
		}
		sendActionRequest(action);
	}
	
	/**
	 * 
	 */
	function initRunningTaskPage()
	{
		if (typeof(Storage) !== "undefined"){
			if(localStorage.getItem("temperaturesPlot") !== null){			
				temperaturesPlot =  JSON.parse(localStorage.getItem("temperaturesPlot"));
			}
		}
		
		fabApp.freezeMenu('<?php echo $type ?>');
		freezeUI();
		getTrace();
		
		
		<?php if($type=="print"): ?>
		initGraph();
		<?php endif; ?>
		traceMonitor();
		getTaskMonitor(true)
	}
	
	/**
	 * get task monitor json
	 */
	function getTaskMonitor(firstCall)
	{
		$.get('/temp/task_monitor.json'+ '?' + jQuery.now(), function(data, status){
			manageMonitor(data);
			if(firstCall) {
				handleTaskStatus(data.task.status, true);
				setTemperaturesSlidersValue();
				updateSpeed(data.override.speed);
				updateFlowRate(data.override.flow_rate);
				updateFan(data.override.fan);
				updateZOverride(data.override.z_override);
				elapsedTime = parseInt(data.task.duration);
				estimatedTime = parseInt(data.task.estimated_time);
				
				timerInterval = setInterval(timer, 1000);
				setInterval(jsonMonitor, 1000);
			}
		});
	}
	
	/**
	 * update timers
	 */
	function updateTimers(started, duration, estimated)
	{
		estimatedTime = parseInt(estimated);
	}
	
	/**
	 * 
	 */
	function timer()
	{
		elapsedTime++;
		remainingTime = estimatedTime - elapsedTime;
		$(".elapsed-time").html(transformSeconds(elapsedTime));
		if(estimatedTime == 0)
			$(".estimated-time-left").html('Waiting for first move...');
		else
			$(".estimated-time-left").html(transformSeconds(remainingTime));
	}
	
	if(typeof manageMonitor != 'function'){
		window.manageMonitor = function(data){
			handleTaskStatus(data.task.status);
			updateProgress(data.task.percent);
			updateSpeed(data.override.speed);
			<?php if($type == "print"): ?>
			updateFlowRate(data.override.flow_rate);
			updateFan(data.override.fan);
			<?php endif; ?>
			<?php if($type == "mill" || $type == "laser"): ?>
			updateRPM(data.override.rpm);
			<?php endif; ?>
			updateTimers(data.task.started_time, data.task.duration, data.task.estimated_time);
		};
	}
	
	/**
	 *  monitor interval if websocket is not available
	 */
	function jsonMonitor()
	{
		if(!socket_connected) getTaskMonitor(false);
	}
	/**
	 *  trace interval if websocket is not available
	 */
	function traceMonitor()
	{
		if(!socket_connected) getTrace();
	}
	/**
	* get trace
	*/
	function getTrace()
	{
		$.get('/temp/trace'+ '?' + jQuery.now(), function(data, status){
			fabApp.handleTrace(data);
		});
	}
	
	/**
	 * handle task status
	 */
	function handleTaskStatus(status, firstCall)
	{
		var taskType = "<?php echo isset($type_label) ? $type_label : ucfirst($type); ?>";
		switch(status){
			case 'paused':
				if(firstCall){
					var element = $(".isPaused-button");
					element.html('<i class="fa fa-play"></i> Resume '+taskType);
					element.attr('data-action', 'resume');
				}
				break;
			case 'started':
				if(firstCall){
					var element = $(".isPaused-button");
					element.html('<i class="fa fa-pause"></i> Pause '+taskType);
					element.attr('data-action', 'pause');
				}
				break;
			case 'aborting':
				aborting();
			case 'aborted':
				aborted();
				break;
			case 'completing':
				completingTask();
				break;
			case 'completed':
				completeTask();
				break;
		}
	}
	
	/**
	*
	*/
	function aborting()
	{
		openWait('<i class="fa fa-spinner fa-spin "></i> Aborting print', 'Please wait..', false);
	}
	
	/**
	* handle "completing" status
	*/
	function completingTask()
	{
		openWait('<i class="fa fa-spinner fa-spin "></i> Completing <?php echo ucfirst($type) ?>', 'Please wait...\r\nMoving to safe zone', false);
	}
	/**
	* complete task
	*/
	function completeTask()
	{	
		openWait('<i class="fa fa-check "></i> <?php echo ucfirst($type) ?> completed ! ', null, false);

		setTimeout(function(){
			closeWait();
			gotoWizardFinish();
			fabApp.unFreezeMenu();
			unFreezeUI();
			clearInterval(timerInterval);
			elapsedTime = 0;
			estimatedTime = 0;
		}, 3000);
		
		if(zOverride != 0)
			$('#save-z-height-section').slideDown();
	}
	
	/**
	 * update progress infos
	 */
	function updateProgress(value)
	{
		$(".task-progress").html(parseInt(value) + " %");
		$("#task-progress-bar").attr("style", "width:" +value +"%;");
	}
	/**
	 * update flow rate infos
	 */
	function updateFlowRate(value)
	{
		$(".task-flow-rate").html(parseInt(value));
		$("#task-flow-rate-bar").attr("style", "width:" + ((value/500)*100) +"%;");
		if(!isFlowRateSliderBusy && !wasFlowRateSliderMoved){
			$('.slider-task-flow-rate').html(parseInt(value));
			if(typeof flowRateSlider !== 'undefined'){
				flowRateSlider.noUiSlider.set(value);
			}
		}
	}
	/**
	 * update fan infos
	 */
	function updateFan(value)
	{
		$(".task-fan").html(parseInt((value/255)*100));
		$("#task-fan-bar").attr("style", "width:" +((value/255)*100) +"%;");
		if(!isFanSliderBusy && !wasFanSliderMoved){
			value = ((value/255)*100);
			$('.slider-task-fan').html(parseInt(value));
			if(typeof fanSlider !== 'undefined'){
				fanSlider.noUiSlider.set(value);
			}
		}

		
	}
	/**
	* update z override value
	*/
	function updateZOverride(value)
	{	
		zOverride = value;
		console.log('updateZOverride: ', zOverride);
		$(".z-height").html(value);
	}
	
	/**
	*
	*/
	function updateRPM(value)
	{
		$(".task-rpm").html(parseInt(value));
		$("#task-rpm-bar").attr("style", "width:" + ((value/<?php echo isset($rpm_max) ? $rpm_max : 14000; ?>)*100) +"%;");
		if(!isRpmSliderBusy && !wasRpmSliderMoved){
			$('.slider-task-rpm').html(parseInt(value));
			if(typeof rpmSlider !== 'undefined'){
				rpmSlider.noUiSlider.set(value);
			}
		} 
	} 
	
	/**
	*
	*/
	function updateSpeed(value)
	{
		$(".task-speed").html(parseInt(value));
		$("#task-speed-bar").attr("style", "width:" + ((value/500)*100) +"%;");
		if(!isSpeedSliderBusy && !wasSpeedSliderMoved){
			$('.slider-task-speed').html(parseInt(value));
			if(typeof speedSlider !== 'undefined'){
				speedSlider.noUiSlider.set(value);
			}
		}
	}
	
</script>

