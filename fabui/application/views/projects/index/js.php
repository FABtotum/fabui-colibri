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

	default_limit = <?php echo $default_limit; ?>;
	default_offset = <?php echo $default_offset; ?>;
	$(document).ready(function(){
		get_projects(0);
		$(".sync").on('click', function(){
			get_projects(1);
		});
		$("#load-more-button").on('click', function(){
			var limit = $(this).attr('data-attribute-limit');
			var offset = $(this).attr('data-attribute-offset');
			get_projects(0, limit, offset);
		});
			
	});
	
</script>
