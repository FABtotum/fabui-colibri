head_tour_steps = [
      {
        element: "#menu-item-maintenance",
        title: _("Maintenance menu"),
        content: _("Click on the menu item to continue"),
        reflex: true,
        backdrop: true,
        backdropContainer : '#left-panel'
      },
      {
        element: "#menu-item-maintenance-head",
        title: _("Head management"),
        content: _("<strong>Click</strong> on the menu item to continue"),
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
        title: _("Select head"),
        content: _("Choose the installed head<br>Note: if the correct head is selected press Next"),
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
        title: _("Install head"),
        content: _("Click on this button"),
        reflex: true,
        backdrop: true,
        backdropContainer : '#content',
        placement:'left'
      }
];
