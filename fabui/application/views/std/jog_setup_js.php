<?php
/**
 * 
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<script type="text/javascript">

	var jog_touch;
	var jog_controls;
	var jog_busy = false;
	var touch_busy = false;

	$(document).ready(function() {
		
		//~ $(".axisxy").on('click', moveXYZ);
		//~ $(".axisz").on('click', moveXYZ);
		$(".setzero").on('click', jogSetAsZero);
		
		$('.knob').knob({
			change: function (value) {
			},
			release: function (value) {
				rotation(value);
			},
			cancel: function () {
				console.log("cancel : ", this);
			}
		});
		
		$('.knob').keypress(function(e) {
			if(e.which == 13) {
				rotation($(this).val());
			}
		 });
		 
		 var touch_options = {
			guides: false,
			center: false,
			highlight: false,
			background: false,
			disabled: true,
			
			left:2.0,
			right:212.0,
			top:232.0,
			bottom:2.0,
			
			cursorX:2,
			cursorY:2,
			
			touch: function(e) {
				console.log(e.x, e.y);
				
				var x = Math.round(e.x, 3);
				var y = Math.round(e.y, 3);
				
				if(touch_busy)
					return false;
					
				touch_busy = true;
				fabApp.jogMdi('G90\nG0 X'+x+' Y'+y+' F5000\nM400', function(e){
					touch_busy = false;
				});
					
				return true;
			}
		 };
		 
		 jog_touch =  $('.bed-image').jogtouch(touch_options);
		 
		 $('.touch-home-xy').on('click', function(e) {
			
			$('.touch-home-xy').addClass('disabled');
			fabApp.jogMdi('G28 X Y', function(e){
				unlock_touch();
				});
			return false;
		 });
		 
		var controls_options = {
			hasZero:true,
			hasRestore:true,
			compact:false
		};
		
		jog_controls = $('.jog-controls-holder').jogcontrols(controls_options);
		
		jog_controls.on('click', jogAction);
	});
	
	function unlock_touch()
	{
		jog_touch.jogtouch('enable');
		$('.button_container').slideUp();
		$('[data-toggle="tooltip"], .tooltip').tooltip("hide");
	}
	
	function rotation(value)
	{
		
	}
	
	function jogAction(e)
	{
		console.log("== jog_busy", jog_busy);
		if(jog_busy)
			return false;
		
		var xystep   = $("#xy-step").val();
		var zstep    = $("#z-step").val();
		var feedrate = $("#feedrate").val();
		var cmd      = '';
		var action = e.action;
		
		var mul = jog_controls.jogcontrols('getMultiplier');
		
		zstep *= mul;
		xystep *= mul;
		
		console.log('jog action', action, mul);
		
		switch(action)
		{
			case "z-down":
				cmd = 'G91\nG0 Z+'+zstep+' F'+feedrate;
				break;
			case "z-up":
				cmd = 'G91\nG0 Z-'+zstep+' F'+feedrate;
				break;
			case "right":
				cmd = 'G91\nG0 X+'+xystep+' F'+feedrate;
				break;
			case "left":
				cmd = 'G91\nG0 X-'+xystep+' F'+feedrate;
				break;
			case "up":
				cmd = 'G91\nG0 Y+'+xystep+' F'+feedrate;
				break;
			case "down":
				cmd = 'G91\nG0 Y-'+xystep+' F'+feedrate;
				break;
			case "down-right":
				cmd = 'G91\nG0 X+'+xystep+' Y-'+xystep+' F'+feedrate;
				break;
			case "up-right":
				cmd = 'G91\nG0 X+'+xystep+' Y+'+xystep+' F'+feedrate;
				break;
			case "down-left":
				cmd = 'G91\nG0 X-'+xystep+' Y-'+xystep+' F'+feedrate;
				break;
			case "up-left":
				cmd = 'G91\nG0 X-'+xystep+' Y+'+xystep+' F'+feedrate;
				break;
			case "home-xy":
				cmd = 'G28 X Y';
				unlock_touch();
				break;
			case "home-z":
				cmd = 'G27 Z';
				break;
			case "home-xyz":
				cmd = 'G27';
				unlock_touch();
				break;
		}
		
		if(cmd != '')
		{
			cmd += '\nM400';
			
			jog_busy = true;
			fabApp.jogMdi(cmd, function(e) {
				jog_busy = false;
			});
		}
		
		return false;
	}

</script>
