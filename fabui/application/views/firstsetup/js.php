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
		var degOffset = 0;
		var li = $(s).find('li');
		var deg = ($(s).hasClass('half') ? 180/(li.length-1) : 360/li.length)  + degOffset;
		for(var i=0; i<li.length; i++) {
			var d = $(s).hasClass('half') ? (i*deg)-90 : i*deg;
			$(s).hasClass('open') ? rotate(li[i],d) : rotate(li[i],angleStart);
		}
	}
	
	function start_tour()
	{
		var tour_type = $(this).attr("data-attr");
		endTour();
		startTour(tour_type);
		console.log(tour_type);
	}
	
	$(document).ready(function() {
		endTour();
		
		tours_html = '<ul>';
		
		// @TODO: hard wired first-setup tours
		
		firstsetup_tours = ["head", "bed", "nozzle_assisted", "spool_load", "feeder"]
		
		var idx = 1;
		for(tour_id in firstsetup_tours)
		{
			
			tour_id = firstsetup_tours[tour_id];
			
			tour = available_tours[tour_id];
			
			if(tour.show != true)
				continue;
			
			tour_class = "btn-default";
			
			/*if( hasTourEnded(tour_id) )
			{
				tour_class = "btn-success";
			}*/
			
			tours_html += '<li class="round-item start-tour btn '+tour_class+'" data-attr="'+tour_id+'" data-placement="top" data-trigger="hover" data-rel="popover" title="'+tour.title+'" data-content="'+tour.description+'">';
			tours_html += '<label>'+tour.name+'</label>';
			tours_html += '<span class="icon">'+tour.icon+'</span>';
			if( hasTourEnded(tour_id) )
			{
				
				tours_html += '<span class="badge">Done</span>';
			}
			else
			{
				tours_html += '<span class="badge">'+idx+'</span>';
			}
			tours_html += '</li>';
			
			idx++;
	/*<ul>
		<li class="start-tour btn btn-default" data-attr="language">
			<label>Language</label>
			<span class="icon"><i class="fa fa-flag-o" aria-hidden="true"></i></span>
			<!--span class="mybadge label label-success"><i class="fa fa-check-circle-o" aria-hidden="true"></i></span-->
		</li>
		
		
		<li class="start-tour btn btn-default" data-attr="plugins"><label>Plugins</label><span class="icon"><i class="fa fa-plug" aria-hidden="true"></i></span></li>
		<li class="start-tour btn btn-default" data-attr="update"><label>Update</label><span class="icon"><i class="fa fa-refresh" aria-hidden="true"></i></span></li>
		<li class="start-tour btn btn-default" data-attr="nozzle_assisted"><label>Nozzle</label><span class="icon"><i class="fa fa-user-circle fa-4 icon" aria-hidden="true"></i></span></li>
		<li class="start-tour btn btn-default" data-attr="feeder" data-placement="top" data-trigger="hover" data-rel="popover" title="Popover Header" data-content="Some content inside the popover"><label>Feeder</label><span class="icon"><i class="fa fa-cog" aria-hidden="true"></i></span></li>
		<li class="start-tour btn btn-default" data-attr="head"><label>Head</label><span class="icon"><i class="fa fa-toggle-down" aria-hidden="true"></i></span></li>
		<li class="start-tour btn btn-default" data-attr="bed"><label>Bed</label><span class="icon"><!--i class="icon-fab-jog" aria-hidden="true"></i--></span></li>
		<li class="start-tour btn btn-default" data-attr="spool_load"><label>Spool</label><span class="icon"><!--i class="fa fa-user-circle fa-4 icon" aria-hidden="true"></i--></span></li>
		<li class="start-tour btn btn-default" data-attr="jog"><label>Jog</label><span class="icon"><i class="icon-fab-jog" aria-hidden="true"></i></span></li>
	</ul>*/
			

		}
		
		tours_html += '</ul>';
		$("#tours-container").html(tours_html);
		
		$("[rel=popover], [data-rel=popover]").popover();
		
		$(".start-tour").on('click', start_tour);
		toggleOptions('.circular-layout');
	});
	

	
</script>
