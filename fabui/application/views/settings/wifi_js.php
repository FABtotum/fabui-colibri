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
	
	var apMacAddress = '<?php echo isset($wlanInfo['ap_mac_address']) ? $wlanInfo['ap_mac_address'] : '' ?>';
	var wifiSelected;
	var wifiPassword;
	
	$(document).ready(function() {
		scan();
		initPasswordModalValidator();
		$("#scanButton").on('click', scan);
		$(".show-password").on('click', showPassword);
		$("#modalConnectButton").on('click', passwordModalConnect);
		$("#hiddenWifiButton").on('click', showHiddenModal);
		$("#hiddenModalConnectButton").on('click', hiddenWifiModalConnect);
		$(".show-details").on('click', showDetails);
	});
	
	/**
	 * scan wifi networks
	 */
	function scan()
	{
		$(".table-container").css('opacity', '0.1');
		$.ajax({
			type: 'get',
			url: '<?php echo site_url('settings/scanWifi'); ?>',
			dataType: 'json'
		}).done(function(response) {
			buildTable(response);
			$(".table-container").css('opacity', '1');
		});
		
	}
	
	/**
	 *  build nets table
	 */
	function buildTable(nets)
	{
		$(".nets-table").remove();
		var table = '<table class="table table-striped table-forum nets-table"><tbody>';
		$.each(nets, function( index, net ) {
			
  			var protected = net.encryption_key == 'on' ? 'Protected <i class="fa fa-lock"></i>' : '';
  			var channel   = net.channel != '' ? '( Channel ' + net.channel + ')' : '';
  			var buttonAttributeProtected = net.encryption_key == 'on' ? 'true' : 'false';
  			var buttonAttributeAction = apMacAddress == net.address ? 'disconnect' : 'connect';
  			var buttonLabel =  apMacAddress == net.address ? 'Disconnect' : 'Connect';
  			var trClass = apMacAddress == net.address ? 'warning' : '';
  			
  			table += '<tr class="'+ trClass+'">';
  			table += '<td class="text-center va-middle" style="width: 40px;"><i class="icon-communication-035 fa-2x text-muted"></i></td>';
  			table += '<td>';
  			table += '<p>'+net.essid+'<span class="hidden-xs pull-right">Signal level: '+net.signal_level +'/100</span></p>';
  			table += '<div class="hidden-xs progress progress-sm progress-striped active"><div class="progress-bar  bg-color-blue" aria-valuetransitiongoal="'+ net.signal_level +'"></div></div>';
  			table += '<small class="hidden-xs note">'+ protected +' ' + net.protocol + ' / ' + net.mode +' / ' + net.frequency + ' ' + channel + ' </small>';
  			table += '</td>';
  			table += '<td style="width: 100px" class="text-right va-middle"><button data-attribute-essid="'+net.essid+'" data-attribute-action="'+buttonAttributeAction+'" data-attribute-protected="'+buttonAttributeProtected+'" class="btn btn-default btn-sm btn-block connect">'+buttonLabel+'</button></td></td>';
  			table += '</tr>';
		});
		table += '</tbody></table>';
		$(".table-container").html(table);
		$('.progress-bar').progressbar();
		$('.connect').on('click', connectionManager);
	}
	/**
	 * 
	 */
	function connectionManager()
	{	
		var element   = $(this);
		wifiSelected  = element.attr('data-attribute-essid');
		var action    = element.attr('data-attribute-action');
		var protected = eval(element.attr('data-attribute-protected'));
		
		if(action == 'connect') connectToWifi(wifiSelected, protected);
		else disconnectFromWifi();
	}
	/**
	 * connect to wifi net
	 */
	function connectToWifi(essid, isProtected)
	{
		if(isProtected){
			showPasswordModal(essid);
		}else{
			sendActionRequest('connect', essid);
		}
	}
	/**
	 * disconnect from wifi
	 */
	function disconnectFromWifi()
	{
		sendActionRequest('disconnect', wifiSelected);
	}
	
	/**
	 * 
	 */
	function sendActionRequest(action, essid, password)
	{
		$('#passwordModal').modal('hide');
		$('#hiddenWifiModal').modal('hide');
		var connectionLabel = action == 'connect' ? 'Connecting to ' : 'Disconnecting from ';
		openWait('<i class="fa fa-circle-o-notch fa-spin"></i> '+ connectionLabel + ' ' + essid);
		essid = essid || '';
		password = password || '';
		
		$.ajax({
			type: 'post',
			url: '<?php echo site_url('settings/wifiAction'); ?>/' + action,
			data: {essid:essid, password: password},
			dataType: 'json'
		}).done(function(response) {
			waitContent('Refreshing page');
			setTimeout(function() {
				document.location.href = '<?php echo site_url('settings/wifi'); ?>';
			}, 3000);
		});
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
	 * show or hide password
	 */
	function showPassword()
	{
		var type = $(this).is(":checked") ? 'text' : 'password';
		$(".input-password").attr('type', type);
	}
	/**
	 * called from "connect" button on password modal
	 */
	function passwordModalConnect()
	{
		if($("#passwordModalForm").valid()){
			sendActionRequest('connect', wifiSelected, $("#wifiPassword").val());
		}
	}
	/**
	 * 
	 */
	function initPasswordModalValidator()
	{
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
	}
	/**
	 * 
	 */
	function showHiddenModal()
	{	
		resetForms();
		$('#hiddenWifiModal').modal({});
	}
	/**
	 * 
	 */
	function initHiddenWifiFormValidator()
	{
		$("#hiddenWifiForm").validate({
			rules:{
				hiddenWifiPassword:{
					required: true
				},
				hiddenWifiName: {
					required: true
				}
			},
			messages: {
				hiddenWifiPassword: {
					required: 'Please insert valid password'
				},
				hiddenWifiName: {
					required: 'Please insert WiFi Name'
				}
			},
			errorPlacement : function(error, element) {
				error.insertAfter(element.parent());
			}
		});
	}
	/**
	 * called from "connect" button on hidden wifi modal
	 */
	function hiddenWifiModalConnect()
	{
		if($("#hiddenWifiForm").valid()){
			sendActionRequest('connect', $("#hiddenWifiName").val(), $("#hiddenWifiPassword").val());
		}
	}
	/**
	 * 
	 */
	function resetForms()
	{	
		$("#wifiPassword").val('');
		$("#hiddenWifiName").val('');
		$("#hiddenWifiPassword").val('');
		$(".show-password").attr('checked', false);
		$(".input-password").attr('type', 'password');
	}
	/**
	 * 
	 */
	function showDetails()
	{
		var button = $(this);
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
</script>