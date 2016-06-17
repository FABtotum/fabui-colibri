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

	$(document).ready(function() {
		initUI();
		initEvents();
	});
	
	//
	function initUI()
	{	
	}
	
	//
	function initEvents()
	{
		$(':radio[name="settings_type"]').change(function() {
			var type = $(this).filter(':checked').val();
			if(type == 'custom'){
				$(".custom_settings").slideDown();
			}else{
				$(".custom_settings").slideUp();
			}
		});
		
		$("#save").on('click', saveSettings);	
	}
	
	//
	function saveSettings()
	{
		var data = {};
		$(".tab-content :input").each(function (index, value) {
			if($(this).is('input:text') || $(this).is('select') || $(this).is(':input[type="number"]') || ($(this).is('input:radio') && $(this).is(':checked')) ){
				data[$(this).attr('id')] = $(this).val();
			}
		});
		
		$.ajax({
			type: 'post',
			url: '<?php echo site_url('settings/saveSettings'); ?>',
			data : data,
			dataType: 'json'
		}).done(function(response) {
			
		});
		
		
		console.log(data);
	}
</script>