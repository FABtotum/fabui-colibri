head_tour_steps = [
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
        content: "<strong>Click</strong> on the menu item to continue",
        reflex: true,
        backdrop: true,
        backdropContainer : '#left-panel',
        // workaround for backdrop hide not detected
        /*delay: {
            show: 0,
            hide:200
            }*/
      },
      {
        element: "#heads",
        title: "Select head",
        content: "Choose the installed head",
        reflex: 'change',
        backdrop: true,
        backdropContainer : '#content',
        orphan: true, // workaround for element being loaded later
        // workaround for backdrop hide not detected
        /*delay: {
            show: 200,
            hide:0
            }*/
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
];
