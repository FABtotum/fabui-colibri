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
		console.log(version);
		
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
		// doUploadFirmware
		var version = $("#fw-version").val();
		console.log(version);
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
		openWait('<i class="fa fa-spinner fa-spin"></i> Installing <strong>'+version+'</strong> firmware...');                          
		$.ajax({
			url: '<?php echo site_url('firmware/doFlashFirmware') ?>/'+version,
			dataType: 'json',
			cache: false,
			contentType: false,
			processData: false,                       
			type: 'post',
			success: function(response){
				console.log(response);
				
				
				setTimeout(function(){
						closeWait();
						location.reload();
					}, 10000
				);
				/*if(response.installed == true){
					waitContent('Plugin installed successfully<br>Redirecting to plugins page...');
					setTimeout(function(){
							window.location = '<?php echo site_url("#plugin");?>';
					}, 3000);
				}
				else
				{
					closeWait();
					$.smallBox({
						title : "Error",
						content : 'Uploaded zip file is not a plugin archive.',
						color : "#C46A69",
						timeout: 10000,
						icon : "fa fa-warning"
					});
				}*/
			}
		 });
	}
	
	function doFirmwareUpload()
	{
		openWait('<i class="fa fa-spinner fa-spin"></i> Uploading and installing firmware...');
		var hexFile = $('#hex-file').prop('files')[0];   
		var form_data = new FormData();                  
		form_data.append('hex-file', hexFile);
		console.log(form_data);                             
		$.ajax({
			url: '<?php echo site_url('firmware/doUploadFirmware') ?>',
			dataType: 'json',
			cache: false,
			contentType: false,
			processData: false,
			data: form_data,                         
			type: 'post',
			success: function(response){
				console.log(response);
				setTimeout(function(){
						closeWait();
						location.reload();
					}, 10000
				);
				/*if(response.installed == true){
					waitContent('Plugin installed successfully<br>Redirecting to plugins page...');
					setTimeout(function(){
							window.location = '<?php echo site_url("#plugin");?>';
					}, 3000);
				}
				else
				{
					closeWait();
					$.smallBox({
						title : "Error",
						content : 'Uploaded zip file is not a plugin archive.',
						color : "#C46A69",
						timeout: 10000,
						icon : "fa fa-warning"
					});
				}*/
			}
		 });
	}
	
</script>
