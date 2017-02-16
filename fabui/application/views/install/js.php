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
		initValidate();
		initWizard();
		initTimeZone();
		$("#i-agree").click(function(){
			$this=$("#terms");
			if($this.checked) {
				$('#termsConditionModal').modal('toggle');
			} else {
				$this.prop('checked', true);
				$('#termsConditionModal').modal('toggle');
			}
		});
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
		        	email: "Your email address must be in the format of name@domain.com"
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
		    	first_name: "Please specify your First name",
		   		last_name: "Please specify your Last name",
		      	email: {
		        	required: "We need your email address to contact you",
		        	email: "Your email address must be in the format of name@domain.com"
		      	},
		      	confirmPassword:{
		      		equalTo : 'Please enter the same password as above'
		      	},
		      	terms : {
					required : 'You must agree with Terms and Conditions'
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
		        		
		        		if(index == 3){
		        			$(".next").find('a').attr('style', 'cursor: pointer !important;');
                       		$(".next").find('a').html('Install');
		        		}
		        		
		        		if(index == 4){
		        			install();
		        		}
		        		
			  		}
			},
			'onPrevious': function(tab, navigation, index){
				$(".next").find('a').html('Next');
			},
			'onLast': function(tab, navigation, inde){
				console.log("last");
			},
			'onFinish': function(tab, navigation, inde){
				console.log("finish");
			}
			
		});
	}
	
	/**
	 * 
	 */
	function install()
	{
		$(".next").find('a').html('Installing...');
		$("#browser-date").val(moment().format('YYYY-MM-DD HH:mm:ss'));
		openWait("<i class='fa fa-spin fa-spinner'></i> Installation in progress", "This may take awhile, please wait", false);
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
		var lang = navigator.language || navigator.userLanguage;
		$("#language").val(lang);
	}
</script>
