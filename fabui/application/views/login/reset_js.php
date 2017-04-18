<?php
/**
 * 
 * @author Krios Mane, Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<script type="text/javascript">
	$(document).ready(function() {
		initValidate();
		
		$("#register-button").click(function(){

			var $valid = $("#form-register").valid();
			
			if(!$valid)
			{
				return false;
			}
			else
			{
				$("#register-button").addClass('disabled');	
				$("#form-register").submit();
			}
			
		});
	})
		
	function initValidate()
	{
		$("#form-register").validate({

			// Rules for form validation
			rules : {
				
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
				
			},

			// Messages for form validation
			messages : {
				
				
				password : {
					required : _("Please enter your password")
				},
				passwordConfirm : {
					required : _("Please enter your password one more time"),
					equalTo : _("Please enter the same password as above")
				}
			},

			// Do not change code below
			errorPlacement : function(error, element) {
				error.insertAfter(element.parent());
			}
		});
				
	}
</script>
