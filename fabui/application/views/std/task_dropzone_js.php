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
	});
	/**
	* gotoWizardStep(2);
	**/
	function initDropZone()
	{	
		disableButton("#dropzone-make");
		disableButton("#dropzone-cancel");
		Dropzone.autoDiscover = false;
		filesDropzone = $(document.body).dropzone({
			url: "<?php echo site_url("projectsmanager/uploadFile") ?>", // Set the url
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
			 	this.on("uploadprogress", function(file, progress) { console.log(progress); });
			 	this.on("complete", function(file){
				 	console.log(file);
				 	var response = jQuery.parseJSON(file.xhr.response);
				 	if(response.upload == true){
				 		idFile = response.fileId;
				 		enableButton("#dropzone-make");
				 		fileFromDropzone = true;
					}
				 	
			 	})
			}   
		});
	}
	/**
	*
	**/
	function showDropzoneModal()
	{
		$('#dropzone-modal').modal({
			keyboard : false
		});
	}
	/**
	* 
	**/
	function dropzoneMakeButton()
	{
		gotoWizardStep(2);
		$('#dropzone-modal').modal('hide')
	}
	/**
	*
	**/
	function cancelFileUpload()
	{
		
	}
</script>