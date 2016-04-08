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
	 * 
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
	app.checkForFirstSetupWizard = function(){
		$.get('/fabui/controller/first_setup', function(data, status){
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
		RESETTING_CONTROLLER = true;
		openWait("<i class=\"fa fa-circle-o-notch fa-spin\"></i> Resetting controller");
		$.get($.reset_controller_url_action, function(){
			closeWait();
			RESETTING_CONTROLLER = false;
		});
	}
	/*
	 * stop all operations and task on the fabtotum and refresh the page after 3 seconds
	 */
	app.stopAll = function(message) {
		message = message || 'Aborting all operations ';
		openWait(message, ' ', false);
		STOPPING_ALL = true;
		$.get($.stop_all_url_action, function(){
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
		IS_MACRO_ON = true;
		openWait("<i class='fa fa-circle-o-notch fa-spin'></i> Restart in progress");
		$.ajax({
			url: $.reboot_url_action,
		}).done(function(data) {
		
		}).fail(function(jqXHR, textStatus){
			setTimeout(function() {
				waitContent("Restarting please wait...");
				IS_MACRO_ON = false;
				document.location.href = $.logout_url;
			}, 21000);
		});
	};
	/*
	 * launch poweroff command and show popup with instructions after 5 seconds
	 */
	app.poweroff = function() {
		IS_MACRO_ON = true;
		openWait('<i class="fa fa-circle-o-notch fa-spin"></i> Shutdown in progress');
		$.ajax({
			url: $.poweroff_url_action,
		}).done(function(data) {
			
		}).fail(function(jqXHR, textStatus){
			setTimeout(function() {
				waitTitle('Now you can switch off the power');
				showShutdownImage(); //utility function stored in utilities.js
				closeWait();
				IS_MACRO_ON = false;
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
				var html = '<div class="row"><div class="col-sm-12"><div class="alert alert-danger alert-block animated fadeIn"><button class="close" data-dismiss="alert">×</button><h4 class="alert-heading"> <i class="fa fa-refresh"></i> New important software updates are now available, <a style="text-decoration:underline; color:white;" href="/fabui/updates">update now!</a> </h4></div></div></div>';
				if($.module != 'updates') $("#content").prepend(html);
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
	 * 
	 */
	app.webSocket = function () {
		
		function isWebSocketAvailable(){
			return ("WebSocket" in window);
		}
		
		function onOpen(){
			$.socket_connected = true;
		}
		
		function onClose(){
			$.socket_connected = false;
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
					default:
						break;
				}
			}catch(e){
				return;
			}
		}
		
		if(isWebSocketAvailable()){
			$.socket = new FabWebSocket($.socket_host, $.socket_port);
			$.socket.bind('message', onMessage);
			$.socket.bind('open', onOpen);
			$.socket.bind('close', onClose);
			$.socket.connect();
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
	 * 
	 */
	app.writeSerialResponseToConsole = function(data){
			console.log(data);
			var commands = data.command.split('\n');
			var replys   = data.response.split('\n');
			
			for(var i=0; i<commands.length; i++){
				$(".console").append(commands[i] + ' : ' +  replys[i] + '\n');
			}
			$(".console").append('<hr class="simple">');
			$('.console').scrollTop(1E10);
		
	};
	return app;

})({});

jQuery(document).ready(function() {
	//init
	fabApp.webSocket();
	fabApp.FabActions();
	fabApp.domReadyMisc();
	fabApp.drawBreadCrumb();
	fabApp.checkUpdates();
	fabApp.checkForFirstSetupWizard();
	//launch intervals
	$.notification_interval = setInterval(fabApp.checkNotifications, $.notification_interval_timer);
});