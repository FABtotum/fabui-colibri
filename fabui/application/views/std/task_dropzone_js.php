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

	$(document).ready(function() {
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
		Dropzone.autoDiscover = false;
		filesDropzone = $(document.body).dropzone({
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
					$(".dropzone-file-name").html(file.name);
					showDropzoneModal();
					done();
				}
			},
			init: function() {
				filesDropzone = this;
			 	this.on("addedfile", function(file) {});
			 	this.on("uploadprogress", function(file, progress) { 
				 	$(".dropzone-file-upload-percent").html(parseInt(progress) + " %");
				 	$(".dropzone-progress-bar").attr("style", "width:"+parseInt(progress)+"%");
				 });
			 	this.on("complete", function(file){
				 	
			 		
				 	var response = jQuery.parseJSON(file.xhr.response);
				 	if(response.upload == true){
					 	
				 		$(".dropzone-upload-label").html("<?php echo _("Uploaded"); ?>");
				 		$(".dropzone-file-upload-percent").html('<i class="fa fa-check"></i>');
				 		idFile = response.fileId;
				 		enableButton("#dropzone-make");
				 		enableButton("#dropzone-cancel");
				 		fileFromDropzone = true;
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
		disableButton("#dropzone-make");
		disableButton("#dropzone-cancel");
		$(".dropzone-progress-bar").attr("style", "width:0%");
		$(".dropzone-upload-label").html("<i class='fa fa-upload'></i> <?php echo _("Uploading"); ?>");
		$('#dropzone-modal').modal({
			backdrop : 'static'
		});
	}
	/**
	* 
	**/
	function dropzoneMakeButton()
	{
		<?php if($type == 'print'):?>
		startTask();
		<?php endif; ?>
		$('#dropzone-modal').modal('hide')
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
			console.log(response);
		})
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