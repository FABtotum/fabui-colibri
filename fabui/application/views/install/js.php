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
	}
	/**
	 * 
	 */
	function initWizard()
	{
		$('#bootstrap-wizard-1').bootstrapWizard({
			'tabClass': 'form-wizard',
			'onNext': function (tab, navigation, index) {
		    	var $valid = $("#install-form").valid();
		      		if (!$valid) {
		      			$validator.focusInvalid();
		      			return false;
		      		} else {
			      		
		        		$('#bootstrap-wizard-1').find('.form-wizard').children('li').eq(index - 1).addClass('complete');
		        		$('#bootstrap-wizard-1').find('.form-wizard').children('li').eq(index - 1).find('.step').html('<i class="fa fa-check"></i>');
		        		handleStep(index);
			  		}
			},
			'onPrevious': function(tab, navigation, index){
				handleStep(index);
			},
			'onLast': function(tab, navigation, inde){
				console.log("last");
			},
			'onFinish': function(tab, navigation, inde){
				console.log("finish");
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
				$(".next").find('a').html( "<?php echo _("Skip") ?>" );
				break;
    		case 3:
        		<?php if(count($steps) == 5): ?>
					$(".next").find('a').html( "<?php echo _("Skip") ?>" );
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
		$("#install-form").submit();
		
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
		$.ajax({
			type: 'get',
			url: "<?php echo site_url('control/scanWifi'); ?>/"+iface,
			dataType: 'json'
		}).done(function(response) {
			if(response)
				buildWifiTable(iface, response);
			else
				console.log("NO WIFI");
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
			html += '<td class="va-middle text-center '+net.essid+'-td-button" style="width: 100px" class="text-right va-middle"><button type="button" data-attribute-essid="'+net.essid+'" data-attribute-iface="'+iface+'" data-attribute-action="'+buttonAttributeAction+'" data-attribute-protected="'+buttonAttributeProtected+'" class="btn btn-default btn-sm btn-block  connect">'+buttonLabel+'</button></td></td>';
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
		var action    = element.attr('data-attribute-action');
		var protected = eval(element.attr('data-attribute-protected'));

		connectToWifi(wifiIface, wifiSelected, protected);
	}
	/**
	*
	**/
	function connectToWifi(iface, essid, isProtected)
	{
		if(isProtected){
			showPasswordModal(essid);
		}else{
			sendActionRequest('connect', essid);
		}
	}
	/**
	*
	**/
	function showPasswordModal(essid)
	{
		resetForms();
		$("#passwordModalTitle").html('<?php echo _('Password for')?> <strong>' + essid + '</strong>');
		$('#passwordModal').modal({});
	}
	/**
	*
	**/
	function resetForms()
	{	
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
		
		$.ajax({
			type: 'post',
			url: "<?php echo site_url('control/saveNetworkSettings'); ?>/connect/",
			data: data,
			dataType: 'json'
		}).done(function(response) {

			console.log(response);
			
			if(response.wlan0.wireless.ssid == data['ap-ssid']){
				waitContent('<i class="fa fa-check"></i> ' + _("Connected"));
				setTimeout(function(){
					$(".tr-" + data['ap-ssid']).addClass('success');
					$("." + data['ap-ssid'] +"-td-button").html("<i class='fa fa-check'></i>");
					$(".next").trigger('click');
					closeWait();
				}, 3000);
			}else{
				closeWait();
				showErrorAlert( _('Please check the password'), _('Connection failed'));
			}
		});
	}
</script>
