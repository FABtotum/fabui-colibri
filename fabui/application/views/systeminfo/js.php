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
		});

		<?php if($this->session->user['role'] == 'administrator'): ?>

			initFormValidator();
		
		    $(".system-date-time").click(function(e){
		    	$('#dateTimeModal').modal({});
		    });

		    $(".unit-color").click(function(e){
		    	$('#unitColorModal').modal({});
		    });

		    $(".unit-serial-number").click(function(e){
		    	$('#unitSerialNumberModal').modal({});
		    });

		    $(".host-name").click(function(e){
		    	$('#hostNameModal').modal({});
		    });

		    $(".language").click(function(e){
			    $("#languageModal").modal({});
		    });
		    
	    	$("#dateTimeSave").on('click', saveDateTime);
	    	$("#unitColorSave").on('click', saveUnitColor);
	    	$("#unitSerialNumberSave").on('click', saveSerialNumber);
	    	$("#hostNameSave").on('click', saveHostName);
	    	$("#langaugeSave").on('click', saveLanguage);
	    <?php endif; ?>
		
	 });

	 <?php if($this->session->user['role'] == 'administrator'):?>
		/**
		*
		**/
	 	function initFormValidator()
	 	{
	 		$("#unit-serial-number-form").validate({
				// Rules for form validation
				rules : {
					unit_serial_number : {
						required : true,
						minlength: 13,
					}
				},
				// Messages for form validation
				messages : {
					unit_serial_number : {
						required : "<?php echo _("Please enter serial number")?>",
						minlength : "<?php echo _("Serial number has 13 characters") ?>",
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
		function saveDateTime()
		{
			
			var data = getDataFromForm("#date-time-form");
			openWait('<i class="fa fa-cog fa-spin "></i> <?php echo _("Applying new settings") ?>', _("Please wait"), false);
			$("#dateTimeSave").html('<i class="fa fa-save"></i> <?php echo _('Saving')?> ...');
			$('#dateTimeModal').modal('hide');
	
			$.ajax({
				type: 'post',
				url: '<?php echo site_url('control/saveDateTime');?>',
				data : data,
				error: function(jqXHR, textStatus, errorThrown) {
					openWait('<i class="fa fa-check "></i> <?php echo _("Settings applied") ?>', _("Reloading page"), false);
					setTimeout(function() {
						location.reload();
					}, 5000);
				}
			}).done(function(response) {
			});
		 }
		 /**
		 **
		 **/
		 function saveUnitColor()
		 {
			 var data = getDataFromForm("#unit-color-form");
			 openWait('<i class="fa fa-cog fa-spin "></i> <?php echo _("Setting unit color") ?>', _("Please wait"), false);
			 $("#unitColorSave").html('<i class="fa fa-save"></i> <?php echo _('Saving')?> ...');
			 $('#unitColorModal').modal('hide');

			 $.ajax({
				type: 'post',
				url: '<?php echo site_url('control/saveSystemInfo/unit_color');?>/' + $("#unit_color").val(),
				data : data,
			}).done(function(response) {
				openWait('<i class="fa fa-check "></i> <?php echo _("Settings applied") ?>', '', false);
				setTimeout(function() {
					$(".unit-color").html($("#unit_color").val().capitalize());
					$("#unitColorSave").html('<i class="fa fa-save"></i> <?php echo _('Save')?>');
					closeWait();
				}, 1500);
			});
		 }
		 /**
		 *
		 **/
		 function saveSerialNumber()
		 {
			if(!$("#unit-serial-number-form").valid()){

				return false;
			} 
			openWait('<i class="fa fa-cog fa-spin "></i> <?php echo _("Setting serial number") ?>', _("Please wait"), false);
			$("#unitSerialNumberSave").html('<i class="fa fa-save"></i> <?php echo _('Saving')?> ...');
			$('#unitSerialNumberModal').modal('hide');

			$.ajax({
				type: 'post',
				url: '<?php echo site_url('control/saveSystemInfo/serial_number');?>/' + $("#unit_serial_number").val().toUpperCase(),
			}).done(function(response) {
				openWait('<i class="fa fa-check "></i> <?php echo _("Settings applied") ?>', '', false);
				setTimeout(function() {
					$(".unit-serial-number").html($("#unit_serial_number").val().toUpperCase());
					$("#unitSerialNumberSave").html('<i class="fa fa-save"></i> <?php echo _('Save')?>');
					closeWait();
				}, 1500);
			});
			 
		 }
		 /**
		 *
		 **/
		 function saveHostName()
		 {
			var data = {
				"dnssd-hostname" : $("#dnssd-hostname").val(),
				"dnssd-name" : $("#dnssd-name").val(),
				"active" : "dnssd",
				"net_type" : "dnssd"
			};
			
			$("#hostNameSave").html('<i class="fa fa-save"></i> <?php echo _('Saving')?> ...');
			openWait('<i class="fa fa-cog fa-spin "></i> <?php echo _("Setting new host name") ?>', _("Please wait"), false);
			$('#hostNameModal').modal('hide');
			
			$.ajax({
				type: 'post',
				url: '<?php echo site_url('settings/saveNetworkSettings/connect');?>',
				data : data
			}).done(function(response) {
				setTimeout(function() {
					$(".host-name").html($("#dnssd-hostname").val() + " - " + $("#dnssd-name").val());
					$("#hostNameSave").html('<i class="fa fa-save"></i> <?php echo _('Save')?>');
					closeWait();
				}, 1500);
				
			});
		 }
		 /**
		 *
		 **/
		 function saveLanguage()
		 {
			$("#langaugeSave").html('<i class="fa fa-save"></i> <?php echo _('Saving')?> ...');
			openWait("<i class='fa fa-spin fa-spinner'></i> <?php echo _("Translating"); ?> ..", "<?php echo _("Please wait"); ?>", false);

			$.ajax({
				type: 'post',
				url: '<?php echo site_url('control/setLanguage');?>/' + $("#language-select").val(),
			}).done(function(response) {
				location.reload();
			});
			
			$('#languageModal').modal('hide');
			$("#language-form").submit();
			
		 }
	 <?php endif; ?>
</script>
