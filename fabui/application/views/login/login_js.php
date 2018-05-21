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
		fabApp.FabActions();
		initValidate();
		$("#login-form").on('submit', submitForm);
		$("#send-mail").on('click', sendResetEmail);
		$("#forgot-password").on('click', showForgotPasswordModal);
		$("#reload-page").on('click', reloadPage);
		<?php if($fabid == true && $fabid_active):?>
			fabApp.fabIDLogin();
		<?php endif; ?>

		<?php if($fabid_active): ?>
		$("#local-access").on("click", function(){
			$("#fabid-access-form-container").slideUp(function(){
				$("#local-access-form-container").slideDown();
			});
		});

		$("#fabid-access").on("click", function(){
			$("#local-access-form-container").slideUp(function(){
				$("#fabid-access-form-container").slideDown();
			});
		});
		<?php endif; ?>
				
	});
	
	function showForgotPasswordModal()
	{
		$('#password-modal').modal({
			keyboard : false
		});
	}
	
	function reloadPage()
	{
		location.reload();
	}
	
	function sendResetEmail() {

		$("#error-message").hide();
		$("#send-mail").addClass('disabled');
		$("#send-mail").html( _("Sending...") );

		$.ajax({
			url : "/fabui/login/sendResetEmail",
			data : {
				email : $("#mail-for-reset").val()
			},
			type : 'POST',
			dataType : 'json'
		}).done(function(response) {

			$("#send-mail").removeClass('disabled');
			$("#send-mail").html('Send Mail');
			if (!response.user) {
				$("#error-message").show();
				return false;
			}
			else{
				$("#error-message").hide();
				if (response.sent) {
					$('#password-modal').modal('hide')
					$.smallBox({
						title : _("Success"),
						content : "<i class='fa fa-check'></i>" + _("A message was sent to that address containing a link to reset your password "),
						color : "#659265",
						iconSmall : "fa fa-thumbs-up bounce animated",
						timeout : 4000
					});
				}
			}
		});
	}
	
	function submitForm()
	{
		$("#browser-date").val(moment().format('YYYY-MM-DD HH:mm:ss'));
	}
	
	function initValidate()
	{
		$("#login-form").validate({
			// Rules for form validation
			rules : {
				email : {
					required : true,
					email : true
				},
				password : {
					required : true,
					minlength : 3,
					maxlength : 20
				}
			},
			// Messages for form validation
			messages : {
				email : {
					required : "<?php echo _("Please enter your email address");?>",
					email : "<?php echo _("Please enter a valid email address");?>"
				},
				password : {
					required : "<?php echo _("Please enter your password");?>"
				}
			},
			// Do not change code below
			errorPlacement : function(error, element) {
				error.insertAfter(element.parent());
			}
		});
	}
</script>
