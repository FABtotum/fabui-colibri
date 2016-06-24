<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<script type="text/javascript">
	
	var filesDropzone;
	var numFiles = 0;
	var fileList = new Array();
	
	$(document).ready(function() {
		initDropzone();
		initValidate();
		$("#save").on('click', saveObject);
	});
	
	function initDropzone()
	{
		$("div#newObjectDropzone").dropzone({ 
			url: "<?php echo site_url('filemanager/uploadFile/') ?>",
			addRemoveLinks : true, 
			dictDefaultMessage: '<span class="text-center"><span class="font-lg visible-xs-block visible-sm-block visible-lg-block"><span class="font-lg"><i class="fa fa-caret-right text-danger"></i> Drops files <span class="font-xs">to upload</span></span><span>&nbsp&nbsp<h4 class="display-inline"> (or click)</h4></span>',
			parallelUploads: 1,
			uploadMultiple: false,
			acceptedFiles: '.gcode, .GCODE, .nc, .NC',
			autoProcessQueue: false,
			dictRemoveFile: 'Remove file',
			dictMaxFilesExceeded: 'You can upload 10 files at time',
			init: function(){
				filesDropzone = this;
				this.on("complete", function (file) {
					var response = jQuery.parseJSON(file.xhr.response);
					if(response.upload == true){
						fileList.push(response.fileId);
					}
					$(".result-" + file.name.replace(/[\s+|\,|\(|\)|\.]/g, '')).html('<i class="fa fa-check"></i>');
					if(this.getQueuedFiles().length > 0){
						this.processQueue(); 
					}else{
						submitForm();
					}
				}); 
				this.on("addedfile", function(file){
					if(numFiles == 0 && $("#name").val() == ''){
						$("#name").val(file.name);
					}
					numFiles ++;
				});
				this.on("removedfile", function(file){
					numFiles --;
				});
				this.on("uploadprogress", function(file, progress) {
					$("." + file.name.replace(/[\s+|\,|\(|\)|\.]/g, '')).attr('style', 'width:' + progress + '%');
				});
			}
		});
	}
	/**
	 * 
	 */
	function initValidate()
	{
		$("#object-form").validate({

			// Rules for form validation
			rules : {
				name : {
					required : true
				}
			},
			// Messages for form validation
			messages : {
				name : {
					required : 'Please enter object name'
				}
			},
			// Do not change code below
			errorPlacement : function(error, element) {
				error.insertAfter(element.parent());
			}
		});
	}
	/**
	 * 
	 */
	function saveObject()
	{
		if($("#object-form").valid()){
			if(filesDropzone.getQueuedFiles().length > 0){
				crateProgressBars();
				$('#progressModal').modal({
  					keyboard: false,
  					backdrop: 'static'
				});
				$("#progressModal").modal("show");
				filesDropzone.processQueue(); 
			}else{
				submitForm();
			}
		}
	}
	/**
	 * 
	 */
	function submitForm()
	{
		$("#filesID").val(fileList);
		openWait("Save Object");
		$("#object-form").submit();
	}
	
	/**
	 * 
	 */
	function crateProgressBars()
	{
		var html = '';
		$.each(filesDropzone.getQueuedFiles(), function(index, file){
			var className = file.name.replace(/[\s+|\,|\(|\)|\.]/g, '');
			html += '<p> ' + file.name  + '<span class="pull-right result-'+className+'"></span></p>';  
			html += '<div class="progress progress-xs"><div class="progress-bar ' + className  +'" role="progressbar" style="width: 0%"></div></div>';
		});
		$("#progressModalBody").html(html);
	}
		
</script>