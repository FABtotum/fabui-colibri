<script type="text/javascript">

	var editor;
	var contentLoaded = false;

	$(function () {
		
		<?php if($is_editable == True): ?>
		$("#load-content").on('click', load_file_content);
		<?php endif; ?>
		
		$(".button-action").on('click', button_actions);
		
		$("#save").on('click', save_file);
		
	});

	function load_file_content(){
		
		if(!contentLoaded){
			$(".btn").addClass('disabled');
			
			var download_url = "<?php echo 'http://'.$_SERVER['HTTP_HOST'].str_replace($upload_path, '/uploads/', $file['file_path'].'/'.urlencode($file['file_name']))."?t=".time() ?>";
			
			$.get( download_url, function( data ) {
				$("#editor").html(data);
				editor = ace.edit("editor");
				editor.getSession().setMode("ace/mode/gcode");
				editor.renderer.setShowPrintMargin(false);
				$("#file-content-title").html('Content');
				$("#editor").show();
				$(".btn").removeClass('disabled');
				$("#load-content").addClass('disabled');
				$("#also-content").removeAttr('disabled');
				contentLoaded = true;

				editor.getSession().on('change', function(e) {
	                $("#also-content").prop('checked', true);
	            });
	            
			 });
		}
	}

	function button_actions(){
			
		var action = $( this ).attr('data-action');
		
		if(action == "")
		{
			show_message("<?php echo _('Please select an action') ?>");
			return false;
		}
		
		switch(action)
		{
			case 'delete':
				ask_delete();
				break;
			case 'download':
				download_file();
				break;
		}
	}
	
	function delete_file()
	{
		$(".button-action").addClass("disabled");
		
		var list = new Array();
		list.push(<?php echo $file['id']; ?>);
		
		$.ajax({
				type: "POST",
				url: "<?php echo site_url('projectsmanager/deleteFiles') ?>",
				dataType: 'json',
				data: {ids: list}
			}).done(function(response) {
				if (response.success == true)
				{
					document.location.href = "<?php echo site_url('#projectsmanager/project/'. $object['id']); ?>";
					//~ location.reload();
				}
				else 
				{
					fabApp.showErrorAlert(response.message, '<?php echo _("Error deleting file");?>');
				}
				
				
			});
	}

	function ask_delete(ids){
		$.SmartMessageBox({
				title: "<i class='fa fa-exclamation-triangle txt-color-orangeDark'></i> <?php echo _('Warning') ?>!",
				content: "<?php echo _('Do you really want to remove this file') ?>",
				buttons: '[<?php echo _('No') ?>][<?php echo _('Yes') ?>]'
			}, function(ButtonPressed) {
				if (ButtonPressed === "<?php echo _('Yes') ?>")
				{
					delete_file();
				}
				if (ButtonPressed === "<?php echo _('No') ?>")
				{
					
				}
			});
	}

	
	function download_file(){
		document.location.href = '<?php echo 'projectsmanager/download/file/' . $file['id'] ?>';
	}

	function save_file(){
		$(".ace_editor").css('opacity', '0.1');   
		$('#save').addClass("disabled");
		$('#save').html('<i class="fa fa-spin fa-spinner"></i> <?php echo _('Saving') ?>');

		var note         = encodeURIComponent($.trim($("#note").val()));
		var name         = encodeURIComponent($.trim($("#name").val()));
		var file_content = '';

		var data = { 
				file_id: <?php echo $file['id']; ?>,  
				file_path : '<?php echo $file['full_path'] ?>', 
				note: note, 
				name: name};

		if($('#also-content').is(":checked")){
			data.file_content = encodeURIComponent($.trim(editor.getSession().getValue()));
		}

			
		$.ajax({
		  type: "POST",
		  url: "<?php echo site_url("projectsmanager/updateFile"); ?>",
		  data: data,
		  dataType: 'json'
		}).done(function( response ) {
			
			$.smallBox({
				title : "Success",
				content : "<i class='fa fa-check'></i> <?php echo _('The file was saved') ?>",
				color : "#659265",
				iconSmall : "fa fa-thumbs-up bounce animated",
				timeout : 4000
			});
			
			$('#save').removeClass("disabled");
			$('#save').html('<i class="fa fa-save"></i> <?php echo _('Save') ?>');
			$(".ace_editor").css('opacity', '1'); 
		  
		});

		return false;
	}

</script>
