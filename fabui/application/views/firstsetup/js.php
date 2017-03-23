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
	});
	
	function start_tour()
	{
		var tour_type = $(this).attr("data-attr");
		//console.log('start_head_install_intro');
		
		startTour(tour_type);
		
		/*if(tour == false)
		{
			
		}
		else
		{
			tour.restart();
			
			if( $("#menu-item-maintenance").parent().hasClass("open") )
			{
				tour.goTo(1);
			}
			tour.start(true);
		}*/
	}
	
</script>
