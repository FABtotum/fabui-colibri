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
        	app.jogMoveXY($(this).attr("data-attribue-direction"));
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
		app.serial("setNozzleTemp", parseInt(e[0]));
	}
	/*
	 * 
	 */
	app.jogMoveXY = function (value) {
		app.serial("moveXY", value);
	}
	/*
	 * 
	 */
	app.jogAxisZ = function (func, direction){
		app.serial('moveZ', direction); 
	}
	/*
	 * 
	 */
	app.jogZeroAll = function () {
		app.serial("zeroAll", true);
	}
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
			var controller = $(this).attr('data-controller');
			if(jQuery.inArray( controller, excepet_item_menu ) >= 0 ){
				if(controller == except){
					$(this).append('<span class="badge bg-color-red pull-right inbox-badge freeze-menu">!</span>');
				}
			}else{
				$(this).addClass('menu-disabled');
				$(this).removeAttr('href');
			}
		});
	};
	/*
	 * 
	 */
	app.unFreezeMenu = function () {
		var a = $("nav li > a");
		a.each(function() {
			$(this).removeClass('menu-disabled');
			$(this).attr('href', $(this).attr('data-href'));
		});
		$(".freeze-menu").remove();
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
	 * launch reboot command and refresh the page after 21 seconds
	 */
	app.reboot = function() {
		$.is_macro_on = true;
		openWait("<i class='fa fa-circle-o-notch fa-spin'></i> Restart in progress");
		$.ajax({
			url: reboot_url_action,
		}).done(function(data) {
		
		}).fail(function(jqXHR, textStatus){
			setTimeout(function() {
				waitContent("Restarting please wait...");
				is_macro_on = false;
				document.location.href = logout_url;
			}, 21000);
		});
	};
	/*
	 * launch poweroff command and show popup with instructions after 5 seconds
	 */
	app.poweroff = function() {
		is_macro_on = true;
		openWait('<i class="fa fa-circle-o-notch fa-spin"></i> Shutdown in progress');
		$.ajax({
			url: poweroff_url_action,
		}).done(function(data) {
			
		}).fail(function(jqXHR, textStatus){
			setTimeout(function() {
				waitTitle('Now you can switch off the power');
				showShutdownImage(); //utility function stored in utilities.js
				closeWait();
				is_macro_on = false;
			}, 5000);
		});
	};
	/*
	 *  logout from fabui
	 */
	app.logout = function() {
		$.root_.addClass('animated fadeOutUp');
		setTimeout(function(){
			window.location = $.logout_url;
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
	 * 
	 */
	app.webSocket = function () {
		function isWebSocketAvailable(){
			return ("WebSocket" in window);
		}
		function onOpen(){
			socket_connected = true;
			app.afterSocketConnect();
		}
		function onClose(){
			socket_connected = false;
		}
		function onMessage(data){
			try {
				var obj = jQuery.parseJSON(data);
				switch(obj.type){
					case 'temperature':
						app.updateTemperaturesInfo(obj.data);
						break;
					case 'serial':
						app.writeSerialResponseToConsole(obj.data);
						break;
					case 'macro':
						app.manageMacro(obj.data);
						break;
					case 'emergency':
						app.manageEmergency(obj);
						break;
					case 'alert':
						app.manageAlert(obj);
						break;
					case 'task':
						app.manageTask(obj.data);
						break;
					case 'system':
						app.manageSystem(obj.data);
						break;
					case 'usb':
						app.usb(obj.data.status, obj.data.alert);
					default:
						break;
				}
			}catch(e){
				return;
			}
		}
		if(isWebSocketAvailable()){
			socket = new FabWebSocket(socket_host, socket_port);
			socket.bind('message', onMessage);
			socket.bind('open', onOpen);
			socket.bind('close', onClose);
			socket.connect();
		}
	};
	/*
	 * update temperatures info
	 */
	app.updateTemperaturesInfo = function(data){
		var re = /ok\sT:([+|-]*[0-9]*.[0-9]*)\s\/([+|-]*[0-9]*.[0-9]*)\sB:([+|-]*[0-9]*.[0-9]*)\s\/([+|-]*[0-9]*.[0-9]*)/;
		if ((match = re.exec(data.response)) !== null){
			var ext_temp   = match[1];
			var ext_target = match[2];
			var bed_temp   = match[3];
			var bed_target = match[4];
			//update top bar
			$("#top-bar-nozzle-actual").html(parseInt(ext_temp));
			$("#top-bar-nozzle-target").html(parseInt(ext_target));
			$("#top-bar-bed-actual").html(parseInt(bed_temp));
			$("#top-bar-bed-target").html(parseInt(bed_target));
			//top bar sliders
			document.getElementById('top-act-bed-temp').noUiSlider.set([parseInt(bed_temp)]);
			document.getElementById('top-bed-target-temp').noUiSlider.set([parseInt(bed_target)]);
			if($("#top-act-ext-temp").length > 0){
				document.getElementById('top-act-ext-temp').noUiSlider.set([parseInt(ext_temp)]);
				document.getElementById('top-ext-target-temp').noUiSlider.set([parseInt(ext_target)]);
			}
			//save to browser storage
			if ( typeof (Storage) !== "undefined") {
				localStorage.setItem("nozzle_temp", ext_temp);
				localStorage.setItem("nozzle_temp_target", ext_target);
				localStorage.setItem("bed_temp", bed_temp);
				localStorage.setItem("bed_temp_target", bed_target);
			}
			//if module is jog
			if($.module == "jog"){
				if($("#act-ext-temp").length > 0){
					$("#ext-actual-degrees").html(parseInt(ext_temp) + '&deg;C');
					document.getElementById('act-ext-temp').noUiSlider.set([parseInt(ext_temp)]);
					document.getElementById('ext-target-temp').noUiSlider.set([parseInt(ext_target)]);
				}
				$("#bed-actual-degrees").html(parseInt(bed_temp) + '&deg;C');
				document.getElementById('act-bed-temp').noUiSlider.set([parseInt(bed_temp)]);
				document.getElementById('bed-target-temp').noUiSlider.set([parseInt(bed_target)]);
			}
			
		}
	};
	/*
	 * write serial replys to jog console
	 */
	app.writeSerialResponseToConsole = function(data){
			var commands = data.command.split('\n');
			var replys   = data.response.split('\n');
			for(var i=0; i<commands.length; i++){
				if(commands.length > 1) $.console.append(commands[i] + ' : ' +  replys[i] + '\n');
				else{
					if(replys.length > 2){ // for commands like M763, M765
						$.console.append(commands[i] + ' : ' +  replys[0] + '\n');					
						for(var j=1;j<replys.length;j++){
							if(replys[j] != '\n' && replys[j] != '') $.console.append(replys[j] + '\n');
						}	
					} else $.console.append(commands[i] + ' : ' +  replys[i] + '\n'); //for commands that have a lot of replys like M503
				}
			}
			$.console.append('\n').scrollTop(1E10);
	};
	/*
	 * manage macro response or trace
	 */
	app.manageMacro = function(data){
		switch(data.type){
			case 'trace':
				$.console.html(data.content).scrollTop(1E10);
				waitContent(data.content); //display also on wait modal popup
				break;
			case 'response':
				if(data.content == true) $.is_macro_on = false;
				break;
			case 'status':
				break;
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
			app.stopAll('Front panel has been opened.<br> Aborting all operations');
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
			case 'trace':
				$.console.html(data.content).scrollTop(1E10);
				waitContent(data.content);
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
		if (typeof manage_task_monitor == 'function') manage_task_monitor(data);
	};
	/*
	 * read temperatures
	 */
	app.getTemperatures = function(){
		if(socket_connected && (is_macro_on == false && is_task_on == false && is_emergency == false)) app.serial('getTemperatures', '');
	}
	/*
	 * send command to the serial port
	 */
	app.serial = function(func, val) {
		
		
		var xyStep       = $(".xyStep").length > 0 ? $(".xyStep").val() : 10;
		var zStep        = $(".zStep").length > 0 ?  $(".zStep").val() : 5;
		var xyzFeed      = $(".xyzFeed").length > 0 ? $(".xyzFeed").val() : 1000;
		var extruderFeed = $("#extruder-feedrate").length > 0 ? $("#extruder-feedrate").val() : 300;
		
		var data = {
			'method'           : func,
			'value'            : val,
			'step'             : {'xy':  xyStep, 'z':zStep},
			'feedrate'         : {'xyz': xyzFeed, 'extruder':extruderFeed}
		};
		
		
		console.log(data);
		
		if(socket_connected == true){
			var messageToSend = {
				'function' : 'serial',
				'params' : data
			};
			socket.send('message', JSON.stringify(messageToSend));
		}else{
			$.ajax({
				type: "POST",
				url: serial_exec_url_action,
				data: data,
				dataType: 'json'
			}).done(function( data ) {
				app.writeSerialResponseToConsole(data.data);
			});
		}
	};
	/*
	 * check if internet connection is available
	 */
	app.isInternetAvailable = function(){
		$.get(check_internet_url_action + '?' + jQuery.now(), function(data){
			app.showConnected(data == 1);
		});
	};
	/*
	 * show or hide connected icon
	 */
	app.showConnected = function(available) {
		if(available)$(".lock-ribbon").before('<span class="ribbon-button-alignment internet animated bounceIn" ><span class="btn btn-ribbon "  rel="tooltip" data-placement="right" data-original-title="Connected to internet" data-html="true"><i class="fa fa-globe "></i></span></span>');
		else $(".internet").remove();
		$("[rel=tooltip], [data-rel=tooltip]").tooltip();
	};
	/*
	 * manage sockets messages having system type
	 */
	app.manageSystem = function(data){
		switch(data.type){
			case 'usb':
				app.usb(data.status, data.alert);
				break;
			case 'lock':
				break;
		}
	};
	/*
	 * notify when usb disk is inserted or removed
	 */
	app.usb = function (inserted, notify){
		if(inserted && $(".usb-ribbon").length == 0) $(".breadcrumb").before('<span class="ribbon-button-alignment usb-ribbon animated bounceIn" ><span class="btn btn-ribbon "  rel="tooltip" data-placement="right" data-original-title="USB disk inserted" data-html="true"><i class="fa fa-usb "></i></span></span>');
		else if(inserted == false) $(".usb-ribbon").remove();
		$("[rel=tooltip], [data-rel=tooltip]").tooltip();
		if(notify == true){
			var message = 'USB Disk';
			message += inserted == true ? ' inserted' : ' removed';
			$.smallBox({
				title : "FABtotum Personal Fabricator",
				content : message,
				color : "#296191",
				timeout : 3000,
				icon : "fa fa-usb"
			});
		}
	};
	/*
	 * things to do when socket is connected
	 */
	app.afterSocketConnect = function(){
		if(socket_connected == true){
			socket.send('message', '{"function": "getTasks"}'); //check for tasks
			socket.send('message', '{"function": "usbInserted"}');   //check for if usb disk is connected
		}
	}
	return app;
})({});

