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

if(!isset($extruder_min) || $extruder_min == 0) $extruder_min = 0;
if(!isset($extruder_max) || $extruder_max == 0) $extruder_max = 250;
if(!isset($bed_min)      || $bed_min == 0)      $bed_min = 10;
if(!isset($bed_max)      || $bed_max == 0)      $bed_max = 100;

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
	var skipEngage = <?php echo $this->session->settings['feeder']['engage'] == false ? 'true' : 'false' ?>; //force true if feeder engage is hidden
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
	
	var isExtSliderBusy         = false;
	var isBedSliderBusy         = false;
	var wasExtSliderMoved       = false;
	var wasBedSliderMoved       = false;
	//
	var soft_extruder_min  = <?php echo $extruder_min; ?>;
	
	var zOverrideTimeoout = null;
	var zOverrideValue = 0;
	<?php if($type=="print"): ?>
	var nozzleOffset = parseFloat(<?php echo $head['nozzle_offset']; ?>);
	<?php endif;?>
	// internal state
	var local_task_state = '';
	//reloading state
	var isAborted = false;
	var isAboting = false;
	
	$(document).ready(function() {
		initSliders();
		<?php if($runningTask == true): ?>
		initRunningTaskPage();
		<?php endif; ?>
		$(".action").on('click', doAction);
		$(".graph-line-selector").on('click', setGraphLines);
		
		$("#shutdown-switch").on('change', shutdownSwitchChange);
		$("#email-switch").on('change', emailSwitchChange);

		<?php if($type=="print"): ?>
		disableButton('.change-filament-button');
		disableButton("#filament-start-button");
		$(".change-filament-button").on('click', showChangeFilamentModal);
		$(".filament-button-choose-action").on('click', filamentSetMode);
		$("#filament-start-button").on('click', startFilamentAction);
		<?php endif; ?>
		
	});
	/**
	*
	**/
	function emailSwitchChange()
	{
		sendActionRequest('sendEmail', $(this).is(':checked')?"on":"off");
	}
	/**
	*
	**/
	function shutdownSwitchChange()
	{
		sendActionRequest('autoShutdown', $(this).is(':checked')?"on":"off");
	}
	/**
	* freeze ui
	*/
	function freezeUI()
	{
		disableButton('.button-prev');
		disableButton('.button-next');
		fabApp.disableTopBarControls();
	}
	/**
	*
	*/
	function unFreezeUI()
	{
		fabApp.enableTopBarControls();
	}
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
				range: {'min': 0, 'max' : <?php echo $extruder_max; ?>},
				pips: {
					mode: 'values',
					values: [0, <?php echo $extruder_min == 0 ? 175 : $extruder_min; ?>, <?php echo $extruder_max; ?>],
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
			wasExtSliderMoved = true;
		});
		extruderSlider.noUiSlider.on('change', function(e){
			onChange('extruder-target', e);
		});
		extruderSlider.noUiSlider.on('end', function(e){
			isExtSliderBusy = false;
		});
		extruderSlider.noUiSlider.on('start', function(e){
			isExtSliderBusy = true;
			wasExtSliderMoved = true;
		});
		//bed
		bedSlider.noUiSlider.on('slide',  function(e){
			onSlide('bed-target', e);
			wasBedSliderMoved = true;
		});
		bedSlider.noUiSlider.on('change', function(e){
			onChange('bed-target', e);
		});
		bedSlider.noUiSlider.on('end', function(e){
			isBedSliderBusy = false;
		});
		bedSlider.noUiSlider.on('start', function(e){
			isBedSliderBusy = true;
			wasBedSliderMoved = true;
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
				fabApp.showInfoAlert(_("Extruder temperature set to {0} &deg;").format(extruder_target));
				break;
			case 'bed-target':
				fabApp.serial("setBedTemp",parseInt(value[0]));
				fabApp.showInfoAlert(_("Bed temperature set to {0} &deg;").format(parseInt(value[0])));
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
				if(!isExtSliderBusy)
					extruderSlider.noUiSlider.set(ext_temp_target);
			}
			if(typeof bedSlider !== 'undefined'){
				if(!isBedSliderBusy)
					bedSlider.noUiSlider.set(bed_temp_target);
			}
		});
	}
	
	/**
	 * init graph
	 */
	function initGraph()
	{

		if ($('#temperatures-chart').is(":visible") == false) {
			return
		}
		
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
	     	 	label: _("Ext temp"),
	     	 	color: "#FF0000",
	     	 	points: {"show" : false}
			});
			
		//extruder target line
		if(showExtTarget)
			data.push({
				data: seriesExtTarget,
	      		lines: { show: true, fill: false, lineWidth:1 },
	     	 	label: _("Ext target"),
	     	 	color: "#ff9933",
	     	 	points: {"show" : false}
			});
			
		//bed actual temp line
		if(showBedActual)
			data.push({
				data: seriesBedTemp,
	      		lines: { show: true, fill: true },
	     	 	label: _("Bed temp"),
	     	 	color: "#3276B1"
			});
			
		//bed target temp line
		if(showBedTarget)
			data.push({
				data: seriesBedTarget,
				lines: { show: true, fill: false, lineWidth:1 },
	     	 	label: _("Bed target"),
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
	
	function zOverrideCallback()
	{
		zOverrideTimeoout = null;
		sendActionRequest('zHeight', zOverrideValue);
		<?php if($type=="print"): ?>
		var newNozzleOffset = parseFloat(nozzleOffset) + parseFloat(zOverrideValue);
		$.ajax({
			type: 'post',
			url: '<?php echo site_url('nozzle/storeNozzleOffset/') ?>/' + newNozzleOffset,
			dataType: 'json'
		}).done(function(response) {
			console.log('new nozzle offset stored');
		});
		<?php endif; ?>
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
					message = _(taskType+" paused");
					break;
				case 'resume':
					message = _(taskType+" resumed");
					break;
				case 'speed':
					message = _("Speed override changed to {0}").format(value);
					break;
				case 'flowRate':
					message = _("Flow Rate override changed to {0}").format(value);
					break;
				case 'fan':
					message = _("Fan override changed to {0}").format(value);
					break;
				case 'zHeight':
					//~ if(value.charAt(0) == '+') message=_("Z height increased");
					//~ else message = _("Z height decreased");
					message = _("Z height override changed to {0}mm").format(value)
					break;
				case 'rpm':
					// _( " is not a type, is there to prevent this string from being extracted by gettext
					message = _( "<?php echo isset($rpm_message) ? $rpm_message : "RPM speed set to {0}"; ?>" ).format(value);
					break;
				case 'sendEmail':
					message=_("Send email on task finish: {0}").format( _(value) );
					break;
				case 'autoShutdown':
					break;
				default:
					message=_("Unknown action: {0}").format(action);
			}
			fabApp.showInfoAlert(message);
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
				var new_value = parseFloat(eval(operation)).toFixed(2);
				$(".z-height").html(new_value);
				
				if(zOverrideTimeoout)
					clearTimeout(zOverrideTimeoout);
					
				zOverrideValue = new_value;
				zOverrideTimeoout = setTimeout(zOverrideCallback, 500);
				
				break;
		}
	}
	
	/**
	 * 
	 */
	function pauseResume(action, element)
	{
		var taskType = "<?php echo isset($type_label) ? $type_label : ucfirst($type); ?>";
		var element = $(".action-pause");
		if(action == 'pause') {
			element.attr('data-action', 'resume');
			element.html('<i class="fa fa-play"></i> <span class="hidden-xs">'+_("Resume")+" </span>" );
			enableButton('.change-filament-button');
			fabApp.enableTopBarTempsControls();
		}else if(action == 'resume'){
			freezeUI();
			element.attr('data-action', 'pause');
			element.html('<i class="fa fa-pause"></i> <span class="hidden-xs">'+_("Pause")+" </span>" );
			disableButton('.change-filament-button');
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
		disableCompleteSteps();
		
		<?php if($type=="print"): ?>
		initGraph();
		<?php endif; ?>
		traceMonitor();
		setInterval(traceMonitor, 1000);
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
				if(data.hasOwnProperty("task"))
				{
					handleTaskStatus(data.task.status, true);
					elapsedTime = parseInt(data.task.duration);
					estimatedTime = parseInt(data.task.estimated_time);
					updateSendEmailCheckBox(data.task.send_email);
				}
					
				setTemperaturesSlidersValue();
				if(data.hasOwnProperty("override"))
				{
					updateSpeed(data.override.speed);
					updateFlowRate(data.override.flow_rate);
					updateFan(data.override.fan);
					updateZOverride(data.override.z_override);
				}
				if(data.hasOwnProperty("gpusher"))
				{
					updateFileInfo(data.gpusher.file);
				}
				
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
		var estimatedTimeLeft = transformSeconds(remainingTime);
		
		if(estimatedTime == 0)
			estimatedTimeLeft = "<?php echo _("Waiting for first move");?>...";
		else if(estimatedTime < 0)
			estimatedTimeLeft = 0;
		
		$(".estimated-time-left").html(estimatedTimeLeft);
	}
	
	window.manageMonitor = function(data){
		if(data.hasOwnProperty("task"))
		{
			handleTaskStatus(data.task.status);
			updateProgress(data.task.percent);
			updateTimers(data.task.started_time, data.task.duration, data.task.estimated_time);
		}
		
		if(data.hasOwnProperty("override"))
		{
			updateSpeed(data.override.speed);
			<?php if($type == "print"): ?>
			updateFlowRate(data.override.flow_rate);
			updateFan(data.override.fan);
			if(data.hasOwnProperty("print") && 
			   data.print.hasOwnProperty("layer_current") && 
			   data.print.hasOwnProperty("layer_total"))
			{
				updateLayer(data.print.layer_current, data.print.layer_total);
			}
			<?php endif; ?>
			<?php if($type == "mill" || $type == "laser"): ?>
			updateRPM(data.override.rpm);
			<?php endif; ?>
		}
	};
	
	window.updateTemperatures = function(ext_temp, ext_temp_target, bed_temp, bed_temp_target)
	{
		updateExtTarget(ext_temp_target);
		updateBedTarget(bed_temp_target);
	}
	
	/**
	 *  monitor interval if websocket is not available
	 */
	function jsonMonitor()
	{
		 if(!socket_connected || socket.fallback) getTaskMonitor(false);
	}
	/**
	 *  trace interval if websocket is not available
	 */
	function traceMonitor()
	{
		 if(!socket_connected || socket.fallback) getTrace();
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
				{
					var element = $(".isPaused-button");
					element.html('<i class="fa fa-play"></i> <span class="hidden-xs">'+_("Resume") + '</span>' );
					element.attr('data-action', 'resume');
					enableButton('.change-filament-button');
					if(firstCall) fabApp.enableTopBarTempsControls();
				}
				break;
			case 'started':
				if(firstCall){
					var element = $(".isPaused-button");
					element.html('<i class="fa fa-pause"></i> <span class="hidden-xs">'+_("Pause") + '</span>' );
					element.attr('data-action', 'pause');
					disableButton('.change-filament-button');
				}
				break;
			case 'aborting':
				aborting();
				break;
			case 'terminated':
			case 'aborted':
				aborted();
				break;
			case 'completing':
				completingTask();
				break;
			case 'completed':
				completeTask();
				break;
			case 'running': 
				{
					var element = $(".isPaused-button");
					if( element.attr('data-action') == 'resume' )
					{
						element.html('<i class="fa fa-pause"></i> <span class="hidden-xs">'+_("Pause") + '</span>' );
						element.attr('data-action', 'pause');
						disableButton('.change-filament-button');
					}
				} 
				break;
			default:
				disableButton('.change-filament-button');
				break;
		}
	}
	
	/**
	 * 
	 */
	function abort()
	{
		var taskType = "<?php echo $type; ?>";
		if (typeof ga !== 'undefined') {
			ga('send', 'event', '<?php echo $type; ?>', 'abort', '<?php echo $type; ?> aborted');
		}
		openWait('<i class="fa fa-spinner fa-spin "></i> '+_( "Aborting {0}").format(taskType), _("Please wait")+"...", false);
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
		if(isAborted == false){
			var taskType = "<?php echo ucfirst($type); ?>";
			openWait('<i class="fa fa-check "></i> '+ _("{0} aborted").format(taskType), _("Reloading page")+"...", false);
			isAborted = true;
			setTimeout(function(){
				location.reload();
			}, 5000);
		}
	}
	/**
	*
	*/
	function aborting()
	{
		if(isAborting == false ){
			var taskType = "<?php echo $type; ?>";
			openWait('<i class="fa fa-spinner fa-spin "></i> '+_( "Aborting {0}").format(taskType), _("Please wait")+"...", false);
			isAborting = true;
		}
	}
	
	/**
	* handle "completing" status
	*/
	function completingTask()
	{
		var taskType = "<?php echo $type; ?>";
		openWait('<i class="fa fa-spinner fa-spin "></i> '+_("Completing {0}").format(taskType) , _("Please wait") + "...\r\n" + _("Moving to safe zone"), false);
	}
	/**
	* complete task
	*/
	function completeTask()
	{	
		var taskType = "<?php echo ucfirst($type); ?>";
		openWait('<i class="fa fa-check "></i> '+ _("{0} completed !").format(taskType), null, false);
	
		setTimeout(function(){
			closeWait();
			number_tasks = number_tasks - 1;
			gotoWizardFinish();
			fabApp.unFreezeMenu();
			unFreezeUI();
			fabApp.updateNotificationBadge();
			clearInterval(timerInterval);
			elapsedTime = 0;
			estimatedTime = 0;
			if (typeof ga !== 'undefined') {
				ga('send', 'event', '<?php echo $type; ?>', 'complete', '<?php echo $type; ?> completed');
			}
			
		}, 3000);
		
		if(zOverride != 0)
			$('#save-z-height-section').slideDown();
	}
	
	/**
	 * update progress infos
	 */
	function updateProgress(value)
	{
		$(".task-progress").html(parseFloat(value).toFixed(1) + " %");
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
	/**
	*
	**/
	function updateExtTarget(value)
	{
		if(!isExtSliderBusy){
			$('.slider-extruder-target').html(parseInt(value));
			if(typeof extruderSlider !== 'undefined'){
				extruderSlider.noUiSlider.set(value);
			}
		}
	}
	/**
	*
	**/
	function updateBedTarget(value)
	{
		if(!isBedSliderBusy){
			$('.slider-bed-target').html(parseInt(value));
			if(typeof bedSlider !== 'undefined'){
				bedSlider.noUiSlider.set(value);
			}
		}
	}
	/**
	*
	**/
	function updateSendEmailCheckBox(bool)
	{
		$('#email-switch').prop('checked', bool);
	}
	/**
	*
	**/
	function updateFileInfo(file)
	{
		$(".task-file-name").html('<b>' + file.name + '</b>');
	}
	<?php if($type=="print"): ?>
	/**
	*
	**/
	function showChangeFilamentModal()
	{
		$('#filament-change-modal').modal({
			backdrop : 'static'
		});
	}
	/**
	*
	**/
	function filamentSetMode()
	{
		$(".filament-button-choose-action").removeClass('btn-primary');
		$(this).addClass('btn-primary');
		var action = $(this).attr('data-action');
		$(".filament-button-choose-action").find('span').html('');
		$(this).find('span').html('<i class="fa fa-check"></i>');
		$(".filament-action-descritpion").addClass("hidden");
		$("#filament-" + action + "-description").removeClass("hidden");
		$("#filament-start-button").attr('data-action', action);
		enableButton("#filament-start-button");
	}
	/**
	*
	**/
	function startFilamentAction()
	{
		var action = $(this).attr('data-action');
		var filament = $("#filament").val();
		openWait("<i class='fa fa-circle-o-notch fa-spin'></i> <?php echo _("Please wait");?>", null, false);
		$.ajax({
			type: "POST",
			url: "<?php echo site_url("spool") ?>/" + action + '/' + filament + '/1',
			dataType: 'json'
		}).done(function( response ) {
			closeWait();
	  	});
	}
	/**
	*
	**/
	function updateLayer(current, total)
	{
		if(total > 0){
			$(".task-layer-current").html((parseInt(current)+1));
			$(".task-layer-total").html(parseInt(total));
			$(".layer-info").removeClass("hidden");
		}
	}
	<?php endif; ?>
</script>

