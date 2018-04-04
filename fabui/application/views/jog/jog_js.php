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
if(!isset($bed_min)) 		$bed_min = 0;
if(!isset($bed_max)) 		$bed_max = 100;
if(!isset($rpm_min)) 		$rpm_min = 6000;
if(!isset($rpm_max)) 		$rpm_max = 14000;

?>
<script type="text/javascript">

	/* sliders */
	var fanSlider;
	var isFanSliderBusy = false;
	var wasFanSliderMoved = false;
	
	var rpmSlider;
	var counterRpmSlider;
	var isRpmSliderBusy = false;
	var isCounterRpmSliderBusy = false;
	var wasRpmSliderMoved = false;
	var wasCounterRpmSliderMoved = false;
	
	var bedSlider;
	var isBedSliderBusy = false;
	var wasBedSliderMoved = false;
	
	var extruderSlider;
	var isExtSliderBusy = false;
	var wasExtSliderMoved = false;
	
	/* jog */
	var jog_touch;
	var jog_controls;
	var jog_is_xy_homed = false;
	var cold_extrustion_enabled = false;
	var jog_busy = false;
	var search_filter = 'gcode';
	var extruder_mode = 'none';

	var waitForAutoComplete = false;

	$(document).ready(function() {

		$('#mdiCommands').textcomplete([
			{
				id: 'shortcuts',
		       	words: [<?php foreach($shortcuts as $key => $value):?>"<?php echo str_replace('!', '', $key) ?>",<?php endforeach;?>],
		       	match: /\B!([\-+\w]*)$/,
		        search: function (term, callback) {
		        	term = term.toUpperCase();
		            callback($.map(this.words, function (word) {
		                return word.indexOf(term) === 0 ? word : null;
		            }));
		        },
		        index: 1,
		        replace: function (word) {
		            return '!' + word;
		        }
		    }],
		    {
				debounce : 10,
		    	onKeydown: function (e, commands) {
			    	waitForAutoComplete = true;
			        if (e.ctrlKey && e.keyCode === 74) {
			            return commands.KEY_ENTER;
			        }
		    	}
			});
		
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
			hasRestore:false,
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
				var x = Math.round(e.x, 3);
				var y = Math.round(e.y, 3);
				
				if(jog_busy)
					return false;
					
				jog_busy = true;
				fabApp.jogMdi('G90\nG0 X'+x+' Y'+y+' F'+$("#xyzFeed").val() +'\nM400', function(e){
					writeJogResponse(e);
					jog_busy = false;
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
	
	window.updateTemperatures = function(ext_temp, ext_temp_target, bed_temp, bed_temp_target)
	{
		updateExtTarget(ext_temp_target);
		updateBedTarget(bed_temp_target);
	}
	
	function selectFilter()
	{
		search_filter = $(this).attr('data-attr');
		
		var new_text = search_filter=='gcode'?"<?php echo _("Search for a code"); ?> ...":"<?php echo _("Search in the description"); ?> ...";
		
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

	function initSliders()
	{
		<?php if($headPrintSupport): ?>
		//extruder target
		if(typeof extruderSlider == "undefined")
		{
			noUiSlider.create(document.getElementById('create-ext-target-slider'), {
				start: <?php echo $extruder_min; ?>,
				connect: "lower",
				range: {'min': <?php echo $extruder_min; ?>, 'max' : <?php echo $extruder_max; ?>},
				pips: {
					mode: 'values',
					values: [0, 175, <?php echo $extruder_max ?>],
					density: 4,
					format: wNumb({
						postfix: '&deg;'
					})
				}
			});
			extruderSlider = document.getElementById('create-ext-target-slider');
			extruderSlider.noUiSlider.on('slide',  function(e){
				onSlide('extruder-target', e);
				wasExtSliderMoved = true;
			});
			extruderSlider.noUiSlider.on('change', function(e){
				onChange('extruder-target', e);
			});
			extruderSlider.noUiSlider.on('end', function(e){
				isExtSliderBusy = false;
			});
			extruderSlider.noUiSlider.on('start', function(e){
				isExtSliderBusy = true;
				wasExtSliderMoved = true;
			});
		}
		<?php endif; ?>
		
		<?php if($haveBed): ?>
		//bed target slider
		if(typeof bedSlider == "undefined")
		{
			noUiSlider.create(document.getElementById('create-bed-target-slider'), {
				start: 0,
				connect: "lower",
				range: {'min': <?php echo $bed_min; ?>, 'max' : <?php echo $bed_max; ?>},
				pips: {
					mode: 'positions',
					values: [0,25,50,75,<?php echo $bed_max; ?>],
					density: 5,
					format: wNumb({
						postfix: '&deg;'
					})
				}
			});
			bedSlider      = document.getElementById('create-bed-target-slider');
			bedSlider.noUiSlider.on('slide',  function(e){
				onSlide('bed-target', e);
				wasBedSliderMoved = true;
			});
			bedSlider.noUiSlider.on('change', function(e){
				onChange('bed-target', e);
			});
			bedSlider.noUiSlider.on('end', function(e){
				isBedSliderBusy = false;
			});
			bedSlider.noUiSlider.on('start', function(e){
				isBedSliderBusy = true;
				wasBedSliderMoved = true;
			});
		}
		<?php endif; ?>
		
		<?php if($headFanSupport): ?>
		//fan slider
		if(typeof fanSlider == "undefined")
		{
			noUiSlider.create(document.getElementById('create-fan-slider'), {
				start: 0,
				step: 5,
				connect: "lower",
				range: {'min': 0, 'max' : 100},
				pips: {
					mode: 'positions',
					values: [0,50,100],
					density: 10,
					format: wNumb({
						postfix: '%'
					})
				}
			});
			fanSlider      = document.getElementById('create-fan-slider');
			
			fanSlider.noUiSlider.on('change', function(e){
				onChange('fan', e);
			});
			fanSlider.noUiSlider.on('slide', function(e){
				onSlide('fan', e);
				wasFanSliderMoved = true;
			});
			fanSlider.noUiSlider.on('end', function(e){
				isFanSliderBusy = false;
			});
			fanSlider.noUiSlider.on('start', function(e){
				isFanSliderBusy = true;
				wasFanSliderMoved = true;
			});
		}
		<?php endif; ?>
		
		<?php if($headMillSupport): ?>
		//rpm slider
		if(typeof rpmSlider == "undefined")
		{
			noUiSlider.create(document.getElementById('create-rpm-slider'), {
				start: 0,
				connect: "lower",
				step: 100,
				range: {'min': 0, 'max' : <?php echo $rpm_max; ?>},
				pips: {
					mode: 'values',
					values: [6000,8000,10000,12000,<?php echo $rpm_max; ?>],
					density: 10,
					format: wNumb({})
				}
			});
			rpmSlider = document.getElementById('create-rpm-slider');
			
			rpmSlider.noUiSlider.on('change', function(e){
				onChange('rpm', e);
			});
			rpmSlider.noUiSlider.on('slide', function(e){
				onSlide('rpm', e);
				wasRpmSliderMoved = true;
			});
			rpmSlider.noUiSlider.on('end', function(e){
				isRpmSliderBusy = false;
			});
			rpmSlider.noUiSlider.on('start', function(e){
				isRpmSliderBusy = true;
				wasRpmSliderMoved = true;
			});
		}
		/*
		if(typeof counterRpmSlider == "undefined")
		{
			noUiSlider.create(document.getElementById('create-counter-rpm-slider'), {
				start: 0,
				connect: "lower",
				step: 100,
				range: {'min': 0, 'max' : <?php echo $rpm_max; ?>},
				pips: {
					mode: 'values',
					values: [6000,8000,10000,12000,<?php echo $rpm_max; ?>],
					density: 10,
					format: wNumb({})
				}
			});
			counterRpmSlider = document.getElementById('create-counter-rpm-slider');
			
			counterRpmSlider.noUiSlider.on('change', function(e){
				onChange('counter-rpm', e);
			});
			counterRpmSlider.noUiSlider.on('slide', function(e){
				onSlide('counter-rpm', e);
				wasCounterRpmSliderMoved = true;
			});
			counterRpmSlider.noUiSlider.on('end', function(e){
				isCounterRpmSliderBusy = false;
			});
			counterRpmSlider.noUiSlider.on('start', function(e){
				isCounterRpmSliderBusy = true;
				wasCounterRpmSliderMoved = true;
			});
		}
		*/
		<?php endif; ?>

	}
	
		/**
	 * event on slider slide
	 */
	function onSlide(element, value)
	{
		
		switch(element){
			case 'extruder-target':
				var extruder_target = parseInt(value);
				$(".slider-extruder-target").html(extruder_target);
				break;
			case 'bed-target':
				$(".slider-bed-target").html(parseInt(value));
				break;
			case 'fan':
				$('.slider-task-fan').html(parseInt(value));
				break;
			case 'rpm':
				if(parseInt(value) < <?php echo isset($rpm_min) ? $rpm_min : 6000; ?>)
					$('.slider-task-rpm').html("Off");
				else
					$('.slider-task-rpm').html(parseInt(value));
				break;
			case 'counter-rpm':
				if(parseInt(value) < <?php echo isset($rpm_min) ? $rpm_min : 6000; ?>)
					$('.slider-task-counter-rpm').html("Off");
				else
					$('.slider-task-counter-rpm').html(parseInt(value));
				break;
			
		}
	} 
	/**
	 * event on slider set
	 */
	function onChange(element, value)
	{
		switch(element){
			case 'extruder-target':
				fabApp.serial("setExtruderTemp", parseInt(value[0]), writeJogResponse);
				break;
			case 'bed-target':
				fabApp.serial("setBedTemp", parseInt(value[0]), writeJogResponse);
				break;
			case 'fan':
				//sendActionRequest('fan', parseInt(value[0]) );
				var pwm = Math.round( (parseInt(value[0]) * 255)/100);
				if(pwm == 0)
					fabApp.jogMdi("M107", writeJogResponse);
				else
					fabApp.jogMdi("M106 S"+pwm, writeJogResponse);
				break;
			case 'rpm':
				var rpm = parseInt(value[0]);
				if(rpm < <?php echo isset($rpm_min) ? $rpm_min : 6000; ?>)
					fabApp.jogMdi("M5", writeJogResponse);
				else
					fabApp.jogMdi("M3 S"+rpm, writeJogResponse);
				break;
			case 'counter-rpm':
				var rpm = parseInt(value[0]);
				if(rpm < <?php echo isset($rpm_min) ? $rpm_min : 6000; ?>)
					fabApp.jogMdi("M5", writeJogResponse);
				else
					fabApp.jogMdi("M4 S"+rpm, writeJogResponse);
					
				break;
		}
	}
	
	/**
	*
	*/
	function showActionAlert(message)
	{
		$.smallBox({
			title : "Info",
			content : message,
			color : "#5384AF",
			timeout: 3000,
			icon : "fa fa-check bounce animated"
		});
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
				title: "<h4><span class='txt-color-orangeDark'><i class='fa fa-exclamation-triangle fa-2x'></i></span>&nbsp;&nbsp; <?php echo _("Turning the cold extrusion protection off might be dangerous if the filament has not been removed from the head already"); ?>.<br><?php echo _("Do you want to disable cold extrusion?");?></h4>",
				buttons: '[No][Yes]'
			}, function(ButtonPressed) {
			   
				if (ButtonPressed === "Yes")
				{
					fabApp.jogMdi("M302 S0", writeJogResponse);
					cold_extrustion_enabled = true;
				}
				if (ButtonPressed === "No")
				{	
					fabApp.jogMdi("M302 S<?php echo $head_min_temp; ?>", writeJogResponse);
					cold_extrustion_enabled = false;
				}
			});
		}
		else
		{
			fabApp.jogMdi("M302 S<?php echo $head_min_temp; ?>", writeJogResponse);
			cold_extrustion_enabled = false;
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
		
		fabApp.jogMdi("G91\nG0 E"+sign+""+extruderStep+" F"+extruderFeed, writeJogResponse);
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
				html += '<span class="jog_response code">' + item.code + '</span>';
				
				$.each( item.reply, function(ii, line) {
					var tmp = line.trim();
					var cls = "jog_response reply";
					if(tmp.startsWith('ok'))
					{
						line = line.replace(/ok/g, '<span class="ok">ok</span>');
					}
					var smallClass = item.response == false ? 'txt-color-red' : '';
					html += '<br><span class="'+cls+'"><small class="' + smallClass + '">' + line + '</small></span>';
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
				fabApp.jogMove(e.action, xyStep*mul, xyzFeed, waitForFinish, writeJogResponse);
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
		if(waitForAutoComplete == true){
			setTimeout(function(){
				fabApp.jogMdi( $("#mdiCommands").val(), writeJogResponse);
			}, 10);
		}else{
			fabApp.jogMdi( $("#mdiCommands").val(), writeJogResponse);
			waitForAutoComplete = false;
		}
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
	
	function updateExtTarget(value)
	{
		if(!isExtSliderBusy){
			$('.slider-extruder-target').html(parseInt(value));
			if(typeof extruderSlider !== 'undefined'){
				extruderSlider.noUiSlider.set(value);
			}
		}
	}
	
	function updateBedTarget(value)
	{
		if(!isBedSliderBusy){
			$('.slider-bed-target').html(parseInt(value));
			if(typeof bedSlider !== 'undefined'){
				bedSlider.noUiSlider.set(value);
			}
		}
	}
	    
</script>
