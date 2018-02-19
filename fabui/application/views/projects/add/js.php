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
	main_form    = "#create-project-form";
	$(document).ready(function(){
		dropzones[counter] = initDropzone("#dropzone-part-0", "<?php echo site_url('projects/upload/file') ?>", "<?php echo implode(',', $accepted_files); ?>");
		
		$("#add-part").on('click', addPartForm);
		initValidator("#create-project-form");
		$("#save-project").on('click', function(){
			//saveProject("#create-project-form");
			startUpload("#create-project-form");
		});
		
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
	**/
	function addPartForm()
	{
		counter++;
		$.ajax({
			type: "POST",
			url: "<?php echo site_url('projects/getPartForm') ?>/" + counter,
			dataType: "html",
		}).done(function( response ) {
			$(".form-actions").before(response);
			initButtons();
			dropzones[counter] = initDropzone("#dropzone-part-"+counter, "<?php echo site_url('projects/upload/file') ?>", "<?php echo implode(',', $accepted_files); ?>");
			$('#create-project-form').bootstrapValidator('addField', $('[name="part-'+counter+'-name"]'));
			$('#create-project-form').bootstrapValidator('addField', $('[name="part-'+counter+'-description"]'));
			$('#create-project-form').bootstrapValidator('addField', $('[name="part-'+counter+'-tool"]'));
			
		});
	}

	/**
	*
	**/
	function removePart()
	{
		var index = $(this).attr('data-index');
		$("#project-part-" + index).remove();
		$('#create-project-form').bootstrapValidator('removeField', $('[name="part-'+index+'-name"]'));
		$('#create-project-form').bootstrapValidator('removeField', $('[name="part-'+index+'-description"]'));
		$('#create-project-form').bootstrapValidator('removeField', $('[name="part-'+index+'-tool"]'));
		dropzones[index].disable();
	}

</script>
