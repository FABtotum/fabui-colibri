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
		checkBundleStatus();
	});	
	function checkBundleStatus()
	{
		$(".label-status").html('<i class="fa fa-spin fa-spinner"></i> checking system');
		$.ajax({
			type: "POST",
			url: "<?php echo site_url('updates/bundleStatus') ?>",
			dataType: 'json'
		}).done(function(response) {
			$(".label-status").html('<i class="fa fa-check"></i> check complete');

			var html = '<ul class="list-unstyled text-left">';
			$.each(response, function(i, item) {

				html += '<li>' + i +'</li>';
				
			    console.log(i);
			});
			html += '</ul>';
			$(".label-status").append(html);
			console.log(response);
		});
	}
</script>
