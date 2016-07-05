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
	var idFile <?php echo $runningTask ? ' = '.$runningTask['id_file'] : ''; ?>; //file to create
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
	
	
	$(document).ready(function() {
		initWizard();
		<?php if(!$runningTask): ?>
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
	
	<?php if(!$runningTask): ?>
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
			setInterval(timer, 1000);
			setInterval(jsonMonitor, 1000);
			idTask = response.id_task;
			closeWait();
			initSliders();
			setTimeout(initGraph, 1000);
			setTemperaturesSlidersValue(response.temperatures.extruder, response.temperatures.bed);
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
				break; 
		}
	}
	
	if(typeof manageMonitor != 'function'){
		window.manageMonitor = function(data){
			updateProgress(data.<?php echo $type; ?>.stats.percent);
			updateSpeed(data.<?php echo $type; ?>.stats.speed);
			updateFlowRate(data.<?php echo $type; ?>.stats.flow_rate);
			updateFan(data.<?php echo $type; ?>.stats.fan);
			updateTemperatures(data.<?php echo $type; ?>.stats.extruder, data.<?php echo $type; ?>.stats.extruder_target, data.<?php echo $type; ?>.stats.bed, data.<?php echo $type; ?>.stats.bed_target);
			//updateGraph();
			updateTimers(data.<?php echo $type ?>.started);
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
		getTaskMonitor(true);
		setInterval(jsonMonitor, 1000);
		setTimeout(initGraph, 1000);
		setTimeout(initSliders, 1000);
	}
	
	/**
	 * get task monitor json
	 */
	function getTaskMonitor(firstCall)
	{
		$.get('/temp/task_monitor.json', function(data, status){
			manageMonitor(data);
			if(firstCall) {
				isPaused(data.<?php echo $type; ?>.paused);
				var serverDate = new Date((data.<?php echo $type; ?>.started) * 1000 );
				var browserDate = new Date();
				//set sliders target - just on first call
				setTemperaturesSlidersValue(data.<?php echo $type; ?>.stats.extruder_target, data.<?php echo $type; ?>.stats.bed_target);
				
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
	 * set if task is paused
	 */
	function isPaused(bool)
	{
		var element = $(".isPaused-button");
		if(bool == 'False'){
			element.html('<i class="fa fa-pause"></i> Pause Print');
			element.attr('data-action', 'pause');
		}else if(bool == 'True'){
			element.html('<i class="fa fa-play"></i> Resume Print');
			element.attr('data-action', 'resume');
		}
	}
	/**
	 * update progress infos
	 */
	function updateProgress(value)
	{
		$(".task-progress").html(value + " %");
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
		$(".task-fan").html(((value/255)*100));
		$("#task-fan-bar").attr("style", "width:" +((value/255)*100) +"%;");
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
	function updateTemperatures(ext, extTarget, bed, bedTarget)
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
		
		$(".extruder-temp").html(ext);
		$(".extruder-target").html(extTarget);
		$(".bed-temp").html(bed);
		$(".bed-target").html(bedTarget);
		
		if(typeof (Storage) !== "undefined") {
			localStorage.setItem('temperaturesPlot', JSON.stringify(temperaturesPlot));
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
			colors : ["#FF0000", "#57889c", "#0000FF"],
			legend: {
				show : true
			},
			grid: {
				hoverable : true,
				clickable : true,
				borderWidth : 0
			},
		});
		
		setInterval(updateGraph, 1000);
	}
	/**
	 * get plots for temperatures graph
	 */
	function getPlotTemperatures()
	{
		var seriesExtTemp = [];
		var seriesExtTarget = [];
		var seriesBedTemp = [];
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
		      		lines: { show: true, fill: false },
		     	 	label: "Ext temp: ",
		    	},
		    	{
		 			data: seriesBedTemp,
		      		lines: { show: true, fill: false },
		     	 	label: "Bed temp: ",
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
		
		$.ajax({
			type: 'post',
			url: '<?php echo site_url('create/action/'); ?>/' + action,
			dataType: 'json'
		}).done(function(response) {
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
			range: {'min': 0, 'max' : 250},
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
			range: {'min': 0, 'max' : 100},
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
		extruderSlider.noUiSlider.on('set', function(e){
			onSet('extruder-target', e);
		});
		//bed
		bedSlider.noUiSlider.on('slide',  function(e){
			onSlide('bed-target', e);
		});
		bedSlider.noUiSlider.on('set', function(e){
			onSet('bed-target', e);
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
		
		//speedSlider = document.getElementById('create-speed-slider');
		
		$(".sliders").on({
			'slide': onSlide,
			'set': onSet
		});
	}
	
	/**
	 * event on slider slide
	 */
	function onSlide(element, value)
	{
		switch(element){
			case 'extruder-target':
				$(".slider-extruder-target").html(value);
				break;
			case 'bed-target':
				$(".slider-bed-target").html(value);
				break;
		}
	}
	/**
	 * event on slider set
	 */
	function onSet(element, value)
	{
		switch(element){
			case 'extruder-target':
				fabApp.serial("setExtruderTemp",value[0]);
				break;
			case 'bed-target':
				fabApp.serial("setBedTemp",value[0]);
				break;
		}
	}
	/**
	 * set initial target for temperatures sliders
	 */
	function setTemperaturesSlidersValue(extruder, bed)
	{
		$(".slider-extruder-target").html(extruder);
		$(".slider-bed-target").html(bed);
		
		extruderSlider.noUiSlider.set(extruder);
		bedSlider.noUiSlider.set(bed);
	}
</script>