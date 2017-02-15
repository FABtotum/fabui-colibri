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
			&& $(this).attr('href').startsWith('#') == false 
			&& $(this).hasClass('no-ajax') == false ) 
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

/**
* Refresh datatable
* @tableId Table ID
* @urlData URL where to get the data from
*/
function RefreshTable(tableId, urlData)
{
    $(tableId + "_wrapper").css({ opacity: 0.3 });	
    $.getJSON(urlData, null, function( json )
    {
        table = $(tableId).dataTable();
        oSettings = table.fnSettings();
        
        table.fnClearTable(this);
        
        for (var i=0; i<json.aaData.length; i++)
        {
          table.oApi._fnAddData(oSettings, json.aaData[i]);
        }
        
        oSettings.aiDisplay = oSettings.aiDisplayMaster.slice();
        table.fnDraw();
        $(tableId + "_wrapper").css({ opacity: 1 });
    
    });
}
/**
* Base64 Object
**/
var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9+/=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/rn/g,"n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}
/**
* capitalize first char of a string
*/
String.prototype.capitalize = function() {
    return this.charAt(0).toUpperCase() + this.slice(1);
}
/**
 *  replace all occurrences
 */
String.prototype.replaceAll = function(search, replacement) {
    var target = this;
    return target.replace(new RegExp(search, 'g'), replacement);
};
/**
 * Python like string format function 
 */
String.prototype.format = function() {
    var str = this;   
    var num = arguments.length; 
    console.log('pyformat', arguments, 'str:', str);
    
    var matches = str.match(/{[0-9]}/g);
    if( !matches || matches.length !== num ) 
         throw " wrong number of arguments";
    
    for (var i = 0; i < num; i++)
        str = str.replace('{'+i+'}', arguments[i]);
	
    return str;
};
