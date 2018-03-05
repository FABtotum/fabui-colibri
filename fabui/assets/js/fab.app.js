/** @file fab.app.js
 *  @brief FABUI-colibri WebApp implementation
 *
 *
 *  @author Krios Mane (km@fabtotum.com)
 *  @author Daniel Kesler (dk@fabtotum.com)
 * 
 *  @bug No known bugs.
 */

/*                  ______________________________________
           ________|                                      |_______
           \       |           fabui-colibri WebApp       |      /
            \      |      Copyright © 2018 FABteam        |     /
            /      |______________________________________|     \
           /__________)                                (_________\
 *
 * =======================================================================
 * =======================================================================
**/

/**
 * fabApp is a variable holding fabApp object instance.
 * @class
 */
fabApp = (function(app) {
	app.installed_head =  null;
	app.rebooting = false; //is the unit rebooting?
	app.intervals = new Array();
	app.dropZoneList = new Array();
	app.favicon_interval = null;
	app.favicon = '';
	app.FabActions = function(){
		var fabActions = {	
			userLogout: function($this){
				$.SmartMessageBox({
					title: "<i class='fa fa-sign-out txt-color-orangeDark'></i> " + _("Hi")  + " <span class='txt-color-orangeDark'><strong>" + $this.data("user-name") + "</strong></span> ",
					content : $this.data('logout-msg') || "You can improve your security further after logging out by closing this opened browser",
					buttons: "[" + _("Cancel") + "][" + _("Go") + "]",
					input: "select",
					options: "[" + _("Shutdown")  +"][" + _("Restart")  + "][" + _("Logout") + "]"
				}, function(ButtonPressed, Option) {
					if(ButtonPressed == _("Cancel")){ //cancel
						return;
					}
					if (Option == _("Logout")) { //logout
						app.logout();
					}
					if(Option == _("Shutdown")){ //shutdown
						app.poweroff();
					}
					if(Option == _("Restart")){ //restart
						app.reboot();
					}
				});
			},
			
			resetController: function($this){
				$.SmartMessageBox({
                    title: "<i class='fa fa-bolt'></i> <span class='txt-color-orangeDark'><strong>Reset Controller</strong></span> ",
                    content: $this.data("reset-msg") || "You can improve your security further after logging out by closing this opened browser",
                    buttons: "[" + _("No") + "][" + _("Yes") + "]"
                }, function(ButtonPressed) {
                   if(ButtonPressed == _("Yes")) app.resetController(); //yes
               });
				
			},
			
			emergencyButton: function($this){
				app.stopAll();
			}
		};
		
		$.root_.on('click', '[data-action="fabUserLogout"]', function(e) {
			var $this = $(this);
			fabActions.userLogout($this);
			e.preventDefault();
			//clear memory reference
			$this = null;
			
		});
		
		$.root_.on('click', '[data-action="resetController"]', function(e) {
			var $this = $(this);
			fabActions.resetController($this);
			e.preventDefault();
			//clear memory reference
			$this = null;
			
		});
		
		$.root_.on('click', '[data-action="emergencyButton"]', function(e) {
			var $this = $(this);
			fabActions.emergencyButton($this);
			e.preventDefault();
			//clear memory reference
			$this = null;
			
		});
		
		$.root_.on('click', '[data-action="fabidLogin"]', function(e) {
			app.fabIDLogin();
			e.preventDefault();
		});
		
		
		
	};
	/**
	 * 
	 */
	app.jogActionHandler = function(e) {
		
		var mul          = e.multiplier;
		var zstep        = mul * 0.5;
		var xystep       = mul * 1;
		var feedrate     = 1000;
		var waitForFinish= false;
		
		switch(e.action)
		{
			case "right":
			case "left":
			case "up":
			case "down":
			case "down-right":
			case "up-right":
			case "down-left":
			case "up-left":
				app.jogMove(e.action, xystep, feedrate, waitForFinish );
				break;
			case "z-down":
			case "z-up":
				app.jogMove(e.action, zstep, feedrate, waitForFinish);
				break;
			case "home-xy":
			case "home-z":
			case "home-xyz":
				app.jogHomeXYZ();
				break;
		}
		
		return false;
	};
	/**
	* init temperatures and jog control on top bar
	**/
	app.initTopBarControls = function () {
		
		$("#top-temperatures").click(function(a) {
			var b = $(this);
		   	b.next(".top-ajax-temperatures-dropdown").is(":visible") ? (b.next(".top-ajax-temperatures-dropdown").fadeOut(150), b.removeClass("active")) : (b.next(".top-ajax-temperatures-dropdown").fadeIn(150), b.addClass("active"));
		   	var c = b.next(".top-ajax-temperatures-dropdown").find(".btn-group > .active > input").attr("id");
		   	b = null, c = null, a.preventDefault()
       	});
		
		//init temperatures sliders on top
		if (typeof(Storage) !== "undefined") {
			$(".top-bar-nozzle-actual").html(parseInt(localStorage.getItem("nozzle_temp")));
			$(".top-bar-nozzle-target").html(parseInt(localStorage.getItem("nozzle_temp_target")));
			$(".top-bar-bed-actual").html(parseInt(localStorage.getItem("bed_temp")));
			$(".top-bar-bed-target").html(parseInt(localStorage.getItem("bed_temp_target")));
		}
		
		app._createExtruderTemperaturesTopSliders(250);
		app._createBedTemperaturesTopSliders(100);
		
		//jog 
		$("#jog-shortcut").click(function(a) {
        	var b = $(this);
            b.next(".top-ajax-jog-dropdown").is(":visible") ? (b.next(".top-ajax-jog-dropdown").fadeOut(150), b.removeClass("active")) : (b.next(".top-ajax-jog-dropdown").fadeIn(150), b.addClass("active"));
            var c = b.next(".top-ajax-jog-dropdown").find(".btn-group > .active > input").attr("id");
            b = null, c = null, a.preventDefault()
        });
		
		var controls_options = {
			hasZero:false,
			hasRestore:false,
			compact:true,
			percentage:0.95
		};
		
		var $jog_controls_top = $('.top-ajax-jog-controls-holder').jogcontrols(controls_options).on('action', app.jogActionHandler);
		
		$(document).mouseup(function(a) {
            $(".top-ajax-temperatures-dropdown").is(a.target) || 0 !== $(".top-ajax-temperatures-dropdown").has(a.target).length || ($(".top-ajax-temperatures-dropdown").fadeOut(150), $(".top-ajax-temperatures-dropdown").prev().removeClass("active"))
            $(".top-ajax-jog-dropdown").is(a.target) || 0 !== $(".top-ajax-jog-dropdown").has(a.target).length || ($(".top-ajax-jog-dropdown").fadeOut(150), $(".top-ajax-jog-dropdown").prev().removeClass("active"))
        });
		
		
	}
	/**
	 * 
	 */
	app._createExtruderTemperaturesTopSliders = function (max_temp){
		//nozzle target		
		if($("#top-ext-target-temp").length > 0) {
			if(max_temp == 0) max_temp = 1; //workaround for slider init
			
			if(document.getElementById('top-ext-target-temp').noUiSlider != null)
				document.getElementById('top-ext-target-temp').noUiSlider.destroy();
			
			app.topExtruderTargetSlider = noUiSlider.create(document.getElementById('top-ext-target-temp'), {
				start: typeof (Storage) !== "undefined" ? localStorage.getItem("nozzle_temp_target") : 0,
				connect: "lower",
				range: {'min': 0, 'max' : parseInt(max_temp)},
				pips: {
					mode: 'positions',
					values: [0,25,50,75,100],
					density: 5,
					format: wNumb({
						postfix: '&deg;'
					})
				}
			});
			//events
			document.getElementById("top-ext-target-temp").noUiSlider.on('slide',  app.topExtTempSlide);
			document.getElementById("top-ext-target-temp").noUiSlider.on('change', app.topExtTempChange);
			document.getElementById("top-ext-target-temp").noUiSlider.on('start',  app.blockSliders);
			document.getElementById("top-ext-target-temp").noUiSlider.on('end',    app.enableSliders);
		}
		
		//nozzle actual
		if($("#top-act-ext-temp").length > 0) {
			if(document.getElementById('top-act-ext-temp').noUiSlider != null)
				document.getElementById('top-act-ext-temp').noUiSlider.destroy();
			
			app.topExtruderActualSlider = noUiSlider.create(document.getElementById('top-act-ext-temp'), {
				start: typeof (Storage) !== "undefined" ? localStorage.getItem("nozzle_temp") : 0,
				connect: "lower",
				range: {'min': 0, 'max' : parseInt(max_temp)},
				behaviour: 'none'
			});
			$("#top-act-ext-temp .noUi-handle").remove();
		}
	}
	/**
	 * 
	 */
	app._createBedTemperaturesTopSliders = function (max_temp){
		
		//bed target
		if($("#top-bed-target-temp").length > 0) {
			
			if(document.getElementById('top-bed-target-temp').noUiSlider != null)
				document.getElementById('top-bed-target-temp').noUiSlider.destroy();
			
			noUiSlider.create(document.getElementById('top-bed-target-temp'), {
				start: typeof (Storage) !== "undefined" ? localStorage.getItem("bed_temp_target") : 0,
				connect: "lower",
				range: {'min': 0, 'max' : parseInt(max_temp)},
				pips: {
					mode: 'positions',
					values: [0,25,50,75,100],
					density: 5,
					format: wNumb({
						postfix: '&deg;'
					})
				}
			});
			//events
			document.getElementById("top-bed-target-temp").noUiSlider.on('slide',  app.topBedTempSlide);
			document.getElementById("top-bed-target-temp").noUiSlider.on('change', app.topBedTempChange);
			document.getElementById("top-bed-target-temp").noUiSlider.on('start',  app.blockSliders);
			document.getElementById("top-bed-target-temp").noUiSlider.on('end',    app.enableSliders);
			
		}
		
		//bet actual
		if($("#top-act-bed-temp").length > 0) {
			
			if(document.getElementById('top-act-bed-temp').noUiSlider != null)
				document.getElementById('top-act-bed-temp').noUiSlider.destroy();
			
			noUiSlider.create(document.getElementById('top-act-bed-temp'), {
				start: typeof (Storage) !== "undefined" ? localStorage.getItem("bed_temp") : 0,
				connect: "lower",
				range: {'min': 0, 'max' : parseInt(max_temp)},
				behaviour: 'none'
			});
			$("#top-act-bed-temp .noUi-handle").remove();
		}
		
	}
	/**
	*
	**/
	app.disableTopBarControls = function () {
		
		app.disableTopBarTempsControls();
		app.disableTopBarJogControls();
		$("#top-installed-head").attr("href", "javascript:void(0)");
	}
	/**
	 * 
	 */
	app.disableTopBarTempsControls = function(){
		$("#top-temperatures").off();
	}
	/**
	 * 
	 */
	app.disableTopBarJogControls = function() {
		$("#jog-shortcut").off();
	}
	/**
	 * 
	 */
	app.enableTopBarTempsControls = function()
	{
		$("#top-temperatures").click(function(a) {
			var b = $(this);
		   	b.next(".top-ajax-temperatures-dropdown").is(":visible") ? (b.next(".top-ajax-temperatures-dropdown").fadeOut(150), b.removeClass("active")) : (b.next(".top-ajax-temperatures-dropdown").fadeIn(150), b.addClass("active"));
		   	var c = b.next(".top-ajax-temperatures-dropdown").find(".btn-group > .active > input").attr("id");
		   	b = null, c = null, a.preventDefault()
       	});
	}
	/**
	 * 
	 */
	app.enableTopBarJogControls = function ()
	{
		$("#jog-shortcut").click(function(a) {
        	var b = $(this);
            b.next(".top-ajax-jog-dropdown").is(":visible") ? (b.next(".top-ajax-jog-dropdown").fadeOut(150), b.removeClass("active")) : (b.next(".top-ajax-jog-dropdown").fadeIn(150), b.addClass("active"));
            var c = b.next(".top-ajax-jog-dropdown").find(".btn-group > .active > input").attr("id");
            b = null, c = null, a.preventDefault()
        });
	}
	/**
	*
	**/
	app.enableTopBarControls = function ()
	{
		app.enableTopBarTempsControls();
		app.enableTopBarJogControls();
		$("#top-installed-head").attr("href", head_page_ajax_url);
	}
	/**
	*
	**/
	app.domReadyMisc = function() {
		app.urlIntegrityCheck();
		// update notification when ajax-dropdown is closed
		$(document).mouseup(function(e) {
			if (!$('.ajax-dropdown').is(e.target) && $('.ajax-dropdown').has(e.target).length === 0) {
				
				if($('.ajax-dropdown').is(":visible")){
					app.updateNotificationBadge();
				}
			}
		});
		
        $(".language").click(function() {

			var actual_lang = $("#actual_lang").val();
			var new_lang = $(this).attr("data-value");
		
			if (actual_lang != new_lang) {
				$("#lang").val(new_lang);
				openWait('<i class="fa fa-flag"></i><br> Loading language ');
				$("#lang_form").submit();
			}
		
		});
		
		$("#lock").click(function() {
			app.lockScreen();
		});
		
		$("#refresh-notifications").click(function() {
			app.refreshNotificationsContent();
		});
		
		app.initTopBarControls();
		
		if(window.self !== window.top){
			$("#header").hide();
			$("#left-panel").css("padding-top", "0");
		}
	};
	/*
	 * 
	 */
	app.topBedTempSlide = function(e){
		$(".top-bar-bed-target").html(parseInt(e[0]));
		$("#bed-degrees").html(parseInt(e[0]) + '&deg;C');    
	    if($("#bed-target-temp").length > 0){
			document.getElementById('bed-target-temp').noUiSlider.set([parseInt(e[0])]);
	    }
	}
	/*
	 * 
	 */
	app.topBedTempChange = function(e){
		app.serial("setBedTemp", parseInt(e[0]));
	}
	/*
	 * 
	 */
	app.blockSliders = function(){
		
	}
	/*
	 * 
	 */
	app.enableSliders = function(){
		
	}
	/*
	 * 
	 */
	app.topExtTempSlide = function(e){
		$(".top-bar-nozzle-target").html(parseInt(e[0]));
	    $("#ext-degrees").html(parseInt(e[0]) + '&deg;C');
	    if($("#ext-target-temp").length > 0){
	    	document.getElementById('ext-target-temp').noUiSlider.set([parseInt(e[0])]);
	    }
	}
	/**
	 * 
	 */
	app.topExtTempChange = function(e){
		app.serial("setExtruderTemp", parseInt(e[0]));
	}
	
	/**
	 * Move the head and/or bed.
	 * @memberof fabApp
	 * 
	 * @param action   Movement directio (right,left,up,down,z-up,z-down...)
	 * @param step     Movement step in mm
	 * @param feedrate Movement feedrate in mm/min
	 * @param waitforfinish Add M400 to sync the finish callback to end of movement
	 * @param {Function} callback Callback function on execution finish
	 */
	app.jogMove = function (action, step, feedrate, waitforfinish, callback) {
		return app.serial("move", action, callback, step, feedrate, waitforfinish);
	}
	
	/**
	 * Set extruder mode.
	 * @memberof fabApp
	 * 
	 * @param mode   Operation mode (extruder, 4th-axis)
	 * @param {Function} callback Callback function on execution finish
	 */
	app.jogSetExtruderMode = function (mode, callback) {
		return app.serial("setExtruderMode", mode, callback);
	}
	/**
	 * Set current position of all axis to zero.
	 * @memberof fabApp
	 * 
	 * @param {Function} callback Callback function on execution finish
	 */
	app.jogZeroAll = function (callback) {
		return app.serial("zeroAll", true, callback);
	}
	/**
	 * Home XY axis.
	 * @memberof fabApp
	 * 
	 * @param {Function} callback Callback function on execution finish
	 */
	app.jogHomeXY = function (callback) {
		return app.serial("home", "home-xy", callback);
	}
	/**
	 * Home all axis. Z homing is done using z-min endstop.
	 * @memberof fabApp
	 * 
	 * @param {Function} callback Callback function on execution finish
	 */
	app.jogHomeXYZ = function (callback) {
		return app.serial("home", "home-xyz-min", callback);
	}
	/**
	 * Home Z axis using z-min endstop.
	 * @memberof fabApp
	 * 
	 * @param {Function} callback Callback function on execution finish
	 */
	app.jogHomeZ = function (callback) {
		return app.serial("home", "home-z-min", callback);
	}
	/**
	 * Get current jog position
	 * @memberof fabApp
	 * 
	 * @param {Function} callback Callback function on execution finish
	 * @returns {Object}
	 */
	app.jogGetPosition = function (callback) {
		return app.serial("getPosition", true, callback);
	}
	/**
	 * Send gcode commands to jog handler.
	 * @memberof fabApp
	 * 
	 * @param {Function} callback Callback function on execution finish
	 */
	app.jogMdi = function(value, callback) {
		
		var commands = value.split("\n");
		var fixed = [];
		for(var i=0; i<commands.length; i++)
		{
			fixed.push( commands[i].split(";")[0] );
		}
		
		return app.serial('manualDataInput', fixed.join("\n"), callback);
	};
	/*
	 * 
	 */
	app.drawBreadCrumb = function () {
		var a = $("nav li.active > a");
		var b = a.length;
		a.each(function() {
			bread_crumb.append($("<li></li>").html($.trim($(this).clone().children(".badge").remove().end().text()))), --b || (document.title = 'FABUI - ' + bread_crumb.find("li:last-child").text())
		});
	};
	/**
	 * Freeze menu whene tasks are running.
	 * @memberof fabApp
	 * 
	 * @param except Menu item(s) to keep unfrozen.
	 */
	app.freezeMenu = function(except){
		var excepet_item_menu = new Array();
		excepet_item_menu[0] = 'dashboard';
		excepet_item_menu[1] = 'projectsmanager';
		excepet_item_menu[2] = 'make/history';
		excepet_item_menu[3] = except;
		
		var a = $("nav li > a");
		a.each(function() {
			var link = $(this);
			var controller = link.attr('data-controller');
			if(jQuery.inArray( controller, excepet_item_menu ) >= 0 ){
				if(controller == except){
					app.unFreezeParent(link);
					if($(".freeze-menu").length == 0) link.append('<span class="badge bg-color-red pull-right inbox-badge freeze-menu">!</span>');
				}
				number_tasks =  1;
			}else{
				if(!link.next().is('ul')){ // disable only is not a parent link
					link.addClass('menu-disabled');
					link.attr('style', 'color: #bfbfbf !important');
					link.removeAttr('href');
					link.click(function () {return false;});
				}
			}
		});
		app.updateNotificationBadge();
		app.setWorkingFavicon();
	};
	/**
	 * Unfreeze all menu items.
	 * @memberof fabApp
	 */
	app.unFreezeMenu = function () {
		var a = $("nav li > a");
		a.each(function() {
			var link = $(this);
			link.removeClass('menu-disabled');
			link.removeAttr("style");
			link.attr('href', $(this).attr('data-href'));
			if(!link.next().is('ul')){
				link.unbind('click');
			}
		});
		$(".freeze-menu").remove();
		app.removeWorkingFavicon();
	}
	/**
	*
	*/
	app.unFreezeParent = function(link) {
		//TODO
	}
	/**
	 * Show warning message.
	 * @memberof fabApp
	 * 
	 * @param {String} message Message text
	 * @param {String} title   Message title. (default: Warning)
	 */
	app.showWarningAlert = function (message, title) {
		
		title = title || _("Warning") ;
		
		$.smallBox({
			title : title,
			content : message,
			color : "#C46A69",
			timeout: 10000,
			icon : "fa fa-exclamation-triangle"
		});
	}
	/**
	 * Show error message.
	 * @memberof fabApp
	 * 
	 * @param {String} message Message text
	 * @param {String} title   Message title. (default: Error)
	 */
	app.showErrorAlert = function (message, title ) {
		
		title = title || _("Error") ;
		$.smallBox({
			title : title,
			content : message,
			color : "#C46A69",
			timeout: 10000,
			icon : "fa fa-exclamation-triangle"
		});
	}
	/**
	 * Show info message.
	 * @memberof fabApp
	 * 
	 * @param {String} message Message text
	 * @param {String} title   Message title. (default: Info)
	 */
	app.showInfoAlert = function(message, title) {
		title = title || _("Info") ;
		$.smallBox({
			title : title,
			content : message,
			color : "#5384AF",
			timeout: 3000,
			icon : "fa fa-check bounce animated"
		});
	}
	/*
	 *  check for first setup wizard
	 */
	app.checkForFirstSetupWizard = function(){
		$.get(first_setup_url_action, function(data, status){
			if(data.exists == true){
				setTimeout(function() {
						$.smallBox({
							title : "Wizard Setup",
							content : _("It seems that you still did not complete the first recommended setup")+ ": <ul><li>" + _("Install head") + "</li> <li>"+ _("Manual Bed Calibration") + "</li><li>" + _("Nozzle height calibration") +" </li></ul><br>" + _("Without a proper calibration you will not be able to use the FABtotum correctly") + "<br>" + _("Do you want to do it now?") + " <br><br><p class='text-align-right'><a href='#maintenance/first-setup' class='btn btn-primary btn-sm'>" + _("Yes") + "</a> <a href='javascript:javascript:void(0);' class='btn btn-danger btn-sm'>"+ _("No") + "</a> <a href='javascript:fabApp.finalizeWizard();' class='btn btn-warning btn-sm'>" + _("Don't ask me anymore") + "</a> </p>",
							color : "#296191",
							icon : "fa fa-warning swing animated"
						});
				}, 1000);
			}
		});
	};
	app.finalizeWizard = function()
	{
		$.get(first_setup_url_action + '/finalize', function(data, status){
		});
	}
	/**
	 * Launch reset controller command.
	 * @memberof fabApp
	 */
	app.resetController = function() {
		openWait("<i class=\"fa fa-cog fa-spin\"></i> " + _("Resetting controller"), _("Please wait"), false);
		$.get(reset_controller_url_action, function(){
			closeWait();
		});
	}
	/**
	 * Stop all operations and tasks on the fabtotum and refresh the page after 10 seconds.
	 * @memberof fabApp
	 */
	app.stopAll = function(message) {
		message = message || '<i class="fa fa-warning"></i> ' +  _("Emergency stop") ;
		openWait(message, ' ', false);
		$.get(stop_all_url_action, function(){
			waitContent('<i class="fa fa-cog fa-spin"></i> ' + _("Restarting all services") + '<br> ' + _("Please wait"));
			setTimeout(function(){
				waitContent('<i class="fa fa-refresh fa-spin"></i> ' + _("Reloading page"));
				location.reload(); 
			}, 10000);
		});
	}
	/**
	 * Show a message and refresh the page after 3 seconds
	 * @memberof fabApp
	 * 
	 * @param {String} message Message text.
	 */
	app.refreshPage = function(message, timeout) {
		message = message || _("Aborting all operations");
		timeout = timeout || 3000; 
		openWait(message, ' ', false);
		waitContent(_("Reloading page"));
		setTimeout(function(){ 
			location.reload();
		}, timeout);
	}
	/**
	 * Launch reboot command and refresh the page when it's ready again.
	 * @memberof fabApp
	 */
	app.reboot = function() {
		app.rebooting = true;
		clearInterval(temperatures_interval);
		//$.is_macro_on = true;
		openWait("<i class='fa fa-cog fa-spin'></i> " + _("Restart in progress") , _("Please wait") + '...', false);
		$.ajax({
			url: reboot_url_action,
		}).done(function(data) {
			waitContent( _("You will be redirected to login page") );
			app.redirectToUrlWhenisReady(login_url);
		}).fail(function(jqXHR, textStatus){
			//clear intervals
			waitContent( _("You will be redirected to login page") );
			app.redirectToUrlWhenisReady(login_url);
		});
	};
	/**
	 * Launch poweroff command and show popup with instructions.
	 * @memberof fabApp
	 */
	app.poweroff = function() {
		clearInterval(temperatures_interval);
		//is_macro_on = true;
		openWait('<i class="fa fa-cog fa-spin"></i> ' + _("Shutdown in progress") , _("Please wait") + '...', false);
		$.ajax({
			url: poweroff_url_action,
		}).done(function(data) {
			app.showAlertToPowerOff();
		}).fail(function(jqXHR, textStatus){
			app.showAlertToPowerOff();
		});
	};
	/**
	 * Logout from fabui.
	 * @memberof fabApp
	 */
	app.logout = function() {
		$.root_.addClass('animated fadeOutUp');
		setTimeout(function(){
			window.location = logout_url;
		}, 1000);
	};
	/**
	 * Lock fabui screen.
	 * @memberof fabApp
	 */
	app.lockScreen = function(){
		$.root_.addClass('animated fadeOutUp');
		setTimeout(function(){
			$("#lock-screen-form").submit();
		}, 1000);
		
	};
	/*
	 * update notification badge
	 */
	app.updateNotificationBadge = function () {
		
		var totalNotifications = number_updates + number_tasks + number_plugin_updates;
		if(totalNotifications < 0) totalNotifications = 0;
		
		if((totalNotifications) > 0){
			$("#activity").find('.badge').addClass('bg-color-red bounceIn animated');
			
		}else{
			$("#activity").find('.badge').removeClass('bg-color-red bounceIn animated');
		}
		
		$("#activity").find('.badge').html(totalNotifications);
		$(".updates-number").html( '(' + (number_updates + number_plugin_updates) + ')');
		$(".tasks-number").html( '(' + number_tasks + ')');
			
		var a = $("nav li > a");
		a.each(function() {
			var link = $(this);
			var controller = link.attr('data-controller');
			if(controller == 'updates'){
				$("#update-menu-badge").remove();
				if((number_updates+number_plugin_updates) > 0)
				{
					link.append('<span id="update-menu-badge" class="badge pull-right inbox-badge bg-color-red margin-right-13">'+(number_updates+number_plugin_updates)+'</span>');
				}
			}
		});
		
		var a = $("nav li > a");
		a.each(function() {
			var link = $(this);
			var controller = link.attr('data-controller');
			if(controller == 'plugin'){
				$("#plugin-update-menu-badge").remove();
				if(number_plugin_updates > 0 ){
					link.append('<span id="plugin-update-menu-badge" class="badge pull-right inbox-badge bg-color-red margin-right-13">'+number_plugin_updates+'</span>');
				}
			}
		});
	};
	/*
	 * refresh notification content (dropdown list)
	 */
	app.refreshNotificationsContent = function () {
		$(".notification").each(function(index, element) {
			var obj = $(this);
			if (obj.hasClass('active')) {
				var url = obj.find('input[name="activity"]').attr("id");
				var container = $(".ajax-notifications");
				loadURL(url, container);
			}
		});
	};
	/*
	 * Notification interval, check if there are notifications to show (updates, tasks, etc)
	 * if app is connected to the websocket return
	 */
	app.checkNotifications = function () {
		
	}
	/*
	 * Safety interval, check safety status when web socket is not available
	 */
	app.checkSafetyStatus = function() {
		if($.socket_connected == false && $.is_emergency == false){
			$.get($.safety_json_url + '?' + jQuery.now(), function(data) {
				if (data.type == 'emergency') app.manageEmergency(data);
			});
		}
	}
	/*
	 * Manage jog response and jog callbacks
	 */
	app.manageJogResponse = function(data) {
		var stamp = null;
		var response = [];
		
		for(i in data.commands)
		{
			if(stamp != null)
			{
				response.push(data.commands[i]);
			}
			else
			{
				stamp = i.split('_')[0];
				response.push(data.commands[i]);
			}
		}
		if(app.ws_callbacks.hasOwnProperty(stamp))
		{
			app.ws_callbacks[stamp](response);
			delete app.ws_callbacks[stamp];
		}
		app.writeSerialResponseToConsole(data);
	};
	/**
	 * 
	 */
	app.ws_callbacks = {};
	/**
	* web socket error handler - try to reconnect when ws connection is closed
	* 
	**/
	app.ws_onerror = function(e)
	{
		if(ws == null)
			return;
		
		if(debugState) console.log ('Error with WebSocket', ws.readyState);
		if(app.rebooting == false){ //reconnect only if the unit is not rebooting
			app.ws_callbacks = {};
			socket_connected = false;
			app.ws_reconnecting = true;
			
			app.ws_failed++;
			
			if(debugState)
				console.log('wesocket error counter:'+ app.ws_failed);
			
			if( app.ws_failed < 50 )
			{
				setTimeout(function(e){
					app.ws_reconnecting = false;
					app.webSocket(true);
					}, 1000);
			}
			else
			{
				if(ws != null)
				{
					ws.close();
				}
				socket = ws = null;
				app.ws_reconnecting = true;
				socket_connected = false;
				clearInterval(temperatures_interval);
				app.checkConnectivity();
			}
		}
	}
	/**
	* websocket onclose event handler
	**/
	app.ws_onclose = function(e)
	{
		if(ws == null)
			return;
			
		if(debugState) console.log ('WebSocket onClose',ws.readyState);
		if(app.rebooting == false){ //reconnect only if the unit is not rebooting
			app.ws_callbacks = {};	
			socket_connected = false;
			socket = null;
			
			if( app.ws_failed > 50 )
				return;
			
			if(app.ws_reconnecting == false)
			{
				app.ws_reconnecting = true;
				setTimeout(function(e){
					app.ws_reconnecting = false;
					app.webSocket(true);
					}, 1000);
			}
		}
	}
	/**
	*
	*/
	app.ws_reconnecting = false;
	app.ws_failed = 0;
	/**
	* WebSocket for faster communications
	* it goes automatically with ajax pulling method if WebSocket is not supported
	*/
	app.webSocket = function(force_fallback)
	{
		
		if( app.ws_failed > 50 )
			return;
		
		force_fallback = force_fallback || false;
		
		options = {
			http:           websocket_fallback_url,
			force_fallback : force_fallback,        //force websocket emulation instead of native implementation
			interval       : 5000                   // number of ms between poll request
		};
		
		socket = ws = $.WebSocket ('ws://'+socket_host+':'+socket_port, null, options);
		
		ws.onerror = app.ws_onerror;
		ws.onclose = app.ws_onclose;

		// if connection is opened => start opening a pipe (multiplexing)
		ws.onopen = function () {
			socket_connected = true;
			if(debugState) root.console.log("WebSocket opened as" , socket.fallback?"fallback":"native" );
			app.afterSocketConnect();
		};  
		/**
		 * handle messages from the server
		 */
		ws.onmessage = function (e) {
			try {
				var obj = jQuery.parseJSON(e.data);
				if(debugState) console.log("✔ WebSocket received message: %c [" + obj.type + "]", debugStyle);
				switch(obj.type){
					case 'temperatures':
						app.updateTemperatures(obj.data);
						break;
					case 'emergency':
						app.manageEmergency(obj.data);
						break;
					case 'alert':
						app.manageAlert(obj.data);
						break;
					case 'task':
						app.manageTask(obj.data);
						break;
					case 'usb':
						app.usb(obj.data.status, obj.data.alert);
						break;
					case 'eth':
						app.eth(obj.data.status);
						break;
					case 'jog':
						app.manageJogResponse(obj.data);
						break;
					case 'trace':
						app.handleTrace(obj.data.content);
						break;
					case 'poll':
						app.handlePollMessage(obj);
						break;
					case 'updates':
						app.handleUpdatesData(obj.data);
						break;
					case 'hardware-settings':
						app.handleSettingsData(obj.data);
						break;
					case 'network-info':
						app.handleNetworkInfoData(obj.data);
						break;
					default:
						break;
				}
				
				app.manageCustomNotifications(obj);
			}catch(e){
				return;
			}
		}
		setTimeout(app.checkWebSocketStatus, 1000);
	};
	/**
	 *  check websocket availability
	 *  if not switch to ajax-polling mode
	 */
	app.checkWebSocketStatus = function()
	{
		if( ws == null )
			return;
		
		if(app.ws_reconnecting == false && ws.readyState == 3){
			app.ws_onerror();
		}
		if(ws.readyState == 1){
			socket_connected = true;
			if(debugState) root.console.log("✔  WebSocket connected as" , socket.fallback?"fallback":"native");
		}
	}
	/*
	 * update printer status 
	 */
	app.updateTemperatures = function(data){
		//update temperatures
		app.updateTemperaturesInfo(data.temperatures.ext_temp, data.temperatures.ext_temp_target, data.temperatures.bed_temp, data.temperatures.bed_temp_target);
	}
	
	/**
	 * @param array ext_temp, ext_temp_target, bed_temp,bed_temp_target
	 * update temperatures info
	 */
	app.updateTemperaturesInfo = function(ext_temp, ext_temp_target, bed_temp,bed_temp_target){
		
		if(ext_temp.constructor === Array){
			ext_temp = ext_temp[ext_temp.length - 1];
		}
		if(ext_temp_target.constructor === Array){
			ext_temp_target = ext_temp_target[ext_temp_target.length - 1];
		}
		if(bed_temp.constructor === Array){
			bed_temp = bed_temp[bed_temp.length - 1];
		}
		if(bed_temp_target.constructor === Array){
			bed_temp_target = bed_temp_target[bed_temp_target.length - 1];
		}
		
		//update top bar
		if($(".top-bar-nozzle-actual").length > 0) $(".top-bar-nozzle-actual").html(parseInt(ext_temp));
		if($(".top-bar-nozzle-target").length > 0) $(".top-bar-nozzle-target").html(parseInt(ext_temp_target));
		
		
		
		$(".top-bar-bed-actual").html(parseInt(bed_temp));
		$(".top-bar-bed-target").html(parseInt(bed_temp_target));
		//top bar sliders
		document.getElementById('top-act-bed-temp').noUiSlider.set([parseInt(bed_temp)]);
		document.getElementById('top-bed-target-temp').noUiSlider.set([parseInt(bed_temp_target)]);
		
		if($("#top-act-ext-temp").length > 0){
			document.getElementById('top-act-ext-temp').noUiSlider.set([parseInt(ext_temp)]);
			document.getElementById('top-ext-target-temp').noUiSlider.set([parseInt(ext_temp_target)]);
		}
		//save to browser storage
		if ( typeof (Storage) !== "undefined") {
			localStorage.setItem("nozzle_temp", ext_temp);
			localStorage.setItem("nozzle_temp_target", ext_temp_target);
			localStorage.setItem("bed_temp", bed_temp);
			localStorage.setItem("bed_temp_target", bed_temp_target);
		}
		
		//handle tempearaturesPlot for graphs
		var extruderTemp = {'value': parseFloat(ext_temp), 'time': new Date().getTime()};
		var extruderTargetTemp = {'value': parseFloat(ext_temp_target), 'time': new Date().getTime()};
		var bedTemp = {'value': parseFloat(bed_temp), 'time': new Date().getTime()};
		var bedTargetTemp = {'value': parseFloat(bed_temp_target), 'time': new Date().getTime()};
		
		if(temperaturesPlot.extruder.temp.length > maxTemperaturesPlot)   temperaturesPlot.extruder.temp.shift();
		if(temperaturesPlot.extruder.target.length > maxTemperaturesPlot) temperaturesPlot.extruder.target.shift();
		if(temperaturesPlot.bed.temp.length > maxTemperaturesPlot)        temperaturesPlot.bed.temp.shift();
		if(temperaturesPlot.bed.target.length > maxTemperaturesPlot)      temperaturesPlot.bed.target.shift();
		
		temperaturesPlot.extruder.temp.push(extruderTemp);
		temperaturesPlot.extruder.target.push(extruderTargetTemp);
		temperaturesPlot.bed.temp.push(bedTemp);
		temperaturesPlot.bed.target.push(bedTargetTemp);
		
		if(typeof (Storage) !== "undefined") {
			localStorage.setItem('temperaturesPlot', JSON.stringify(temperaturesPlot));
		}
		
		//just for create controller
		if($(".extruder-temp").length > 0)   $(".extruder-temp").html(parseFloat(ext_temp).toFixed(0));
		if($(".extruder-target").length > 0) $(".extruder-target").html(parseFloat(ext_temp_target).toFixed(0));
		if($(".bed-temp").length > 0)        $(".bed-temp").html(parseFloat(bed_temp).toFixed(0));
		if($(".bed-target").length > 0)      $(".bed-target").html(parseFloat(bed_temp_target).toFixed(0));
		
		if (typeof window.updateTemperatures == 'function') window.updateTemperatures(ext_temp, ext_temp_target, bed_temp,bed_temp_target);
	};
	/*
	 * write serial replys to jog console
	 */
	app.writeSerialResponseToConsole = function(data){
		
		if($(".jogResponseContainer").length > 0){
			var html = '';
			$.each(data.commands, function(i, item) {
				html += '<span class="jog_response ">' + item.code + ' : <small>' + item.reply + '</small> </span><hr class="simple">';
			});
			
			$(".consoleContainer").append(html);
			$(".jogResponseContainer").animate({ scrollTop: $('.jogResponseContainer').prop("scrollHeight")}, 1000);
		}
	};

	/*
	 * Check if there are some operations before leaving the page
	 */
	app.checkExit = function(){
		if(is_stopping_all == false && is_macro_on == true){
			return _("You have attempted to leave this page. The Fabtotum Personal Fabricator is still working. Are you sure you want to reload this page?");
		}
	};
	/*
	 * manage emergeny alerts
	 */
	app.manageEmergency = function(data) {
		if(is_emergency == true) return; //exit if is already on emergency status
		var code = parseInt(data.code);
		is_emergency = true;
		
		switch(code)
		{
			case 102:
				app.refreshPage(_("Front panel has been opened") + '.<br> ' + _("Aborting all operations"), 10000);
				setTimeout(function(){
					waitContent( "<i class='fa fa-cog fa-spin'></i> " +  _("Restarting all services"));
				}, 5000);
				break;
			case 103:
				//TODO
				app.showInstallHeadModal();
				break;
			default:
				app.showEmergencyAlert(code);
		}
	};
	/**
	*
	**/
	app.showEmergencyAlert = function (error_code)
	{
		var buttons = '[' + _("Ok")  + '][' + _("Ignore") + ']';
		$.SmartMessageBox({
			buttons : buttons,
			title : "<h4><span class='txt-color-orangeDark'><i class='fa fa-warning fa-2x'></i></span>&nbsp;&nbsp;" + emergency_descriptions[error_code] + "<br>&nbsp;" + _("Press OK to continue or Ignore to disable this warning") + "</h4>"
		},function(ButtonPressed) {
			if(ButtonPressed == _("Ok") || (ButtonPressed == _("Ignore") && buttons.indexOf(_("Install head")) > -1) ) app.setSecure(1);
			else if(ButtonPressed == _("Ignore")) app.setSecure(0);
		});
	}
	/**
	*
	**/
	app.showInstallHeadModal = function ()
	{	
		var options = '';	
		$.each(heads, function(i, item) {
			options += '['+item.name +']';
		});
		
		$.SmartMessageBox({
			title : '<i class="fa fa-warning txt-color-orangeDark"></i> ' + emergency_descriptions[ERROR_MIN_TEMP],
			content : _("Before proceed make sure the head is properly locked in place"),
			buttons : "[" + _("Install head")  + "]["+ _("Ignore") +"]",
			input : "select",
			options : options,
			selected: app.installed_head.name
		}, function(ButtonPressed, Value) {
			if(ButtonPressed == _("Ignore")) app.setSecure(0);
			if(ButtonPressed == _("Install head")){
				$.each(heads, function(i, item) {
					if(Value == item.name){
						app.installHead(i);
						return;
					}
				});
			}
		});
		
		$("#txt1").on("change", function() {
			
			var selected_head = $(this).find("option:selected").text();
			if((selected_head == 'Laser Head')  || (selected_head == 'Laser Head Pro')) {
				if($(".laser-head-plugin-note").length <= 0)
					$(".MessageBoxButtonSection").append('<span class="pull-left margin-top-10 laser-head-plugin-note font-xs">'+ _("Please make sure") +' <a target="_blank" href="#plugin">Laser Plugin</a> ' + _("is active") +' </span>');
			}
			else $(".laser-head-plugin-note").remove();
		});
	}
	/*
	 * alive the fabtotum after an emergency
	 */
	app.setSecure = function(bool){
		is_macro_on = true;
		$.ajax({
			type : "POST",
			url : set_secure_url + '/'+bool,
			data : {mode : bool},
			dataType : 'json'
		}).done(function(response) {
			is_emergency = false;
			is_macro_on  = false;
		});
	}
	/**
	*
	**/
	app.installHead = function(head)
	{
		openWait('<i class="fa fa-cog fa-spin"></i> ' + _("Installing head"), _("Please wait") + '...', false);
		$.ajax({
			type : "POST",
			url : install_head_url + '/'+head,
			dataType : 'json'
		}).done(function(response) {
			location.reload();
		});
	}
	/**
	 * redirect to new head installation page
	 */
	app.goToInstallNewHead = function(){
		$.root_.addClass('animated fadeOutUp');
		document.location.href = new_head_url_action;
		location.reload();
	};
	/**
	 * manage upcoming alerts from the printer
	 */
	app.manageAlert = function(data){
		var code = parseInt(data.code);
		$.smallBox({
			title : "Message",
			content : emergency_descriptions[code],
			color : "#5384AF",
			timeout : 10000,
			icon : "fa fa-warning"
		});
	};
	/*
	 * manage tasks
	 */
	app.manageTask = function(data){
		
		switch(data.type){
			case 'notifications':
				app.setTasks(data);
				app.updateNotificationBadge();
				break;
			case 'monitor':
				app.manageTaskMonitor(data);
				break;
		}
	};
	/*
	 * set tasks
	 */
	app.setTasks = function(data){
		number_tasks = data.number;
		is_task_on = number_tasks > 0;
		if(is_task_on == true){
			$.each(data.items, function() {
				var row = this;
				controller = row.controller;
				if (controller == 'make') controller += '/' + row.type;
				app.freezeMenu(controller); //freeze menu
				$(".task-list").find('span').html('	' + _("Tasks") + ' (' + data.number + ') '); //update number on ajax dropdown list
				app.updateNotificationBadge();
			});
		}else app.unFreezeMenu();
	};
	/*
	 * manage tasks's json files known as monitor files
	 */
	app.manageTaskMonitor = function(data){
		if (typeof manageMonitor == 'function') manageMonitor(data.content);
	};
	
	/*
	 * manage custom notifications
	 */
	app.manageCustomNotifications = function(data){
		if (typeof customNotificationsHandler == 'function') customNotificationsHandler(data);
	};
	
	/*
	 * 
	 */
	app.getStatus = function(){
		if(socket_connected && (is_macro_on == false && is_task_on == false && is_emergency == false)) app.serial('getStatus', '');
	}
	/*
	 * read temperatures
	 */
	app.getTemperatures = function(){
		if(debugState) root.console.log("✔ getTemperatures");
		//TODO new version
		if(socket_connected) { 
			app.serial('getTemperatures', '');
		}
		else{
			$.get(temperatures_file_url + '?' + jQuery.now(), function(data){
				app.updateTemperaturesInfo(data.ext_temp, data.ext_temp_target, data.bed_temp, data.bed_temp_target);
			});
		}
	}
	
	/**
	 * Jog serial function
	 * Used to send individual gcode commands, move the jog or get temperature values
	 */
	app.serial = function(func, val, callback, step, feedrate, waitforfinish) {
		// IE11 compatibility
		if(step == undefined) step = 0;
		if(feedrate == undefined) feedrate = 0;
		if(waitforfinish == undefined) waitforfinish = false;
		
		if(debugState) root.console.log("✔ app.serial: " + func + ', ' + val);
		
		var stamp = Date.now();
		
		if(ws == null)
			return stamp;
		
		var data = {
			'method'           : func,
			'value'            : val,
			'stamp'            : stamp,
			'step'             : step,
			'feedrate'         : feedrate,
			'waitforfinish'    : waitforfinish
		};
		
		var messageToSend = {
			'function' : 'serial',
			'params' : data
		};
		
		if($.isFunction(callback))
		{
			app.ws_callbacks[stamp] = callback;
		}
		
		socket.send( JSON.stringify(messageToSend) );
		
		return stamp;
	};
	/**
	 * Check if internet connection is available
	 * @memberof fabApp
	 * @returns {Boolean}
	 */
	app.isInternetAvailable = function(){
		if(debugState) root.console.log("✔ app.isInternetAvailable");
		$.get(check_internet_url_action + '?' + jQuery.now(), function(data){
			app.showConnected(data == 1);
		});
	};
	/**
	 * show or hide connected icon
	 */
	app.showConnected = function(available) {
		if(available)$(".lock-ribbon").before('<span class="ribbon-button-alignment internet animated bounceIn" ><span class="btn btn-ribbon "  rel="tooltip" data-placement="right" data-original-title="Connected to internet" data-html="true"><i class="fa fa-globe "></i></span></span>');
		else $(".internet").remove();
		$("[rel=tooltip], [data-rel=tooltip]").tooltip();
	};
	/**
	 * notify when usb disk is inserted or removed
	 */
	app.usb = function (status, notify){
		if(status == 'inserted' && $(".usb-ribbon").length == 0) $(".breadcrumb").before('<span class="ribbon-button-alignment usb-ribbon animated bounceIn" ><span class="btn btn-ribbon "  rel="tooltip" data-placement="right" data-original-title="USB disk inserted" data-html="true"><i class="fa fa-usb "></i></span></span>');
		else if(status == 'removed') $(".usb-ribbon").remove();
		$("[rel=tooltip], [data-rel=tooltip]").tooltip();
		if(notify == true){
			var message = _("USB Disk");
			message += ' ' + status;
			$.smallBox({
				title : "FABtotum Personal Fabricator",
				content : message,
				color : "#296191",
				timeout : 3000,
				icon : "fa fa-usb"
			});
		}
	};
	/**
	 * notify when eth cable is plugged or unplugged
	 */
	app.eth = function (status){
		var message = _("Ethernet cable") + " " + _(status);
		$.smallBox({
			title : _("Network"),
			content : message,
			color : "#296191",
			timeout : 3000,
			icon : "fa fa-sitemap"
		});
	};
	/**
	 * things to do when socket is connected
	 */
	app.afterSocketConnect = function(){
		
		if(socket_connected == true){
			socket.send('{"function": "getUpdates"}');
			socket.send('{"function": "getHardwareSettings"}');
			socket.send('{"function": "getNetworkInfo"}');
			socket.send('{"function": "usbInserted"}');   //check for if usb disk is connected
		}
	}
	/**
	 * handle trace content from task/macro
	 */
	app.handleTrace = function(content) {
		var contentSplitted = content.split('\n');
		var html = '';
		$.each(contentSplitted, function( index, value ) {
				if(value != '')
					html += '<p>'+ value +'</p>';
		});
		if($(".trace-console").length > 0){
			$(".trace-console").html(html).scrollTop(1E10);
			$(".trace-console").parent().scrollTop(1E10);
		}
		waitContent(html);
	}
	/**
	 * Reset temperatures plot
	 * @memberof fabApp
	 * 
	 * @param {Integer} elements How many elements to keep. 0 reset all.
	 */
	app.resetTemperaturesPlot = function(elements)
	{
		elements = elements || 0;	
		if(elements > 0){
			if(temperaturesPlot.extruder.temp.length > elements){
				temperaturesPlot.extruder.temp.splice(0, temperaturesPlot.extruder.temp.length - 5);
			}
			if(temperaturesPlot.extruder.target.length > elements){
				temperaturesPlot.extruder.target.splice(0, temperaturesPlot.extruder.target.length - 5);
			}
			if(temperaturesPlot.bed.temp.length > elements){
				temperaturesPlot.bed.temp.splice(0, temperaturesPlot.bed.temp.length - 5);
			}
			if(temperaturesPlot.bed.target.length > elements){
				temperaturesPlot.bed.target.splice(0, temperaturesPlot.bed.target.length - 5);
			}
		}else{
			temperaturesPlot = {extruder: {temp: [], target: []}, bed: {temp:[], target:[]}};
		}
		if(typeof (Storage) !== "undefined") {
			localStorage.setItem('temperaturesPlot', JSON.stringify(temperaturesPlot));
		}
	}
	/**
	 * check if there are running tasks, and more
	 * @param {Boolean} init // @TODO: check what its used for
	 */
	app.getState = function(init)
	{
		var freezing_status = ['running', 'aborting', 'completing'];
		$.get(task_monitor_file_url + '?' + jQuery.now(), function(data, status){
			if(data.task.hasOwnProperty('status')){
				if(jQuery.inArray( data.task.status, freezing_status ) >= 0 ){
					app.freezeMenu(data.task.type);
					number_tasks = 1;
					
				}else{
					if(!init) app.unFreezeMenu();
					number_tasks = 0;
				}
			}
			app.updateNotificationBadge();
		});
	}
	/**
	*
	**/
	app.getUpdates = function() {
		
		$.get(updates_json_url + '?' + jQuery.now(), function(data, status){
			app.handleUpdatesData(data);
		});
		
	}
	/**
	 * 
	 */
	app.handleUpdatesData = function(data)
	{
		number_updates = data.update.bundles;
		
		number_plugin_updates = data.update.plugins;
		
		if(data.update.firmware) number_updates += 1;
		if(data.update.boot) number_updates += 1;
		
		app.updateNotificationBadge();
		
		if(number_updates > 0 && location.hash != "#updates"){
			$.smallBox({
				title : _("Updates center"),
				content : _("New updates are available") + "<p class='text-align-right'><a href='#updates' class='btn btn-primary btn-sm'>"+_("Update now")+"</a></p>",
				color : "#296191",
				timeout: 15000,
				icon : "fa fa-refresh swing animated"
			});
		}
	}
	/**
	 * Redirect to a specific url only when the url responds 200
	 * @memberof fabApp
	 * 
	 * @param {String} url URL to redirect to
	 **/
	app.redirectToUrlWhenisReady = function (url)
	{
		$.get(url)
			.done(function(result) {				
				document.location.href = url;
			})
			.fail(function(jqXHR, textStatus, errorThrown) {
				setTimeout( function() {
					app.redirectToUrlWhenisReady(url);
				}, 500 );
			});
	}
	/**
	 * Force recovery mode on next boot.
	 * @memberof fabApp
	 **/
	app.forceRecovery = function (){
		
		app.rebooting = true;
			openWait("<i class='fa fa-warning'></i> " + _("Entering recovery mode"), _("You will be redirected to recovery page"), false);
			$.get(set_recovery_url + '/activate', function(data){ 
				$.ajax({
					url: reboot_url_action,
				}).done(function(data) {
					app.redirectToUrlWhenisReady('http://'+ location.hostname);
				}).fail(function(jqXHR, textStatus){
					//clear intervals
					app.redirectToUrlWhenisReady('http://'+ location.hostname);
				});
			});

	}
	/**
	* get hardware settings
	**/
	app.getSettings = function() {
		$.get(control_url + '/getSettings', function(data, status){
			app.handleSettingsData(data);
		});
	}
	/**
	 * 
	 */
	app.handleSettingsData = function(data)
	{
		app.analizeMenu(data);
		app.setInstalledHeadInfo(data);
		app.analizeTopBar(data);
		
	}
	/**
	* analize menu to check if something must be hided
	**/
	app.analizeMenu = function (settings)
	{			
		var a = $("nav li > a");
		var unit_type = app.getUnitType(settings.hardware.id);
		a.each(function() {
			var link = $(this);
			var href = link.attr('data-href');
			var controller = link.attr('data-controller');
			link.parent().removeClass('hidden');
			switch(href){
				case 'maintenance/feeder-engage':
				case 'maintenance/4th-axis':
					if(settings.feeder.engage == false) link.parent().addClass('hidden');
					break;
				case 'maintenance/feeder-profiles':
					if(settings.feeder.available == false) link.parent().addClass('hidden');
					break;
				case 'settings/cam':
					if(settings.hardware.camera.available == false) link.parent().addClass('hidden');
					break;
				case 'scan':
					if(settings.scan.available == false) link.parent().addClass('hidden');
					break;
				default:
			}
		});
	}
	/**
	 * analize topbar depending on settings
	 */
	app.analizeTopBar = function(settings)
	{
		if(app.installed_head != null){
			if(app.installed_head.working_mode == HEAD_WORKING_MODE_LASER || app.installed_head.working_mode == HEAD_WORKING_MODE_CNC 
				|| app.installed_head.working_mode == HEAD_WORKING_MODE_SCANNER || app.installed_head.working.mode == HEAD_WORKING_MODE_SLA){
				$(".top-ajax-temperatures-dropdown .head-working-mode-"+HEAD_WORKING_MODE_FFF).remove();
				$(".top-ajax-temperatures-dropdown .head-working-mode-"+HEAD_WORKING_MODE_HYBRID).remove();
				$(".top-ajax-temperatures-dropdown").find('h4').removeClass('margin-top-50');
				$(".top-ajax-temperatures-dropdown").attr('style', 'min-height: 130px; height:130px;');
				$("#top-temperatures .head-working-mode-"+HEAD_WORKING_MODE_FFF).remove();
			}
			$("#top-temperatures").removeClass('hidden');
		}
	}
	/**
	 * get installed head from settings and print head name to top bar
	 */
	app.setInstalledHeadInfo = function(settings)
	{	
		
		if(typeof(heads[settings.hardware.head]) !== "undefined"){
			app.installed_head = heads[settings.hardware.head];
			$(".installead-head-name").html(heads[settings.hardware.head].name);
			app._createExtruderTemperaturesTopSliders(app.installed_head['max_temp']);
		}else{
			$(".installead-head-name").html(_("No head installed"));
		}
	}
	/**
	* initi vars from localstorage if it is enabled
	**/
	app.initFromLocalStorage = function ()
	{
		if (typeof(Storage) !== "undefined"){
			if(localStorage.getItem("temperaturesPlot") !== null){			
				temperaturesPlot =  JSON.parse(localStorage.getItem("temperaturesPlot"));
			}
		} 
	}
	/**
	 * get feeds
	 **/
	app.getFeeds = function ()
	{
		$.get(dashboard_url + '/updateFeeds', function(data, status){});
	}
	/**
	* get network interfaces and show icon on ribbon
	**/
	app.getNetworkInfo = function ()
	{
		$.get(network_info_url + '?' + jQuery.now(), function(data, status){
			app.handleNetworkInfoData(data);
		});
	}
	/**
	 * 
	 */
	app.handleNetworkInfoData = function(data)
	{
		var hotstname = window.location.hostname;
		var connectionType = '';
		
		$(".wifi-ribbon-icon").remove();
		$(".internet-ribbon-icon").remove();
		if(data.interfaces != null){
			
			if(data.interfaces.hasOwnProperty('eth0')){
				
				var eth_address = data.interfaces.eth0.ipv4_address.split("/");					
				if(eth_address[0] == hotstname) connectionType = 'eth';
			}
			
			if(data.interfaces.hasOwnProperty('wlan0')){
				
				var icon = 'fa fa-wifi';
				var title = _("Wifi connected");
				var wifi_ip_address = data.interfaces.wlan0.wireless.ip_address ;
				if(data.interfaces.wlan0.wireless.mode == 'accesspoint'){
					icon = 'icon-communication-035';
					title = _("Access Point");
					wifi_ip_address = '';
				}
				
				if(data.interfaces.wlan0.wireless.hasOwnProperty('ssid')){
					$("#ribbon-left-buttons").prepend('<span data-title="' + title + ' <br> ' + data.interfaces.wlan0.wireless.ssid  + '<br>' + wifi_ip_address+'"  rel="tooltip" data-html="true" data-placement="bottom" class="btn btn-ribbon wifi-ribbon-icon"><i class="' + icon + '"></i></span>');	
				}
				if(data.interfaces.wlan0.wireless.ip_address == hotstname) {
					connectionType = 'wlan';
				}
			}
			if(data.interfaces.hasOwnProperty('wlan1')){
				
				var icon = 'fa fa-wifi';
				var title = _("Wifi connected");
				var wifi_ip_address = data.interfaces.wlan1.wireless.ip_address ;
				if(data.interfaces.wlan1.wireless.mode == 'accesspoint'){
					icon = 'icon-communication-035';
					title = _("Access Point");
					wifi_ip_address = '';
				}
				
				if(data.interfaces.wlan1.wireless.hasOwnProperty('ssid')){
					$("#ribbon-left-buttons").prepend('<span data-title="' + title + ' <br> ' + data.interfaces.wlan0.wireless.ssid  + '<br>' + wifi_ip_address+'"  rel="tooltip" data-html="true" data-placement="bottom" class="btn btn-ribbon wifi-ribbon-icon"><i class="' + icon + '"></i></span>');	
				}
				
				if(data.interfaces.wlan1.wireless.ip_address == hotstname) {
					connectionType = 'wlan';
				}
			}
			if(data.interfaces.hasOwnProperty('bluetooth')){
				var bluetooth = data.interfaces.bluetooth;
				if(bluetooth.powered == true){
					var bluetooth_icon = 'fab fa-bluetooth-b';
					var bluetoot_title = _("Bluetooth enabled");
					
					if(bluetooth.hasOwnProperty('paired') && bluetooth.paired.connected == true){
						bluetooth_icon = 'fab fa-bluetooth txt-color-blueLight';
						bluetoot_title = _("Bluetooth connected to:") + '<br>' + bluetooth.paired.name;
					}
					
					$("#ribbon-left-buttons").prepend('<span data-title="'+bluetoot_title+'"  rel="tooltip" data-html="true" data-placement="bottom" class="btn btn-ribbon wifi-ribbon-icon"><i class="'+bluetooth_icon+'"></i></span>');	
				}
			}
		}
		if(data.internet){
			$("#ribbon-left-buttons").prepend('<span data-title="' + _("Internet available") + '"  rel="tooltip" data-placement="bottom" class="btn btn-ribbon internet-ribbon-icon"><i class="fa fa-globe"></i></span>');
		}else{
			$("#ribbon-fabid-button").html("<i class='fa fa-warning text-danger'></i> " + _("No internet connection")).attr('data-original-title',_("Check network settings and try again")).attr('data-title', _("Check network settings and try again"));
		}
		if(connectionType == 'eth') {
			$(".eth-ribbon").remove();
			$("#ribbon-left-buttons").prepend('<span style="padding-top:2px;" data-title="' + _("Connected with ethernet cable") + '<br> ' +eth_address[0] +'" rel="tooltip" data-html="true" data-placement="bottom" class="btn btn-ribbon eth-ribbon txt-color-blue"><i class="icon-communication-088 "></i></span>');	
		}else if(connectionType == 'wlan'){
			$(".wifi-ribbon-icon").find('i').addClass('txt-color-blue');
		}
		pageSetUp();
	}
	/**
	* notify user when is possible to power off the printer
	**/
	app.showAlertToPowerOff = function ()
	{
		$.get(base_url)
			.done(function(result) {				
				setTimeout( function() {
					app.showAlertToPowerOff();
				}, 50 );
			})
			.fail(function(jqXHR, textStatus, errorThrown) {
				setTimeout(function() {
					waitTitle(_("Now you can switch off the power"));
					waitContent(_("Note: 5 seconds after the beep it's safe to switch off your unit."));
				}, 10000);
			});
	}
	/**
	 * 
	 */
	app.handlePollMessage = function(object)
	{
		app.handleTrace(object.data.trace.data);
		app.manageTask(object.data.task.data);
		app.usb(object.data.usb.data.status, object.data.usb.data.alert);
		
		if(!object.data.notify.data.last_event.hasOwnProperty('seen') && object.data.notify.data.last_event.type=="emergency"){
			app.manageEmergency(object.data.notify.data.last_event.data);
		}
	}
	/**
	 * Get Unit type from version ID.
	 * 
	 * @param {Integer} id Unit version ID
	 * @returns {String} Unit type (UNKNWON, GENERAL, CORE, PRO, HYDRA)
	 */
	app.getUnitType = function(id)
	{
		id = parseInt(id);
		var type = UNIT_UNKNOWN;
		if(id>=4000 && id<5000){
			type=UNIT_PRISM;
		}else if(id>=3000 && id<4000){
			type = UNIT_HYDRA;
		}else if(id>=2000 && id<3000){
			type = UNIT_PRO;
		}else if(id>= 1000 && id<2000){
			type = UNIT_CORE ;
		}else if(id>= 0 && id<1000){
			type = UNIT_GENERAL;
		}
		return type;
	}
	/**
	 * check if we are connected to the printer
	 */
	app.checkConnectivity = function()
	{
		if(app.rebooting == false){
			$.get(base_url).done(function(result) {
				/**
				 * @TODO
				 */
			}).fail(function(jqXHR, textStatus, errorThrown) {
				openWait('<i class="fa fa-warning"></i> ' + _("No connection detected"), _("Unable to connect to the FABtotum Personal Fabricator") + '<br>' + _("Please check ethernet cable or wifi connection and then reload the page"), false); 
			});
		}
	}
	/**
	 * 
	 */
	app.getNotify = function()
	{
		$.get(notify_json_url + '?' + jQuery.now(), function(data, status){
			if(data && data.hasOwnProperty('last_event')){
				if(data.last_event.hasOwnProperty('type') && data.last_event.type == 'emergency'){
					app.manageEmergency(data.last_event.data);
				}
			}
		});
	}
	/**
	 * 
	 */
	app.fabIDLogin = function(){
		
		var back_url     = location.host + myfabtotun_back_uri;
		var complete_url = myfabtotum_login_url + '?url=' + back_url;
		
		var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
	    var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;
		
		var windowSize = {"width": 500, "height": 500};
		var position   = {"left": ($(window).width()/2)-(windowSize.width/2), "top": ($(window).height()/2)-(windowSize.height/2) };
		
		window.open(complete_url, "myFabtotumIDLogin", "width="+windowSize.width+", height="+windowSize.height+", top="+position.top+", lef="+position.left+",  location=no, toolbar=no, menubar=no, resizable=no, titlebar=no");
	}
	/**
	 * 
	 */
	app.doFunctionOverWS = function(func)
	{
		if(!socket_connected || socket.fallback){
			switch(func){
				case 'getNetworkInfo':
					app.getNetworkInfo();
					break;
				case 'getUpdates':
					app.getUpdates();
					break;
				case 'getHardwareSettings':
					app.getSettings();
					break;
			}
		}else{
			socket.send('{"function": "'+func+'"}');
		}
	}
	/**
	 * 
	 */
	app.myFabtotumPrintersList = function()
	{
		$.get(myfabtotum_printers_list, function(data, status){
			if(data.status == true){
				if(data.printers && data.printers.length > 0){
					$.each(data.printers, function(i, item) {
						$.ajax({
							type: "GET",
							url: 'http://'+item.iplan+'/fabui/login',
							timeout: 5000,
							dataType: 'html',
							success: function(resonse, status){
								$("#my-fabtotum-ribbon-label").removeClass("hidden");
								var printer = '<a href="http://'+item.iplan+'/fabui/#dashboard" target="_blank" class="btn btn-ribbon no-ajax hidden-xs"><i class="fa-lg fa-fw fabui-core"></i> '+item.name+'</a>';
								$("#ribbon-right-buttons").append(printer);
							}
						}).done(function( response, status ) {});
					});
				}
			}
		});
	}
	/**
	 * 
	 */
	app.urlIntegrityCheck = function()
	{
		/**
		 * check if is a valid url
		 * es: /fabui/#dashboard (valid)
		 * es: /fabui/dashboard (not valid)
		 */	
		if(ENVIROMENT == 'production'){	
			if(document.location.pathname != "/fabui/"){
				document.location.href = document.location.pathname.replace("/fabui/", "/fabui/#");
			}
		}
		/**
		 * borrowed from app.min.js
		 */
		if ($('nav').length) {
		    checkURL();
		}
	}
	/**
	 *  clear all saved intervals
	 */
	app.clearIntervals = function()
	{
		jQuery.each(app.intervals, function( index, value ) {
			  clearInterval(value);
		});
		app.intervals = [];
	}
	/**
	 * 
	 * 
	 */
	app.checkSafety = function(type, bed_in_place, element_to_hide) {
		$(element_to_hide).css("opacity", "0.5");
		var url = safety_url + type + '/'+ bed_in_place;
		_make_call(url);
		app.intervals.push(setInterval(_make_call, 2000, url));
		var interval_reference = app.intervals[app.intervals.length-1];
		
		/**
		 * 
		 */
		function _make_call(url)
		{
			$.ajax({
				type: "POST",
				url: url,
				dataType: 'json'
			}).done(function( data ) {
				_process_response(data);
			});
		}
		
		/**
		 * 
		 */
		function _process_response(data)
		{
			if(data.all_is_ok == true){
				$(element_to_hide).show().css("opacity", "1");
				$(".safety-check-container").remove();
				clearInterval(interval_reference);
			}else{
				
				if($(element_to_hide).length <= 0) return false;
				
				var bed = data.bed_in_place == false ? 'mill.png' : 'glass.png';

				if(data.head_is_ok){
					var head_title = '<strong>' + _("Correct head/module installed") + '</strong> <i class="fa fa-check-circle text-success fa-2x"></i>';
					var head_subtitle = '';
				}else{
					var head_title =  data.head_in_place ? '<strong>' + _("Wrong head/module installed") + '</strong> <i class="fa fa-times-circle text-danger fa-2x"></i>' : '<strong>' + _("No head installed") + '</strong>'; 
					var head_subtitle = _("Please install a {0} head.").replace("{0}", type);
				}

				if(data.bed_is_ok){
					var bed_title = '<strong>' +  _("Bed inserted correctly") + '</strong> <i class="fa fa-check-circle text-success fa-2x"></i>';
					var bed_subtitle = '';
				}else{
					var bed_title = '<strong>'+ _("Bed inserted incorrectly") + '</strong> <i class="fa fa-times-circle text-danger fa-2x"></i>' ;
					var bed_subtitle = _("Please flip the bed to the other side.");;
				}
				
				var content = '<div class="row safety-check-container">\
					<div class="col-sm-12">\
						<div class="well">\
							<div class="row text-center">\
								<h1><strong>'+_("Safety check")+'</strong></h1>\
							</div>\
							<div class="row">\
								<div class="col-sm-6 col-xs-6">\
									<div class="row text-center">\
										<div class="">\
											<img style="max-height:320px; display:inline;" class="img-responsive" src="/assets/img/head/photo/'+fabApp.installed_head['filename']+'.png">\
										</div>\
									</div>\
									<div class="row text-center">\
										<h4>'+head_title+'</h4>\
										<h3>'+head_subtitle+'</h3>\
									</div>\
								</div>\
								<div class="col-sm-6 col-xs-6">\
									<div class="row">\
										<div class="text-center">\
											<img style="max-height:320px; display:inline;" class="img-responsive" src="/assets/img/controllers/bed/hybrid_bed_'+bed+'">\
										</div>\
									</div>\
									<div class="row text-center">\
										<h4>'+bed_title+'</h4>\
										<h3>'+bed_subtitle+'</h3>\
									</div>\
								</div>\
							</div>\
						</div>\
					</div>\
				</div>';
				if($(".safety-check-container").length >= 0){
					$(".safety-check-container").remove();
				}
				$("#content").prepend(content);
				$(element_to_hide).hide();	
			}
		}
	}
	/**
	 * add animated gif during tasks
	 * FIREFOX suppor it
	 * other browesers needs a workaorund
	 */
	app.setWorkingFavicon = function() {
		
		app.favicon = $("link[rel='icon']");
		
		if(isFirefox){
			$("link[rel='icon']").remove();
		    $("link[rel='shortcut icon']").remove();
			$("head").append('<link rel="icon" href="/assets/img/favicon/working.gif" type="image/gif">');
		}else{
			var max_counter = 29;
			image_counter = 0;
			$("link[rel='icon']").remove();
		    $("link[rel='shortcut icon']").remove();
		    $("head").append('<link rel="icon" id="favicon" href="/assets/img/favicon/animated/tmp-'+image_counter+'.gif" type="image/gif">');
		    
			app.favicon_interval = setInterval(function() {
			    $("#favicon").attr("href", '/assets/img/favicon/animated/tmp-'+image_counter+'.gif');
				if(image_counter == max_counter)
			        image_counter = 0;
			    else
			        image_counter++;
			}, 10);
		}
	}
	/**
	 * 
	 */
	app.removeWorkingFavicon = function()
	{
		clearInterval(app.favicon_interval);
		$("link[rel='icon']").remove();
		$("head").append(app.favicon);
		
	}
	return app;
})({});

