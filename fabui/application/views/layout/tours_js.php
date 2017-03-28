<?php
/**
 * 
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>

<?php 
// Load tour js files
foreach($available_tours as $tour_file) {
    echo '<script type="text/javascript" src="/assets/js/tours/'.$tour_file.'.js?v='.FABUI_VERSION.'"></script>'.PHP_EOL;
}

// http://preview.codecanyon.net/item/tour-tip-guide/full_screen_preview/10922922?ref=jqueryrain

?>

<script type="text/javascript">
    
var tour = false;
var tour_name = "";

function endTour()
{
    if(tour != false)
    {
        tour.end();
        tour = false;
    }
    
    //localStorage.removeItem("tour_current_step");
   // localStorage.removeItem("tour_ended");
}

function updateTour()
{
    if(tour != false)
    {
        if(!tour.ended())
        {
            tour.start(true);
            var step = tour.getCurrentStep();
            tour.goTo(step);
        }
    }
}

function startTour(name)
{

    steps = [];
    var firstStep = 0;
    
    var available_tours = {
        <?php 
            foreach($available_tours as $tour_file) 
            {
                echo $tour_file .': '.$tour_file.'_tour_steps,';
            }
        ?>
    };
    
    if( !available_tours.hasOwnProperty(name) )
    {
        console.log('tour not found', name);
        return false;
    }
    else
    {
        steps = available_tours[name];
        tour_name = name;
        console.log('startTour', name);
    }
    
    // @TODO: make this automatic based on steps data
    switch(name)
    {
        case "head":
        case "nozzle-fine":
        case "nozzle-assisted":
        case "bed":
        case "spool-load":
        case "spool-unload":
            if( $("#menu-item-maintenance").parent().hasClass("active") )
            {
                firstStep = 1;
            }
            break;
    }
    
    tour = new Tour({
        steps: steps,
        debug:true,
        storage: false
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
    
    //@TODO: make a failsave version of this
    //~ localStorage.removeItem("tour_current_step");
    //~ localStorage.removeItem("tour_ended");
    
    //localStorage.removeItem("tour_current_step");
    //localStorage.removeItem("tour_ended");
    
    tour.init();
    //tour.restart();
    tour.start(true);
    tour.goTo(firstStep);
    
    return true;
}

</script>
