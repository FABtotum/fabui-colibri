fabApp = (function(app) {
	
	app.rebooting = false; //is the unit rebooting?
	
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
		
	};
		
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
		
		//bed target
		noUiSlider.create(document.getElementById('top-bed-target-temp'), {
			start: typeof (Storage) !== "undefined" ? localStorage.getItem("bed_temp_target") : 0,
			connect: "lower",
			range: {'min': 0, 'max' : 100},
			pips: {
				mode: 'positions',
				values: [0,25,50,75,100],
				density: 5,
				format: wNumb({
					postfix: '&deg;'
				})
			}
		});
		//bet actual
		noUiSlider.create(document.getElementById('top-act-bed-temp'), {
			start: typeof (Storage) !== "undefined" ? localStorage.getItem("bed_temp") : 0,
			connect: "lower",
			range: {'min': 0, 'max' : 100},
			behaviour: 'none'
		});
		$("#top-act-bed-temp .noUi-handle").remove();
		
		//nozzle target
		noUiSlider.create(document.getElementById('top-ext-target-temp'), {
			start: typeof (Storage) !== "undefined" ? localStorage.getItem("nozzle_temp_target") : 0,
			connect: "lower",
			range: {'min': 0, 'max' : 250},
			pips: {
				mode: 'positions',
				values: [0,25,50,75,100],
				density: 5,
				format: wNumb({
					postfix: '&deg;'
				})
			}
		});
		//nozzle actual
		noUiSlider.create(document.getElementById('top-act-ext-temp'), {
			start: typeof (Storage) !== "undefined" ? localStorage.getItem("nozzle_temp") : 0,
			connect: "lower",
			range: {'min': 0, 'max' : 250},
			behaviour: 'none'
		});
		$("#top-act-ext-temp .noUi-handle").remove();
		//bed events
		document.getElementById("top-bed-target-temp").noUiSlider.on('slide',  app.topBedTempSlide);
		document.getElementById("top-bed-target-temp").noUiSlider.on('change', app.topBedTempChange);
		document.getElementById("top-bed-target-temp").noUiSlider.on('start',  app.blockSliders);
		document.getElementById("top-bed-target-temp").noUiSlider.on('end',    app.enableSliders);
		//nozzle events
		document.getElementById("top-ext-target-temp").noUiSlider.on('slide',  app.topExtTempSlide);
		document.getElementById("top-ext-target-temp").noUiSlider.on('change', app.topExtTempChange);
		document.getElementById("top-ext-target-temp").noUiSlider.on('start',  app.blockSliders);
		document.getElementById("top-ext-target-temp").noUiSlider.on('end',    app.enableSliders);
		
		
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
	**/
	app.disableTopBarControls = function () {
		$("#top-temperatures").off();
		$("#jog-shortcut").off();
	}
	/**
	*
	**/
	app.enableTopBarControls = function ()
	{
		$("#top-temperatures").click(function(a) {
			var b = $(this);
		   	b.next(".top-ajax-temperatures-dropdown").is(":visible") ? (b.next(".top-ajax-temperatures-dropdown").fadeOut(150), b.removeClass("active")) : (b.next(".top-ajax-temperatures-dropdown").fadeIn(150), b.addClass("active"));
		   	var c = b.next(".top-ajax-temperatures-dropdown").find(".btn-group > .active > input").attr("id");
		   	b = null, c = null, a.preventDefault()
       	});
		
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
	app.domReadyMisc = function() {
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
	 * Move the head or bead
	 * @action   Movement directio (right,left,up,down,z-up,z-down...)
	 * @step     Movement step in mm
	 * @feedrate Movement feedrate in mm/min
	 * @waitforfinish Add M400 to sync the finish callback to end of movement
	 * @callback Callback function on execution finish
	 */
	app.jogMove = function (action, step, feedrate, waitforfinish, callback) {
		return app.serial("move", action, callback, step, feedrate, waitforfinish);
	}
	
	/**
	 * Set current position of all axis to zero
	 * @callback Callback function on execution finish
	 */
	app.jogExtrude = function (action, step, feedrate, waitforfinish, callback) {
		//return app.serial("zeroAll", true, callback);
	}
	
	app.jogSetExtruderMode = function (mode, callback) {
		return app.serial("setExtruderMode", mode, callback);
	}
	
	/**
	 * Set current position of all axis to zero
	 * @callback Callback function on execution finish
	 */
	app.jogZeroAll = function (callback) {
		return app.serial("zeroAll", true, callback);
	}
	/**
	 * Home XY axis
	 * @callback Callback function on execution finish
	 */
	app.jogHomeXY = function (callback) {
		return app.serial("home", "home-xy", callback);
	}
	/**
	 * Home all axis and Z using z-min endstop
	 * @callback Callback function on execution finish
	 */
	app.jogHomeXYZ = function (callback) {
		return app.serial("home", "home-xyz-min", callback);
	}
	/**
	 * Home Z axis using z-min endstop
	 * @callback Callback function on execution finish
	 */
	app.jogHomeZ = function (callback) {
		return app.serial("home", "home-z-min", callback);
	}
	/**
	 * Get current jog position
	 * @callback Callback function on execution finish
	 */
	app.jogGetPosition = function (callback) {
		return app.serial("getPosition", true, callback);
	}
	/**
	 * Send gcode commands to jog handler.
	 * @callback Callback function on execution finish
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
	/*
	 * freeze menu whene tasks are running
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
					link.removeAttr('href');
					link.click(function () {return false;});
				}
			}
		});
		app.updateNotificationBadge();
	};
	/*
	 * 
	 */
	app.unFreezeMenu = function () {
		var a = $("nav li > a");
		a.each(function() {
			var link = $(this);
			link.removeClass('menu-disabled');
			link.attr('href', $(this).attr('data-href'));
			if(!link.next().is('ul')){
				link.unbind('click');
			}
		});
		$(".freeze-menu").remove();
	}
	/**
	*
	*/
	app.unFreezeParent = function(link) {
		//TODO
	}
	/**
	* show error message 
	*/
	app.showWarningAlert = function (message) {
		$.smallBox({
			title : "Warning",
			content : message,
			color : "#C46A69",
			timeout: 10000,
			icon : "fa fa-warning"
		});
	}
	/**
	* show error message 
	*/
	app.showErrorAlert = function (message) {
		$.smallBox({
			title : "Error",
			content : message,
			color : "#C46A69",
			timeout: 10000,
			icon : "fa fa-warning"
		});
	}
	/**
	* show info message
	*/
	app.showInfoAlert = function(message) {
		$.smallBox({
			title : "Info",
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
		$.get($.first_setup_url_action, function(data, status){
			if(data.response == true){
				setTimeout(function() {
						$.smallBox({
							title : "Wizard Setup",
							content : "It seems that you still did not complete the first recommended setup:<ul><li>Manual Bed Calibration</li><li>Probe Lenght Calibration</li><li>Engage Feeder</li></ul><br>Without a proper calibration you will not be able to use the FABtotum correctly<br>Do you want to do it now?<br><br><p class='text-align-right'><a href='/fabui/maintenance/first-setup' class='btn btn-primary btn-sm'>Yes</a> <a href='javascript:dont_ask_wizard();' class='btn btn-danger btn-sm'>No</a> <a href='javascript:finalize_wizard();' class='btn btn-warning btn-sm'>Don't ask me anymore</a> </p>",
							color : "#296191",
							icon : "fa fa-warning swing animated"
						});
				}, 1000);
			}
		});
	};
	/*
	 * launch reset controller command
	 */
	app.resetController = function() {
		openWait("<i class=\"fa fa-circle-o-notch fa-spin\"></i> " + _("Resetting controller"), _("Please wait"), false);
		$.get(reset_controller_url_action, function(){
			closeWait();
		});
	}
	/*
	 * stop all operations and task on the fabtotum and refresh the page after 3 seconds
	 */
	app.stopAll = function(message) {
		message = message || _("Aborting all operations") ;
		openWait(message, ' ', false);
		$.get(stop_all_url_action, function(){
			waitContent(_("Reloading page"));
			setTimeout(function(){ 
				location.reload(); 
			}, 5000);
		});
	}
	/*
	 * show a message and refresh the page after 3 seconds
	 */
	app.refreshPage = function(message) {
		message = message || _("Aborting all operations");
		openWait(message, ' ', false);
		waitContent(_("Reloading page"));
		setTimeout(function(){ 
			location.reload();
		}, 3000);
	}
	/*
	 * launch reboot command and refresh the page after 21 seconds
	 */
	app.reboot = function() {
		app.rebooting = true;
		clearInterval(temperatures_interval);
		//$.is_macro_on = true;
		openWait("<i class='fa fa-circle-o-notch fa-spin'></i> " + _("Restart in progress") , _("Please wait") + '...', false);
		$.ajax({
			url: reboot_url_action,
		}).done(function(data) {
			waitContent( _("You will be redirect to login page") );
			app.redirectToUrlWhenisReady(base_url);
		}).fail(function(jqXHR, textStatus){
			//clear intervals
			waitContent( _("You will be redirect to login page") );
			app.redirectToUrlWhenisReady(base_url);
		});
	};
	/*
	 * launch poweroff command and show popup with instructions after 5 seconds
	 */
	app.poweroff = function() {
		clearInterval(temperatures_interval);
		//is_macro_on = true;
		openWait('<i class="fa fa-circle-o-notch fa-spin"></i> ' + _("Shutdown in progress") , _("Please wait") + '...', false);
		$.ajax({
			url: poweroff_url_action,
		}).done(function(data) {
			app.showAlertToPowerOff();
		}).fail(function(jqXHR, textStatus){
			app.showAlertToPowerOff();
		});
	};
	/*
	 *  logout from fabui
	 */
	app.logout = function() {
		$.root_.addClass('animated fadeOutUp');
		setTimeout(function(){
			window.location = logout_url;
		}, 1000);
	};
	/*
	 * lock screen
	 */
	app.lockScreen = function(){
		$.root_.addClass('animated fadeOutUp');
		setTimeout(function(){
			$("#lock-screen-form").submit();
		}, 1000);
		
	};
	/*
	 * check if there are updates avaialabe 
	 */
	app.checkUpdates = function () {
		$.get($.update_check_url, function(data, status){
			if(data.updates.updated == false){
				$.number_updates++;
				$(".update-list").find('span').html('	Updates (1) ');
				$("nav li > a").each(function() {
					if ($(this).attr('data-controller') == 'updates') {
						$(this).append('<span class="badge bg-color-red pull-right inbox-badge animated fadeIn">1</span>');
					}
				});
				app.updateNotificationBadge();
			}
		});
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
		if(number_updates > 0 ){
			
			var a = $("nav li > a");
			a.each(function() {
				var link = $(this);
				var controller = link.attr('data-controller');
				if(controller == 'updates'){
					$("#update-menu-badge").remove();
					link.append('<span id="update-menu-badge" class="badge pull-right inbox-badge bg-color-red margin-right-13">'+number_updates+'</span>');
				}
			});
		}
		
		if(number_plugin_updates > 0 ){
			
			var a = $("nav li > a");
			a.each(function() {
				var link = $(this);
				var controller = link.attr('data-controller');
				if(controller == 'plugin'){
					$("#plugin-update-menu-badge").remove();
					link.append('<span id="plugin-update-menu-badge" class="badge pull-right inbox-badge bg-color-red margin-right-13">'+number_plugin_updates+'</span>');
				}
			});
		}
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
		console.log ('Error with WebSocket', ws.readyState);
		if(app.rebooting == false){ //reconnect only if the unit is not rebooting
			app.ws_callbacks = {};
			socket_connected = false;
			app.ws_reconnecting = true;
			
			setTimeout(function(e){
				app.ws_reconnecting = false;
				app.webSocket(true);
				}, 1000);
		}
	}
	/**
	* websocket onclose event handler
	**/
	app.ws_onclose = function(e)
	{
		console.log ('WebSocket onClose',ws.readyState);
		if(app.rebooting == false){ //reconnect only if the unit is not rebooting
			app.ws_callbacks = {};	
			socket_connected = false;
			socket = null;
			
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
	/**
	* WebSocket for faster communications
	* it goes automatically with ajax pulling method if WebSocket is not supported
	*/
	app.webSocket = function(force_fallback)
	{
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
			if(debugState)
				root.console.log("WebSocket opened as" , socket.fallback?"fallback":"native" );
			app.afterSocketConnect();
		};  
		/**
		 * handle messages from the server
		 */
		ws.onmessage = function (e) {
			try {
				var obj = jQuery.parseJSON(e.data);
				if(debugState)
					console.log("✔ WebSocket received message: %c [" + obj.type + "]", debugStyle);
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
					default:
						break;
				}
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
		if(app.ws_reconnecting == false && ws.readyState == 3){
			app.ws_onerror();
		}
		if(ws.readyState == 1){
			socket_connected = true;
			if(debugState)
				root.console.log("✔  WebSocket connected as" , socket.fallback?"fallback":"native");
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
		$(".top-bar-nozzle-actual").html(parseInt(ext_temp));
		$(".top-bar-nozzle-target").html(parseInt(ext_temp_target));
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
		if($(".extruder-temp").length > 0) $(".extruder-temp").html(parseFloat(ext_temp).toFixed(0));
		if($(".extruder-target").length > 0) $(".extruder-target").html(parseFloat(ext_temp_target).toFixed(0));
		if($(".bed-temp").length > 0) $(".bed-temp").html(parseFloat(bed_temp).toFixed(0));
		if($(".bed-target").length > 0) $(".bed-target").html(parseFloat(bed_temp_target).toFixed(0));
		
		if (typeof window.updateTemperatures == 'function') window.updateTemperatures(ext_temp, ext_temp_target, bed_temp,bed_temp_target);
	};
	/*
	 * display jog response
	 */
	/*
	 * @tag: to_be_removed
	 * app.writeJogResponse = function(data){
		var added = false;
		if($(".jogResponseContainer").length > 0){
			$.each(data, function(i, item) {
				if($(".consoleContainer .response_" + i).length == 0){
					var html = '<i>' + item.code + '</i> :';
					if(item.reply.length > 1){
						$.each(item.reply, function(index, value){
							html += '<p>' + value +'</p>';
						});
					}else{
						html += item.reply[0];
					}
					$(".consoleContainer").append('<div class="jog_response response_' + i + '">' + html + '</div><hr class="simple">');
					added = true;
			    }
			});
			if(added) $(".jogResponseContainer").animate({ scrollTop: $('.jogResponseContainer').prop("scrollHeight")}, 1000);
		}
	}*/
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
	 * check if are some operations before leaving the page
	 */
	app.checkExit = function(){
		if($.is_stopping_all == false && $.is_macro_on == true){
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
				app.refreshPage(_("Front panel has been opened") + '.<br> ' + _("Aborting all operations"));
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
		error_code = 103;	
		var options = '';
		
		$.each(heads, function(i, item) {
			options += '['+item.name +']';
		});
		
		$.SmartMessageBox({
			title : '<i class="fa fa-warning txt-color-orangeDark"></i> ' + emergency_descriptions[error_code],
			content : _("Before proceed make sure the head is properly locked in place"),
			buttons : "[" + _("Install head")  + "]["+ _("Ignore") +"]",
			input : "select",
			options : options
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
		/*
		//create modal if not exists
		if($("#installHeadMoal").length <= 0){
			var heads_options_html = '';
			$.each(heads, function(i, item) {
				heads_options_html += '<option>'+item.name +'</option>';
				
			});
			var html_modal = '<div class="modal fade" id="installHeadMoal" tabindex="-1" role="dialog">'+
				'<div class="modal-dialog" role="document">' +
					'<div class="modal-content">' + 
						'<div class="modal-header">' + 
							'<h4 class="modal-title" id="myModalLabel"><i class="fa fa-warning"></i> '+ emergency_descriptions[error_code] +'</h4>' +
						'</div>' +
						'<div class="modal-body">'+
							'<div class="smart-form">'+
								'<fieldset>'+
									'<section>'+
										'<label class="label"> Select head </label>'+
										'<label class="select">'+
											'<select>'+heads_options_html+'</select>'+
											'<i></i>'+
										'</label>'+
									'</section>'+
								'</fieldset>'+
							'</div>'+
						'</div>' +
						'<div class="modal-footer">' + 
							'<button type="button" class="btn btn-primary"><i class="fa fa-wrench"></i> Install</button>' +
						'</div>' +
					'</div>'+
				'</div>'+
			'</div>';
			$("#main").append(html_modal);
		}
		$("#installHeadMoal").modal({
			backdrop: 'static'
		});
		$('#installHeadMoal').modal('show');
		*/

		
	}
	/*
	 * alive the fabtotum after an emergency
	 */
	app.setSecure = function(bool){
		is_macro_on = true;
		/*
		if(socket_connected == true){
			//socket.send('message', '{"function": "serial", "data":{"mode":' + bool + ' } }');
			app.serial('emergency', bool);
			is_emergency = false;
			is_macro_on  = false;
			return;
		}*/
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
		openWait('<i class="fa fa-circle-o-notch fa-spin"></i> ' + _("Installing head"), _("Please wait") + '...', false);
		$.ajax({
			type : "POST",
			url : install_head_url + '/'+head,
			dataType : 'json'
		}).done(function(response) {
			location.reload();
		});
	}
	/*
	 * redirect to new head installation page
	 */
	app.goToInstallNewHead = function(){
		$.root_.addClass('animated fadeOutUp');
		document.location.href = new_head_url_action;
		location.reload();
	};
	/*
	 * manage upcoming alerts from the printer
	 */
	app.manageAlert = function(data){
		var code = parseInt(data.code);
		$.smallBox({
			title : "Message",
			content : $.emergency_descriptions[code],
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
		$.is_task_on = number_tasks > 0;
		if($.is_task_on == true){
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
	 * 
	 */
	app.getStatus = function(){
		if(socket_connected && (is_macro_on == false && is_task_on == false && is_emergency == false)) app.serial('getStatus', '');
	}
	/*
	 * read temperatures
	 */
	app.getTemperatures = function(){
		if(debugState)
			root.console.log("✔ getTemperatures");
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
	app.serial = function(func, val, callback, step=0, feedrate=0, waitforfinish=false) {
		
		if(debugState)
			root.console.log("✔ app.serial: " + func + ', ' + val);
		
		var stamp = Date.now();
		
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
		/*else
		{
			console.log("no callback");
		}*/
		
		socket.send( JSON.stringify(messageToSend) );
		
		return stamp;
	};
	/**
	 * check if internet connection is available
	 */
	app.isInternetAvailable = function(){
		if(debugState)
			root.console.log("✔ app.isInternetAvailable");
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
			var message = 'USB Disk';
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
			socket.send('{"function": "usbInserted"}');   //check for if usb disk is connected
		}
	}
	/**
	 * handle trace content from task/macro
	 */
	app.handleTrace = function(content) {
		if($(".trace-console").length > 0){
			var contentSplitted = content.split('\n');
			var html = '';
			$.each(contentSplitted, function( index, value ) {
				if(value != '')
					html += '<p>'+ value +'</p>';
			});
			$(".trace-console").html(html).scrollTop(1E10);
			$(".trace-console").parent().scrollTop(1E10);
		}
		waitContent(content);
	}
	/**
	 * reset temperatures plot
	 * @param (int) elements - how many elements to keep. 0 reset all
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
	* init true | false (if is first loading time)
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
			number_updates = data.update.bundles;
			
			number_plugin_updates = data.update.plugins;
			
			if(data.update.firmware) number_updates += 1;
			if(data.update.boot) number_updates += 1;
			
			app.updateNotificationBadge();
		});
		
	}
	/**
	* redirect to a specific url only when the url responds 200
	**/
	app.redirectToUrlWhenisReady = function (url)
	{
		$.get(url)
			.success(function(result) {				
				document.location.href = url;
			})
			.error(function(jqXHR, textStatus, errorThrown) {
				setTimeout( function() {
					app.redirectToUrlWhenisReady(url);
				}, 500 );
			});
	}
	/**
	*
	**/
	app.forceRecovery = function (){
		
		setTimeout(function(){
			openWait("<i class='fa fa-warning'></i> " + _("Oops.. An error occurred"), _("You will be redirect to recovery page"), false);
			
			$.get(set_recovery_url + '/activate', function(data){ 
				$.ajax({
					url: reboot_url_action,
				}).done(function(data) {
				}).fail(function(jqXHR, textStatus){
					//clear intervals
					app.redirectToUrlWhenisReady('http://'+ location.hostname);
				});
			});
			
		}, 5000);
	}
	/**
	* get hardware settings
	**/
	app.getSettings = function() {
		$.get(control_url + '/getSettings', function(data, status){
			app.analizeMenu(data.feeder.show);
		});
	}
	/**
	* analize menu to check if something must be hided
	**/
	app.analizeMenu = function (show_feeder)
	{
		var item_to_hide = ['maintenance/feeder-engage', 'maintenance/4th-axis'];
		var a = $("nav li > a");
		a.each(function() {
			var link = $(this);
			var href = link.attr('data-href');
			if(jQuery.inArray( href, item_to_hide ) >= 0 && show_feeder == false){
				link.parent().addClass('hidden');
			}else{
				link.parent().removeClass('hidden');
			}
		});
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
	* get network interfaces and show icon on ribbon
	**/
	app.getNetworkInfo = function ()
	{
		$.get(network_info_url + '?' + jQuery.now(), function(data, status){
			
			$(".wifi-ribbon-icon").remove();
			$(".internet-ribbon-icon").remove();
			
			if(data.interfaces != null){
			
				if(data.interfaces.hasOwnProperty('wlan0')){
				
					if(data.interfaces.wlan0.wireless.hasOwnProperty('ssid')){
						$(".ribbon-button-alignment").prepend('<span data-title="' + _("Wifi connected") + '"  rel="tooltip" data-placement="bottom" class="btn btn-ribbon wifi-ribbon-icon"><i class="fa fa-wifi"></i></span>');	
					}
				}
				
				if(data.interfaces.hasOwnProperty('wlan1')){
				
					if(data.interfaces.wlan1.wireless.hasOwnProperty('ssid')){
						$(".ribbon-button-alignment").prepend('<span data-title="' + _("Wifi connected") + '"  rel="tooltip" data-placement="bottom" class="btn btn-ribbon wifi-ribbon-icon"><i class="fa fa-wifi"></i></span>');	
					}
				}
			
			}
			
			
			if(data.internet){
				$(".ribbon-button-alignment").prepend('<span data-title="' + _("Internet available") + '"  rel="tooltip" data-placement="bottom" class="btn btn-ribbon internet-ribbon-icon"><i class="fa fa-globe"></i></span>');
			}
			pageSetUp();
		});
	}
	/**
	* notify user when is possible to power off the printer
	**/
	app.showAlertToPowerOff = function ()
	{
		$.get(base_url)
			.success(function(result) {				
				setTimeout( function() {
					app.showAlertToPowerOff();
				}, 50 );
			})
			.error(function(jqXHR, textStatus, errorThrown) {
				setTimeout(function() {
					waitTitle(_("Now you can switch off the power"));
					waitContent(_("Note: 5 seconds after the beep it's safe to switch off your unit."));
					//is_macro_on = false;
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
	return app;
})({});

