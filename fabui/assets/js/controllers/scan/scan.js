/**
 * Scan
 * Provides utilities for Scan
 *
 */
 

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
		$('#myWizard').wizard('next'); //load only if is scan mode is different
		return;
	}
	$.ajax({
		type: 'post',
		url: '/fabui/scan/getReady/' + modeId,
	}).done(function(response) {
		$("#step3").html(response);
		$('#myWizard').wizard('next');
		//disableButton('.button-next');
		scanModeInstructions = modeId;
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
	$(".duck_container").html('<img src="/assets/img/scan/duck_'+ index +'.png"  class="img_responsive" />');
	
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
	
	console.log($image);
	
	var options = {
		responsive: true,
		guides: false,
		viewMode: 1,
		toggleDragModeOnDblclick : false,
		zoomable: false,
		cropBoxResizable: true,
		minCropBoxHeight: 233,
		minCropBoxWidth: 1,
		background: false,
        crop: function (e) {}
    };
	
	$image.on({
    'build.cropper': function (e) {
      //console.log(e.type);
    },
    'built.cropper': function (e) {
      //console.log(e.type);
    },
    'cropstart.cropper': function (e) {
      //console.log(e.type, e.action);
    },
    'cropmove.cropper': function (e) {
      //console.log(e.type, e.action);
    },
    'cropend.cropper': function (e) {
      //console.log(e.type, e.action);
    },
    'crop.cropper': function (e, t) {
		
		$(".sweep-start").val(parseInt(e.x).toFixed());
		$(".sweep-end").val((parseInt(e.width) + parseInt(e.x)));
		
      //console.log(e.type, e.x, e.y, e.width, e.height, e.rotate, e.scaleX, e.scaleY);
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
	var options = {
		responsive: true,
		guides: false,
		viewMode: 1,
		toggleDragModeOnDblclick : false,
		zoomable: false,
		cropBoxResizable: true,
		//minCropBoxHeight: 233,
		//minCropBoxWidth: 185,
		background: false,
        crop: function (e) {}
    };
	
	$image.on({
    'build.cropper': function (e) {
      //console.log(e.type);
    },
    'built.cropper': function (e) {
      //console.log(e.type);
    },
    'cropstart.cropper': function (e) {
      //console.log(e.type, e.action);
    },
    'cropmove.cropper': function (e) {
      //console.log(e.type, e.action);
    },
    'cropend.cropper': function (e) {
		var coords = $image.cropper("getData");
		if(parseInt(coords.x).toFixed() < 32) {
			coords.x = 32;
			$image.cropper("setData", coords);
		}
		//console.log(parseInt(coords.x).toFixed());
		
    },
    'crop.cropper': function (e) {
		
		var coords = e;
		
		if(coords.x < 32) coords.x = 32;
		if(coords.y > 168) coords.y = 168;
		
		$(".probing-x1").val(parseInt(coords.x).toFixed());
		$(".probing-y1").val(parseInt(coords.y).toFixed());
		$(".probing-x2").val(parseInt(coords.width).toFixed());
		$(".probing-y2").val(parseInt(coords.height).toFixed());
		
		/*if(e.x < 32) {
			$image.cropper("setData", {"x": 32});
			return;
		}*/
	 /*
	 if((e.y+ e.height) > 168){
		 console.log("fuori: ", (e.y+ e.height));
		 //$image.cropper("setData", {"y": 170});
		 //$image.cropper("setData", {"height": 168});
	 } 
	 */
	 //if(e.y > 170 ) $image.cropper("setData", {"y": 170});
	 //if(e.height > 168) $image.cropper("setData", {"height": 168});
     //console.log(e.type, e.x, e.y, e.width, e.height, e.rotate, e.scaleX, e.scaleY);
		
	 
    },
    'zoom.cropper': function (e) {
      //console.log(e.type, e.ratio);
    }
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
		openWait('<i class="fa fa-spinner fa-spin"></i> Preaparing Scan');
		$.ajax({
			type: 'get',
			url: '/fabui/scan/prepareScan',
			dataType: 'json'
		}).done(function(response) {
			console.log(response);
			if(response.response == false){
				closeWait();
				showErrorAlert('Warning', response.trace);
			}else{
				button.attr('data-action', 'start');
				closeWait();
				$( "#rotating-first-row" ).remove();
				$( "#rotating-second-row" ).removeClass('hidden').addClass("animated slideInRight");
				
			}
		});
	}else if(action == 'start'){
		
		openWait('start');
		
		var data = {
			'slices'      : $(".quality-slices").val(),
			'iso'         : $(".quality-iso").val(),
			'width'       : $(".quality-resolution-width").val(), 
			'height'      : $(".quality-resolution-height").val(),
			'object_mode' : objectMode,
			'object'      : objectMode == 'new' ? $("#scan-object-name").val() : $("#scan-objects-list").val(),
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
				showErrorAlert('Warning', response.trace);
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
	
	if(action == 'start'){
		
		var data = {
			'slices': $(".quality-slices").val(), 
			'iso'   : $(".quality-iso").val(), 
			'width' : $(".quality-resolution-width").val(), 
			'height': $(".quality-resolution-height").val(), 
			'start' : $(".sweep-start").val(), 
			'end'   : $(".sweep-end").val(),
			//'start'   : 5,
			//'end'     : 8
			'object_mode' : objectMode,
			'object'      : objectMode == 'new' ? $("#scan-object-name").val() : $("#scan-objects-list").val(),
			'file_name'   : $("#scan-file-name").val()
			
		};
		
		openWait('start');
		$.ajax({
			type: 'post',
			url: '/fabui/scan/startScan/' + scanMode,
			data: data,
			dataType: 'json'
		}).done(function(response) {
			console.log(response);
			if(response.start == false){
				closeWait();
				showErrorAlert('Warning', response.trace);
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
		
		var data = {
			'safe_z': $(".probing-z-hop").val(), 
			'threshold': $(".probing-probe-skip").val(), 
			'density' : $(".scan-probing-sqmm").html(),
			'x1' : $(".probing-x1").val(), 
			'y1' : $(".probing-y1").val(), 
			'x2' : $(".probing-x2").val(), 
			'y2' : $(".probing-y2").val(),
			'object_mode' : objectMode,
			'object'      : objectMode == 'new' ? $("#scan-object-name").val() : $("#scan-objects-list").val(),
			'file_name'   : $("#scan-file-name").val()
		};
		
		console.log('start');
		openWait('start');
		$.ajax({
			type: 'post',
			url: '/fabui/scan/startScan/' + scanMode,
			data: data,
			dataType: 'json'
		}).done(function(response) {
			console.log(response);
			if(response.start == false){
				closeWait();
				showErrorAlert('Warning', response.trace);
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
		openWait('<i class="fa fa-spinner fa-spin"></i> Preaparing Scan');
		$.ajax({
			type: 'get',
			url: '/fabui/scan/prepareScan',
			dataType: 'json'
		}).done(function(response) {
			console.log(response);
			if(response.response == false){
				closeWait();
				showErrorAlert('Warning', response.trace);
			}else{
				button.attr('data-action', 'start');
				closeWait();
				$( "#rotating-first-row" ).remove();
				$( "#rotating-second-row" ).removeClass('hidden').addClass("animated slideInRight");
			}
		});
	}else if(action == 'start'){
		startTask();
	}
}
/**
*
*/
function initScanPage(running)
{
	initWizard();
	$(".abort").on('click', abortScan);
	if(running){
		console.log("running");
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
	setInterval(timer, 1000);
}  
/**
* override default manage monitor for scan controller
*/
if(typeof manageMonitor != 'function'){
	window.manageMonitor = function(data){
		updateTaskProgress(data.task.percent);
		if(data.scan.hasOwnProperty('postprocessing_percent')){
			$(".postprocessing").show();
			updatePostprocessingProgressBar(data.scan.postprocessing_percent);
		}
		
		updateSlices(data.scan.scan_total, data.scan.scan_current);
		updateClouds(data.scan.point_count, data.scan.cloud_size);
		updateResolution(data.scan.width, data.scan.height);
		updateIso(data.scan.iso);
		handleTaskStatus(data.task.status);
	};
}
/**
*
**/
function initRunningTaskPage()
{
	getTaskMonitor(true);
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
	
	if(status == 'completing'){		
		if(isCompleting == false) openWait('Finalizing scan');
		isCompleting = true;
	}else if(status == 'completed'){
		if(isCompleted == false) {
			openWait('Scan completed');
			waitContent('refreshing page');
			setTimeout(function () {
				location.reload();
			}, 5000);
		}
		isCompleted = true;
	}else if(status == 'aborting'){
		if(isAborting == false) openWait('Aborting scan');
		isAborting = true;
	}else if(status == 'aborted'){
		if(isAborted == false){
			openWait('Scan aborted');
			waitContent('refreshing page');
			setTimeout(function () {
				location.reload();
			}, 5000);
		}
		isAborted = true;
	}else{
		isRunning = true;
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
	doAction('do_abort', abortCallback);
}
/**
*
**/
function abortCallback(data)
{
	console.log(data);
}
/**
*
**/
function doAction(action, callback)
{
	$.ajax({
		type: 'get',
		url: '/fabui/xmlrpc/method/' + action,
		dataType: 'json'
	}).done(function(response) {
		callback(response);
	});
}
