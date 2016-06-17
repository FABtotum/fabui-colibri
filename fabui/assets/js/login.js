runAllForms();
// Validation
$("#login-form").validate({
	// Rules for form validation
	rules : {
		user_name : {
			required : true
		},
		password : {
			required : true
		}
	},
	// Messages for form validation
	messages : {
		user_name : {
			required : 'Inserire la username',
		},
		password : {
			required : 'Inserire la password'
		}
	},
	// Do not change code below
	errorPlacement : function(error, element) {
		error.insertAfter(element.parent());
	}
});
	