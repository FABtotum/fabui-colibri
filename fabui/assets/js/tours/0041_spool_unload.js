spool_unload_tour_info = {
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
        element: "#menu-item-maintenance-spool",
        title: "Spool menu",
        content: "Click on the menu item to continue",
        reflex: true,
        backdrop: true,
        backdropContainer : '#left-panel',
      },
      {
        element: "#spool-unload-choice",
        title: "Spool load",
        content: "Choose load to continue",
        orphan:true,
        reflex: true,
        backdrop: true,
        backdropContainer : '#content',
      },
      {
        element: "#filament-choice",
        title: "Filament type",
        content: "Select the filament you want to load",
        reflex: true,
        backdrop: true,
        orphab: true,
        backdropContainer : '#content',
        placement:'bottom'
      },
      {
        element: "#wizard-button-next",
        title: "Next",
        content: "Click on the next button to continue",
        reflex: true,
        backdrop: true,
        orphab: true,
        backdropContainer : '#content',
        placement:'left'
      },
      {
        element: "#wizard-button-next",
        title: "Next",
        content: "Click on the next button to start spool unloading",
        reflex: true,
        backdrop: true,
        orphab: true,
        backdropContainer : '#content',
        placement:'left'
      },
	],
	show: false,
	name: _("Spool"),
	icon: '',
	title: _("Unload spool"),
	description: _("Some description")
}
