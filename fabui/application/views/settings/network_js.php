<script type="text/javascript">

	var wifiSelected;
	var wifiIface;
	var wifiPassword;
	var currentEthIPV4;

	$(function () 
	{
		$("#saveButton").on('click', do_save);
		$(".ip").inputmask();
		
		$(".address-mode").on('change', address_mode_change);
		$(".show-password").on('change', show_password);
		
		<?php 
			foreach($interfaces as $iface => $value)
			{
				if($value['do_scan'])
				{
					echo 'scan("'.$iface.'");'.PHP_EOL;
				}
			}
		?>

		$("#scanButton").on('click', do_scan);
		$("#modalConnectButton").on('click', passwordModalConnect);
		
		$('a[data-toggle="tab"]').on('shown.bs.tab', tab_change);
		initFieldValidator();
		triggerPreSelected();
		initEthCurrentIPV4();
	});

	function do_save()
	{
		var tab = $("li.tab.active").attr('data-attribute');
		var net_type = $("li.tab.active").attr('data-net-type');
		
		var data = {};
		$("#"+tab+"-tab :input").each(function (index, value) {
			if($(this).is('input:text') || $(this).is('input:password') || $(this).is('select') || $(this).is(':input[type="number"]') || ($(this).is('input:radio') && $(this).is(':checked')) ){
				data[$(this).attr('id')] = $(this).val();
			}
		});
		
		if(tab == "dnssd")
		{
			net_type = "dnssd";
		}
		
		data['active'] = tab;
		data['net_type'] = net_type;
		
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
		//return false;
		post_data(data);
	}

	function save_wifi(iface, data)
	{
		if( !$("#"+iface+"-tab .addressForm").valid() || !$("#"+iface+"-tab .apForm").valid() ){
			return;
		}
		
		if( data['address-mode'] == 'static-ap' )
		{
			post_data(data);
		}
		else
		{
			if( (data['hidden-passphrase'] != '' || data['hidden-psk'] != '') && data['hidden-ssid'] != '')
			{
				post_data(data);
			}
			else
			{
				$.smallBox({
					title : "<?php echo _('Warning')?>",
					content : '<?php echo _('You need to connect to a network first')?>',
					color : "#C46A69",
					timeout: 10000,
					icon : "fa fa-check bounce animated"
				});
			}
		}
	}

	function save_dnssd(data)
	{
		post_data(data);
	}

	function post_data(data)
	{
		var button = $("#saveButton");
		disableButton("#saveButton");
		button.addClass('disabled');
		button.html('<i class="fa fa-save"></i> <?php echo _('Saving')?>..');

		if(data.hasOwnProperty('hidden-ssid') && data['net_type'] == 'wlan'){
			openWait("<i class='fa fa-spin fa-spinner'></i> <?php echo _("Connecting to") ?> <strong>" + data['hidden-ssid']+'</strong> <i class="fa fa-wifi"></i>', "<?php echo _("Please wait") ?>...", false );
		}else if(data['net_type'] == 'eth' && data['address-mode'] == 'static'){

			openWait("<i class='fa fa-spin fa-spinner'></i> <?php echo _("Saving new ethernet configuration") ?>", "<?php echo _("Please wait") ?>...", false );
			/** 
			* if static ip address change
			**/
			if(currentEthIPV4 != data['ipv4']){
				
				setTimeout(function(){
					waitContent("<?php echo _("Redirect to new address") ?>...");
					fabApp.redirectToUrlWhenisReady('http://'+ data['ipv4'] + '/fabui/#settings/network');
				}, 3000);
			}			
		}
		
		$.ajax({
			type: 'post',
			url: "<?php echo site_url('settings/saveNetworkSettings/connect'); ?>",
			data : data,
			dataType: 'json',
			async: true,
			timout: 20000, //timeout 1 minute,
			error: function(request, status, err) {
				console.log("ERROR");
			}
		}).done(function(response) {
			button.html('<i class="fa fa-save"></i> <?php echo _('Save')?>');
			button.removeClass('disabled');
			/*
			$("#"+data['active']+"-tab #hidden-address-mode").val(data['address-mode']);
			
			$.smallBox({
				title : "<?php echo _('Settings')?>",
				content : '<?php echo _('Network settings saved')?>',
				color : "#5384AF",
				timeout: 1000,
				icon : "fa fa-check bounce animated"
			});
			*/
			/**
			* if wlan
			**/
			//if(data['net_type'] == 'wlan'){
				waitContent("<?php echo _("Reloading page") ?>...");
				fabApp.getNetworkInfo();
				setTimeout(function(){
						if(window.location.href ==  ('<?php echo site_url('#settings/network/') ?>/' + data['active'])){
							location.reload(); 
						}else{
							window.location.href = '<?php echo site_url('#settings/network/') ?>/' + data['active'];
						}
					}, 5000
				);
			//}
						
		}).fail(function(jqXHR, textStatus){
			console.log("FAIL");
		});
	}

	function tab_change(e)
	{
		var target = $(e.target).attr("href");
		
		if( target.startsWith("#wlan") )
		{
			var mode = $(target + ' #address-mode').val();
			if(mode != 'static-ap')
				$("#scanButton").show();
			else
				$("#scanButton").hide();
		}
		else if( target.startsWith("#eth") )
		{
			$("#scanButton").hide();
		}
		else if( target.startsWith("#dnssd") )
		{
			console.log('ddns', target);
			$("#scanButton").hide();
		}
	}

	function address_mode_change()
	{
		var iface = $(this).attr('data-attribute');
		var mode = $("#"+iface+"-tab #address-mode option:selected").val();
		
		switch(mode)
		{
			case "dhcp":
				$("#"+iface+"-tab #address-container").slideUp('slow');
				$("#"+iface+"-tab #ap-container").slideUp('slow');
				$("#"+iface+"-tab #gateway-container").slideUp('slow');
				$("#"+iface+"-table-container").slideDown('slow');
				$("#"+iface+"-tab #dhcp-address-container").slideDown('slow');
				if(iface.startsWith('wlan'))
					$("#scanButton").show();
				else
					$("#scanButton").hide();
				break;
			case "static":
				$("#"+iface+"-tab #address-container").slideDown('slow');
				$("#"+iface+"-tab #ap-container").slideUp('slow');
				$("#"+iface+"-tab #gateway-container").slideDown('slow');
				$("#"+iface+"-table-container").slideDown('slow');
				$("#"+iface+"-tab #dhcp-address-container").slideUp('slow');
				if(iface.startsWith('wlan'))
					$("#scanButton").show();
				else
					$("#scanButton").hide();
				break;
			case "static-ap":
				$("#"+iface+"-tab #address-container").slideDown('slow');
				$("#"+iface+"-tab #ap-container").slideDown('slow');
				$("#"+iface+"-tab #gateway-container").slideUp('slow');
				$("#"+iface+"-tab #dhcp-address-container").slideUp('slow');
				$("#"+iface+"-table-container").slideUp('slow');
				$("#scanButton").hide();
				break;
		}
	}

	function show_password()
	{
		var attr = $(this).attr('data-attribute');
		var obj = (attr != "modal")?$('#'+attr+'-tab .password'):$('#passwordModal #wifiPassword');
		if( $(this).is( ":checked" ) )
			obj.attr('type', 'text');
		else
			obj.attr('type', 'password');
	}

	function do_scan()
	{
		var iface = $("li.tab.active").attr('data-attribute');
		scan(iface);
	}

	/**
	 * scan wifi networks
	 */
	function scan(iface)
	{
		$("#"+iface+"-table-container").css('opacity', '0.1');
		$("#scanButton").html('<i class="fa fa-search"></i> <?php echo _("Scanning") ?>..');
		disableButton("#scanButton");
		$.ajax({
			type: 'get',
			url: "<?php echo site_url('settings/scanWifi'); ?>/"+iface,
			dataType: 'json'
		}).done(function(response) {
			console.log('scan results', response);
			if(response)
				buildTable(iface, response);
			else
				console.log('no scan results');
			$("#"+iface+"-table-container").css('opacity', '1');
			$("#scanButton").html('<i class="fa fa-search"></i> <?php echo _("Scan") ?>');
			enableButton("#scanButton");
		});
	}
	
	/**
	 *  build nets table
	 */
	function buildTable(iface, nets)
	{
		
		$("#"+iface+"-table-container .nets-table").remove();
		var bssid = $("#"+iface+"-tab #hidden-bssid").val().toLowerCase();
		var table = '<table class="table table-striped table-forum nets-table"><tbody>';
		$.each(nets, function( index, net ) {
			
			var protected = net.encryption_key == 'on' ? 'Protected <i class="fa fa-lock"></i>' : '';
			var frequency = net.frequency != '' ? net.frequency : '';
			var buttonAttributeProtected = net.encryption_key == 'on' ? 'true' : 'false';
			var buttonAttributeAction = bssid == net.address ? 'disconnect' : 'connect';
			var buttonLabel =  bssid == net.address ? '<?php echo _('Disconnect')?>' : '<?php echo _('Connect')?>';
			var trClass = bssid == net.address ? 'warning' : '';
						
			table += '<tr class="'+ trClass+'">';
			table += '<td class="text-center va-middle" style="width: 40px;"><i class="icon-communication-035 fa-2x text-muted"></i></td>';
			table += '<td>';
			table += '<p><a data-attribute-essid="'+net.essid+'"  data-attribute-iface="'+iface+'" data-attribute-action="'+buttonAttributeAction+'" data-attribute-protected="'+buttonAttributeProtected+'"   class="font-md connect" href="javascript:void(0);">'+net.essid+'</a><span class="hidden-xs pull-right">Signal level: '+Math.round(net.quality) +'/100</span></p>';
			table += '<div class="hidden-xs progress progress-sm progress-striped active"><div class="progress-bar  bg-color-blue" aria-valuetransitiongoal="'+ net.quality +'" style="width:'+net.quality+'%"></div></div>';
			table += '<small class="hidden-xs note">'+ protected  + ' / ' + net.mode + ' / ' + frequency + ' / ' + net.encryption +' </small>';
			table += '</td>';
			table += '<td class="hidden-xs va-middle" style="width: 100px" class="text-right va-middle"><button data-attribute-essid="'+net.essid+'" data-attribute-iface="'+iface+'" data-attribute-action="'+buttonAttributeAction+'" data-attribute-protected="'+buttonAttributeProtected+'" class="btn btn-default btn-sm btn-block  connect">'+buttonLabel+'</button></td></td>';
			table += '</tr>';
		});
		table += '</tbody></table>';
		$("#"+iface+"-table-container").html(table);
		$('.progress-bar').progressbar({display_text : 'fill' });
		$('.connect').on('click', connectionManager);
	}
	
	function connectionManager()
	{
		var element   = $(this);
		console.log(element);
		wifiSelected  = element.attr('data-attribute-essid');
		wifiIface  	  = element.attr('data-attribute-iface');
		var action    = element.attr('data-attribute-action');
		var protected = eval(element.attr('data-attribute-protected'));
		
		if(action == 'connect') connectToWifi(wifiIface, wifiSelected, protected);
		else askForDisconnectFromWifi(wifiSelected, wifiIface);
	}
	
	function connectToWifi(iface, essid, isProtected)
	{
		if(isProtected){
			showPasswordModal(essid);
		}else{
			sendActionRequest('connect', essid);
		}
	}
	/**
	* ask confirm to disconnect from wifi
	**/
	function askForDisconnectFromWifi(wifiSelected, wifiIface)
	{
		$.SmartMessageBox({
            title : '<i class="fa fa-wifi"></i> ' + wifiSelected,
            content : "<?php echo _('Are you sure you want to disconnect?')?>",
            buttons: "[<?php echo _("No") ?>][<?php echo _("Yes") ?>]"
        }, function(ButtonPressed) {
           if(ButtonPressed == '<?php echo _("Yes") ?>') disconnectFromWifi(wifiIface);
       });
	}
	/**
	*
	*/
	function disconnectFromWifi(iface)
	{
		var complete_url = '<?php echo site_url('#settings/network/') ?>/' + iface;
		
		openWait("<i class='fa fa-spin fa-spinner'></i> <?php echo _("Disconnecting") ?>... ", "<?php echo _("Please wait") ?>...", false );	
		$.ajax({
			type: 'post',
			url: "<?php echo site_url('settings/saveNetworkSettings/disconnect'); ?>",
			data : {
				active : iface,
				net_type : "wlan"
			},
			dataType: 'json'
		}).done(function(response) {
			waitContent("<?php echo _("Reloading page") ?>...");
			fabApp.getNetworkInfo();
			setTimeout(function(){
					if(window.location.href == complete_url){
						location.reload();
					}else{
						window.location.href = complete_url;
					}
				}, 5000
			);
			
		});
	}
	
	/**
	 * show modal form to insert password if is requested
	 */
	function showPasswordModal(essid)
	{	
		resetForms();
		$("#passwordModalTitle").html('<?php echo _('Password for')?> <strong>' + essid + '</strong>');
		$('#passwordModal').modal({});
	}
	/**
	 * called from "connect" button on password modal
	 */
	function passwordModalConnect()
	{
		if($("#passwordModalForm").valid()){
			$("#"+wifiIface+"-tab #hidden-ssid").val(wifiSelected);
			$("#"+wifiIface+"-tab #hidden-passphrase").val($("#wifiPassword").val());
			$("#"+wifiIface+"-tab #hidden-psk").val('');
			do_save();
			$('#passwordModal').modal('hide');
			//sendActionRequest('connect', wifiIface, wifiSelected, $("#wifiPassword").val());
		}
	}
	
	function initFieldValidator()
	{
		jQuery.validator.addMethod("isIPaddress", function(value, element, param) {
			if(!param)
				return true;
			else
			{
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
		}, "<?php echo _('Please specify a valid IP address')?>");
		
		$("#passwordModalForm").validate({
			rules:{
				wifiPassword:{
					required: true
				}
			},
			messages: {
				wifiPassword: {
					required: '<?php echo _('Please insert valid password')?>'
				}
			},
			errorPlacement : function(error, element) {
				error.insertAfter(element.parent());
			}
		});
		
		$(".addressForm").each(function( index ) {
		
			$(this).validate({
				onfocusout: function (element) {},
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
						isIPaddress : '<?php echo _('Please insert a valid IP address')?>'
					},
					netmask : {
						isIPaddress : '<?php echo _('Please insert a valid netmask')?>'
					},
					gateway : {
						isIPaddress : '<?php echo _('Please insert a valid gateway address')?>'
					},
				},
				errorPlacement : function(error, element) {
					error.insertAfter(element.parent());
				}
			});
		
		});
		
		$(".apForm").each(function( index ) {
			$(this).validate({
				onfocusout: function (element) {},
				rules : {
					ssid : {
						required : true,
						minlength : 4,
						maxlength : 63
					},
					password : {
						required : true,
						minlength : 8,
						maxlength : 63
					},
				},
				messages : {
					ssid : {
						required : '<?php echo _('Please specify an SSID')?>',
						minlength : '<?php echo _('Please specify an SSID that is between 4 and 63 characters')?>',
						maxlength : '<?php echo _('Please specify an SSID that is between 4 and 63 characters')?>'
					},
					password : {
						required : '<?php echo _('Please specify a password')?>',
						minlength : '<?php echo _('Please specify a password between 8 and 63 characters')?>',
						maxlength : '<?php echo _('Please specify a password between 8 and 63 characters')?>'
					},
				},
				errorPlacement : function(error, element) {
					error.insertAfter(element.parent());
				}
			});
		});
		
		$("#wifiPassword").inputmask("Regex");
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
	**/
	function triggerPreSelected()
	{
		<?php if($preSelectedInterface != ''): ?>

		var target = '#<?php echo $preSelectedInterface ?>';

		if( target.startsWith("#wlan") )
		{
			var mode = $(target + ' #address-mode').val();
			if(mode != 'static-ap')
				$("#scanButton").show();
			else
				$("#scanButton").hide();
		}
		else if( target.startsWith("#eth") )
		{
			$("#scanButton").hide();
		}
		else if( target.startsWith("#dnssd") )
		{
			$("#scanButton").hide();
		}
		
		<?php endif; ?>
	}
	/**
	* initCurrentIPV4
	**/
	function initEthCurrentIPV4()
	{
		currentEthIPV4 = $("#ipv4").val();
		console.log(currentEthIPV4);
	}
</script>
