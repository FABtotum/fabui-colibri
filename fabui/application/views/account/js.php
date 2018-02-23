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

var fileTypes = [ 'image/jpeg', 'image/pjpeg', 'image/png', 'image/gif'];
var file = null;
	$(document).ready(function() {
		drawBreadCrumb(["<?php echo _("Account"); ?>"]);
		
		initFormValidator();
		$("#save").on('click', handleSaveButton);
		$("#fabid-connect-button").on('click', fabIDConnect);
		$("#fabid-disconnect-button").on('click', askFabIDDisconnect);
		
		$("#fabidModalButton").click(function(e){
	    	/*$('#fabidModal').modal({});*/
	    	fabApp.fabIDLogin();
	    });

	    $("#file").on('change', function(){
	    	handleAvatarPreview();
		});
	});

	function validFileType(file) {
		  for(var i = 0; i < fileTypes.length; i++) {
		    if(file.type === fileTypes[i]) {
		      return true;
		    }
		  }

		  return false;
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
			//var fields = $( "#user-form :input" ).serializeArray();
			var data = getDataFromForm("#user-form");
			$("#save").html('<i class="fa fa-save"></i> <?php echo _("Saving") ?>');
			disableButton('#save');
			openWait('<i class="fa fa-cog fa-spin "></i> <?php echo _("Saving...") ?>', _("Please wait"), false);
			$("#user-form").submit();
			
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

	/**
	*
	*/
	function handleAvatarPreview()
	{

		var preview = document.querySelector('.preview');

		while(preview.firstChild){
			preview.removeChild(preview.firstChild);
		}

		 var reader  = new FileReader();

		var input = $("#file")
		var files = input[0].files;

		if(files.length == 0){
			var para = document.createElement('p');
			$("#image-name").attr("placeholder", _('No files currently selected for upload'));
			preview.appendChild(para);
		}else{

			var para = document.createElement('p');
			var image = document.createElement('img');
			file = files[0];

			if(validFileType(file)){
				image.src = window.URL.createObjectURL(file);
				$(".online").attr('src', image.src);
				image.width = 100;
				preview.appendChild(image);
	    		$("#image-name").attr("placeholder", file.name);
	    		
			}else{
				$("#image-name").attr("placeholder", + file.name + ': ' + + _('Not a valid file type. Update your selection.'));
				;
			}
		}
	}
</script>
