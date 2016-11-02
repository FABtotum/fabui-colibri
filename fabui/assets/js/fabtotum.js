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
		if(	$(this).attr('href') !== undefined 
			&& $(this).attr('href') != "javascript:void(0);" 
			&& $(this).attr('href').startsWith('#') == false ) 
		{
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

/**
* display alert message 
*/
function showErrorAlert(title, content)
{
	$.smallBox({
		title : title,
		content : content,
		color : "#C46A69",
		timeout: 10000,
		icon : "fa fa-warning"
	});
}
/**
* converting file size in bytes to human readable
* @bytes int
* @si boolean
*/
function humanFileSize(bytes, si) {
	si = si || true;
    var thresh = si ? 1000 : 1024;
    if(Math.abs(bytes) < thresh) {
        return bytes + ' B';
    }
    var units = si
        ? ['kB','MB','GB','TB','PB','EB','ZB','YB']
        : ['KiB','MiB','GiB','TiB','PiB','EiB','ZiB','YiB'];
    var u = -1;
    do {
        bytes /= thresh;
        ++u;
    } while(Math.abs(bytes) >= thresh && u < units.length - 1);
    return bytes.toFixed(1)+' '+units[u];
}
