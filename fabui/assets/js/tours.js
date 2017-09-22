var tour = false;
var tour_name = "";

function endTour()
{
    if(tour != false)
    {
        tour.end();
    }
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
    var firstStep = 1;
    
    // @TODO: make this automatic with php
    var available_tours = {
        head:               head_tour_steps,
        nozzle_assisted:    nozzle_assisted_tour_steps,
        nozzle_fine:        nozzle_fine_tour_steps,
        bed:                bed_tour_steps,
        spool_load:         spool_load_tour_steps,
        spool_unload:       spool_unload_tour_steps,
        feeder:             feeder_tour_steps,
    };
    
    if( !available_tours.hasOwnProperty(name) )
    {
        return false;
    }
    else
    {
        steps = available_tours[name];
        tour_name = name;
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
    
    tour.init();
    tour.start(true);
    tour.goTo(firstStep);
    
    return true;
}
