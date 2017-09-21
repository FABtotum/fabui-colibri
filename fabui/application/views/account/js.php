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
	$(document).ready(function() {
		initLanguage();
		initFormValidator();
		$("#save").on('click', saveUser);
		$("#fabid-connect-button").on('click', fabIDConnect);
		$("#fabid-disconnect-button").on('click', askFabIDDisconnect);
		
		$("#fabidModalButton").click(function(e){
	    	$('#fabidModal').modal({});
	    });
	});
	/**
	*
	*/
	function initLanguage()
	{
		<?php if(isset($this->session->user['settings']['language'])): ?>
		$("#settings-language").val('<?php echo $this->session->user['settings']['language'] ?>');
		<?php endif; ?>
	}
	/**
	*
	*/
	function initFormValidator()
	{
		$("#user-form").validate({
			// Rules for form validation
			rules : {
				email : {
					required : true,
					email : true
				},
				first_name : {
					required : true
				},
				last_name : {
					required: true
				}
			},
			// Messages for form validation
			messages : {
				email : {
					required : "<?php echo _("Please enter your email address");?>",
					email : "<?php echo _("Please enter a valid email address") ?>"
				},
				first_name : {
					required : "<?php echo _("Please enter your first name")?>"
				},
				last_name : {
					required : "<?php echo _("Please enter your last name")?>"
				}
			},
			// Do not change code below
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
	**/
	function saveUser()
	{
		if($("#user-form").valid()){
			var fields = $( "#user-form :input" ).serializeArray();
			var data = {};
			jQuery.each( fields, function( index, object ) {
				data[object.name] = object.value;
			});
			$("#save").html('<i class="fa fa-save"></i> <?php echo _("Saving") ?>');
			disableButton('#save');
			$.ajax({
				type: 'post',
				url: '<?php echo site_url('account/saveUser/'.$this->session->user['id']); ?>',
				data : data,
				dataType: 'json'
			}).done(function(response) {
				enableButton('#save');
				$("#save").html('<i class="fa fa-save"></i> Save');

				$.smallBox({
					title : "Settings",
					content : "<?php echo _("Account information saved");?>",
					color : "#5384AF",
					timeout: 3000,
					icon : "fa fa-check bounce animated"
				});

				if("<?php echo $this->session->user['settings']['language'] ?>" != $("#settings-language").val()){
					location.reload();
				}
				
			});
		}
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

			openWait('<i class="fa fa-spinner fa-spin "></i> <?php echo _("Connecting to FABID") ?>', _("Please wait"), false);

			$.ajax({
				type: 'post',
				url: '<?php echo site_url('account/connectFABID'); ?>',
				data : data,
				dataType: 'json'
			}).done(function(response) {
				
				if(response.status == false){
					closeWait();
					fabApp.showErrorAlert(response.message, 'FABID');
				}else{
					$('#fabidModal').modal('hide');
					openWait('<i class="fa fa-check"></i> ' + response.message, _("Reloading page"), false);
					setTimeout(function() {
						location.reload();
					}, 2500);
				}
			});
		}
	}
	/**
	*
	**/
	function askFabIDDisconnect()
	{
		$.SmartMessageBox({
			title : "<?php echo _("Warning"); ?>",
			content : "<?php echo _("Are sure you want to disconnect?"); ?>",
			buttons : '[<?php echo _("No");?>][<?php echo _("Yes");?>]'
		}, function(ButtonPressed) {
			if (ButtonPressed === "<?php echo _("Yes");?>") {
				fabIDDisconnect();
			}
		});
		
	}
	/**
	*
	*/
	function fabIDDisconnect()
	{
		openWait('<i class="fa fa-spinner fa-spin "></i> <?php echo _("Disconnecting from FABID") ?>', _("Please wait"), false);
		$.ajax({
			type: 'post',
			url: '<?php echo site_url('account/disconnectFABID'); ?>',
			dataType: 'json'
		}).done(function(response) {
			openWait('<i class="fa fa-check"></i> <?php echo _("Disconnected");?>', _("Reloading page"), false);
			setTimeout(function() {
				location.reload();
			}, 2500);
		});
	}
</script>
