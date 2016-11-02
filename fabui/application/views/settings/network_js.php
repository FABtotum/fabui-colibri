<script type="text/javascript">

	//~ var apMacAddress = '<?php echo isset($wlanInfo['ap_mac_address']) ? $wlanInfo['ap_mac_address'] : '' ?>';
	var wifiSelected;
	var wifiIface;
	var wifiPassword;

	$(function () {
		
		$("#save").on('click', save);
		$(".ip").inputmask();
		
		$(".address-mode").on('change', address_mode_change);
		$(".show-password").on('change', show_password);
		
		scan('wlan0');
		$("#scanButton").on('click', scan);
		$("#modalConnectButton").on('click', passwordModalConnect);
		
		initFieldValidator();
	});

	function save()
	{
		console.log('save');
		var tab = $("li.tab.active").attr('data-attribute');
		var net_type = $("li.tab.active").attr('data-net-type');
		
		var data = {};
		$("#"+tab+"-tab :input").each(function (index, value) {
			if($(this).is('input:text') || $(this).is('select') || $(this).is(':input[type="number"]') || ($(this).is('input:radio') && $(this).is(':checked')) ){
				data[$(this).attr('id')] = $(this).val();
			}
		});
		
		if(tab == "dnssd")
		{
			net_type = "dnssd";
		}
		
		data['active'] = tab;
		data['active-type'] = net_type;
		
		switch(net_type)
		{
			case "eth":
				save_ethernet(tab, data);
				break;
			case "wlan":
				save_wifi(tab, data);
				break;
			default:
				save_dnssd(data)
				break;
		}
	}

	function save_ethernet(iface, data)
	{
		if( !$("#"+iface+"-tab .addressForm").valid() ){
			return;
		}
		
		console.log('save eth', iface);
		console.log(data);
		post_data(data);
	}

	function save_wifi(iface, data)
	{
		if( !$("#"+iface+"-tab .addressForm").valid() || !$("#"+iface+"-tab .apForm").valid() ){
			return;
		}
		
		console.log('save wifi', iface); 
		console.log(data);
		post_data(data);
	}

	function save_dnssd(data)
	{
		console.log('save dnssd');
		console.log(data);
		post_data(data);
	}

	function post_data(data)
	{
		var button = $("#save");
		button.addClass('disabled');
		button.html('<i class="fa fa-save"></i> Saving..');
		
		console.log('trying to post done');
		$.ajax({
			type: 'post',
			url: '<?php echo 'settings/saveNetworkSettings'; ?>',
			data : data,
			dataType: 'json'
		}).done(function(response) {
			button.html('<i class="fa fa-save"></i> Save');
			button.removeClass('disabled');
			console.log('post done!!!');
			
			$.smallBox({
				title : "Settings",
				content : 'Network settings saved',
				color : "#5384AF",
				timeout: 3000,
				icon : "fa fa-check bounce animated"
			});
			
		});
	}

	function validate_ip(iface)
	{
		var result = true;
		var mode = $("#"+iface+"-address-mode option:selected").val();
		
		$(".tab-content :input[id^="+iface+"-].ip").each(function (index, value) {
			var id = $(this).attr('id');
			console.log(id);
			
			if( !(mode == 'static-ap' && id == iface+"-gateway") && mode != 'dhcp')
			{
			
				var ip = $(this).val();
				ip = ip.split('.');
				
				$(this).removeClass('danger');
				
				for(i=0; i<4; i++)
				{
					if( !$.isNumeric(ip[i]) )
					{
						result = false;
						$(this).addClass('error');
							$.smallBox({
								title : "Warning",
								content : "Invalid IP address",
								color : "#C46A69",
								timeout: 10000,
								icon : "fa fa-warning"
							});
							
						return false;
					}
				}
			}
		});
		
		return result;
	}

	function address_mode_change()
	{
		var iface = $(this).attr('data-attribute');
		var mode = $("#"+iface+"-address-mode option:selected").val();
		console.log('address mode changed', iface, mode);
		switch(mode)
		{
			case "dhcp":
				$("#"+iface+"-tab #address-container").slideUp('slow');
				$("#"+iface+"-tab #ap-container").slideUp('slow');
				$("#"+iface+"-tab #gateway-container").slideUp('slow');
				$("#"+iface+"-table-container").slideDown('slow');
				break;
			case "static":
				$("#"+iface+"-tab #address-container").slideDown('slow');
				$("#"+iface+"-tab #ap-container").slideUp('slow');
				$("#"+iface+"-tab #gateway-container").slideDown('slow');
				$("#"+iface+"-table-container").slideDown('slow');
				break;
			case "static-ap":
				$("#"+iface+"-tab #address-container").slideDown('slow');
				$("#"+iface+"-tab #ap-container").slideDown('slow');
				$("#"+iface+"-tab #gateway-container").slideUp('slow');
				$("#"+iface+"-table-container").slideUp('slow');
				break;
		}
	}

	function show_password()
	{
		var attr = $(this).attr('data-attribute');
		var obj = (attr != "modal")?$('#'+attr+'-tab #password'):$('#passwordModal #wifiPassword');
		if( $(this).is( ":checked" ) )
			obj.attr('type', 'text');
		else
			obj.attr('type', 'password');
	}

	/**
	 * scan wifi networks
	 */
	function scan(iface)
	{
		$("#"+iface+"-table-container").css('opacity', '0.1');
		$.ajax({
			type: 'get',
			url: 'settings/scanWifi/'+iface,
			dataType: 'json'
		}).done(function(response) {
			buildTable(iface, response);
			$("#"+iface+"-table-container").css('opacity', '1');
		});
		
	}
	
	/**
	 *  build nets table
	 */
	function buildTable(iface, nets)
	{
		console.log(nets);
		$(".nets-table").remove();
		var table = '<table class="table table-striped table-forum nets-table"><tbody>';
		$.each(nets, function( index, net ) {
			var protected = net.encryption_key == 'on' ? 'Protected <i class="fa fa-lock"></i>' : '';
			var frequency = net.frequency != '' ? net.frequency : '';
			var buttonAttributeProtected = net.encryption_key == 'on' ? 'true' : 'false';
			//~ var buttonAttributeAction = apMacAddress == net.address ? 'disconnect' : 'connect';
			var buttonAttributeAction = 'connect';
			//~ var buttonLabel =  apMacAddress == net.address ? 'Disconnect' : 'Connect';
			var buttonLabel =  'Connect';
			//~ var trClass = apMacAddress == net.address ? 'warning' : '';
			var trClass = '';
			
			table += '<tr class="'+ trClass+'">';
			table += '<td class="text-center va-middle" style="width: 40px;"><i class="icon-communication-035 fa-2x text-muted"></i></td>';
			table += '<td>';
			table += '<p>'+net.essid+'<span class="hidden-xs pull-right">Signal level: '+Math.round(net.quality) +'/100</span></p>';
			table += '<div class="hidden-xs progress progress-sm progress-striped active"><div class="progress-bar  bg-color-blue" aria-valuetransitiongoal="'+ net.quality +'" style="width:'+net.quality+'%"></div></div>';
			table += '<small class="hidden-xs note">'+ protected  + ' / ' + net.mode + ' / ' + frequency + ' / ' + net.encryption +' </small>';
			table += '</td>';
			table += '<td style="width: 100px" class="text-right va-middle"><button data-attribute-essid="'+net.essid+'" data-attribute-iface="'+iface+'" data-attribute-action="'+buttonAttributeAction+'" data-attribute-protected="'+buttonAttributeProtected+'" class="btn btn-default btn-sm btn-block connect">'+buttonLabel+'</button></td></td>';
			table += '</tr>';
		});
		table += '</tbody></table>';
		$("#wlan0-table-container").html(table);
		$('.progress-bar').progressbar({display_text : 'fill'
			});
		$('.connect').on('click', connectionManager);
	}
	
	/**
	 * 
	 */
	function showDetails()
	{
		var button = $(this);
		var iface = $(this).attr('data-attribute');
		var isVisible = $('.wifi-details').is(":visible");
		if(isVisible){
			$('.wifi-details').slideUp(function(){
				button.find('i').removeClass('fa-angle-double-up').addClass('fa-angle-double-down');
			});
		}else{
			$('.wifi-details').slideDown(function(){
				button.find('i').removeClass('fa-angle-double-down').addClass('fa-angle-double-up');
			});
		}
	}
	
	function connectionManager()
	{
		var element   = $(this);
		wifiSelected  = element.attr('data-attribute-essid');
		wifiIface  	  = element.attr('data-attribute-iface');
		var action    = element.attr('data-attribute-action');
		var protected = eval(element.attr('data-attribute-protected'));
		
		if(action == 'connect') connectToWifi(wifiIface, wifiSelected, protected);
		else disconnectFromWifi(iface);
	}
	
	function connectToWifi(iface, essid, isProtected)
	{
		console.log('connect to ', essid, isProtected, ' @', iface);
		if(isProtected){
			showPasswordModal(essid);
		}else{
			sendActionRequest('connect', essid);
		}
	}
	
	function disconnectFromWifi(iface)
	{
		console.log('disconnect @', iface);
	}
	
	/**
	 * show modal form to insert password if is requested
	 */
	function showPasswordModal(essid)
	{	
		resetForms();
		$("#passwordModalTitle").html('Password for <strong>' + essid + '</strong>');
		$('#passwordModal').modal({});
	}
	/**
	 * called from "connect" button on password modal
	 */
	function passwordModalConnect()
	{
		if($("#passwordModalForm").valid()){
			sendActionRequest('connect', wifiIface, wifiSelected, $("#wifiPassword").val());
		}
	}

	
	function initFieldValidator()
	{
		jQuery.validator.addMethod("isIPaddress", function(value, element, param) {
			if(!param)
				return true;
			else
			{
				console.log('validate ip', element, value)
				var ip = value;
				ip = ip.split('.');
				
				for(i=0; i<4; i++)
				{
					if( !$.isNumeric(ip[i]) )
					{
						return false;
					}
				}
				
				return true;
				
			}
		}, "Please specify a valid IP address");
		
		$("#passwordModalForm").validate({
			rules:{
				wifiPassword:{
					required: true
				}
			},
			messages: {
				wifiPassword: {
					required: 'Please insert valid password'
				}
			},
			errorPlacement : function(error, element) {
				error.insertAfter(element.parent());
			}
		});
		
		$(".addressForm").each(function( index ) {
		
			$(this).validate({
				rules : {
					ipv4 : {
						isIPaddress : true
					},
					netmask : {
						isIPaddress : true
					},
					gateway : {
						isIPaddress : true,
					}
				},
				messages : {
					ipv4 : {
						isIPaddress : 'Please insert a valid IP address'
					},
					netmask : {
						isIPaddress : 'Please insert a valid netmask'
					},
					gateway : {
						isIPaddress : 'Please insert a valid gateway address'
					},
				},
				errorPlacement : function(error, element) {
					error.insertAfter(element.parent());
				}
			});
		
		});
		
		$(".apForm").each(function( index ) {
			$(this).validate({
				rules : {
					ssid : {
						required : true
					},
					password : {
						required : true
					},
				},
				messages : {
					ssid : {
						required : 'Please specify an SSID'
					},
					password : {
						required : 'Please specify a password'
					},
				},
				errorPlacement : function(error, element) {
					error.insertAfter(element.parent());
				}
			});
		});
	}

	/**
	 * 
	 */
	function resetForms()
	{	
		$("#wifiPassword").val('');
		$(".show-password").attr('checked', false);
		$(".input-password").attr('type', 'password');
	}
	
	/**
	 * 
	 */
	function sendActionRequest(action, iface, essid, password)
	{
		$('#passwordModal').modal('hide');
		//~ var connectionLabel = action == 'connect' ? 'Connecting to ' : 'Disconnecting from ';
		//~ openWait('<i class="fa fa-circle-o-notch fa-spin"></i> '+ connectionLabel + ' ' + essid);
		//~ essid = essid || '';
		//~ password = password || '';
		
		//~ $.ajax({
			//~ type: 'post',
			//~ url: '<?php echo site_url('settings/wifiAction'); ?>/' + action,
			//~ data: {essid:essid, password: password},
			//~ dataType: 'json'
		//~ }).done(function(response) {
			//~ waitContent('Refreshing page');
			//~ setTimeout(function() {
				//~ location.reload();
			//~ }, 3000);
		//~ });
	}
</script>
