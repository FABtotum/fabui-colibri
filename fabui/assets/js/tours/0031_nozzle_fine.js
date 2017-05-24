nozzle_fine_tour_info = {
	steps : [
      {
        element: "#menu-item-maintenance",
        title: "Maintenance menu",
        content: "Click on the menu item to continue",
        reflex: true,
        backdrop: true,
        backdropContainer : '#left-panel'
      },
      {
        element: "#menu-item-maintenance-nozzle",
        title: "Nozzle menu",
        content: "Click on the menu item to continue",
        reflex: true,
        backdrop: true,
        backdropContainer : '#left-panel',
      },
      {
        element: "#menu-item-nozzle-height",
        title: "Nozzle height",
        content: "Click on the menu item to continue",
        reflex: true,
        backdrop: true,
        backdropContainer : '#left-panel',
      },
      {
        element: "#nozzle-fine-calibration",
        title: "Calibration",
        content: "Click on this button continue",
        reflex: true,
        backdrop: true,
        orphan: true,
        backdropContainer : '#content',
      },
	],
	show: false,
	name: _("Nozzle"),
	icon: '<i class="fabui-nozzle" aria-hidden="true"></i>',
	title: _("Nozzle height calibration"),
	description: _("After bed is leveled the nozzle height needs to be adjusted")
}
