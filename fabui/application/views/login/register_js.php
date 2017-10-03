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
		
		$("#i-agree").click(function(){
			$this=$("#terms");
			if($this.checked) {
				$('#myModal').modal('toggle');
			} else {
				$this.prop('checked', true);
				$('#myModal').modal('toggle');
			}
		});
		
		initValidate();
	});
	
	function initValidate()
	{
		$("#register-form").validate({

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
				},
				passwordConfirm : {
					required : true,
					minlength : 3,
					maxlength : 20,
					equalTo : '#password'
				},
				first_name : {
					required : true
				},
				last_name : {
					required : true
				},
				terms : {
					required : true
				}
			},

			// Messages for form validation
			messages : {
				login : {
					required : '<?php echo _("Please enter your login name");?>'
				},
				email : {
					required : '<?php echo _("Please enter your email address");?>',
					email : '<?php echo _("Please enter a valid email address");?>'
				},
				password : {
					required : '<?php echo _("Please enter your password");?>'
				},
				passwordConfirm : {
					required : '<?php echo _("Please enter your password one more time");?>',
					equalTo : '<?php echo _("Please enter the same password as above");?>'
				},
				first_name : {
					required : '<?php echo _("Please enter your first name");?>'
				},
				last_name : {
					required : '<?php echo _("Please enter your last name");?>'
				},
				terms : {
					required : '<?php echo _("You must agree with Terms and Conditions");?>'
				}
			},
			// Do not change code below
			errorPlacement : function(error, element) {
				error.insertAfter(element.parent());
			}
		});
	}
</script>
