<?php
/**
 * 
 * @author Krios Mane
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
 /* variable initialization */

if(!isset($extruder_min)) 	$extruder_min = 0;
if(!isset($extruder_max)) 	$extruder_max = 250;
if(!isset($bed_min)) 		$bed_min = 10;
if(!isset($bed_max)) 		$bed_max = 100;

?>
<script type="text/javascript">

	/* sliders */
	var fanSlider;
	
	/* jog */
	var jog_touch;
	var jog_controls;
	var jog_is_xy_homed = false;
	var cold_extrustion_enabled = false;
	var jog_busy = false;
	var touch_busy = false;
	var search_filter = 'gcode';
	var extruder_mode = 'none';

	$(document).ready(function() {
		$(".cold-extrusion").on("click", changeColdExtrusion);
		$(".extrude").on("click", extrude);
		
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
		
		$("#mdiButton").on("click", sendMdiCommands);
		$("#clearButton").on('click', clearJogResponse);
		$("#mdiCommands").on('keydown', handleMdiInputs);
		
		var controls_options = {
			hasZero:true,
			hasRestore:true,
			compact:false,
			percentage:0.85,
			onaction:jogAction
		};
		
		jog_controls = $('.jog-container').jogcontrols(controls_options);
		jog_controls.on('action', jogAction);
		
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
					writeJogResponse(e);
					touch_busy = false;
				});
					
				return true;
			}
		 };
		 
		 jog_touch =  $('.bed-image').jogtouch(touch_options);
		 
		 $('.touch-home-xy').on('click', function(e) {
			
			$('.touch-home-xy').addClass('disabled');
			fabApp.jogHomeXY(jogHomeXYCallback);
			return false;
		 });
		
		initSliders();
		initHelpSearch();
	});
	
	function selectFilter()
	{
		search_filter = $(this).attr('data-attr');
		
		var new_text = search_filter=='gcode'?"Search for a code ...":"Search in the description ...";
		
		$("#fa-icon-search").attr("placeholder", new_text);
	}
	
	function initHelpSearch()
	{
		$("#fa-icon-search").keyup(function() {
			var search = $.trim(this.value);
			
			if (search === "") {
				$(".code").show();
			}
			else {
				hide_divs(search.toUpperCase());
			}
		});
		
		$(".filter-select").on('click', selectFilter);
	}

	function hide_divs(search) {
		
		$(".code").hide(); 
		$(".collapse").collapse('hide'); 

		$(".code").each(function(index, value) {
			
			if( (typeof $(this).attr('data-attr') !== typeof undefined) && ( $(this).attr('data-attr') != false) )
			{
				var search_content = $(this).attr('data-attr');
				
				if(search_filter == 'desc')
				{
					var desc_query = "#" + $(this).attr('data-attr') + "-desc";
					search_content = $(desc_query).html();
				}
				
				if(search_content)
				{
					if( search_content.toUpperCase().indexOf(search) > -1 )
						$(this).show();
				}
			}
			
		});
	}
	
	function changeColdExtrusion(e)
	{
		var action = $(this).attr('data-attribute');
		if(action == "off")
		{
			$.SmartMessageBox({
				title: "<h4><span class='txt-color-orangeDark'><i class='fa fa-warning fa-2x'></i></span>&nbsp;&nbsp; Turning the cold extrusion protection might be dangerous if the fillament has not been removed from the head already.<br>Do you want to disable cold extrusion?</h4>",
				buttons: '[No][Yes]'
			}, function(ButtonPressed) {
			   
				if (ButtonPressed === "Yes")
				{
					fabApp.jogMdi("M302", writeJogResponse);
					cold_extrustion_enabled = true;
				}
				if (ButtonPressed === "No")
				{
					/* do nothing */
				}
			});
		}
		else
		{
			fabApp.jogMdi("M302 S175", writeJogResponse);
		}
	}
	
	function rotation(e)
	{
		var extruderFeed = $("#4thaxis-feedratee").length > 0 ? $("#4thaxis-feedrate").val() : 800;
		if(extruder_mode != '4thaxis')
		{
			// init 4th axis
			fabApp.jogSetExtruderMode('4thaxis', writeJogResponse);
			extruder_mode = '4thaxis';
		}

		fabApp.jogMdi("M82\nG0 E"+e+" F"+extruderFeed, writeJogResponse);
	}

	function extrude(e)
	{
		var sign = $(this).attr('data-attribute-type');
		var extruderStep = $("#extruderStep").length      > 0 ? $("#extruderStep").val()      : 10;
		var extruderFeed = $("#extruder-feedrate").length > 0 ? $("#extruder-feedrate").val() : 300;
		
		if(extruder_mode != 'extruder')
		{
			// init extruder
			fabApp.jogSetExtruderMode('extruder', writeJogResponse);
			extruder_mode = 'extruder';
		}
		
		console.log('Extrude', sign, extruderStep, extruderFeed);
		
		fabApp.jogMdi("M83\nG0 E"+sign+""+extruderStep+" F"+extruderFeed, writeJogResponse);
	}

	function initSliders()
	{
		//extruder target
		if(typeof extruderSlider == "undefined")
		{
			noUiSlider.create(document.getElementById('create-ext-target-slider'), {
				start: typeof (Storage) !== "undefined" ? localStorage.getItem("nozzle_temp_target") : <?php echo $extruder_min; ?>,
				connect: "lower",
				range: {'min': <?php echo isset($extruder_min) ? $extruder_min : 0; ?>, 'max' : <?php echo $extruder_max; ?>},
				pips: {
					mode: 'values',
					values: [0, 175, 250],
					density: 4,
					format: wNumb({
						postfix: '&deg;'
					})
				}
			});
		}
		//bed target slider
		if(typeof bedSlider == "undefined")
		{
			noUiSlider.create(document.getElementById('create-bed-target-slider'), {
				start: typeof (Storage) !== "undefined" ? localStorage.getItem("bed_temp_target") : 0,
				connect: "lower",
				range: {'min': <?php echo $bed_min; ?>, 'max' : <?php echo $bed_max; ?>},
				pips: {
					mode: 'positions',
					values: [0,25,50,75,100],
					density: 5,
					format: wNumb({
						postfix: '&deg;'
					})
				}
			});
		}
		//fan slider
		if(typeof fanSlider == "undefined")
		{
			noUiSlider.create(document.getElementById('create-fan-slider'), {
				start: 0,
				connect: "lower",
				range: {'min': 0, 'max' : 100},
				pips: {
					mode: 'positions',
					values: [0,50,100],
					density: 10,
					format: wNumb({})
				}
			});
		}
		//rpm slider
		if(typeof rpmSlider == "undefined")
		{
			noUiSlider.create(document.getElementById('create-rpm-slider'), {
				start: <?php echo isset($rpm_min) ? $rpm_min : 6000; ?>,
				connect: "lower",
				range: {'min': <?php echo isset($rpm_min) ? $rpm_min : 6000; ?>, 'max' : <?php echo isset($rpm_max) ? $rpm_max : 14000; ?>},
				pips: {
					mode: 'positions',
					values: [0,20,40,60,80,100],
					density: 10,
					format: wNumb({})
				}
			});
			rpmSlider = document.getElementById('create-rpm-slider');
		}
		
		fanSlider      = document.getElementById('create-fan-slider');
		
		extruderSlider = document.getElementById('create-ext-target-slider');
		bedSlider      = document.getElementById('create-bed-target-slider');
		//~ flowRateSlider = document.getElementById('create-flow-rate-slider');
		fanSlider      = document.getElementById('create-fan-slider');
		
		//fan
		/*fanSlider.noUiSlider.on('change', function(e){
			onChange('fan', e);
		});
		fanSlider.noUiSlider.on('slide', function(e){
			onSlide('fan', e);
			wasFanSliderMoved = true;
		});*/
		/*fanSlider.noUiSlider.on('end', function(e){
			isFanSliderBusy = false;
		});
		fanSlider.noUiSlider.on('start', function(e){
			isFanSliderBusy = true;
			wasFanSliderMoved = true;
		});*/
	}
	
	function unlock_touch()
	{
		jog_is_xy_homed = true;
		jog_touch.jogtouch('enable');
		$('.button_container').slideUp();
		$('[data-toggle="tooltip"], .tooltip').tooltip("hide");
		
		jog_touch.jogtouch('cursor',2,2);
	}
	
	function writeJogResponse(e)
	{
		if($(".jogResponseContainer2").length > 0){
			var html = '';
			$.each(e, function(i, item) {
				console.log(item.reply);
				//~ html += '<span class="jog_response ">' + item.code + ' : <small>' + item.reply + '</small> </span><hr class="simple">';
				html += '<span class="jog_response code">' + item.code + '</span>';
				
				$.each( item.reply.split('\n'), function(ii, line) {
					var tmp = line.trim();
					var cls = "jog_response reply";
					if(tmp.startsWith('ok'))
					{
						line = line.replace(/ok/g, '<span class="ok">ok</span>');
					}
					html += '<br><span class="'+cls+'"><small>' + line + '</span>';
				});
				
				html += '<hr class="simple">';
			});
			
			$(".consoleContainer").append(html);
			if( $('.consoleContainer').height() > 150 )
				$(".jogResponseContainer2").animate({ scrollTop: $('.jogResponseContainer2').prop("scrollHeight")}, 200);
		}
		
		jog_busy = false;
	}

	function jogZeroAllCallback(e)
	{
		writeJogResponse(e);
		
		if(jog_is_xy_homed)
		{
			jog_touch.jogtouch('zero');
		}
	}
	
	function jogHomeXYCallback(e)
	{
		writeJogResponse(e);
		unlock_touch();
	}
	
	function jogMoveCallback(e)
	{
		writeJogResponse(e);
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
		
		switch(e.action)
		{
			case "zero":
				fabApp.jogGetPosition( function(e) {
					var tmp = e[0].reply.split(" ");
					var x = tmp[0].replace("X:","");
					var y = tmp[1].replace("Y:","");
					console.log('jog_position', x, y);
				});
				
				fabApp.jogZeroAll(jogZeroAllCallback);
				
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
				fabApp.jogMove(e.action, xyStep*mul, xyzFeed, waitForFinish, jogMoveCallback);
				break;
			case "z-down":
			case "z-up":
				jog_busy = true;
				fabApp.jogMove(e.action, zStep*mul, xyzFeed, waitForFinish, writeJogResponse);
				break;
			case "home-xy":
				fabApp.jogHomeXY(jogHomeXYCallback);
				break;
			case "home-z":
				fabApp.jogHomeZ(writeJogResponse);
				break;
			case "home-xyz":
				fabApp.jogHomeXYZ(jogHomeXYCallback);
				break;
		}
		
		return false;
	}
	
	/**
	 *
	 */
	function sendMdiCommands()
	{
		fabApp.jogMdi( $("#mdiCommands").val(), writeJogResponse);
	}
	/**
	 * clear jog response file
	 */
	function clearJogResponse()
	{
		$(".consoleContainer").html('');
		$(".jogResponseContainer2").animate({ scrollTop: 0}, 200);
	}
	
	/**
	 * handle mdi key inputs
	 */
	function handleMdiInputs(e)
	{
		var code = e.keyCode ? e.keyCode : e.which;
		if($('#enterSend').is(":checked"))
		{
			if(code == 13)
			{ 
				/* enter key */
				if(!e.shiftKey)
				{
					sendMdiCommands();
					e.preventDefault();
					return false;
				}
			}
		}
	}
	    
</script>
