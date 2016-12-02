<script type="text/javascript">
	
	$(function() {
		
		$("#install-button").on('click', doUpload);
		
		$("#plugin-file").on('change', function(){
			
			//~ var allowed_types = new Array('zip', 'xz' ,'gz', 'bz2', 'tgz');
			var allowed_types = new Array('zip');
			
			$(".type-warning").remove();
			$("#install-button").removeClass("disabled");
			
			
			var files = !!this.files ? this.files : [];
			
			var explode = files[0].name.split(".");
			
			var extension = explode[explode.length-1];
			
			if($.inArray(extension.toLowerCase(), allowed_types) == -1){
				
				$(".well").after('<div class="alert alert-warning type-warning"><i class="fa-fw fa fa-warning"></i><strong>Warning</strong> Only .zip files are allowed</div>');
				$(this).val("");
				$("#install-button").addClass("disabled");
			}
			
		});
		
	});

function doUpload()
{
	openWait('<i class="fa fa-spinner fa-spin"></i> Uploading and installing plugin...');
	var pluginFile = $('#plugin-file').prop('files')[0];   
	var form_data = new FormData();                  
	form_data.append('plugin-file', pluginFile);
	console.log(form_data);                             
	$.ajax({
		url: '<?php echo site_url('plugin/doUpload') ?>',
		dataType: 'json',
		cache: false,
		contentType: false,
		processData: false,
		data: form_data,                         
		type: 'post',
		success: function(response){
			console.log(response);
			if(response.installed == true){
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
			}
		}
	 });
}
</script>
