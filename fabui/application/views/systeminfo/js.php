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
	    $(".system-date-time").click(function(e){
	    	$('#dateTimeModal').modal({});
	    });

	    $("#dateTimeSave").on('click', saveDateTime);
		
	 });
	 /**
	 *
	 **/
	function saveDateTime()
	{
		var data = {};
		$("#date-time-form :input").each(function (index, value) {
			if($(this).is('input:text') || $(this).is('textarea') || $(this).is('select') || $(this).is(':input[type="number"]') || $(this).is(':input[type="password"]') || ($(this).is('input:radio') && $(this).is(':checked')) ){
				data[$(this).attr('id')] = $(this).val();
			}
		});

		openWait('<i class="fa fa-cog fa-spin "></i> <?php echo _("Applying new settings") ?>', _("Please wait"), false);
		$("#dateTimeSave").html('<i class="fa fa-save"></i> <?php echo _('Saving')?> ...');
		$('#dateTimeModal').modal('hide');

		$.ajax({
			type: 'post',
			url: '<?php echo site_url('systeminfo/saveDateTime');?>',
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
	 
</script>
