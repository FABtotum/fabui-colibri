<script type="text/javascript">
	
	$(function() {
		
		$("#install-button").on('click', function(){


			var pluginFile = $('#plugin-file').prop('files')[0];   
		    var form_data = new FormData();                  
		    form_data.append('plugin-file', pluginFile);
		    console.log(form_data);                             
		    $.ajax({
		                url: '<?php echo site_url('plugin/doUpload') ?>', // point to server-side PHP script 
		                dataType: 'text',  // what to expect back from the PHP script, if anything
		                cache: false,
		                contentType: false,
		                processData: false,
		                data: form_data,                         
		                type: 'post',
		                success: function(php_script_response){
		                    console.log(php_script_response)// display response from the PHP script, if any
		                }
		     });


			
		});
		
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
</script>
