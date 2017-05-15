<?php
/**
 * 
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */

if(ENVIRONMENT == 'development' ){
	foreach($available_tours as $tour_file) {
	    echo '<script type="text/javascript" src="/assets/js/tours/'.$tour_file.'.js?v='.FABUI_VERSION.'"></script>'.PHP_EOL;
	}
}
?>
<script type="text/javascript">  
var active_tour = false;
var active_tour_name = "";
var available_tours = {
	<?php 
		foreach($available_tours as $tour_file) 
		{
			$tour_id = preg_replace('/^[0-9]+_/', '', $tour_file);
			echo $tour_id .': '.$tour_id.'_tour_info,';
		}
	?>
};
    
function hasTourEnded(name)
{
	if (typeof(Storage) !== "undefined") {
		// Code for localStorage/sessionStorage.
		var tour_end = localStorage.getItem(name + "_end");
		return tour_end == "yes";
	} else {
		// Sorry! No Web Storage support..
		return false;
	}
}
/***
 * @TODO 
 */
function onTourEnd(tour)
{
}
/***
 * 
 */
function endTour()
{
    if(active_tour != false)
    {
        active_tour.end();
        active_tour = false;
    }
}
/***
 * 
 */
function updateTour()
{
    if(active_tour != false)
    {
        if(!active_tour.ended())
        {
            active_tour.start(true);
            var step = active_tour.getCurrentStep();
            active_tour.goTo(step);
        }
    }
}

function startTour(name)
{
    steps = [];
    var firstStep = 0;
        
    if( !available_tours.hasOwnProperty(name) )
    {
        console.error('tour not found', name);
        return false;
    }
    else
    {
        steps = available_tours[name].steps;
        active_tour_name = name;
        console.log('startTour', name);
    }
        
    // Skip steps for open menu-items
    for(idx in steps)
    {
		var element = steps[idx].element;
		if( $(element).length > 0 )
		{
			if( element.startsWith("#menu-item") )
			{
				if( $(element).parent().hasClass("open") )
				{
					firstStep++;
				}
				else
				{
					break;
				}
			}
		}
	}
    
    active_tour = new Tour({
		name: name,
        steps: steps,
        debug:true,
        //storage: false,
        onEnd: onTourEnd,
        /*template: '<div class="popover" role="tooltip">\
                    <div class="arrow"></div> \
                    <h3 class="popover-title"></h3> \
                    <div class="popover-content"></div> \
                    <div class="popover-navigation"> \
                    <div class="btn-group"> \
                        <button class="btn btn-sm btn-default" data-role="prev">&laquo; Prev</button> \
                        <button class="btn btn-sm btn-default" data-role="next">Next &raquo;</button> \
                        <button class="btn btn-sm btn-default" data-role="pause-resume" data-pause-text="Pause" data-resume-text="Resume">Pause</button> \
                    </div> \
                    <button class="btn btn-sm btn-default" data-role="end">End tour</button> \
                </div> </div>'*/
    });
    
    active_tour.init();
    //tour.restart();
    active_tour.start(true);
    active_tour.goTo(firstStep);   
    return true;
}
</script>
