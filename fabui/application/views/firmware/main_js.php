<script type="text/javascript">

	 $(function () {
		
		$("#fw-version").on('change', set_flash_section);
		$(".flash-button").on('click', start_fw_flashing);
		$("#hex-file").on('change', function(){
			$(".flash-button").removeClass("disabled");
		});
	 });

	 function set_flash_section(){
		var version = $(this).val();
		
		if(version == 'upload')
		{
			$(".flash-section").hide();
			$(".upload-section").show();
		}
		else
		{
			$(".flash-section").show();
			$(".upload-section").hide();
		}
	 }

	function start_fw_flashing()
	{
		var version = $("#fw-version").val();
		if(version == 'upload')
		{
			doFirmwareUpload();
		}
		else
		{
			doFlashFirmware(version);
		}
	}
	
	function doFlashFirmware(version)
	{
		openWait('<i class="fa fa-cog fa-spin"></i> Installing <strong>'+version+'</strong> firmware...');                          
		$.ajax({
			url: '<?php echo site_url('firmware/doFlashFirmware') ?>/'+version,
			dataType: 'json',
			cache: false,
			contentType: false,
			processData: false,                       
			type: 'post',
			success: function(response){
				handle_response(response);
			}
		 });
	}
	
	function doFirmwareUpload()
	{
		openWait('<i class="fa fa-cog fa-spin"></i> ' + _("Uploading and installing firmware") + '...');
		var hexFile = $('#hex-file').prop('files')[0];   
		var form_data = new FormData();                  
		form_data.append('hex-file', hexFile);
		                           
		$.ajax({
			url: '<?php echo site_url('firmware/doUploadFirmware') ?>',
			dataType: 'json',
			cache: false,
			contentType: false,
			processData: false,
			data: form_data,                         
			type: 'post',
			success: function(response){
				handle_response(response);
			}
		 });
	}
	/**
	*
	**/
	function handle_response(response)
	{
		if(response.result == false) {
			closeWait();
			$.SmartMessageBox({
				title: "<i class='fa fa-warning txt-color-orangeDark'></i> " + _("Warning"),
				content : '<br><span >' + _("Firmware was not flashed") + '</span><br><span >'+_("Please try again")+'</span><br><span >'+_("If the problem persists please contact support")+'</span>',
				buttons: "[" + _("Ok") + "]",
			}, function(ButtonPressed, Option) {
				if(ButtonPressed == _("Ok")){
					location.reload();
				}
			});
		}else{
			waitContent( _("Firmware flashed correctly") + '<br>' + _("Reloading page"));
			setTimeout(function(){
				location.reload();
				}, 7000
			);
		}
	}
	
</script>
