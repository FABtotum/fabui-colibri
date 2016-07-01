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
		if($(this).hasAttr('href') && ( $(this).attr('href') != "javascript:void(0);" || $(this).attr('href').substring(0, 1) != "#") ) {
    		$(this).attr('href', '#' + $(this).attr('href'));
		}
	});
}
