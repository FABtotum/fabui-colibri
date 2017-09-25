<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<script type="text/javascript">

	var $validator;
	
	$(document).ready(function() {
		runAllForms();
		initLanguage();
		initValidate();
		initWizard();
		initTimeZone();
		scanWifi();

		$("#wifi-scan").on("click", scanWifi);
		
		$("#i-agree").click(function(){
			$this=$("#terms");
			if($this.checked) {
				$('#termsConditionModal').modal('toggle');
			} else {
				$this.prop('checked', true);
				$('#termsConditionModal').modal('toggle');
			}
		});
		$("#language").on('change', setLocale);
		$(".show-password").on('change', show_password);
		$("#modalConnectButton").on('click', passwordModalConnect);

		$("#wifiPassword").keypress(function(e) {
			if(e.which == 13){
				passwordModalConnect();
			}
		});
		
		$("#fabidModalButton").on('click', openFABIDModal);
		$("#fabid-connect-button").on('click', fabIDConnect);
		
	});
	/**
	 * 
	 */
	function initValidate()
	{
		$validator = $("#install-form").validate({
		    
			rules: {
		    	email: {
		        	required: true,
		        	email: "<?php echo _("Your email address must be in the format of name@domain.com");?>"
		      	},
		      	first_name: {
		        	required: true
		      	},
		      	last_name: {
		        	required: true
		      	},
		      	password : {
					required : true,
					minlength : 3,
					maxlength : 20
				},
		      	confirmPassword : {
					required : true,
					minlength : 3,
					maxlength : 20,
					equalTo : '#password'
				},
				terms : {
					required : true
				}
		    },
		    messages: {
		    	first_name: "<?php echo _("Please enter your first name");?>",
		   		last_name: "<?php echo _("Please enter your lasts name");?>",
		      	email: {
		        	required: "<?php echo _("Please enter your email address");?>",
		        	email: "<?php echo _("Your email address must be in the format of name@domain.com");?>"
		      	},
		      	confirmPassword:{
		      		equalTo : '<?php echo _("Please enter the same password as above");?>'
		      	},
		      	terms : {
					required : '<?php echo _("You must agree with Terms and Conditions");?>'
				}
		    },
		    highlight: function (element) {
		   		$(element).closest('.form-group').removeClass('has-success').addClass('has-error');
		    },
		    unhighlight: function (element) {
		    	$(element).closest('.form-group').removeClass('has-error').addClass('has-success');
		    },
		    errorElement: 'span',
		    errorClass: 'help-block',
		    errorPlacement: function (error, element) {
		    	error.insertAfter(element.parent());
		    }
		});
		
		$("#printer-form").validate({
			rules:{
				serial_number:{
					required: true,
				}
			},
			messages: {
				serial_number: {
					required: '<?php echo _('Please insert serial number')?>',
				}
			},
			highlight: function (element) {
		   		$(element).closest('.form-group').removeClass('has-success').addClass('has-error');
		    },
		    unhighlight: function (element) {
		    	$(element).closest('.form-group').removeClass('has-error').addClass('has-success');
		    },
		    errorElement: 'span',
		    errorClass: 'help-block',
		    errorPlacement: function (error, element) {
		    	error.insertAfter(element.parent());
		    }
		});
		
		$("#passwordModalForm").validate({
			rules:{
				wifiPassword:{
					required: true,
					minlength: (function () {
					    return parseInt($("#wifiPasswordMinLength").val());
					})
				}
			},
			messages: {
				wifiPassword: {
					required: '<?php echo _('Please insert valid password')?>',
					minlength: jQuery.validator.format("<?php echo _('Please enter at least {0} characters')?>"),
				}
			},
			errorPlacement : function(error, element) {
				error.insertAfter(element.parent());
			}
		});

		$("#fabid-form").validate({
			// Rules for form validation
			rules : {
				fabid_email : {
					required : true,
					email : true
				},
				fabid_password : {
					required : true
				}
			},
			// Messages for form validation
			messages : {
				email : {
					required : "<?php echo _("Please enter your email address");?>",
					email : "<?php echo _("Please enter a valid email address") ?>"
				},
				fabid_password : {
					required : "<?php echo _("Please enter the password")?>"
				}
			},
			// Do not change code below
			errorPlacement : function(error, element) {
				error.insertAfter(element.parent());
			}
		});
		
	}
	/**
	 * 
	 */
	function initWizard()
	{
		$('#bootstrap-wizard-1').bootstrapWizard({
			'tabClass': 'form-wizard',
			'onNext': function (tab, navigation, index) {

				if(index == 2){
					var $valid = $("#install-form").valid();
					if (!$valid) {
						$validator.focusInvalid();
		      			return false;
					}
				}else if(index == 3){
					var $valid = $("#printer-form").valid();
					if (!$valid) {
						$validator.focusInvalid();
		      			return false;
					}
				}else{
					$('#bootstrap-wizard-1').find('.form-wizard').children('li').eq(index - 1).addClass('complete');
	        		$('#bootstrap-wizard-1').find('.form-wizard').children('li').eq(index - 1).find('.step').html('<i class="fa fa-check"></i>');
	        		handleStep(index);
				}
			},
			'onPrevious': function(tab, navigation, index){
				handleStep(index);
			},
			'onLast': function(tab, navigation, inde){
			},
			'onFinish': function(tab, navigation, inde){
			}
			
		});
	}

	function handleStep(step)
	{
		switch(step)
		{
			case 1:
				$(".next").find('a').html( "<?php echo _("Next") ?>" );
				break;
			case 2:
				//$(".next").find('a').html( "<?php echo _("Skip") ?>" );
				break;
    		case 3:
        		<?php if(count($steps) == 5): ?>
					//$(".next").find('a').html( "<?php echo _("Skip") ?>" );
					break;
				<?php else: ?>
					$(".next").find('a').attr('style', 'cursor: pointer !important;');
					$(".next").find('a').html( "<?php echo _("Install") ?>" );
					break;
				<?php endif; ?>
			case 4:
				<?php if(count($steps) == 5): ?>
					$(".next").find('a').attr('style', 'cursor: pointer !important;');
					$(".next").find('a').html( "<?php echo _("Install") ?>" );
				break;
				<?php else: ?>
					install();
				<?php endif; ?>
			case 5:
    			install();
    			break;
		}
	}
	/**
	 * 
	 */
	function install()
	{
		$(".form-bootstrapWizard").addClass("hidden");
		$(".tab-content").addClass("hidden");
		$("#install-animation").slideDown();
		/*
		$("#install-form").submit();
		*/
		var data = {};
		var installFields  = $( "#install-form :input" ).serializeArray();
		var printerFields  = $( "#printer-form :input" ).serializeArray();
		var timeZoneFields = $( "#tz-form :input" ).serializeArray();
		var localFields = $( "#locale-form :input" ).serializeArray();

		jQuery.each( installFields, function( index, object ) {
			data[object.name] = object.value;
		});

		jQuery.each( printerFields, function( index, object ) {
			data[object.name] = object.value;
		});

		jQuery.each( timeZoneFields, function( index, object ) {
			data[object.name] = object.value;
		});

		jQuery.each( localFields, function( index, object ) {
			data[object.name] = object.value;
		});

		
		$.ajax({
			type: 'post',
			url: '<?php echo site_url('install/do');?>',
			data : data,
			dataType: 'json',
			timeout: 10000,
			error: function(jqXHR, textStatus, errorThrown) {
				
				var time = textStatus=="timeout" ? 1000 : 5000;
				setTimeout(function() {
					location.href="<?php echo site_url('login'); ?>";
				}, time); 
			} 
		}).done(function(response) {
		});
		
		
	}
	/**
	* detect and select timezone
	*/
	function initTimeZone()
	{
		var tz = jstz.determine();
		$("#timezone").val(tz.name());
	    
	}
	/**
	* detect and select language 
	*/
	function initLanguage()
	{
	}
	/**
	*
	**/
	function setLocale()
	{
		var locale = $(this).val();
		$("#locale").val(locale);
		openWait("<i class='fa fa-spin fa-spinner'></i> <?php echo _("Translating"); ?> ..", "<?php echo _("Please wait"); ?>", false);
		$("#locale-form").submit();
	}
	/**
	*
	*/
	function scanWifi()
	{
		var iface = 'wlan0';
		$("#wlan-table-container").css('opacity', '0.1');
		$("#wifi-scan").html('<i class="fa fa-search"></i> <?php echo _("Scanning") ?>..')
		disableButton("#wifi-scan");
		$.ajax({
			type: 'get',
			url: "<?php echo site_url('control/scanWifi'); ?>/"+iface,
			dataType: 'json'
		}).done(function(response) {
			if(response)
				buildWifiTable(iface, response);
			$("#wlan-table-container").css('opacity', '1')
			$("#wifi-scan").html('<i class="fa fa-search"></i> <span class="hidden-xs"><?php echo _("Scan") ?></span>');
			enableButton("#wifi-scan");
		});
	}
	/**
	*
	**/
	function buildWifiTable(iface, nets)
	{
		var html = '<table class="table table-striped"><tbody>';

		$.each(nets, function( index, net ) {

			var protected = net.encryption_key == 'on' ? 'Protected <i class="fa fa-lock"></i>' : '';
			var frequency = net.frequency != '' ? net.frequency : '';
			var buttonAttributeProtected = net.encryption_key == 'on' ? 'true' : 'false';
			var buttonAttributeAction = 'connect';
			var buttonLabel =  '<?php echo _('Connect')?>';
			var trClass = 'tr-' + net.essid;
					
			html += '<tr class="'+ trClass+'">';
			html += '<td class="text-center va-middle"><i class="fa fa-wifi fa-2x  text-muted"></i></td>';
			html += '<td class="text-left">';
			//html += '<p><a data-attribute-essid="'+net.essid+'"  data-attribute-iface="'+iface+'" data-attribute-action="'+buttonAttributeAction+'" data-attribute-protected="'+buttonAttributeProtected+'"   class="font-md connect" href="javascript:void(0);">'+net.essid+'</a><span class="hidden-xs pull-right">Signal level: '+Math.round(net.quality) +'/100</span></p>';
			html += '<a data-attribute-essid="'+net.essid+'"  data-attribute-iface="'+iface+'" data-attribute-action="'+buttonAttributeAction+'" data-attribute-protected="'+buttonAttributeProtected+'"   class="font-md connect" href="javascript:void(0);">'+net.essid+'</a>';
			//html += '<div class="hidden-xs progress progress-sm progress-striped active"><div class="progress-bar  bg-color-blue" aria-valuetransitiongoal="'+ net.quality +'" style="width:'+net.quality+'%"></div></div>';
			html += '<br><small class="hidden-xs note">'+ protected  + ' / ' + net.mode + ' / ' + frequency + ' / ' + net.encryption +' </small>';
			html += '</td>';
			html += '<td class="va-middle text-center '+net.essid+'-td-button" style="width: 100px" class="text-right va-middle"><button type="button" data-attribute-encryption="'+net.encryption+'" data-attribute-essid="'+net.essid+'" data-attribute-iface="'+iface+'" data-attribute-action="'+buttonAttributeAction+'" data-attribute-protected="'+buttonAttributeProtected+'" class="btn btn-default btn-sm btn-block  connect">'+buttonLabel+'</button></td></td>';
			html += '</tr>';

		});
				
		html += '</tbody></table>';
		$("#wlan-table-container").html(html);
		$('.connect').on('click', connectionManager);		
	}
	/**
	*
	**/
	function connectionManager()
	{
		var element   = $(this);
		wifiSelected  = element.attr('data-attribute-essid');
		wifiIface  	  = element.attr('data-attribute-iface');
		var encryption = element.attr('data-attribute-encryption');
		var action    = element.attr('data-attribute-action');
		var protected = eval(element.attr('data-attribute-protected'));

		connectToWifi(wifiIface, wifiSelected, protected, encryption);
	}
	/**
	*
	**/
	function connectToWifi(iface, essid, isProtected, encryption)
	{
		if(isProtected){
			showPasswordModal(essid, encryption);
		}else{
			sendActionRequest('connect', essid);
		}
	}
	/**
	*
	**/
	function showPasswordModal(essid, encryption)
	{
		resetForms();
		$("#wifiPasswordMinLength").val(getWifiPasswordLength(encryption));
		$("#passwordModalTitle").html('<?php echo _('Password for')?> <strong>' + essid + '</strong>');
		$('#passwordModal').modal({});
	}
	/**
	*
	**/
	function getWifiPasswordLength(encryption)
	{
		var length = 0;		
		switch (encryption) {
		    case "802.11i/WPA2":
		    	length = 8;
		        break;
		    case "WPA2":
		    	length = 8;
		        break;
		    default:
			    length = 8;
		        break;
		}
		return length;
	}
	/**
	*
	**/
	function resetForms()
	{	
		$("#wifiPasswordMinLength").val('');
		$("#wifiPassword").val('');
		$(".show-password").attr('checked', false);
		$(".input-password").attr('type', 'password');
	}
	/**
	*
	**/
	function show_password()
	{
		var attr = $(this).attr('data-attribute');
		var obj = (attr != "modal")?$('#'+attr+'-tab .password'):$('#passwordModal #wifiPassword');
		if( $(this).is( ":checked" ) )
			obj.attr('type', 'text');
		else
			obj.attr('type', 'password');
	}
	/**
	*
	**/
	function passwordModalConnect()
	{
		if($("#passwordModalForm").valid()){
			$("#hidden-ssid").val(wifiSelected);
			$("#hidden-passphrase").val($("#wifiPassword").val());
			$("#hidden-psk").val('');
			save_wifi();
			$('#passwordModal').modal('hide');
			//sendActionRequest('connect', wifiIface, wifiSelected, $("#wifiPassword").val());
		}
	}
	/**
	*
	**/
	function save_wifi()
	{
		
		var data =  {
			'net_type'          : 'wlan',
			'active'            : 'wlan0',
			'address-mode'      : $("#address-mode").val(),
			'ap-ssid'           : $("#hidden-ssid").val(),
			'ap-password'       : $("#hidden-passphrase").val(),
			'hidden-passphrase' : $("#hidden-passphrase").val(),
			'hidden-psk'        : $("#hidden-psk").val(),
			'hidden-ssid'        : $("#hidden-ssid").val()
		};

		openWait("<i class='fa fa-spin fa-spinner'></i> " + _("Connecting to <strong>{0}</strong>").format(data['hidden-ssid']) + ' <i class="fa fa-wifi"></i>', _("Please wait"), false );
		scrollToTop();
		$.ajax({
			type: 'post',
			url: "<?php echo site_url('control/saveNetworkSettings'); ?>/connect/",
			data: data,
			dataType: 'json'
		}).done(function(response) {
						
			if(response.wlan0.wireless.ssid == data['ap-ssid'] && response.wlan0.wireless.wpa_state == "COMPLETED"){
				waitContent('<i class="fa fa-check"></i> ' + _("Connected"));
				setTimeout(function(){
					$(".tr-" + data['ap-ssid']).addClass('success');
					$("." + data['ap-ssid'] +"-td-button").html("<i class='fa fa-check'></i>");
					$(".next").trigger('click');
					closeWait();
				}, 3000);
			}else{
				closeWait();
				fabApp.showErrorAlert( _('Please check the password'), _('Connection failed'));
			}
		}).fail(function(jqXHR, textStatus){
			
			setTimeout(function(){
				$(".tr-" + data['ap-ssid']).addClass('success');
				$("." + data['ap-ssid'] +"-td-button").html("<i class='fa fa-check'></i>");
				$(".next").trigger('click');
				closeWait();
			}, 3000);
			
		});;
	}
	/**
	/*
	**/
	function openFABIDModal()
	{
		$("#fabid_email").val($("#email").val());
		$('#fabidModal').modal({});
	}
	/**
	*
	**/
	function fabIDConnect()
	{
		if($("#fabid-form").valid()){
			var fields = $( "#fabid-form :input" ).serializeArray();
			var data = {};
			jQuery.each( fields, function( index, object ) {
				data[object.name] = object.value;
			});
			
			data['fabid_serial_number'] = $("#serial_number").val();
			
			openWait('<i class="fa fa-spinner fa-spin "></i> <?php echo _("Connecting to FABID") ?>', _("Please wait"), false);

			$.ajax({
				type: 'post',
				url: '<?php echo site_url('myfabtotum/connect/0'); ?>',
				data : data,
				dataType: 'json'
			}).done(function(response) {

				closeWait();
				if(response.connect.status == true){
					$("#fabid").val(response.fabid);
					$("#fabidModalButton").addClass('btn-success').html('<i class="fa fa-check"></i> <?php echo _("Connected via FABID"); ?> (' + response.fabid +')');
					$('#fabidModal').modal('hide');
				}else{
					fabApp.showErrorAlert(response.connect.message, 'FABID');
				}

				
				
			});
		}
	}
</script>
