fabApp = (function(app) {
	
	app.FabActions = function(){
	
		var fabActions = {
			userLogout: function($this){
				$.SmartMessageBox({
					title: "<i class='fa fa-sign-out txt-color-orangeDark'></i> Hi <span class='txt-color-orangeDark'><strong>" + $this.data("user-name") + "</strong></span> ",
					content : $this.data('logout-msg') || "You can improve your security further after logging out by closing this opened browser",
					buttons: "[Cancel][Go]",
					input: "select",
					options: "[Shutdown][Restart][Logout]"
				}, function(ButtonPressed, Option) {
					if(ButtonPressed == 'Cancel'){
						return;
					}
					if (Option == "Logout") {
						app.logout();
					}
					if(Option == 'Shutdown'){
						app.poweroff();
					}
					if(Option == 'Restart'){
						app.reboot();
					}
				});
			},
			
			resetController: function($this){
				$.SmartMessageBox({
                    title: "<i class='fa fa-bolt'></i> <span class='txt-color-orangeDark'><strong>Reset Controller</strong></span> ",
                    content: $this.data("reset-msg") || "You can improve your security further after logging out by closing this opened browser",
                    buttons: "[No][Yes]"
                }, function(ButtonPressed) {
                   if(ButtonPressed == 'Yes') app.resetController();
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
	
		
	app.domReadyMisc = function() {
		
		if (typeof(Storage) !== "undefined"){
			if(localStorage.getItem("temperaturesPlot") !== null){			
				temperaturesPlot =  JSON.parse(localStorage.getItem("temperaturesPlot"));
			}
		}
		
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
        
        
        $(".language").click(function() {

			var actual_lang = $("#actual_lang").val();
			var new_lang = $(this).attr("data-value");
		
			if (actual_lang != new_lang) {
				$("#lang").val(new_lang);
				openWait('<i class="fa fa-flag"></i><br> Loading language ');
				$("#lang_form").submit();
			}
		
		});
		
		$(".lock-ribbon").click(function() {
			app.lockScreen();
		});
		
		$("#refresh-notifications").click(function() {
			app.refreshNotificationsContent();
		});
        
        $(document).mouseup(function(a) {
            $(".top-ajax-temperatures-dropdown").is(a.target) || 0 !== $(".top-ajax-temperatures-dropdown").has(a.target).length || ($(".top-ajax-temperatures-dropdown").fadeOut(150), $(".top-ajax-temperatures-dropdown").prev().removeClass("active"))
            $(".top-ajax-jog-dropdown").is(a.target) || 0 !== $(".top-ajax-jog-dropdown").has(a.target).length || ($(".top-ajax-jog-dropdown").fadeOut(150), $(".top-ajax-jog-dropdown").prev().removeClass("active"))
        });
        
        $(".top-directions").on("click", function(){
        	app.jogMoveXY($(this).attr("data-attribute-direction"));
        });
        
        $(".top-axisz").on("click", function(event){
        	app.jogAxisZ($(this).attr("data-attribute-function"), $(this).attr("data-attribute-value"));
        	event.preventDefault();
        });
        
		$(".zero_all").on("click", function(){
			app.jogZeroAll();
		});
		
		//init temperatures sliders on top
		if (typeof(Storage) !== "undefined") {
			$("#top-bar-nozzle-actual").html(parseInt(localStorage.getItem("nozzle_temp")));
			$("#top-bar-nozzle-target").html(parseInt(localStorage.getItem("nozzle_temp_target")));
			$("#top-bar-bed-actual").html(parseInt(localStorage.getItem("bed_temp")));
			$("#top-bar-bed-target").html(parseInt(localStorage.getItem("bed_temp_target")));
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
		document.getElementById("top-bed-target-temp").noUiSlider.on('slide', app.topBedTempSlide);
		document.getElementById("top-bed-target-temp").noUiSlider.on('change', app.topBedTempChange);
		document.getElementById("top-bed-target-temp").noUiSlider.on('start', app.blockSliders);
		document.getElementById("top-bed-target-temp").noUiSlider.on('end', app.enableSliders);
		//nozzle events
		document.getElementById("top-ext-target-temp").noUiSlider.on('slide', app.topExtTempSlide);
		document.getElementById("top-ext-target-temp").noUiSlider.on('change', app.topExtTempChange);
		document.getElementById("top-ext-target-temp").noUiSlider.on('start', app.blockSliders);
		document.getElementById("top-ext-target-temp").noUiSlider.on('end', app.enableSliders);
		
	};
	/*
	 * 
	 */
	app.topBedTempSlide = function(e){
		$("#top-bar-bed-target").html(parseInt(e[0]));
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
		$("#top-bar-nozzle-target").html(parseInt(e[0]));
	    $("#ext-degrees").html(parseInt(e[0]) + '&deg;C');
	    if($("#ext-target-temp").length > 0){
	    	document.getElementById('ext-target-temp').noUiSlider.set([parseInt(e[0])]);
	    }
	}
	/*
	 * 
	 */
	app.topExtTempChange = function(e){
		app.serial("setExtruderTemp", parseInt(e[0]));
	}
	/*
	 * 
	 */
	app.jogMoveXY = function (value, callback) {
		return app.serial("moveXY", value, callback);
	}
	/*
	 * 
	 */
	app.jogAxisZ = function (func, direction, callback){
		return app.serial('moveZ', direction, callback); 
	}
	/*
	 * 
	 */
	app.jogZeroAll = function (callback) {
		app.serial("zeroAll", true, callback);
	}
	/*
	 * 
	 */
	app.jogExtrude = function(sign, callback) {
		app.serial('extrude', sign, callback);
	}
	/**
	 * Send gcode commands to jog handler.
	 * @callback Callback function on execution finish
	 */
	app.jogMdi = function(value, callback) {
		console.log(value);
		return app.serial('manualDataInput', value, callback);
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
		excepet_item_menu[1] = 'objectmanager';
		excepet_item_menu[2] = 'make/history';
		excepet_item_menu[3] = except;
		
		var a = $("nav li > a");
		a.each(function() {
			var link = $(this);
			var controller = link.attr('data-controller');
			if(jQuery.inArray( controller, excepet_item_menu ) >= 0 ){
				if(controller == except){
					//link.addClass('except-link');
					app.unFreezeParent(link);
					link.append('<span class="badge bg-color-red pull-right inbox-badge freeze-menu">!</span>');
				}
			}else{
				link.addClass('menu-disabled');
				link.removeAttr('href');
			}
		});
		$('.menu-disabled').click(function () {return false;});
	};
	/*
	 * 
	 */
	app.unFreezeMenu = function () {
		var a = $("nav li > a");
		$('.menu-disabled').unbind('click');
		a.each(function() {
			var link = $(this);
			link.removeClass('menu-disabled');
			link.attr('href', $(this).attr('data-href'));
		});
		$(".freeze-menu").remove();
		
	}
	/**
	*
	*/
	app.unFreezeParent = function(link){
		//TODO
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
		$.is_macro_on = true;
		openWait("<i class=\"fa fa-circle-o-notch fa-spin\"></i> Resetting controller");
		$.get(reset_controller_url_action, function(){
			closeWait();
			$.is_task_on = true;
		});
	}
	/*
	 * stop all operations and task on the fabtotum and refresh the page after 3 seconds
	 */
	app.stopAll = function(message) {
		message = message || 'Aborting all operations ';
		openWait(message, ' ', false);
		$.is_stopping_all = true;
		$.get(stop_all_url_action, function(){
			waitContent("Refreshing page");
			setTimeout(function(){ 
				location.reload(); 
			}, 3000);
		});
	}
	/*
	 * show a message and refresh the page after 3 seconds
	 */
	app.refreshPage = function(message) {
		message = message || 'Aborting all operations ';
		openWait(message, ' ', false);
		waitContent("Refreshing page");
		setTimeout(function(){ 
			location.reload();
		}, 3000);
	}
	/*
	 * launch reboot command and refresh the page after 21 seconds
	 */
	app.reboot = function() {
		clearInterval(temperatures_interval);
		//$.is_macro_on = true;
		openWait("<i class='fa fa-circle-o-notch fa-spin'></i> Restart in progress", 'Please wait...', false);
		$.ajax({
			url: reboot_url_action,
		}).done(function(data) {
		}).fail(function(jqXHR, textStatus){
			//clear intervals
			waitContent("you will be redirect to login page");
			app.redirectToUrlWhenisReady(base_url);
		});
	};
	/*
	 * launch poweroff command and show popup with instructions after 5 seconds
	 */
	app.poweroff = function() {
		clearInterval(temperatures_interval);
		//is_macro_on = true;
		openWait('<i class="fa fa-circle-o-notch fa-spin"></i> Shutdown in progress', 'Please wait...', false);
		$.ajax({
			url: poweroff_url_action,
		}).done(function(data) {
			
		}).fail(function(jqXHR, textStatus){
			setTimeout(function() {
				waitTitle('Now you can switch off the power');
				waitContent('');
				//is_macro_on = false;
			}, 10000);
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
		$("#lock-screen-form").submit();
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
				//var html = '<div class="row"><div class="col-sm-12"><div class="alert alert-danger alert-block animated fadeIn"><button class="close" data-dismiss="alert">×</button><h4 class="alert-heading"> <i class="fa fa-refresh"></i> New important software updates are now available, <a style="text-decoration:underline; color:white;" href="/fabui/updates">update now!</a> </h4></div></div></div>';
				//if($.module != 'updates') $("#content").prepend(html);
			}
		});
	};
	/*
	 * update notification badge
	 */
	app.updateNotificationBadge = function () {
		if(($.number_updates + $.number_tasks) > 0){
			$("#activity").find('.badge').html(($.number_updates + $.number_tasks));
			$("#activity").find('.badge').addClass('bg-color-red bounceIn animated');
		}else{
			$("#activity").find('.badge').removeClass('bg-color-red bounceIn animated');
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
	 
	app.ws_callbacks = {};
	app.webSocket = function()
	{
		options = {
			http: websocket_fallback_url,
		};
		
		socket = ws = $.WebSocket ('ws://'+socket_host+':'+socket_port, null, options);

		// WebSocket onerror event triggered also in fallback
		ws.onerror = function (e) {
			console.log ('Error with WebSocket uid: ' + e.target.uid);
		};

		// if connection is opened => start opening a pipe (multiplexing)
		ws.onopen = function () {
			socket_connected = true;
			if(debugState)
				root.console.log("WebSocket opened as" , socket.fallback?"fallback":"native" );
			
			app.afterSocketConnect();
		};  
		
		ws.onmessage = function (e) {
			try {
				var obj = jQuery.parseJSON(e.data);
				if(debugState)
					console.log("✔ WebSocket received message: %c [" + obj.type + "]", debugStyle);
				
				switch(obj.type){
					case 'temperatures':
						fabApp.updateTemperatures(obj.data);
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
					case 'jog':
						app.manageJogResponse(obj.data);
						break;
					case 'trace':
						app.handleTrace(obj.data.content);
						break;
					default:
						break;
				}
			}catch(e){
				return;
			}
		}
	};
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
		$("#top-bar-nozzle-actual").html(parseInt(ext_temp));
		$("#top-bar-nozzle-target").html(parseInt(ext_temp_target));
		$("#top-bar-bed-actual").html(parseInt(bed_temp));
		$("#top-bar-bed-target").html(parseInt(bed_temp_target));
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
		
	};
	/*
	 * display jog response
	 */
	app.writeJogResponse = function(data){
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
	}
	/*
	 * write serial replys to jog console
	 */
	app.writeSerialResponseToConsole = function(data){
		
		if($(".jogResponseContainer").length > 0){
			var html = '';
			$.each(data.commands, function(i, item) {
				console.log(item.reply);
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
			return "You have attempted to leave this page. The Fabtotum Personal Fabricator is still working. Are you sure you want to reload this page?";
		}
	};
	/*
	 * manage emergeny alerts
	 */
	app.manageEmergency = function(data) {
		if(is_emergency == true) return; //exit if is already on emergency status
		var code = parseInt(data.code);
		if(code == 102){ // if panel door is open force emergency button
			//app.stopAll('Front panel has been opened.<br> Aborting all operations');
			app.refreshPage('Front panel has been opened.<br> Aborting all operations');
			return;
		}
		is_emergency = true;
		var buttons = '[OK][IGNORE]';
		if(code == 103) buttons = '[IGNORE] [INSTALL HEAD]';
		$.SmartMessageBox({
			buttons : buttons,
			title : "<h4><span class='txt-color-orangeDark'><i class='fa fa-warning fa-2x'></i></span>&nbsp;&nbsp;" + emergency_descriptions[code] + "<br>&nbsp;Press OK to continue or Ignore to disable this warning</h4>"
		},function(ButtonPressed) {
			if(ButtonPressed == 'OK' || (ButtonPressed == 'IGNORE' && buttons.indexOf("INSTALL HEAD") > -1) ) app.setSecure(1);
			else if(ButtonPressed == 'IGNORE') app.setSecure(0);
			else if(ButtonPressed == 'INSTALL HEAD') app.goToInstallNewHead();
		});
	};
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
		$.number_tasks = data.number;
		$.is_task_on = $.number_tasks > 0;
		if($.is_task_on == true){
			$.each(data.items, function() {
				var row = this;
				controller = row.controller;
				if (controller == 'make') controller += '/' + row.type;
				app.freezeMenu(controller); //freeze menu
				$(".task-list").find('span').html('	Tasks (' + data.number + ') '); //update number on ajax dropdown list
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
	app.serial = function(func, val, callback) {
		
		if(debugState)
			root.console.log("✔ app.serial: " + func + ', ' + val);
		
		var xyStep       = $("#xyStep").length            > 0 ? $("#xyStep").val()            : 10;
		var zStep        = $("#zStep").length             > 0 ? $("#zStep").val()             : 5;
		var extruderStep = $("#extruderStep").length      > 0 ? $("#extruderStep").val()      : 10;
		var xyzFeed      = $("#xyzFeed").length           > 0 ? $("#xyzFeed").val()           : 1000;
		var extruderFeed = $("#extruder-feedrate").length > 0 ? $("#extruder-feedrate").val() : 300;
		
		var stamp = Date.now();
		
		var data = {
			'method'           : func,
			'value'            : val,
			'stamp'            : stamp,
			'useXmlrpc'        : xmlrpc,
			'step'             : {'xy':  xyStep, 'z':zStep, 'extruder': extruderStep},
			'feedrate'         : {'xyz': xyzFeed, 'extruder':extruderFeed}
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
	 * things to do when socket is connected
	 */
	app.afterSocketConnect = function(){
		if(socket_connected == true){
			//socket.send('{"function": "getTasks"}'); //check for tasks, @tag:remove
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
				temperaturesPlot.extruder.temp.splice(0, temperaturesPlot.extruder.temp.length - 10);
			}
			if(temperaturesPlot.extruder.target.length > elements){
				temperaturesPlot.extruder.target.splice(0, temperaturesPlot.extruder.target.length - 10);
			}
			if(temperaturesPlot.bed.temp.length > elements){
				temperaturesPlot.bed.temp.splice(0, temperaturesPlot.bed.temp.length - 10);
			}
			if(temperaturesPlot.bed.target.length > elements){
				temperaturesPlot.bed.target.splice(0, temperaturesPlot.bed.target.length - 10);
			}
		}else{
			temperaturesPlot = {extruder: {temp: [], target: []}, bed: {temp:[], target:[]}};
		}
		
	}
	/**
	* check if there are running tasks, and more
	*/
	app.getState = function()
	{
		var freezing_status = ['running', 'aborting', 'completing'];
		$.get(task_monitor_file_url + '?' + jQuery.now(), function(data, status){
			if(data.task.hasOwnProperty('status')){
				if(jQuery.inArray( data.task.status, freezing_status ) >= 0 ){
					app.freezeMenu(data.task.type);
				}else{
					app.unFreezeMenu();
				}
			}
		});
	}
	/**
	* redirect to a specific url only when the url responds 200
	**/
	app.redirectToUrlWhenisReady = function (url, timer)
	{
		timer = timer | 1000;
		var checkInterval = setInterval(function(){
			$.get(url)
				.success(function(result) { clearInterval(checkInterval); document.location.href = url; })
				.error(function(jqXHR, textStatus, errorThrown) { });
		}, timer);
	}
	/**
	*
	**/
	app.forceRecovery = function (){
		
		setTimeout(function(){
			openWait("<i class='fa fa-warning'></i> Oops.. An error occurred", 'you will be redirect to recovery page', false);
			
			$.get(set_recovery_url + '/activate', function(data){ 
				console.log(data);
				
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
	return app;
})({});

