/**
 * Scan
 * Provides utilities for Scan
 *
 */

var buildPlateDimensions = {
    probe: {
        minX : 20,
        maxX : 210,
        minY : 60,
        maxY : 230,
        width : 214,
        height : 234,
        offsetX : -17.5,
        offsetY : -59
    },
    sweep : {
        minX : 20,
        maxX : 200,
        minY : 0,
        maxY : 234,
        width : 214,
        height : 234
    }
};

var buildPlateImageOffsets = {
    left  : 10,
    right : 9,
    top   : 12,
    bottom: 10
};

//init wizard
function initWizard()
{
	wizard = $('.wizard').wizard();
	disableButton('.button-prev');
	disableButton('.button-next');
	
	$('.wizard').on('changed.fu.wizard', function (evt, data) {
		
		//checkWizard();
	});
	
	$('#myWizard').on('clicked.fu.wizard', function (evt, data) {
		//console.log(evt);
		//console.log(data);
	});
	
	$('.button-prev').on('click', function(e) {
		handleWizard('prev');
	});
	
	$('.button-next').on('click', function(e) {
		handleWizard('next');
	});
	
}
/**
*
*/
function handleWizard(action)
{
	if(action == 'next') handleWizardNext();
	if(action == 'prev') handleWizardPrev();
	/*
	var step = $('.wizard').wizard('selectedItem').step;
	console.log(action + " : " + step);
	
	switch(step){
		case 1:
			break;
		case 2:
			getReady(scanMode);
			return;
			break;
		case 3:
			break;
	}
	if(action == 'next') $('#myWizard').wizard('next');
	if(action == 'prev') $('#myWizard').wizard('previous');
	*/
}
/**
*
*/
function handleWizardNext()
{
	var step = $('.wizard').wizard('selectedItem').step;
	var button = $('.button-next');
	console.log("next : " + step);
	switch(step){
		case 1:
			$('#myWizard').wizard('next');
			enableButton('.button-prev');
			enableButton('.button-next');
			break;
		case 2:
			$('#image').cropper('disable');
			getReady(scanMode);
			/*disableButton('.button-next');*/
			break;
		case 3:
			if(button.attr('data-scan') == 'rotating') handleRotatingScan(); //if rotating
			if(button.attr('data-scan') == 'sweep') handleSweepScan(); //if sweep
			if(button.attr('data-scan') == 'probing') handleProbingScan(); //if probing
			if(button.attr('data-scan') == 'photogrammetry') handlePhotogrammetry();
	}
}
/**
*
*/
function handleWizardPrev()
{
	var step = $('.wizard').wizard('selectedItem').step;
	console.log("prev : " + step);
	switch(step){
		case 1:
			break;
		case 2:
			$('#myWizard').wizard('previous');
			disableButton('.button-prev');
			break;
		case 3:
			$('#image').cropper('enable');

			$('#myWizard').wizard('previous');
			enableButton('.button-prev');
			enableButton('.button-next');
			break;
	}
}
/**
*
*/
function checkWizard()
{
	var step = $('.wizard').wizard('selectedItem').step;
	//console.log("Step: ", step);
}
/**
* retrieve scan mode information
*/
function getScanInformation()
{
	var dropdown = $("#id_scan_mode");
	var scan_mode = dropdown.val();
	
	$.ajax({
		type: 'post',
		dataType: 'json',
		url: '/fabui/scan/getScanModeInfo/' + scan_mode,
	}).done(function(response) {
		showScanInformation(scan_mode, response);
	});
}
/**
* display scan information
* @param int mode
* @param json object
*/
function showScanInformation(mode, object)
{
	var html = '<div class="well well-light">';
	html += '<h5>' + object.info.name + '</h5>';
	html += '<p>'+ object.info.description +'</p>';
	html += '</div>';
	$(".scan-mode-information").html(html);
	
}
/**
*
*/
function objectAction()
{
	var radiobox = $(this);
	$(".existing-object, .new-object").addClass('hidden');  
	var inputToShow = radiobox.val() == 'new' ? '.new-object' : '.existing-object';
	$(inputToShow).removeClass('hidden');
	
}
/**
*
*/
function setScanMode()
{
	var button = $(this);
	scanMode = button.attr('data-scan-mode');
	
	$.ajax({
		type: 'post',
		url: '/fabui/scan/getScanModeSettings/' + scanMode,
	}).done(function(response) {
		$("#step2").html(response);
		$('#myWizard').wizard('next');
		enableButton('.button-prev');
		enableButton('.button-next');
		pageSetUp();
		//getReady(mode_id);
	});
}
/**
* get ready instructions
*/
function getReady(modeId)
{
	if(scanModeInstructions == modeId) {
		console.log("is the same mode, I'm not goint to load getReady");
		$('#myWizard').wizard('next'); //load only if is scan mode is different
		return;
	}
	console.log("loading new instruction page");
	$.ajax({
		type: 'post',
		url: '/fabui/scan/getReady/' + modeId,
	}).done(function(response) {
		$("#step3").html(response);
		$('#myWizard').wizard('next');
		//disableButton('.button-next');
		scanModeInstructions = modeId;
		console.log("done loading new instruction page");
	});
}
/**
*
*/
function setObjectMode()
{
	var radio = $(this);
	objecMode = radio.val();
	if(objecMode == 'new'){
		$(".section-existing-object").hide();
		$(".section-new-object").show();
	}else{
		$(".section-existing-object").show();
		$(".section-new-object").hide();
	}
	
	console.log('setObjectMode', objecMode);
}
/**
* handle rotating slider scan
*/
function initRotatingSlider()
{
	noUiSlider.create(document.getElementById('rotating-slider'), {
		start: 20,
		step: 20,
		connect: "lower",
		range: {'min': 20, 'max' : 100},
	});
	rotatingSlider = document.getElementById('rotating-slider');
	
	rotatingSlider.noUiSlider.on('slide',  function(e){
		var qualityIndex;
		switch(parseInt(e)){
			case 0:
				qualityIndex = 0;
				break;
			case 20:
				qualityIndex = 0;
				break;
			case 40:
				qualityIndex = 1;
				break;
			case 60:
				qualityIndex = 2;
				break;
			case 80:
				qualityIndex = 3;
				break;
			case 100:
				qualityIndex = 4;
				break;
			default:
				qualityIndex = 0;
				break;
		}
		setScanQuality('rotating', scanQualites[qualityIndex], qualityIndex);
	});
}
/**
* handle rotating slider scan
*/
function initSweepSlider()
{
	noUiSlider.create(document.getElementById('sweep-slider'), {
		start: 20,
		step: 20,
		connect: "lower",
		range: {'min': 20, 'max' : 100},
	});
	sweepSlider = document.getElementById('sweep-slider');
	
	sweepSlider.noUiSlider.on('slide',  function(e){
		var qualityIndex;
		switch(parseInt(e)){
			case 0:
				qualityIndex = 0;
				break;
			case 20:
				qualityIndex = 0;
				break;
			case 40:
				qualityIndex = 1;
				break;
			case 60:
				qualityIndex = 2;
				break;
			case 80:
				qualityIndex = 3;
				break;
			case 100:
				qualityIndex = 4;
				break;
			default:
				qualityIndex = 0;
				break;
		}
		setScanQuality('sweep', scanQualites[qualityIndex], qualityIndex);
	});
}
/**
* display scan quality infos
*/
function setScanQuality(mode, object, index)
{
	$(".scan-quality-name").html(object.info.name);
	$(".scan-quality-description").html(object.info.description);
	$(".duck_container").html('<img src="/assets/img/controllers/scan/duck_'+ index +'.png"  class="img_responsive" />');
	
	$(".quality-slices").val(object.values.slices);
	$(".quality-iso").val(object.values.iso);
	$(".quality-resolution-width").val(object.values.resolution.width);
	$(".quality-resolution-height").val(object.values.resolution.height);
	
}
/**
*
*/
function initSweepCrop()
{	
	var $image = $('#image');
	
	$(".sweep-start").attr('min', buildPlateDimensions.sweep.minX);
	$(".sweep-start").attr('max', buildPlateDimensions.sweep.maxX);
	$(".sweep-end").attr('min', buildPlateDimensions.sweep.minX);
	$(".sweep-end").attr('max', buildPlateDimensions.sweep.maxX);
	
	var xCorrect = buildPlateImageOffsets.left;
	var yCorrect = buildPlateImageOffsets.top;   
	var realWidth = buildPlateDimensions.sweep.width + buildPlateImageOffsets.left + buildPlateImageOffsets.right;
	var realHeight = buildPlateDimensions.sweep.height + buildPlateImageOffsets.top + buildPlateImageOffsets.bottom;

	var sweepLeft = xCorrect + buildPlateDimensions.sweep.minX;
	var sweepTop  = yCorrect + (buildPlateDimensions.sweep.height - buildPlateDimensions.sweep.maxY);
	var sweepWidth = buildPlateDimensions.sweep.maxX - buildPlateDimensions.sweep.minX;
	var sweepHeight = buildPlateDimensions.sweep.maxY - buildPlateDimensions.sweep.minY;

	var options = {
		responsive: true,
		guides: false,
		viewMode: 1,
		toggleDragModeOnDblclick : false,
		zoomable: false,
		cropBoxResizable: true,
		
		// Bed mapping
		useMappedDimensions : true,
		mappedWidth : realWidth,
		mappedHeight : realHeight,
		
		initCropBoxX : sweepLeft,
		initCropBoxY : sweepTop,
		initCropBoxWidth : sweepWidth, 
		initCropBoxHeight : sweepHeight,
		
		minCropBoxLeft : sweepLeft,
		minCropBoxTop : sweepTop,
		
		maxCropBoxWidth : sweepWidth,
		maxCropBoxHeight : sweepHeight,
		
		minCropBoxHeight: 5,
		minCropBoxWidth: 5,
		
		background: false,
		crop: function (e) {}
    };
	
	/*var xCorrect = buildPlateImageOffsets.left;
	var yCorrect = buildPlateImageOffsets.top;   
	var realWidth = buildPlateDimensions.sweep.width + buildPlateImageOffsets.left + buildPlateImageOffsets.right;
	var realHeight = buildPlateDimensions.sweep.height + buildPlateImageOffsets.top + buildPlateImageOffsets.bottom;

	var probeLeft = xCorrect + buildPlateDimensions.sweep.minX;
	var probeTop  = yCorrect + (buildPlateDimensions.sweep.height - buildPlateDimensions.sweep.maxY);
	var probeWidth = buildPlateDimensions.probe.maxX - buildPlateDimensions.sweep.minX;
	var probeHeight = buildPlateDimensions.probe.maxY - buildPlateDimensions.sweep.minY;

	var options = {
		responsive: true,
		guides: false,
		viewMode: 1,
		toggleDragModeOnDblclick : false,
		zoomable: false,
		cropBoxResizable: true,
		
		// Bed mapping
		useMappedDimensions : true,
		mappedWidth : realWidth,
		mappedHeight : realHeight,
		
		initCropBoxX : xCorrect,
		initCropBoxY : yCorrect,
		initCropBoxWidth : buildPlateDimensions.sweep.width, 
		initCropBoxHeight : buildPlateDimensions.sweep.height,
		
		
		
		minCropBoxLeft : xCorrect,
		minCropBoxTop : yCorrect,
		
		maxCropBoxWidth : buildPlateDimensions.sweep.width,
		maxCropBoxHeight : buildPlateDimensions.sweep.height,
		
		minCropBoxHeight: 50,
		minCropBoxWidth: 10,
		
		background: false,
        crop: function (e) {}
    };*/
	
	$image.on({
    'crop.cropper': function (e) {

      var tmp1 = $image.cropper('mapToDimensionNatural', e.x, e.y);
      var tmp2 = $image.cropper('mapToDimensionNatural', e.width, e.height);

      var x1 = Math.abs(tmp1.x - xCorrect);
      var x2 = Math.abs(tmp1.x + tmp2.x - xCorrect);
      
      $(".sweep-start").val( x1.toFixed() );
      $(".sweep-end").val( x2.toFixed() );
    },
    'zoom.cropper': function (e) {
      //console.log(e.type, e.ratio);
    }
  }).cropper(options);
}
/**
*
*/
function initProbeCrop()
{
	var $image = $('#image');
	$(".probing-x1").attr( {'min' : buildPlateDimensions.probe.minX,
	 						'max' : buildPlateDimensions.probe.maxX} );
	$(".probing-x2").attr( {'min' : buildPlateDimensions.probe.minX,
	 						'max' : buildPlateDimensions.probe.maxX} );
	$(".probing-y1").attr( {'min' : buildPlateDimensions.probe.minY,
	 						'max' : buildPlateDimensions.probe.maxY} );
	$(".probing-y2").attr( {'min' : buildPlateDimensions.probe.minY,
							'max' : buildPlateDimensions.probe.maxY} );
	
	var xCorrect = buildPlateImageOffsets.left;
	var yCorrect = buildPlateImageOffsets.top;   
	var realWidth = buildPlateDimensions.probe.width + buildPlateImageOffsets.left + buildPlateImageOffsets.right;
	var realHeight = buildPlateDimensions.probe.height + buildPlateImageOffsets.top + buildPlateImageOffsets.bottom;

	var probeLeft = xCorrect + buildPlateDimensions.probe.minX;
	var probeTop  = yCorrect + (buildPlateDimensions.probe.height - buildPlateDimensions.probe.maxY);
	var probeWidth = buildPlateDimensions.probe.maxX - buildPlateDimensions.probe.minX;
	var probeHeight = buildPlateDimensions.probe.maxY - buildPlateDimensions.probe.minY;

	var options = {
		responsive: true,
		guides: false,
		viewMode: 1,
		toggleDragModeOnDblclick : false,
		zoomable: false,
		cropBoxResizable: true,
		
		// Bed mapping
		useMappedDimensions : true,
		mappedWidth : realWidth,
		mappedHeight : realHeight,
		
		initCropBoxX : probeLeft,
		initCropBoxY : probeTop,
		initCropBoxWidth : probeWidth, 
		initCropBoxHeight : probeHeight,
		
		minCropBoxLeft : probeLeft,
		minCropBoxTop : probeTop,
		
		maxCropBoxWidth : probeWidth,
		maxCropBoxHeight : probeHeight,
		
		minCropBoxHeight: 5,
		minCropBoxWidth: 5,
		
		background: false,
		crop: function (e) {}
    };
	
	$image.on({
	'crop.cropper': function (e) {
		var tmp1 = $image.cropper('mapToDimensionNatural', e.x, e.y);
		var tmp2 = $image.cropper('mapToDimensionNatural', e.width, e.height);
		
		var x1 = Math.abs(tmp1.x - xCorrect);
		var x2 = Math.abs(tmp1.x + tmp2.x - xCorrect);
		var y2 = Math.abs(buildPlateDimensions.probe.height - (tmp1.y - yCorrect) );
		var y1 = Math.abs(buildPlateDimensions.probe.height - (tmp1.y + tmp2.y - yCorrect) );
		
		$(".probing-x1").val(x1.toFixed());
		$(".probing-y1").val(y1.toFixed());
		$(".probing-x2").val(x2.toFixed());
		$(".probing-y2").val(y2.toFixed());
	},
  }).cropper(options);
}
/**
*
*/
function initProbingSlider()
{
	noUiSlider.create(document.getElementById('probing-slider'), {
		start: 0,
		step: 20,
		connect: "lower",
		range: {'min': 0, 'max' : 100},
	});
	probingSlider = document.getElementById('probing-slider');
	
	probingSlider.noUiSlider.on('slide',  function(e){
		var qualityIndex;
		switch(parseInt(e)){
			case 0:
				qualityIndex = 0;
				break;
			case 20:
				qualityIndex = 1;
				break;
			case 40:
				qualityIndex = 2;
				break;
			case 60:
				qualityIndex = 3;
				break;
			case 80:
				qualityIndex = 4;
				break;
			case 100:
				qualityIndex = 5;
				break;
			default:
				qualityIndex = 0;
				break;
		}
		setProbingQuality(probingQualities[qualityIndex], qualityIndex);
	});		
}
/**
*
*/
function setProbingQuality(object, index)
{
	$(".scan-probing-quality-name").html(object.info.name);
	$(".scan-probing-sqmm").html(object.values.sqmm);
}
/**
*
*/
function handleRotatingScan()
{
	var button = $('.button-next');
	var action = button.attr('data-action');
	
	if(action == 'prepare'){
		openWait('<i class="fa fa-spinner fa-spin"></i> Preparing Scan');
		$.ajax({
			type: 'get',
			url: '/fabui/scan/prepareScan',
			dataType: 'json'
		}).done(function(response) {
			console.log(response);
			if(response.response == false){
				closeWait();
				showErrorAlert('Warning', response.message);
			}else{
				button.attr('data-action', 'start');
				closeWait();
				$( "#rotating-first-row" ).remove();
				$( "#rotating-second-row" ).removeClass('hidden').addClass("animated slideInRight");
				
			}
		});
	}else if(action == 'start'){
		
		openWait('Start');
		
		var $radio = $(':radio[name="object_type"]:checked');
		var object_mode = $radio.val();
		
		var data = {
			'slices'      : $(".quality-slices").val(),
			'iso'         : $(".quality-iso").val(),
			'width'       : $(".quality-resolution-width").val(), 
			'height'      : $(".quality-resolution-height").val(),
			'object_mode' : object_mode,
			'object'      : object_mode == 'new' ? $("#scan-object-name").val() : $("#scan-objects-list option:selected").val(),
			'file_name'   : $("#scan-file-name").val()
		};
		
		$.ajax({
			type: 'post',
			url: '/fabui/scan/startScan/' + scanMode,
			data: data,
			dataType: 'json'
		}).done(function(response) {
			console.log(response);
			if(response.start == false){
				closeWait();
				showErrorAlert('Warning', response.message);
			}else{
				startTask();
			}
		});
	}
}

