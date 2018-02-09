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
	var counter = 0;
	var dropzones = [];
	$(document).ready(function(){
		initDropzone('#dropzone-part-0');
		$("#add-part").on('click', addPartForm);
		$('#crate-project-form').bootstrapValidator();
		
	});

	/**
	*
	*/
	function initButtons()
	{
		$(".remove-part").on('click', removePart);
	}
	
	/**
	*
	*/
	function initDropzone(element)
	{
		dropzones[counter] = $("div" + element).dropzone({
			url: "<?php echo site_url('projects/upload/file') ?>",
			acceptedFiles: "<?php echo implode(',', $accepted_files); ?>",
			addRemoveLinks : true, 
			autoProcessQueue: false,
			dictRemoveFile: "<?php echo _("Remove file");?>",
			dictMaxFilesExceeded: "<?php echo  _("You can upload just {{maxFiles}} file at time"); ?>", 
			init: function(){
				
			}
		});

		console.log(dropzones);
	}

	/**
	*
	**/
	function addPartForm()
	{
		counter++;
		$.ajax({
			type: "POST",
			url: "<?php echo site_url('projects/getPartForm') ?>/" + counter,
			dataType: "html",
		}).done(function( response ) {
			$("#crate-project-form").append(response);
			initButtons();
			initDropzone("#dropzone-part-"+counter);
		});
	}

	/**
	*
	**/
	function removePart()
	{
		var index = $(this).attr('data-index');
		$("#project-part-" + index).remove();
	}
</script>
