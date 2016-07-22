<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<script type="text/javascript">
	
	var wizard; //wizard object
	var responsiveHelper_dt_basic = undefined;
	var breakpointDefinition = { tablet : 1024, phone : 480};
	var filesTable; //table object
	var recentFilesTable; //table object
	var idFile <?php echo $runningTask != false ? ' = '.$runningTask['id_file'] : ''; ?>; //file to create
	var skipEngage = <?php echo $this->session->settings['feeder']['show'] == false ? 'true' : 'false' ?>; //force true if feeder engage is hidden
	var temperaturesGraph;
	var temperaturesPlot = {extruder: {temp: [], target: []}, bed: {temp:[], target:[]}};
	var maxTemperaturesPlot = 200;
	var idTask <?php echo $runningTask ? ' = '.$runningTask['id'] : ''; ?>;
	var monitorInterval;
	var timerInterval;
	var elapsedTime = 0;
	//sliders
	<?php if($type == "print"): ?>
	var extruderSlider;
	var bedSlider;
	var flowRateSlider;
	<?php endif; ?>
	var speedSlider;
	var fanSlider;
	var zOverride = 0;
	
	$(document).ready(function() {
		initWizard();
		<?php if($runningTask == false): ?>
		initFilesTable();
		initRecentFilesTable();
		<?php else: ?>
		initRunningTaskPage();
		<?php endif; ?>
		$(".action").on('click', doAction);
		
	});
	
	//init wizard flow
	function initWizard()
	{
		wizard = $('.wizard').wizard();
		disableButton('.btn-prev');
		disableButton('.btn-next');
		
		$('.wizard').on('changed.fu.wizard', function (evt, data) {
			checkWizard();
		});
		$('.btn-prev').on('click', function() {
			console.log('prev');
			if(canWizardPrev()){
			}
		});
		$('.btn-next').on('click', function() {
			console.log('next');
			if(canWizardNext()){
				
			}
		});
	}
	
	<?php if($runningTask == false): ?>
	function initFilesTable()
	{
		filesTable = $('#files_table').dataTable({
			"sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'l>r>"+
				"t"+
				"<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
			"autoWidth" : true,
			"preDrawCallback" : function() {
				// Initialize the responsive datatables helper once.
				if (!responsiveHelper_dt_basic) {
					responsiveHelper_dt_basic = new ResponsiveDatatablesHelper($('#files_table'), breakpointDefinition);
				}
			},
			"aaSorting": [],
			"rowCallback" : function(nRow) {
				responsiveHelper_dt_basic.createExpandIcon(nRow);
			},
			"drawCallback" : function(oSettings) {
				responsiveHelper_dt_basic.respond();
				initFilesTableEvents();
			},
			"sAjaxSource": "<?php echo site_url('create/getFiles/'.$printType) ?>",
			"fnRowCallback": function (row, data, index ){
				$('td', row).eq(0).addClass('text-center');
				$('td', row).eq(2).addClass('hidden-xs');
				$('td', row).eq(3).addClass('hidden');
				$('td', row).eq(4).addClass('hidden');
			}
		});

	}
	
	function initRecentFilesTable()
	{
		recentFilesTable = $('#recent_files_table').dataTable({
			"sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'l>r>"+
				"t"+
				"<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
			"autoWidth" : true,
			"preDrawCallback" : function() {
				// Initialize the responsive datatables helper once.
				if (!responsiveHelper_dt_basic) {
					responsiveHelper_dt_basic = new ResponsiveDatatablesHelper($('#recent_files_table'), breakpointDefinition);
				}
			},
			"rowCallback" : function(nRow) {
				responsiveHelper_dt_basic.createExpandIcon(nRow);
			},
			"drawCallback" : function(oSettings) {
				responsiveHelper_dt_basic.respond();
				initRecentFilesTableEvents();
			},
			"sAjaxSource": "<?php echo site_url('create/getRecentFiles/'.$type) ?>",
			"fnRowCallback": function (row, data, index ){
				$('td', row).eq(0).addClass('text-center');
				$('td', row).eq(2).addClass('hidden-xs');
				$('td', row).eq(3).addClass('hidden');
				$('td', row).eq(4).addClass('hidden');
			}
		});
	}
	
	
	//all files table event
	function initFilesTableEvents()
	{	
		$("#files_table tbody > tr").on("click", function(){
			selectFile(this, 'files_table');
		});
	}
	// recent files table event
	function initRecentFilesTableEvents()
	{
		$("#recent_files_table tbody > tr").on("click", function(){
			selectFile(this, 'recent_files_table');
		});
	}
	//select file by clicking on the row
	function selectFile(tr, tableID)
	{
		$("table input[type='radio']").removeAttr('checked');
		$("table tbody > tr").removeClass('bold-text txt-color-blueDark uppercase');
		$(tr).find("input[type='radio']").prop('checked', true);
		$(tr).addClass('bold-text txt-color-blueDark uppercase');
		idFile = $(tr).find("input[type='radio']").val();
		//im in firs step
		enableButton('.btn-next');
	}
	//start create
	function startCreate()
	{
		is_task_on = true;
		openWait('Init print');
		var calibration = $('input[name=calibration]:checked').val();
		var data = {idFile:idFile, skipEngage:skipEngage, calibration:calibration};
		$.ajax({
			type: 'post',
			data: data,
			url: '<?php echo site_url('create/startCreate/'.$type); ?>',
			dataType: 'json'
		}).done(function(response) {
			if(response.start == false){
				$('.wizard').wizard('selectedItem', { step: 2 });
				showErrorResponse(response.trace);
			}else{
				freezeUI();
				setInterval(timer, 1000);
				setInterval(jsonMonitor, 1000);
				idTask = response.id_task;
				initSliders();
				setTimeout(initGraph, 1000);
				setTemperaturesSlidersValue(response.temperatures.extruder, response.temperatures.bed);
				updateZOverride(0);
			}
			closeWait();
			//TODO freeze menu fabApp.freezeMenu();
		});
	}
	<?php endif; ?>
	
	// check if i can move to previous step
	function canWizardPrev()
	{
		var step = $('.wizard').wizard('selectedItem').step;
		return false;
	}
	
	//check if i can move to next step
	function canWizardNext()
	{
		var step = $('.wizard').wizard('selectedItem').step;
		console.log('Can Wizard NExt: ' + step);
		return false;
	}
	
	//enable/disable wizard buttons
	function checkWizard()
	{
		console.log('check Wizard');
		var step = $('.wizard').wizard('selectedItem').step;
		console.log(step);
		switch(step){
			case 1:
				disableButton('.btn-prev');
				enableButton('.btn-next');
				$('.btn-next').find('span').html('Next');
				break;
			case 2:
				enableButton('.btn-prev');
				$('.btn-next').find('span').html('Print');
				break;
			case 3:
				startCreate();
				return false;
				break; 
		}
	}
	
	if(typeof manageMonitor != 'function'){
		window.manageMonitor = function(data){
			handleTaskStatus(data.task.status);
			updateProgress(data.task.percent);
			updateSpeed(data.override.speed);
			updateFlowRate(data.override.flow_rate);
			updateFan(data.override.fan);
			updateTimers(data.task.started_time);
		};
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
		freezeUI();
		setTimeout(initSliders,  1000);
		setInterval(jsonMonitor, 1000);
		setTimeout(initGraph,    1000);
		setTimeout(function(){
			getTaskMonitor(true);
		},    1000);
		
	}
	
	/**
	 * get task monitor json
	 */
	function getTaskMonitor(firstCall)
	{
		$.get('/temp/task_monitor.json'+ '?' + jQuery.now(), function(data, status){
			manageMonitor(data);
			if(firstCall) {
				handleTaskStatus(data.task.status);
				setTemperaturesSlidersValue();
				setSpeedSliderValue(data.override.speed);
				setFlowRateSliderValue(data.override.flow_rate);
				setFanSliderValue(data.override.fan);
				updateZOverride(data.override.z_override);
				elapsedTime = parseInt(data.task.duration);
				setInterval(timer, 1000);
			}
		});
	}
	/**
	 *  monitor interval if websocket is not available
	 */
	function jsonMonitor()
	{
		if(!socket_connected) getTaskMonitor(false);
	}
	/**
	 * handle task status
	 */
	function handleTaskStatus(status)
	{
		console.log("Task status: " + status);
		switch(status){
			case 'paused':
				var element = $(".isPaused-button");
				element.html('<i class="fa fa-play"></i> Resume Print');
				element.attr('data-action', 'resume');
				break;
			case 'started':
				var element = $(".isPaused-button");
				element.html('<i class="fa fa-pause"></i> Pause Print');
				element.attr('data-action', 'pause');
				break;
			case 'completed':
				completeTask();
				break;
		}
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
	 * update speed infos
	 */
	function updateSpeed(value)
	{
		$(".task-speed").html(parseInt(value));
		$("#task-speed-bar").attr("style", "width:" + ((value/500)*100) +"%;");
	}
	/**
	 * update flow rate infos
	 */
	function updateFlowRate(value)
	{
		$(".task-flow-rate").html(parseInt(value));
		$("#task-flow-rate-bar").attr("style", "width:" + ((value/500)*100) +"%;");
	}
	/**
	 * update fan infos
	 */
	function updateFan(value)
	{
		$(".task-fan").html(parseFloat((value/255)*100).toFixed(0));
		$("#task-fan-bar").attr("style", "width:" +((value/255)*100) +"%;");
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
	 * update timers
	 */
	function updateTimers(started)
	{
		
	}
	/**
	 * update temperatures info
	 */
	if(typeof updateTaskTemperatures != 'function'){
		window.updateTaskTemperatures = function(ext, extTarget, bed, bedTarget)
		{
			var extruderTemp = {'value': parseFloat(ext), 'time': new Date().getTime()};
			var extruderTargetTemp = {'value': parseFloat(extTarget), 'time': new Date().getTime()};
			var bedTemp = {'value': parseFloat(bed), 'time': new Date().getTime()};
			var bedTargetTemp = {'value': parseFloat(bedTarget), 'time': new Date().getTime()};
			
			if(temperaturesPlot.extruder.temp.length > maxTemperaturesPlot)   temperaturesPlot.extruder.temp.shift();
			if(temperaturesPlot.extruder.target.length > maxTemperaturesPlot) temperaturesPlot.extruder.target.shift();
			if(temperaturesPlot.bed.temp.length > maxTemperaturesPlot)        temperaturesPlot.bed.temp.shift();
			if(temperaturesPlot.bed.target.length > maxTemperaturesPlot)      temperaturesPlot.bed.target.shift();
			
			temperaturesPlot.extruder.temp.push(extruderTemp);
			temperaturesPlot.extruder.target.push(extruderTargetTemp);
			temperaturesPlot.bed.temp.push(bedTemp);
			temperaturesPlot.bed.target.push(bedTargetTemp);
			
			$(".extruder-temp").html(parseFloat(ext).toFixed(0));
			$(".extruder-target").html(parseFloat(extTarget).toFixed(0));
			$(".bed-temp").html(parseFloat(bed).toFixed(0));
			$(".bed-target").html(parseFloat(bedTarget).toFixed(0));
			
			if(typeof (Storage) !== "undefined") {
				localStorage.setItem('temperaturesPlot', JSON.stringify(temperaturesPlot));
			}

						
		}
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
			
			colors : ["#FF0000", "#3276B1"],
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
	 * get plots for temperatures graph
	 */
	function getPlotTemperatures()
	{
		var seriesExtTemp   = [];
		var seriesExtTarget = [];
		var seriesBedTemp   = [];
		var seriesBedTarget = [];
		
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
		
		return [{
		 			data: seriesExtTemp,
		      		lines: { show: true, fill: true },
		     	 	label: "Ext temp",
		     	 	points: {"show" : false},
		     	 	shadowSize : 0
		    	},
		    	{
		 			data: seriesBedTemp,
		      		lines: { show: true, fill: true },
		     	 	label: "Bed temp",
		    	}
		  	];
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
	 * exec action 
	 */
	function doAction()
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
		openWait('Aborting print');
		$.ajax({
			type: 'post',
			url: '<?php echo site_url('create/abort/'); ?>/' + idTask,
			dataType: 'json'
		}).done(function(response) {
			document.location.href = '<?php echo site_url('make/'.$type); ?>';
		});
	}
	
	/**
	 * 
	 */
	function pauseResume(action, element)
	{
		if(action == 'pause') {
			element.attr('data-action', 'resume');
			element.html('<i class="fa fa-play"></i> Resume print');
		}else if(action == 'resume'){
			element.attr('data-action', 'pause');
			element.html('<i class="fa fa-pause"></i> Pause print');
		}
		sendActionRequest(action);			
	}
	/**
	 * 
	 */
	function sendActionRequest(action, value)
	{
		console.log(action);
		console.log(value);
		value = value || '';
		var message;
		$.ajax({
			type: 'post',
			url: '<?php echo site_url('create/action/'); ?>/' + action + '/' + value,
			dataType: 'json'
		}).done(function(response) {
			switch(action){
				case 'pause':
					message="Print paused";
					break;
				case 'resume':
					message="Print resumed";
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
				default:
					message="Unknown action: "+ action;
			}
			showActionAlert(message);
		});
	}
	
	/**
	 * 
	 */
	function timer()
	{
		elapsedTime++;
		$(".elapsed-time").html(transformSeconds(elapsedTime));
	}
	/**
	 * init sliders
	 */
	function initSliders()
	{	
		<?php if($type == 'print'): ?>
		//extruder target
		noUiSlider.create(document.getElementById('create-ext-target-slider'), {
			start: typeof (Storage) !== "undefined" ? localStorage.getItem("nozzle_temp_target") : 0,
			connect: "lower",
			range: {'min': 175, 'max' : 250},
			pips: {
				mode: 'positions',
				values: [0,25,50,75,100],
				density: 5,
				format: wNumb({
					postfix: '&deg;'
				})
			}
		});
		//bed target slider
		noUiSlider.create(document.getElementById('create-bed-target-slider'), {
			start: typeof (Storage) !== "undefined" ? localStorage.getItem("bed_temp_target") : 0,
			connect: "lower",
			range: {'min': 10, 'max' : 100},
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
		noUiSlider.create(document.getElementById('create-fan-slider'), {
			start: 255,
			connect: "lower",
			range: {'min': 50, 'max' : 100},
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
		});
		//fan
		fanSlider.noUiSlider.on('change', function(e){
			onChange('fan', e);
		});
		fanSlider.noUiSlider.on('slide', function(e){
			onSlide('fan', e);
		});
		<?php endif; ?>
		//speed slider
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
		//speed slider
		speedSlider.noUiSlider.on('change', function(e){
			onChange('speed', e);
		});
		speedSlider.noUiSlider.on('slide', function(e){
			onSlide('speed', e);
		});
	}
	
	/**
	 * event on slider slide
	 */
	function onSlide(element, value)
	{
		
		switch(element){
			case 'extruder-target':
				$(".slider-extruder-target").html(parseFloat(value).toFixed(0));
				break;
			case 'bed-target':
				$(".slider-bed-target").html(parseFloat(value).toFixed(0));
				break;
			case 'flow-rate':
				$('.slider-task-flow-rate').html(parseFloat(value).toFixed(0));
				break;
			case 'fan':
				$('.slider-task-fan').html(parseFloat(value).toFixed(0));
				break;
			case 'speed':
				$('.slider-task-speed').html(parseFloat(value).toFixed(0));
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
				fabApp.serial("setExtruderTemp",value[0]);
				break;
			case 'bed-target':
				fabApp.serial("setBedTemp",value[0]);
				break;
			case 'flow-rate':
				sendActionRequest('flowRate', value[0]);
				break;
			case 'fan':
				sendActionRequest('fan', value[0]);
				break;
			case 'speed':
				sendActionRequest('speed', value[0]);
				break;			
		}
	}
	/**
	 * set initial target for temperatures sliders and temperatures labels
	 */
	function setTemperaturesSlidersValue()
	{	
		$.get(temperatures_file_url + '?' + jQuery.now(), function(data){

			/**
			* extruder
			*/
			if(data.ext_temp.constructor === Array){
				ext_temp = data.ext_temp[data.ext_temp.length - 1];
			}
			if(data.ext_temp_target.constructor === Array){
				ext_temp_target = data.ext_temp_target[data.ext_temp_target.length - 1];
			}
			$(".extruder-temp").html(parseFloat(ext_temp).toFixed(0));
			$(".extruder-target").html(parseFloat(ext_temp_target).toFixed(0));
			/**
			* bed
			*/
			if(data.bed_temp_target.constructor === Array){
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
	 * set initial speed slider values
	 */
	function setSpeedSliderValue(value)
	{
		$('.slider-task-speed').html(parseFloat(value).toFixed(0));
		if(typeof speedSlider !== 'undefined'){
			speedSlider.noUiSlider.set(value);
		}
	}
	/**
	 * set initial flow rate slider values
	 */
	function setFlowRateSliderValue(value)
	{
		$('.slider-task-flow-rate').html(parseFloat(value).toFixed(0));
		if(typeof flowRateSlider !== 'undefined'){
			flowRateSlider.noUiSlider.set(value);
		}
	}
	/**
	* set initial fan slider value
	*/
	function setFanSliderValue(value){
		value = ((value/255)*100);
		$('.slider-task-fan').html(parseFloat(value).toFixed(0));
		if(typeof fanSlider !== 'undefined'){
			fanSlider.noUiSlider.set(value);
		}
	}
	/**
	* complete task
	*/
	function completeTask()
	{	
		openWait("Task completed");
		$.ajax({
			type: 'post',
			url: '<?php echo site_url('create/complete/'); ?>/' + idTask,
			dataType: 'json'
		}).done(function(response) {
			$('.wizard').wizard('selectedItem', { step: 4 });
			closeWait();
			unFreezeUI();
		});
	}
	/**
	* show error message 
	*/
	function showErrorResponse(message)
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
	* freeze ui
	*/
	function freezeUI()
	{
		disableButton('.btn-prev');
		disableButton('.btn-next');
		disableButton('.top-directions');
		disableButton('.top-axisz');
	}
	/**
	*
	*/
	function unFreezeUI()
	{
		enableButton('.top-directions');
		enableButton('.top-axisz');
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
</script>