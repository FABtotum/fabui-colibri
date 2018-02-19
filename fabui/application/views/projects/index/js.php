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

	$(document).ready(function(){
		get_projects(0);
		$(".sync").on('click', function(){
			get_projects(1);
		});
	});
	
</script>
