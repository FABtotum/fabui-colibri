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
	
	var angleStart = -360;
	var radius = 200;
	// jquery rotate animation
	function rotate(li,d) {
		$({d:angleStart}).animate({d:d}, {
			step: function(now) {
				var w = $(li).width();
				var h = $(li).height();
				$(li)
				   .css({ transform: 'rotate('+now+'deg) translate('+radius+'px, 0px) rotate('+(-now)+'deg)' });
			}, duration: 1000
		});
	}

	// show / hide the options
	function toggleOptions(s) {
		$(s).toggleClass('open');
		var li = $(s).find('li');
		var deg = $(s).hasClass('half') ? 180/(li.length-1) : 360/li.length;
		for(var i=0; i<li.length; i++) {
			var d = $(s).hasClass('half') ? (i*deg)-90 : i*deg;
			$(s).hasClass('open') ? rotate(li[i],d) : rotate(li[i],angleStart);
		}
	}
	
	$(document).ready(function() {
		endTour();
		
		$(".start-tour").on('click', start_tour);
		toggleOptions('.circular-layout');
		
	});
	
	function start_tour()
	{
		var tour_type = $(this).attr("data-attr");
		endTour();
		startTour(tour_type);
		console.log(tour_type);
	}
	
</script>
