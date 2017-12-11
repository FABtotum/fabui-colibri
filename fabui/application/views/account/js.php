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
		drawBreadCrumb(["<?php echo _("Account"); ?>"]);
		initLanguage();
		initFormValidator();
		$("#save").on('click', handleSaveButton);
		$("#fabid-connect-button").on('click', fabIDConnect);
		$("#fabid-disconnect-button").on('click', askFabIDDisconnect);
		
		$("#fabidModalButton").click(function(e){
	    	/*$('#fabidModal').modal({});*/
	    	fabApp.fabIDLogin();
	    });
	});
	/**
	*
	*/
	function initLanguage()
	{
		<?php if(isset($this->session->user['settings']['locale'])): ?>
		$("#settings-locale").val('<?php echo $this->session->user['settings']['locale'] ?>');
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

		$("#password-form").validate({
			// Rules for form validation
			rules : {
				old_password : {
					required : true,
				},
				new_password : {
					required : true
				},
				confirm_new_password : {
					required: true,
					equalTo: "#new_password"
				}
			},
			// Messages for form validation
			messages : {
				old_password : {
					required : "<?php echo _("Please enter your old password");?>",
				},
				new_password : {
					required : "<?php echo _("Please enter your new password")?>"
				},
				confirm_new_password : {
					required : "<?php echo _("Please confirm your new password")?>",
					equalTo: "<?php echo _("Please enter the same password as above"); ?>"
					
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

				fabApp.showInfoAlert("<?php echo _("Account information saved");?>", '<?php echo _("Edit profile");?>');
			

				$("#user-name").html(response.first_name + ' ' + response.last_name );
				
				<?php if(isset( $this->session->user['settings']['locale'])):?>
				if("<?php echo $this->session->user['settings']['locale'] ?>" != $("#settings-locale").val()){
					location.reload();
				}
				<?php endif; ?>
				
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
			var saveToDB = true;
			$.ajax({
				type: 'post',
				url: '<?php echo site_url('myfabtotum/connect/'); ?>' + saveToDB,
				data : data,
				dataType: 'json'
			}).done(function(response) {

				if(response.connect.status == true){
					$('#fabidModal').modal('hide');
					openWait('<i class="fa fa-check"></i> <?php echo _("Connected to your FABID account"); ?>', _("Reloading page"), false);
					setTimeout(function() {
						location.reload();
					}, 2500);
				}else{
					closeWait();
					fabApp.showErrorAlert(response.connect.message, 'FABID');
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
			url: '<?php echo site_url('myfabtotum/disconnect'); ?>',
			dataType: 'json'
		}).done(function(response) {
			openWait('<i class="fa fa-check"></i> <?php echo _("Disconnected");?>', _("Reloading page"), false);
			setTimeout(function() {
				location.reload();
			}, 2500);
		});
	}
	/**
	*
	**/
	function handleSaveButton()
	{
		var active_tab = $(".tab-pane.active").attr("id");
		switch(active_tab){
			case 'account-tab':
				saveUser();
				break;
			case 'password-tab':
				saveNewPassword();
				break;
			case 'notifications-tab':
				saveNotifications();
				break;
		}
	}
	/**
	*
	*/
	function saveNewPassword()
	{
		if($("#password-form").valid()){
			var data = getDataFromForm("#password-form");
			$("#save").html('<i class="fa fa-save"></i> <?php echo _("Saving") ?>');
			disableButton("#save");
			$.ajax({
				type: 'post',
				url: '<?php echo site_url('account/saveNewPassword/'); ?>',
				data : data,
				dataType: 'json'
			}).done(function(response) {

				if(response.status == false){
					fabApp.showErrorAlert(response.message, '<?php echo _("Change password");?>');
				}else{
					fabApp.showInfoAlert(response.message, '<?php echo _("Change password");?>');
				}

				enableButton('#save');
				$("#save").html('<i class="fa fa-save"></i> Save');
			});
			
		}
	}
	/**
	*
	**/
	function saveNotifications()
	{
		var data = getDataFromForm("#notifications-form");

		$("#save").html('<i class="fa fa-save"></i> <?php echo _("Saving") ?>');
		disableButton("#save");
		$.ajax({
			type: 'post',
			url: '<?php echo site_url('account/saveNotifications/'); ?>',
			data : data,
			dataType: 'json'
		}).done(function(response) {

			if(response.status == false){
				fabApp.showErrorAlert(response.message, '<?php echo _("Change password");?>');
			}else{
				fabApp.showInfoAlert(response.message, '<?php echo _("Change password");?>');
			}

			enableButton('#save');
			$("#save").html('<i class="fa fa-save"></i> Save');
			
		});
	}
</script>
