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

	$(document).ready(function(){
		initEvents();
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
	function handleAction()
	{
		var action = $(this).attr('data-action');
		switch(action){
			case 'backup':
				doBackUp();
				break;
		}
	}

	/**
	*
	**/
	function doBackUp()
	{
		var data = {};

		data.mode = $(":radio[name='backup_mode']").filter(':checked').val();
		
		$.ajax({
			type: 'post',
			url: '<?php echo site_url('backup/doBackup'); ?>',
			dataType: 'json',
			data: data
		}).done(function(response) {

		});
	}
</script>