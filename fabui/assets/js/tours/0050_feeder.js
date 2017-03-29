feeder_tour_info = {
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
        element: "#menu-item-maintenance-feedercalibration",
        title: _("Feeder menu"),
        content: _("Click on the menu item to continue"),
        reflex: true,
        backdrop: true,
        backdropContainer : '#left-panel',
      },
      {
        element: "#menu-item-feedercalibration-length",
        title: _("Step calibration menu"),
        content: _("Click on the menu item to continue"),
        reflex: true,
        backdrop: true,
        backdropContainer : '#left-panel',
      },
      {
        element: "#button-start-extrude",
        title: _("Start extrude"),
        content: _("Click on this button to start extruding filament"),
        orphan:true,
        reflex: true,
        backdrop: true,
        backdropContainer : '#content',
        placement: 'left'
      },
	],
	show: true,
	name: _("Feeder"),
	icon: '<i class="fa fa-cog" aria-hidden="true"></i>',
	title: _("Feeder calibration"),
	description: _("This step can be skipped")
}
