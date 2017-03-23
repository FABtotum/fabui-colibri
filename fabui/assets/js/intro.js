var tour = {};

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
    tour = new Tour({
      steps: [
      {
        element: "#menu-item-maintenance",
        title: "Title of my step",
        content: "Content of my step"
      },
      {
        element: "#menu-item-maintenance-head",
        title: "Title of my step",
        content: "Content of my step"
      }
    ],
    backdrop: false,
    });

    // Initialize the tour
    tour.init();

    // Start the tour
    tour.start(true);
}