/**
*
*/
function handleSweepScan()
{
	var button = $('.button-next');
	var action = button.attr('data-action');
	
	$('#image').cropper('disable');
	console.log('image.cropper disable');
	
	if(action == 'start'){
		
		var $radio = $(':radio[name="object_type"]:checked');
		var object_mode = $radio.val();
		
		var data = {
			'slices': $(".quality-slices").val(), 
			'iso'   : $(".quality-iso").val(), 
			'width' : $(".quality-resolution-width").val(), 
			'height': $(".quality-resolution-height").val(), 
			'start' : $(".sweep-start").val(), 
			'end'   : $(".sweep-end").val(),
			'object_mode' : object_mode,
			'object'      : object_mode == 'new' ? $("#scan-object-name").val() : $("#scan-objects-list option:selected").val(),
			'file_name'   : $("#scan-file-name").val()
		};
		
		openWait('Start');
		$.ajax({
			type: 'post',
			url: '/fabui/scan/startScan/' + scanMode,
			data: data,
			dataType: 'json'
		}).done(function(response) {
			console.log(response);
			if(response.start == false){
				closeWait();
				showErrorAlert('Warning', response.message);
			}else{
				startTask();
			}
		});
	}
}

/**
*
*/
function handleProbingScan()
{
	var button = $('.button-next');
	var action = button.attr('data-action');
	
	if(action == 'start'){
		
		var $radio = $(':radio[name="object_type"]:checked');
		var object_mode = $radio.val();
		var offsetX = buildPlateDimensions.probe.offsetX;
		var offsetY = buildPlateDimensions.probe.offsetY;
		
		var data = {
			'safe_z': $(".probing-z-hop").val(), 
			'threshold': $(".probing-probe-skip").val(), 
			'density' : $(".scan-probing-sqmm").html(),
			'x1' : ( parseInt($(".probing-x1").val()) + offsetX), 
			'y1' : ( parseInt($(".probing-y1").val()) + offsetY), 
			'x2' : ( parseInt($(".probing-x2").val()) + offsetX), 
			'y2' : ( parseInt($(".probing-y2").val()) + offsetY),
			'object_mode' : object_mode,
			'object'      : object_mode == 'new' ? $("#scan-object-name").val() : $("#scan-objects-list option:selected").val(),
			'file_name'   : $("#scan-file-name").val()
		};
		
		console.log('start');
		openWait('Start');
		$.ajax({
			type: 'post',
			url: '/fabui/scan/startScan/' + scanMode,
			data: data,
			dataType: 'json'
		}).done(function(response) {
			console.log(response);
			if(response.start == false){
				closeWait();
				showErrorAlert('Warning', response.message);
			}else{
				startTask();
			}
		});
	}
}
/**
*
*/
function handlePhotogrammetry()
{
	var button = $('.button-next');
	var action = button.attr('data-action');
	
	if(action == 'prepare'){
		openWait('<i class="fa fa-spinner fa-spin"></i> Preparing Scan');
		$.ajax({
			type: 'get',
			url: '/fabui/scan/prepareScan',
			dataType: 'json'
		}).done(function(response) {
			console.log(response);
			if(response.response == false){
				closeWait();
				showErrorAlert('Warning', response.message);
			}else{
				button.attr('data-action', 'start');
				closeWait();
				$( "#rotating-first-row" ).remove();
				$( "#rotating-second-row" ).removeClass('hidden').addClass("animated slideInRight");
			}
		});
	}else if(action == 'start'){
		var data = {
			'iso': $("#pg-iso option:selected").val(), 
			'slices': $("#pg-slices").val(), 
			'size' : $("#pg-size option:selected").val(),
			'address' : $("#pc-host-address").val(), 
			'port' : $("#pc-host-port").val(), 
		};
		openWait('Start');
		$.ajax({
			type: 'post',
			url: '/fabui/scan/startScan/' + scanMode,
			data: data,
			dataType: 'json'
		}).done(function(response) {
			console.log(response);
			if(response.start == false){
				closeWait();
				showErrorAlert('Warning', response.message);
			}else{
				startTask();
			}
		});
	}
}
/**
*
*/
function initScanPage(running)
{
	initWizard();
	$(".abort").on('click', abortScan);
	$(".pause").on('click', pauseScan);
	if(running){
		initRunningTaskPage();
	}else{
		$(".mode-choise").on('click', setScanMode);
	}
	
}
//when page is ready
$(document).ready(function() { 
	
});
/**
*
**/
function startTask()
{
	closeWait();
	$('#myWizard').wizard('next');
	disableButton('.button-prev');
	disableButton('.button-next');
	fabApp.freezeMenu('scan');
	fabApp.disableTopBarControls();
	setInterval(timer, 1000);
	ga('send', 'event', 'scan', 'start', 'Started scan: ' + scanMode);
	disableCompleteSteps();
	setInterval(traceMonitor, 1000);
	setInterval(jsonMonitor, 1000);
	
}  

