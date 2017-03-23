var tour = false;

function initItroduction()
{
    tour = new Tour({
      steps: [
      {
        element: "#menu-item-maintenance",
        title: "Maintenance menu",
        content: "Click on the menu item to continue",
        reflex: true,
        backdrop: true,
        backdropContainer : '#left-panel'
      },
      {
        element: "#menu-item-maintenance-head",
        title: "Head management",
        content: "Click on the menu item to continue",
        reflex: true,
        backdrop: true,
        backdropContainer : '#left-panel'
      },
      {
        element: "#heads",
        title: "Select head",
        content: "Choose the installed head",
        reflex: true,
        backdrop: true,
        backdropContainer : '#content',
        orphan: true
      },
      {
        element: "#set-head",
        title: "Install head",
        content: "Click on this button",
        reflex: true,
        backdrop: true,
        backdropContainer : '#content',
        placement:'left'
      }
    ],
    
    });
    
    // Initialize the tour
    tour.init();
}

function startIntroduction()
{
    // == IntroJS ==
    //$("#left-panel > nav > ul > li:nth-child(5) > a").trigger('click');
    /*introJs().onexit(function() {
        console.log('introJS on exit');
        alert("exit of introduction");
    });*/

    //~ *introJs()exitOnEsc exitOnOverlayClick
    //introJs().start().setOption("exitOnEsc", false).setOption("exitOnOverlayClick", false);
    
    // == Bootstrap-tour ==
    // Instance the tour
    
    if(tour == false)
    {
        console.log('initItroduction');
        initItroduction();
        tour.start(true);
    }
    console.log('startIntroduction');
    
    // Start the tour
    
}
