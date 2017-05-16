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
if( !isset($type) ) $type = 'mill';
if( !isset($store_position_url) ) $store_position_url = 'std/storePosition/'.$type;
if( !isset($stored_position) ) $stored_position = loadPosition($type);

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
		x : "<?php echo $stored_position['x'];?>",
		y : "<?php echo $stored_position['y'];?>",
		z : "<?php echo $stored_position['z'];?>"
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
		 
		console.log('jog_touch[prev]:', jog_touch);
		 
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
		
		// workaround for missing resize event on show
		$(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {
			jog_touch.jogtouch('resize');
		})
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
		jogFinishAction();
		jog_is_z_homed = true;
		$('.save-indication').show();
	}
	
	function jogFinishAction(e)
	{
		jog_busy = false;
	}
	
	function jogStorePosition()
	{
		
		console.log("Store Position");
		
		fabApp.jogGetPosition( function(e) {
			var tmp = e[0].reply[0].split(" ");
			var x = tmp[0].replace("X:","");
			var y = tmp[1].replace("Y:","");
			var z = tmp[2].replace("Z:","");
			
			console.log('position', x, y, z);
			
			var store = false;
			
			if(!jog_is_xy_homed)
			{
				x = "undefined";
				y = "undefined";
			}
			else
			{
				store = true;
				stored_position.x = x;
				stored_position.y = y;
			}
			
			if(!jog_is_z_homed)
			{
				z = "undefined";
			}
			else
			{
				store = true;
				stored_position.z = z;
			}
			
			data = {x:x, y:y, z:z};
			
			if(store)
			{
				$.ajax({
					type: 'post',
					data: data,
					url: '<?php echo site_url($store_position_url); ?>',
					dataType: 'json'
				}).done(function(data) {
					//~ console.log(response);
					if(data.result)
					{
						if(jog_is_xy_homed)
						{
							if(jog_is_z_homed)
								fabApp.showInfoAlert( _("Position {0}, {1}, {2} stored").format(x,y,z));
							else
								fabApp.showInfoAlert( _("X/Y Position {0}, {1} stored").format(x,y));
						}
						else if(jog_is_z_homed)
						{
							fabApp.showInfoAlert( _("Z Position {0} stored").format(z));
						}
					}
					else
					{
						fabApp.showErrorAlert( _("Failed to store position") );
					}
				})
			}
			
		});
	}
	
	function jogRestoreTo(x = '', y = '', z = '')
	{
		jog_busy = true;
		if( (z != '') && (z != null) && (z != undefined) )
		{
			if( ((x != '') && (x != null) && (x != undefined)) && ((y != '') && (y != null) && (y != undefined)) )
			{
				fabApp.jogMdi('G90\nG0 X'+x+' Y'+y+' Z'+z+' F5000\nM400', function(e){
					fabApp.showInfoAlert(_("Position restored to {0}, {1}, {2}").format(x, y, z) );
					jog_busy = false;
				});
			}
			else
			{
				fabApp.jogMdi('G90\nG0 Z'+z+' F5000\nM400', function(e){
					fabApp.showInfoAlert(_("Z Position restored to {0}").format(z) );
					jog_busy = false;
				});
			}
		}
		else
		{
			if( ((x != '') && (x != null) && (x != undefined)) && ((y != '') && (y != null) && (y != undefined)) )
			{
				fabApp.jogMdi('G90\nG0 X'+x+' Y'+y+' F5000\nM400', function(e){
					fabApp.showInfoAlert(_("X/Y position restored to {0}, {1}").format(x, y) );
					jog_busy = false;
				});
			}
		}
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
		var extruderFeed = $("#4thaxis-feedrate").length > 0 ? $("#4thaxis-feedrate").val() : 300;
		var waitForFinish= true;
		
		console.log(e.action);
		
		switch(e.action)
		{
			case "zero":
				jogStorePosition();
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
				if(jog_is_xy_homed)
				{
					var have_x = stored_position.x != "undefined";
					var have_y = stored_position.y != "undefined";
					if(have_x && have_y)
					{
						console.log('restore:', stored_position);
						jogRestoreTo(stored_position.x, stored_position.y);
					}
					else
					{
						fabApp.showErrorAlert(_("No X/Y position was stored"));
					}
				}
				else
				{
					fabApp.showWarningAlert(_("You need to home X/Y axis first"));
				}
				break;
			case "restore-z":
				if(jog_is_z_homed)
				{
					var have_z = stored_position.z != "undefined";
					if(have_z)
					{
						console.log('restore:', stored_position);
						jogRestoreTo('', '', stored_position.z);
					}
					else
					{
						fabApp.showErrorAlert(_("No Z position was stored"));
					}
				}
				else
				{
					fabApp.showWarningAlert(_("You need to home Z axis first"));
				}
				break;
		}
		
		return false;
	}
	

</script>