/**
*
**/
function initRunningTaskPage()
{
	console.log("init running task");
	fabApp.disableTopBarControls();
	getTaskMonitor(true);
	getTrace();
	setInterval(traceMonitor, 1000);
	setInterval(jsonMonitor, 1000);
}
/**
*
**/
function getTaskMonitor(firstCall)
{
	$.get('/temp/task_monitor.json'+ '?' + jQuery.now(), function(data, status){
		manageMonitor(data);
		if(firstCall){
			elapsedTime = parseInt(data.task.duration);
			setInterval(timer, 1000);
		}
	});
}
/**
 * 
 */
function getTrace()
{
	$.get('/temp/trace'+ '?' + jQuery.now(), function(data, status){
		fabApp.handleTrace(data);
	});
}
/**
 * 
 */
function traceMonitor()
{
	if(!socket_connected || socket.fallback) getTrace();
}
/**
 * 
 */
function jsonMonitor()
{
	if(!socket_connected || socket.fallback) getTaskMonitor(false);
}
/**
*
**/
function updateTaskProgress(value)
{
	$(".task-progress").html(parseFloat(value).toFixed(1) + " %");
	$("#task-progress-bar").attr("style", "width:" +value +"%;");
}
/**
*
**/
function updatePostprocessingProgressBar(value)
{
	$(".postprocessing-progress").html(parseFloat(value).toFixed(1) + " %");
	$("#postprocessing-progress-bar").attr("style", "width:" +value + "%");
}
/**
*
**/
function updateSlices(total, current)
{
	$(".current-scan").html(current);
	$(".total-scan").html(total);
}
/**
*
**/
function timer()
{	
	elapsedTime++;
	$(".elapsed-time").html(transformSeconds(elapsedTime));
}
/**
*
**/
function handleTaskStatus(status)
{
	
	switch(status){
		case 'completing':
			completingTask();
			break;
		case 'completed':
			completeTask();
			break;
		case 'aborting':
			abortingTask();
			break;
		case 'aborted':
			abortTask();
			break;
		default:
			isRunning = true;
			console.log(status);
	}
}
/**
*
**/
function updateClouds(number, size)
{
	$(".cloud-points").html(number);
	$(".cloud-size").html(humanFileSize(size));
}
/**
*
**/
function updateResolution(width, height)
{
	$(".resolution-width").html(width);
	$(".resolution-height").html(height);
}
/**
*
**/
function updateIso(value)
{
	$(".iso").html(value);
}
/**
*
**/
function abortScan()
{	
	disableButton('.abort');
	openWait('<i class="fa fa-spinner fa-spin "></i> Aborting scan', 'Please wait..', false);
	doAction('abort');
}
/**
*
**/
function pauseScan()
{	
	//disableButton('.pause');
	var action = $(".pause").attr('data-action');
	if(action == 'pause')
	{
		$(".pause").attr('data-action', 'resume');
		$(".pause").html('<i class="fa fa-play"></i> Resume scan');
	}
	else
	{
		$(".pause").attr('data-action', 'pause');
		$(".pause").html('<i class="fa fa-pause"></i> Pause scan');
	}
	//
	
	doAction(action);
}
function doAction(action)
{
	$.ajax({
		type: 'post',
		url: "control/taskAction/" + action,
		dataType: 'json'
	}).done(function(response) {
	});
}
/**
*
**/
function completingTask()
{
	if(isCompleting == false) openWait('Finalizing scan');
	isCompleting = true;
}
/***
**
**/
function completeTask()
{
	if(isCompleted == false) {
		closeWait();
		//openWait('Scan completed');
		ga('send', 'event', 'scan', 'complete', 'Completed scan: ' + scanMode);
		gotoWizardFinish();
		fabApp.unFreezeMenu();
		/**
		*
		**/
		console.log(scanMode);
		if(scanMode != "photogrammetry"){
			var projectsManagerURL = '/fabui/projectsmanager/project/' + objectID;
			var downloadURL = '/fabui/projectsmanager/download/file/' + fileID;
			$("#got-to-projects-manager").attr('href', projectsManagerURL);
			$("#download-file").attr('href', downloadURL);
			$("#download-missing-images").hide();
		}else{
			$("#download-file").hide();
			$("#got-to-projects-manager").hide();
		}
		
		transformLinks();
		
	}
	isCompleted = true;
}
/**
*
**/
function abortingTask()
{
	if(isAborting == false) openWait('Aborting scan');
	isAborting = true;
}
/**
*
**/
function abortTask()
{
	if(isAborted == false){
		ga('send', 'event', 'scan', 'abort', 'Aborted scan: ' + scanMode);
		openWait('Scan aborted');
		waitContent('refreshing page');
		setTimeout(function () {
			location.reload();
		}, 5000);
	}
	isAborted = true;
}
/**
*
**/
function gotoWizardFinish()
{
	$('.wizard').wizard('selectedItem', { step: 5 });
}
/**
 * 
*/
function disableCompleteSteps()
{
	$(".steps .complete").css('cursor', 'default');
	$(".steps .complete").on('click', function(){return false;});
}
/**
*
*/
function testArea(e)
{
	var button = $(e.toElement);

	var skip_homing = true;
	
	if(button.attr('data-skip-homing')){
		skip_homing = false;
		button.removeAttr('data-skip-homing');
	}
	
	var offsetX = buildPlateDimensions.probe.offsetX;
	var offsetY = buildPlateDimensions.probe.offsetY;
	
	
	var data = {
		'x1' : ( parseInt($(".probing-x1").val()) + offsetX), 
		'y1' : ( parseInt($(".probing-y1").val()) + offsetY), 
		'x2' : ( parseInt($(".probing-x2").val()) + offsetX), 
		'y2' : ( parseInt($(".probing-y2").val()) + offsetY),
		'skip_homing' : skip_homing
	};
		
	openWait(_("Probing selected area"));
	$.ajax({
		type: 'post',
		url: '/fabui/scan/testProbingArea/',
		data: data,
		dataType: 'json'
	}).done(function(response) {
		closeWait();
	});
	
	
	
}