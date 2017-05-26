head_tour_info = {
	steps : [
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
        content: _("Click on the menu item to continue"),
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
	],
	show: true,
	name: _("Head"),
	//icon: '<i class="fa fa-toggle-down" aria-hidden="true"></i>',
	icon: '<i class="fabui-head-2" aria-hidden="true"></i>',
	title: _("Install head"),
	description: _("Before starting to use your FABtotum unit you need to install a head")
}
