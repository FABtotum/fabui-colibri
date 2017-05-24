bed_tour_info = {
	steps: [ 
		{
        element: "#menu-item-maintenance",
        title: _("Maintenance menu"),
        content: _("Click on the menu item to continue"),
        reflex: true,
        backdrop: true,
        backdropContainer : '#left-panel'
      },
      {
        element: "#menu-item-maintenance-bedcalibration",
        title: _("Bed calibration"),
        content: _("Click on the menu item to continue"),
        reflex: true,
        backdrop: true,
        backdropContainer : '#left-panel',
      },
      {
        element: "#wizard-button-next",
        title: _("Next"),
        content: _("Click on the next button to start bed calibration"),
        reflex: true,
        orphan: true,
        backdrop: true,
        backdropContainer : '#content',
        placement:'left'
      }
	],
	show: true,
	name: _("Bed"),
	//icon: '<i class="fa fa-square-o" aria-hidden="true"></i>',
	icon: '<i class="fabui-bed" aria-hidden="true"></i>',
	title: _("Bed calibration"),
	description: _("To get the first layer right the bed must be well calibrated")
}
