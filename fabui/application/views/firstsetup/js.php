<?php
/**
 * 
 * @author Daniel Kesler
 * @version 1.0
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
/* variable initialization */
?>

<script type="text/javascript">
	
	$(document).ready(function() {
		$(".start-tour").on('click', start_tour);
		
		endTour();
	});
	
	function start_tour()
	{
		var tour_type = $(this).attr("data-attr");
		startTour(tour_type);
		console.log(tour_type);
	}
	
</script>
