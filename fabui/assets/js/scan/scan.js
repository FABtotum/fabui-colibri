/**
 * Scan
 * Provides utilities for Scan
 *
 */
 

//init wizard
function initWizard()
{
	wizard = $('.wizard').wizard();
	disableButton('.btn-prev');
	disableButton('.btn-next');
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
	var mode_id = button.attr('data-scan-mode');
	
	$.ajax({
		type: 'post',
		url: '/fabui/scan/getScanModeSettings/' + mode_id,
	}).done(function(response) {
		$("#step2").html(response);
		$('.wizard').wizard('next');
		enableButton('.btn-prev');
		disableButton('.btn-next');
	});
}
/**
*
*/
function setObjectMode()
{
	var radio = $(this);
	var mode = radio.val();
	if(mode == 'new'){
		$(".section-existing-object").hide();
		$(".section-new-object").show();
	}else{
		$(".section-existing-object").show();
		$(".section-new-object").hide();
	}
}
/**
*
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
		console.log(e);
	});
	
}
//when page is ready
$(document).ready(function() {
	initWizard();
	$(".mode-choise").on('click', setScanMode);
});    