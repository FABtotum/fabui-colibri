<script type="text/javascript">
	
	 $(function () {
		
	 	$("#recovery-button").click(function(e) {
			$.SmartMessageBox({
				title : _("Recovery"),
				content : _("Are you sure you want to enter Recovery mode"),
				buttons : '[No][Yes]'
			}, function(ButtonPressed) {
				if (ButtonPressed === "Yes") {
					fabApp.forceRecovery();
				}else if (ButtonPressed === "No") {
				}

			});
			e.preventDefault();
		})


	 });

</script>
