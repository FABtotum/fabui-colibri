<?php
/**
 * 
 * @author Krios Mane
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>
<script type="text/javascript">

	var uploadDropZone;
	
	$(document).ready(function(){
		initEvents();
		initUploadBackupDropzone();
	});

	/**
	*
	**/
	function initEvents()
	{
		$(".action").on('click', handleAction);
			
		$(":radio[name='backup_mode']").change(function() {
			if($(this).filter(':checked').val() == 'advanced'){
				$("#advanced-backup-fields").slideDown();
			}else{
				$("#advanced-backup-fields").slideUp();
			}
		});
	}
	/**
	*
	**/
	function initUploadBackupDropzone()
	{
		disableButton("#upload-button");
		$("div#upload-backup-dropzone").dropzone({
			url: "<?php echo site_url('backup/upload') ?>",
			addRemoveLinks : true, 
			dictDefaultMessage: '<span class="text-center"><span class="font-lg visible-lg-block visible-md-block visible-sm-block visible-xs-block dictDefaultMessage"><span class="font-lg"><i class="fa fa-caret-right text-danger"></i> <?php echo _("Drop files to upload") ?> </span> <span>&nbsp&nbsp<h4 class="display-inline"> (<?php echo _("or click") ?>)</h4></span>',
			acceptedFiles: ".faback",
			autoProcessQueue: false,
			maxFilesize : <?php echo ($max_upload_file_size/1024); ?>,
			maxFiles: 1,
			dictRemoveFile: "<?php echo _("Remove file");?>",
			dictMaxFilesExceeded: "<?php echo  _("You can upload just {{maxFiles}} file at time"); ?>", 
			init: function(){
				uploadDropZone = this;
				/**
				*
				**/
				this.on("addedfile", function(file){
					enableButton("#upload-button");
				});
				/**
				*
				**/
				this.on("error", function(file, errorMessage){
					fabApp.showErrorAlert("<?php echo _("File error"); ?>", errorMessage);
					disableButton("#upload-button");
					uploadDropZone.removeFile(file);
				});
				/**
				*
				**/
				this.on("removedfile", function(file){
					if(uploadDropZone.files.length ==  1){
						if(uploadDropZone.files[0].status == "queued"){
							enableButton("#upload-button");
						}
					}else{
						disableButton("#upload-button");
					}					
				});
				/**
				*
				**/
				this.on("success", function(file) {
					
					if(file.hasOwnProperty("xhr")){
						var response = jQuery.parseJSON(file.xhr.response);
						processUpload(response);
					}
				});
			}
		});
	}
	/**
	*
	**/
	function processUpload()
	{
		
	}
	/**
	*
	**/
	function handleAction()
	{
		var button = $(this);
		var action = $(this).attr('data-action');
		switch(action){
			case 'backup':
				doBackUp();
				break;
			case 'upload':
				doUpload();
				break;
		}
	}

	/**
	*
	**/
	function doBackUp()
	{
		$('#backupModal').modal('hide');
		openWait('<i class="fa fa-cog fa-spin"></i> ' +  _("Preparing backup"), _('Please wait..'), false);
		disableButton('.action');
		
		var data = {};
		
		data.mode = $(":radio[name='backup_mode']").filter(':checked').val();
		data.advanced = [];
		
		$.each($(".backup-folders"), function( index, value ) {
			if($(value).is(':checked')){
				data.advanced.push($(value).val());
			}
		});
		
		data.advanced = data.advanced.join(",");
		data.firmware = $("#firmware").is(':checked');
		
		$.ajax({
			type: 'post',
			url: '<?php echo site_url('backup/doBackup'); ?>',
			dataType: 'json',
			data: data
		}).done(function(response) {
			enableButton('.action');
			openWait('<i class="fa fa-check"></i> ' +  _("Backup ready"), _('Please wait..'), false);
			setTimeout(function(){
				document.location.href = "<?php echo site_url('backup/download')?>/" + response.file;
				closeWait();
			}, 3000);
			
		});
	}

	/**
	*
	**/
	function doUpload()
	{
		if(uploadDropZone.getQueuedFiles().length > 0){
			//start upload
			uploadDropZone.processQueue();
		}
	}
</script>