<?php
/**
 * 
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
/* variable initialization */
if( !isset($fourth_axis) ) $fourth_axis = False;
if( !isset($$stored_position) ) $stored_position = array("x" => "undefined", "y" => "undefined", "z" => "undefined");

?>
<script type="text/javascript">

	/* jog */
	var jog_touch;
	var jog_controls;
	var jog_is_xy_homed = false;
	var jog_is_z_homed = false;
	var cold_extrustion_enabled = false;
	var touch_busy = false;
	var jog_busy = false;
	var extruder_mode = 'none';
	
	var stored_position = {
		x : <?php echo $stored_position['x'];?>,
		y : <?php echo $stored_position['y'];?>,
		z : <?php echo $stored_position['z'];?>
	};

	$(document).ready(function() {
		
		<?php if($fourth_axis): ?>
		$('.knob').knob({
			//draw: draw_knob,
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
		<?php endif; ?>
		 
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
				var x = Math.round(e.x, 3);
				var y = Math.round(e.y, 3);
				
				if(jog_busy)
					return false;
					
				jog_busy = true;
				fabApp.jogMdi('G90\nG0 X'+x+' Y'+y+' F5000\nM400', function(e){
					jog_busy = false;
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
		jog_controls.on('action', jogAction);
	});
	
	function unlock_touch()
	{
		jog_is_xy_homed = true;
		jog_touch.jogtouch('enable');
		$('.button_container').slideUp();
		$('[data-toggle="tooltip"], .tooltip').tooltip("hide");
		jog_touch.jogtouch('cursor',2,2);
	}
	
	function rotation(value)
	{
		
	}
	
	function jogHomeXYCallback(e)
	{
		unlock_touch();
		jogFinishAction();
		$('.save-indication').show()
	}
	
	function jogHomeXYZCallback(e)
	{
		jogHomeXYCallback(e);
		jog_is_z_homed = true;
		$('.save-indication').show();
	}
	
	function jogHomeZCallback(e)
	{
		jog_is_z_homed = true;
		$('.save-indication').show();
	}
	
	function jogFinishAction(e)
	{
		jog_busy = false;
	}
	
	function jogAction(e)
	{
		if(jog_busy)
			return false;
			
		var mul          = e.multiplier;
		var xyStep       = $("#xyStep").length            > 0 ? $("#xyStep").val()            : 1;
		var zStep        = $("#zStep").length             > 0 ? $("#zStep").val()             : 0.5;
		var extruderStep = $("#extruderStep").length      > 0 ? $("#extruderStep").val()      : 10;
		var xyzFeed      = $("#xyzFeed").length           > 0 ? $("#xyzFeed").val()           : 1000;
		var extruderFeed = $("#extruder-feedrate").length > 0 ? $("#extruder-feedrate").val() : 300;
		var waitForFinish= true;
		
		console.log(e.action);
		
		switch(e.action)
		{
			case "zero":
				jogSetAsZero();
				break;
			case "right":
			case "left":
			case "up":
			case "down":
			case "down-right":
			case "up-right":
			case "down-left":
			case "up-left":
				jog_busy = true;
				if(jog_is_xy_homed)
					jog_touch.jogtouch('jogmove', e.action, xyStep*mul);
				fabApp.jogMove(e.action, xyStep*mul, xyzFeed, waitForFinish, jogFinishAction);
				break;
			case "z-down":
			case "z-up":
				jog_busy = true;
				fabApp.jogMove(e.action, zStep*mul, xyzFeed, waitForFinish, jogFinishAction);
				break;
			case "home-xy":
				if(jog_is_z_homed && !jog_is_xy_homed)
					fabApp.jogHomeXYZ(jogHomeXYZCallback);
				else
					fabApp.jogHomeXY(jogHomeXYCallback);
				break;
			case "home-z":
				fabApp.jogHomeZ(jogHomeZCallback);
				break;
			case "home-xyz":
				fabApp.jogHomeXYZ(jogHomeXYZCallback);
				break;
			case "restore-xy":
				fabApp.showInfoAlert("restore XY");
				if(jog_is_xy_homed)
				{
					var have_x = stored_position.x != "undefined";
					var have_y = stored_position.y != "undefined";
					if(have_x && have_y)
					{
						console.log('stoed:', stored_position);
					}
				}
				break;
			case "restore-z":
				if(jog_is_z_homed)
				{
					console.log('stoed:', stored_position);
				}
				break;
		}
		
		return false;
	}
	

</script>
