<?php
/**
 * 
 * @author Krios Mane
 * @version 1.0
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>
<script type="text/javascript">
	var filesDropzone;
	var fileAccepted = false;
	var fileType = '<?php echo $print_type?>';
	
	$(document).ready(function() {
		destroyDropZone();		
		initDropZone();
		$("#dropzone-make").on('click', dropzoneMakeButton);
		$("#dropzone-cancel").on('click', cancelFileUpload);
		<?php if($type == 'print'):?>
		$('input[name=dropzone-calibration]').on('click',dropZoneSetCalibration);
		<?php endif; ?>
	});
	/**
	* gotoWizardStep(2);
	**/
	function initDropZone()
	{
		/*
		if(dropZoneInitialized == true) {
			return;
		}
		if(typeof safety_result != "undefined" && safety_result == false) return;
		*/
		Dropzone.autoDiscover = false;
		
		$(document.body).dropzone({
			url: "<?php echo site_url("projectsmanager/uploadFile") ?>", // Set the url,
			parallelUploads: 1,
			maxFiles: 1,
			maxFilesize: <?php echo getUploadMaxFileSize(); ?>,
			accept: function(file, done){
				var acceptedFiles = "<?php echo $accepted_files; ?>";
				var ext = file.name.substr(file.name.lastIndexOf('.') + 1);
				if(acceptedFiles.indexOf(ext) == -1){
					fabApp.showErrorAlert("<?php echo _("You can't upload files of this type.") ?>");
					return false;
				}else{
					$(".dropzone-file-name").html('<strong>' + file.name + '</strong> <small>(' + humanFileSize(file.size) + ')</small> ');
					showDropzoneModal();
					done();
				}
			},
			init: function() {
				fabApp.dropZoneList.push(this);
				
			 	this.on("addedfile", function(file) {
				});

			 	this.on("uploadprogress", function(file, progress) {
				 					 	
				 	$(".dropzone-file-upload-percent").html(parseInt(progress) + " %");
				 	$(".dropzone-progress-bar").attr("style", "width:"+parseInt(progress)+"%");
				});
			 	this.on("complete", function(file){
				 	var response = jQuery.parseJSON(file.xhr.response);
					if(response.type == fileType ){
					 	if(response.upload == true){
					 		fileFromDropzone = true;
					 		idFile = response.fileId;
					 		setTimeout(function(){
						 		$(".dropzone-upload-label").html("<?php echo _("Uploaded"); ?>");
						 		$(".dropzone-file-upload-percent").html('<i class="fa fa-check"></i>');
						 		$("#dropzone-make").html("<?php echo $type_action; ?>");
						 		enableButton("#dropzone-make");
						 		enableButton("#dropzone-cancel");
					 		}, 1000);
						}
					}else{
						hideDropzoneModal();
						fabApp.showErrorAlert("<?php echo _("Invalid file.") ?>");
					}
				 	this.removeFile(file);
			 	})
			}   
		});
	}
	/**
	*
	**/
	function showDropzoneModal()
	{
		$("#dropzone-make").html(_("Wait") + '...');
		disableButton("#dropzone-make");
		disableButton("#dropzone-cancel");
		$(".dropzone-progress-bar").attr("style", "width:0%");
		$(".dropzone-upload-label").html("<i class='fa fa-upload'></i> <?php echo _("Uploading"); ?>");
		$('#dropzone-modal').modal({
			backdrop : 'static'
		});
	}
	/***
	*
	**/
	function hideDropzoneModal()
	{
		$('#dropzone-modal').modal('hide');
	}
	/**
	* 
	**/
	function dropzoneMakeButton()
	{
		<?php if($type == 'print'):?>
		startTask();
		<?php elseif($type == 'mill'): ?>
		gotoWizardStep(2);
		<?php endif; ?>
		$('#dropzone-modal').modal('hide');
	}
	/**
	* delte uploaded file
	**/
	function cancelFileUpload()
	{
		var data = {
			"ids": [idFile]
		};
		$.ajax({
			type: 'post',
			data: data,
			url: '<?php echo site_url("projectsmanager/deleteFiles") ?>',
			dataType: 'json'
		}).done(function(response) {
			fileFromDropzone = false;
		})
	}
	/**
	*
	**/
	function destroyDropZone()
	{	
		$.each(fabApp.dropZoneList, function( index, value ) {
			value.destroy();
		});
	}
	<?php if($type == 'print'):?>
	/**
	*
	*/
	function dropZoneSetCalibration()
	{
		$('input[name=calibration]').val($(this).val());
	}
	<?php endif; ?>
</script>