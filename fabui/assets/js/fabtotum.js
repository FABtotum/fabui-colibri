/***
 * UTILS FUNCTIONS 
 */
/**
 * 
 * @param {Object} element
 * @desc enable button
 */
function enableButton(element)
{
	$(element).removeClass('disabled');
	$(element).prop("disabled",false);
}
/**
 * 
 * @param {Object} element
 * @desc enable button
 */
function disableButton(element)
{
	$(element).addClass('disabled');
	$(element).prop("disabled",true);
}

/**
 * add # to all links present in $('#content')
 * is needed if ajax page is true 
 */
function transformLinks(container)
{
	container = container || $('#content');
	$.each( container.find('a'), function() {
		if($(this).attr('title') !== undefined && ( $(this).attr('href') != "javascript:void(0);" || $(this).attr('href').substring(0, 1) != "#") ) {
    		$(this).attr('href', '#' + $(this).attr('href'));
		}
	});
}

/**
 * @seconds int
 * return transform seconds to HH:MM:SS format
 */
function transformSeconds(seconds)
{
	var sec_num = parseInt(seconds, 10); // don't forget the second param
    var hours   = Math.floor(sec_num / 3600);
    var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
    var seconds = sec_num - (hours * 3600) - (minutes * 60);

    if (hours   < 10) {hours   = "0"+hours;}
    if (minutes < 10) {minutes = "0"+minutes;}
    if (seconds < 10) {seconds = "0"+seconds;}
    return hours+':'+minutes+':'+seconds;
}
