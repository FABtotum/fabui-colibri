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
	<?php if($type == "print"): ?>
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
		initWizard();
		<?php if($runningTask == false): ?>
		initFilesTable();
		initRecentFilesTable();
		<?php if($type == "mill"): ?>
		initJog();
		<?php endif; ?>
		<?php else: ?>
		initRunningTaskPage();
		<?php endif; ?>
		$(".action").on('click', doAction);
		$(".graph-line-selector").on('click', setGraphLines);
		$(".new-print").on('click', function(){$('.wizard').wizard('selectedItem', { step: 1 });});
		$(".restart-print").on('click', function(){$('.wizard').wizard('selectedItem', { step: 1 });});
		$(".save-z-height").on('click', saveZHeight);

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
			},
			"fnInitComplete": function(){
			<?php if($what_id != ''): ?>
				var rows = filesTable.fnGetNodes();
				var data = filesTable.fnGetData();
				$(data).each(function(index) {
					if(parseInt(data[index][3]) == <?php echo $what_id; ?>){
						selectFile(rows[index], 'files_table');
						var settings = filesTable.fnSettings();
						var displayLength = settings._iDisplayLength;
						var pageNumber = Math.floor(index / displayLength);
						filesTable.fnPageChange(pageNumber);
						$(".btn-next").trigger("click");
						return;					
					}
				});
			<?php endif; ?>	
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
		filesTable.select(tr);
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
		openWait('<i class="fa fa-spinner fa-spin "></i> Preparing <?php echo ucfirst($type) ?>', 'Please wait');
		
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
				showErrorAlert(response.message);
			}else{
				fabApp.freezeMenu('<?php echo $type ?>');
				freezeUI();
				/*timerInterval = setInterval(timer, 1000);*/
				setInterval(jsonMonitor, 1000);
				idTask = response.id_task;
				initSliders();
				<?php if($type == 'print'): ?>
				fabApp.resetTemperaturesPlot(50);
				setTimeout(initGraph, 1000);
				setTemperaturesSlidersValue(response.temperatures.extruder, response.temperatures.bed);
				<?php endif; ?>
				getTaskMonitor(true);
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
			<?php if($type == "print"): ?>
			updateFlowRate(data.override.flow_rate);
			updateFan(data.override.fan);
			<?php endif; ?>
			<?php if($type == "mill"): ?>
			updateRPM(data.override.rpm);
			<?php endif; ?>
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
		getTrace();
		setTimeout(initSliders,  1000);
		setInterval(jsonMonitor, 1000);
		<?php if($type=="print"): ?>
		setTimeout(initGraph,    1000);
		<?php endif; ?>
		setTimeout(traceMonitor, 1000);
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
				handleTaskStatus(data.task.status, true);
				setTemperaturesSlidersValue();
				updateSpeed(data.override.speed);
				updateFlowRate(data.override.flow_rate);
				updateFan(data.override.fan);
				updateZOverride(data.override.z_override);
				elapsedTime = parseInt(data.task.duration);
				timerInterval = setInterval(timer, 1000);
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
		console.log("Task status: " + status);
		switch(status){
			case 'paused':
				if(firstCall){
					var element = $(".isPaused-button");
					element.html('<i class="fa fa-play"></i> Resume Print');
					element.attr('data-action', 'resume');
				}
				break;
			case 'started':
				if(firstCall){
					var element = $(".isPaused-button");
					element.html('<i class="fa fa-pause"></i> Pause Print');
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
	 * update timers
	 */
	function updateTimers(started)
	{
		
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
		//bed actul line
		if(showBedActual)
			data.push({
				data: seriesBedTemp,
	      		lines: { show: true, fill: true },
	     	 	label: "Bed temp",
	     	 	color: "#3276B1"
			});
		//bed target line
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
			url: '<?php echo site_url('create/abort/'); ?>/' + idTask,
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
		if(typeof extruderSlider == "undefined")
			noUiSlider.create(document.getElementById('create-ext-target-slider'), {
				start: typeof (Storage) !== "undefined" ? localStorage.getItem("nozzle_temp_target") : 0,
				connect: "lower",
				range: {'min': 0, 'max' : 250},
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

		<?php if($type == "mill"): ?>
		if(typeof rpmSlider == "undefined")
			noUiSlider.create(document.getElementById('create-rpm-slider'), {
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
		rpmSlider = document.getElementById('create-rpm-slider');
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
			$('.wizard').wizard('selectedItem', { step: 4 });
			fabApp.unFreezeMenu();
			unFreezeUI();
			clearInterval(timerInterval);
			elapsedTime = 0;			
		}, 3000);
		
		if(zOverride > 0){ //	
		}
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
	* save and override z height
	*/
	function saveZHeight()
	{
		disableButton('.save-z-height');
		$.ajax({
			type: 'post',
			url: '<?php echo site_url('probe/overrideLenght'); ?>/' + zOverride,
			dataType: 'json'
		}).done(function(response) {
			console.log(response);
			showActionAlert("Z's Height saved");
			enableButton('.save-z-height');
		});
	}
	/**
	*
	*/
	function aborting()
	{
		openWait('<i class="fa fa-spinner fa-spin "></i> Aborting print', 'Please wait..', false);
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
	<?php if($type == "mill"): ?>
	/**
	*
	*/
	function initJog()
	{
		$(".directions").on("click", function(){
        	fabApp.jogMoveXY($(this).attr("data-attribute-direction"));
        });
		$(".jog-axisz").on("click", function(event){
        	fabApp.jogAxisZ($(this).attr("data-attribute-function"), $(this).attr("data-attribute-value"));
        	event.preventDefault();
        });
	}
	/**
	*
	*/
	function updateRPM(value)
	{
		$(".task-rpm").html(parseInt(value));
		$("#task-rpm-bar").attr("style", "width:" + ((value/500)*100) +"%;");
		if(!isRpmSliderBusy && !wasRpmSliderMoved){
			$('.slider-task-rpm').html(parseInt(value));
			if(typeof rpmSlider !== 'undefined'){
				rpmSlider.noUiSlider.set(value);
			}
		}
	}
	<?php endif;?>
</script>
