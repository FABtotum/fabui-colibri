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
		$("#start-head-install-intro").on('click', start_head_install_intro);
	});
	
	function start_head_install_intro()
	{
		console.log('start_head_install_intro');
		
		startTour("head");
		
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
