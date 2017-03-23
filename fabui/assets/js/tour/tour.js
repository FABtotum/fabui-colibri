var tour = false;
var tour_name = "";

function updateTour()
{
    if(tour != false)
    {
        tour.start(true);
    }
}

function startTour(name)
{
    if(tour != false)
    {
        tour.restart();
        tour.init();
        //tour.goTo(0);
        tour.start(true);
        return;
    }

    tour_name = name;
    
    steps = [];
    
    switch(name)
    {
        case "head":
            steps = head_tour_steps;
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
    
    localStorage.removeItem("tour_current_step");
    localStorage.removeItem("tour_ended");
    
    tour.init();
    //tour.restart();
    //tour.goTo(0);
    tour.start(true);
}
