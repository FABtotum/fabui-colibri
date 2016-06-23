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
			//parallelUploads: 2,
			//maxFiles: 10,
			uploadMultiple: true,
			acceptedFiles: '.gcode, .GCODE, .nc, .NC',
			autoProcessQueue: false,
			dictRemoveFile: 'Remove file',
			dictMaxFilesExceeded: 'You can upload 10 files at time',
			init: function(){
				filesDropzone = this;
				this.on("complete", function (file) {
					console.log(file);
					if(this.getQueuedFiles().length > 0){
						this.processQueue(); 
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
		$("#object-form").submit();
	}
		
</script>