<script type="text/javascript">
	
	var oTable;
	var recenTable;
	var print_type = '<?php echo $type; ?>';
	var request_file;
	var interval_autostart;
	var isEngageFeeder = 0;
	var ajax_endpoint  = '<?php echo module_url('create') ?>';
	var calibration = 'homing';
	var print_started = false;
	var blockSliderExt = false;
	var blockSliderBed = false;
	var blockSliderSpeed = false;
	var blockSliderFan = false;
	var blockSliderFlowRate = false;
	var print_started = <?php echo $running ? strtolower(json_decode(file_get_contents(json_decode(file_get_contents($task['attributes']), true)['monitor']), true)['print']['print_started']) : 'false' ?>;
	var id_task;
	var elapsed_time = 0;
	var attributes_file = '<?php echo $running ? $task['attributes'] : '' ?>';
	var data_file = '<?php echo $running ? json_decode(file_get_contents($task['attributes']), true)['data'] : '' ?>';
	var interval_monitor;
	var max_plot = 200;
	var progress_percent = 0;
	var speed = 100;
	var layer_actual;
	var layer_total;
	var speed_slider;
	var nozzle_slider;
	var bed_slider;
	var fan_slider;
	var flow_rate_slider;
	var rpm_slider;
	var nozzle_temperatures = [];
	var nozzle_target_temperatures = [];
	var bed_temperatures = [];
	var bed_target_temperatures = [];
	var speed_percent = 0;
	var request_file = parseInt('<?php echo $request_file; ?>');
	var request_obj  = parseInt('<?php echo $request_obj; ?>');
	var do_request_file = request_file > 0;
	var startFromRecent = false;
	
	$(document).ready(function() {
		
		//init tables
		oTable = $('#objects_table').dataTable({
			"aaSorting": [],
			"bFilter": true,
			"sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6 hidden-xs'f><'col-sm-6 col-xs-12 hidden-xs'<'toolbar'>>r>"+
				"t"+
				"<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
			"autoWidth": false,
		});
		
		recenTable = $('#recent_table').dataTable({
			"aaSorting": [],
			"bFilter": true,
			"sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6 hidden-xs'f><'col-sm-6 col-xs-12 hidden-xs'<'toolbar'>>r>"+
				"t"+
				"<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
			"autoWidth": false,
		});
		
		//events
		$(".recent-obj-file").on('click', function() {
			select_recent_file($(this).val());
		});
		
		$('.file-recent-row').on('click', select_file_recent_row);
		
		$("#turn-off").on('change', function(){
            _controls_listener($(this));
        });
        
        $('#print-button').on('click', function() {
			print_object();	
		});
		
		//wizard
		var wizard = $('.wizard').wizard({});
		//button next click
		$('#btn-next').on('click', function() {
			if(check_wizard_next()){
				var step = $('.wizard').wizard('selectedItem').step
				if(startFromRecent == true && step==1) $('.wizard').wizard('selectedItem', { step: 3 });
				else $('.wizard').wizard('next');
			}
		});
		//button prev click
		$('#btn-prev').on('click', function() {
			if(check_wizard_prev()){
				var step = $('.wizard').wizard('selectedItem').step
				if(startFromRecent == true && step==3) $('.wizard').wizard('selectedItem', { step: 1 });
				$('.wizard').wizard('previous');
			}
		});
		//wizard events
		$('.wizard').on('changed.fu.wizard', function (evt, data) {
			check_wizard();
		});
		$('.wizard').on('stepclick', function(e, data) {
			
			$('.wizard').wizard('selectedItem', { step: data.step });
			check_wizard();
		});
		
		//init sliders
		initSliders();
		
		//disable sliders
		disableControlsTab();
		
		$(".sliders").on({
			slide: manage_slide,
        	change: manage_change
	   	});
	   	
	   	$('.controls').on('click', function() {
			_controls_listener($(this));
		});
		
		$('#stop-button').on('click', ask_stop);
		
		$(".restart").on('click', restart_create);
       	$(".new").on('click', new_create);
       	$(".save-z-override").on('click', save_z_override);
       	
       	$(".obj").on('click', selectObj);
       	
       	<?php if($running): ?> resume(); <?php endif; ?>
       	
       	//if file si pre-selected
       	if(request_file > 0 && request_obj > 0 && <?php echo $running ? 'true' : 'false' ?> == false){
       		 var rows = oTable.fnGetNodes();
       		 $(rows).each(function() {
                if($(this).attr('data-id') == request_obj){
                    $(this).trigger('click');
                }
            });
            $("#btn-next").trigger('click');
            $("#btn-next").trigger('click');
       	}
       	
		
	});
	
	
	//enable/disable wizard button
	function check_wizard(){
    	var step = $('.wizard').wizard('selectedItem').step;    	
    	switch(step){
    		case 1:
    			disable_button("#btn-prev");
    			if(startFromRecent == true){
    				enable_button("#btn-next");	
    			}else if(typeof object != 'undefined'){
    				enable_button("#btn-next");
    			}
    			stopCountDown();
    			break;
    		case 2:
    			enable_button("#btn-prev");
    			if(typeof file_selected != 'undefined' && file_selected != '') enable_button("#btn-next");
    			else disable_button("#btn-next");
    			stopCountDown();
    			break;
    		case 3:    			
    			enable_button("#btn-prev");
    			disable_button("#btn-next");
    			if($('input[name="calibration"]').is(":visible")) startCountDown();
    			break;
    	}
    }
    //check if i can move to next step
    function check_wizard_next(){
    	var step = $('.wizard').wizard('selectedItem').step;
    	switch(step){
    		case 1:
    			if(request_file > 0) return true;
    			if(typeof object != 'undefined' && $("#table-objects").is(":visible")) return true;
    			if(startFromRecent == true && $("#recent_table").is(":visible")) return true;
    			break;
    		case 2:
    			if(request_file > 0) return true;		
    			if(typeof file_selected != 'undefined' && file_selected != '') return true;
    			break;
    		case 3:
    		case 4:
    			return true;
    			break;
    	}
    	return false;
    }
    //check if i can go back to prev step
    function check_wizard_prev(){
    	var step = $('.wizard').wizard('selectedItem').step;
    	switch(step){
    		case 2:
    			return true;
    			break;
    		case 3:
    			return true;
    			break;
    	}
    	return false;
    }
	
	//select file form recent table
	function select_recent_file(idFile){
		
		$(".model-info").remove();
		var recent_file = recent_files[idFile];
		
		id_file = idFile;
		id_object = recent_file.id_object;
		file_selected = recent_file;
		
		startFromRecent = true;
		
		if(recent_file.attributes != '' && recent_file.attributes != 'Processing'){
			$("#recent_table").after(model_info(recent_file));
		}
		
		$.ajax({
			url : '/fabui/create/show/' + print_type,
			cache : false
		}).done(function(html) {
			$("#step4").html(html);
		});
	
		resetTableObjects();
		enable_button('#btn-next');
		resetTableObjects();
	}
	
	function select_file_recent_row(){
		$(this).find(':first-child').find('input').prop("checked", true);
		var idFile = $(this).find(':first-child').find('input').val();
		$("#recent_table tbody tr").removeClass('success');
		$(this).addClass('success');
		select_recent_file(idFile);
	}
	
	
	//init all sliders
	function initSliders() {
	
		noUiSlider.create(document.getElementById('velocity'), {
			start: 100,
			connect: "lower",
			range: {'min': 0, 'max' : 500},
			pips: {
				mode: 'positions',
				values: [0,20,40,60,80,100],
				density: 10,
				format: wNumb({})
			}
		});
		
		<?php if($type == 'additive'): ?>
			noUiSlider.create(document.getElementById('fan'), {
				start: 255,
				connect: "lower",
				range: {'min': 50, 'max' : 100},
				pips: {
					mode: 'positions',
					values: [0,50,100],
					density: 10,
					format: wNumb({})
				}
			});
			
			noUiSlider.create(document.getElementById('flow-rate'), { 
				start: 100,
				connect: "lower",
				range: {'min': 0, 'max' : 500},
				pips: {
					mode: 'positions',
					values: [0,20,40,60,80,100],
					density: 10,
					format: wNumb({})
				}
			});
	
			noUiSlider.create(document.getElementById('temp1'), {
				start: 0,
				connect: "lower",
				range: {'min': 0, 'max' : 250},
				pips: {
					mode: 'positions',
					values: [0,25,50,75,100],
					density: 5,
					format: wNumb({
						postfix: '&deg;'
					})
				}
			});
			
			noUiSlider.create(document.getElementById('act-ext-temp'), {
				start: 0,
				connect: "lower",
				range: {'min': 0, 'max' : 250},
				behaviour: 'none'
			});
			
			
			
			$("#act-ext-temp .noUi-handle").remove();
			
			noUiSlider.create(document.getElementById('temp2'), {
				start: 0,
				connect: "lower",
				range: {'min': 0, 'max' : 100 },
				pips: {
					mode: 'positions',
					values: [0,25,50,75,100],
					density: 5,
					format: wNumb({
						postfix: '&deg;'
					})
				}
			});
			
			noUiSlider.create(document.getElementById('act-bed-temp'), {
				start: 0,
				connect: "lower",
				range: {'min': 0, 'max' : 100},
				behaviour: 'none'
			});
			
			
			$("#act-bed-temp .noUi-handle").remove();
		
		<?php endif; ?>
		
		<?php if($type == 'subtractive'): ?>
			noUiSlider.create(document.getElementById('rpm'), {
				start: 6000,
				connect: "lower",
				range: {'min': 6000, 'max' : 14000 },
				pips: {
					mode: 'positions',
					values: [0,20,40,60,80,100],
					density: 10,
					format: wNumb({})
				}
			});
		<?php endif; ?>
		
		
		speed_slider = document.getElementById('velocity');
		<?php if($type == 'additive'): ?>
			nozzle_slider = document.getElementById('temp1');
			bed_slider    = document.getElementById('temp2');
			fan_slider    = document.getElementById('fan');
			flow_rate_slider = document.getElementById('flow-rate');
		<?php endif; ?>
		<?php if($type == 'subtractive'): ?>
			rpm_slider = document.getElementById('rpm');
		<?php endif ?>
		
		
		/*event sliders*/
		speed_slider.noUiSlider.on('slide', manageSpeedSlider);
		speed_slider.noUiSlider.on('change', setSpeed);
		
		<?php if($type == 'additive'): ?>
			nozzle_slider.noUiSlider.on('slide', manageNozzleSlider);
			nozzle_slider.noUiSlider.on('change', setNozzleTemp);
			bed_slider.noUiSlider.on('slide', manageBedSlider);
			bed_slider.noUiSlider.on('change', setBedTemp);
			fan_slider.noUiSlider.on('slide', manageFanSlider);
			fan_slider.noUiSlider.on('change', setFan);
			flow_rate_slider.noUiSlider.on('slide', manageFlowRateSlider);
			flow_rate_slider.noUiSlider.on('change', setFlowRate);
		<?php endif; ?>
		
		<?php if($type == 'subtractive'): ?>
			rpm_slider.noUiSlider.on('slide', manageRpmSlider);
			rpm_slider.noUiSlider.on('change', setRpm);
		<?php endif; ?>
			
	}
	
	function manageNozzleSlider(e){
		extruder_target = parseInt(e[0]);
	   	$("#label-temp1-target").html('' + parseInt(e[0]) + '&deg;C');
	   	$("#top-bar-nozzle-target").html(parseInt(e[0]));
	   	blockSliderExt = true;
	}
	
	function setNozzleTemp(e){
		_do_action('temp1', parseInt(e[0]));
	}
	
	function manageBedSlider(e){
		bed_target = parseInt(e[0]);
	   	$("#label-temp2-target").html('' + parseInt(e[0]) + '&deg;C');
	   	$("#top-bar-bed-target").html(parseInt(e[0]));
	   	blockSliderBed = true;
	}
	
	function setBedTemp(e){
		_do_action('temp2', parseInt(e[0]));
	}
	
	function manageSpeedSlider(e){
		$(".label-velocity").html('' + parseInt(e[0]) + '%');
	   	speed = parseInt(e[0]);
	   	var speed_percent = (speed/500) * 100;
	   	$('.speed-progress').attr('style', 'width:' + parseFloat(speed_percent) + '%');
	   	blockSliderExt = true;
	}
	
	function setSpeed(e){
		_do_action('velocity', parseInt(e[0]));
		blockSliderExt = false;
	}
	
	function manageFanSlider(e){
		$(".label-fan").html('' + parseInt(e[0]) + '%');
	   	$('.fan-progress').attr('style', 'width:' + parseInt(e[0]) + '%');
	}
	
	function setFan(e){
		_do_action('fan', parseInt(e[0]));
	}
	
	function manageFlowRateSlider(e){
		$(".label-flow-rate").html('' + parseInt(e[0]) + '%');
	  	var flow_percent =  (parseInt(e[0]) / 500) * 100;	
	   	$('.flow-rate-progress').attr('style', 'width:' + parseInt(flow_percent) + '%');
	   	blockSliderFlowRate = true;
	}
	
	function setFlowRate(e){
		_do_action('flow-rate', parseInt(e[0]));
		blockSliderFlowRate = false;
	}
	
	function manageRpmSlider(e){
		var rpm_percent = (parseInt(e[0])/14000) * 100;
	   	$(".label-rpm").html('' + parseInt(e[0]) + '');
	   	$('.rpm-progress').attr('style', 'width:' + parseFloat(rpm_percent) + '%');
	}
	
	function setRpm(e){
		_do_action('rpm', parseInt(e[0]));
	}
	
	function disableSliders(){
		speed_slider.setAttribute('disabled', true);
		<?php if($type == 'additive'): ?>
		    nozzle_slider.setAttribute('disabled', true);
			bed_slider.setAttribute('disabled', true);
			fan_slider.setAttribute('disabled', true);
			flow_rate_slider.setAttribute('disabled', true);
		<?php endif; ?>
		<?php if($type == 'subtractive'): ?>
			rpm_slider.setAttribute('disabled', true);
		<?php endif; ?>
	}
	
	function enableSliders(){
		speed_slider.removeAttribute('disabled');
		<?php if($type == 'additive'): ?>
		    nozzle_slider.removeAttribute('disabled');
			bed_slider.removeAttribute('disabled');
			fan_slider.removeAttribute('disabled');
			flow_rate_slider.removeAttribute('disabled');
		<?php endif; ?>
		<?php if($type == 'subtractive'): ?>
			rpm_slider.removeAttribute('disabled');
		<?php endif; ?>	
	}
	
	function disableControlsTab(){
		disableSliders();
		$("#controls").find('button').addClass('disabled');
	}
	
	function enableControlsTab(){
		enableSliders();
		$("#controls").find('button').removeClass('disabled');
	}
	
	function manage_slide(e){
    
	   var id = $(this).attr('id');
	   switch(id){
	   	case 'velocity':
	   		 $(".label-"+ id ).html('' + parseInt($(this).val()) + '%');
	   		 speed = parseInt($(this).val());
	   		 var speed_percent = (speed/500) * 100;
	   		 $('.speed-progress').attr('style', 'width:' + parseFloat(speed_percent) + '%');
	   		 blockSliderSpeed = true;
	   		 break;
	   	case 'temp1':
	   		extruder_target = parseInt($(this).val());
	   		$("#label-"+ id + '-target' ).html('' + parseInt($(this).val()) + '&deg;C');
	   		blockSliderExt = true;
	   		break;
	   	case 'temp2':
	   		bed_target = parseInt($(this).val());
	   		$("#label-"+ id + '-target' ).html('' + parseInt($(this).val()) + '&deg;C');
	   		blockSliderBed = true;
	   		break;
	   	case 'rpm':
	   		var rpm_percent = (parseInt($(this).val())/14000) * 100;
	   		$(".label-"+ id ).html('' + parseInt($(this).val()) + '');
	   		$('.rpm-progress').attr('style', 'width:' + parseFloat(rpm_percent) + '%');
	   		break;
	   	case 'fan':
	   		$(".label-"+ id ).html('' + parseInt($(this).val()) + '%');
	   		$('.fan-progress').attr('style', 'width:' + parseInt($(this).val()) + '%');
	   		break;
	   	case 'flow-rate':
	   		$(".label-"+ id ).html('' + parseInt($(this).val()) + '%');
	   		var flow_percent =  (parseInt($(this).val()) / 500) * 100;
	   		$('.flow-rate-progress').attr('style', 'width:' + parseInt(flow_percent) + '%');
	   		blockSliderFlowRate = true;
	   		break;
	   }
	}
	
	function manage_change(e){
	   	var action = $(this).attr('data-action');
		var value  = parseInt($(this).val());
		_do_action(action, value);
		if(action == 'stop'){
			_stop_monitor();
			_stop_timer();
			_stop_trace();
			stopped = 1;
		}
	}
	
	function restart_create(){
		document.location.href = '<?php echo site_url('make/'.strtolower($label)); ?>?obj='+id_object+'&file='+id_file;
	}
	
	function new_create(){
		document.location.href = '<?php echo site_url('make/'.strtolower($label)); ?>';
	}
	
	function save_z_override(){
		$.ajax({
			type: "POST",
			url : "<?php echo module_url('maintenance').'ajax/override_probe_lenght.php' ?>",
			data : {over : z_override},
			dataType: "json"
		}).done(function( data ) {
			$(".z-override-alert").slideUp('slow', function(){
				$.smallBox({
					title : "Z Height",
					content : 'New value saved',
					color : "#5384AF",
					timeout : 10000,
					icon : "fa fa-check"
				});
			});
		});
	}
	
	function selectObj(){
		$(this).find(':first-child').find('input').prop("checked", true);
		$("#objects_table tbody tr").removeClass('success');
		$(this).addClass('success');
		var id = $(this).attr("data-id");
		
		id_object = id;
		
		$.ajax({
			url : '<?php echo module_url('objectmanager')?>ajax/object.php',
			dataType : 'json',
			type : "POST",
			async : true,
			data : {
				printable : true,
				id_object : id,
				print_type: print_type
			}
		}).done(function(response) {
	
			object = response;
			detail_object(object);
			detail_files(object);
			startFromRecent = false;
			enable_button('#btn-next');
			resetTableRecent();
		});
	}
	
	function resetTableRecent(){
		$( "#recent_table tbody > tr > td" ).find('input').each(function() {
			$(this).prop('checked', false);
		});
		$( "#recent_table tbody > tr " ).each(function() {
			$(this).removeClass('success');
		});
		$(".model-info").remove();
		startFromRecent = false;
	}
	
	function stopCountDown(){
		autostart_timer = 20;
		$(".autostart-timer").html(autostart_timer);
		clearInterval(interval_autostart);
	}
	
	function startCountDown(){
		interval_autostart   = setInterval(countDown, 1000);
	}
	
	function countDown(){
		autostart_timer = autostart_timer - 1;
	    $(".autostart-timer").html(autostart_timer);
	        	
	    if(autostart_timer == 0){
	    	$("#modal_link").trigger('click');
	    }
	}
	
	/**
	 *  OVVERRIDE GENERAL MONITOR FUNCTION
	 */
	function manage_task_monitor(obj){
		if(obj.type=="monitor"){
			if(obj.content != ""){
				data = jQuery.parseJSON(obj.content);
				monitor(data);
			}
			
		}
	}
	
	function monitor(data){
		
		id_task = data.task_id;
		if (data.print.completed == 'True') {
			print_finished = true;
			finalize_print();
			return;
		}

		if (parseFloat(data.print.stats.percent) > 0) {
			$('#stop-button').removeClass('disabled');
			$('.controls').removeClass('disabled');
		}
		
		if(!print_started){
			if(data.print.print_started == "True"){
				$(".controls-tab").removeClass("disabled");
				$(".controls-tab").find("a").attr("data-toggle", "tab").trigger("click");
				print_started = true;
				enableControlsTab();
			}
		}
		
		<?php if($type == 'additive'): ?>
			if (!blockSliderExt) {
				nozzle_slider.noUiSlider.set([parseInt(data.print.stats.extruder_target)]); 
				$("#label-temp1-target").html(parseInt(data.print.stats.extruder_target) + '&deg;C');
				$(".nozzle-target").html(parseInt(data.print.stats.extruder_target));
			}
		
			if (!blockSliderBed) {
				bed_slider.noUiSlider.set([parseInt(data.print.stats.bed_target)]);
				$("#label-temp2-target").html(parseInt(data.print.stats.bed_target) + '&deg;C');
			}
			
			
			if (!blockSliderSpeed) {
				speed_slider.noUiSlider.set([parseInt(data.print.stats.speed)]); 
			}
			
			if (!blockSliderFlowRate) {
				flow_rate_slider.noUiSlider.set([parseInt(data.print.stats.flow_rate)]); 
			}
		<?php endif; ?>
		
		progress = data.print.stats.percent;
		
		// manage layers if present
		if(data.print.stats.hasOwnProperty('layers')){
			if(data.print.stats.layers.total.length == 1){
				$(".layers").removeClass('hidden');
				$(".layer-actual").html(parseInt(data.print.stats.layers.actual));
				$(".layer-total").html(parseInt(data.print.stats.layers.total[0]));
				var layer_percent = (parseInt(data.print.stats.layers.actual) / parseInt(data.print.stats.layers.total[0]) ) * 100;
				$('.progress-layer').attr('style', 'width:' + parseFloat(layer_percent) + '%');
				$('.layer-percent').html('('+number_format(parseFloat(layer_percent), 2, ',', '.') +'%)');
				$(".layer").html(parseInt(data.print.stats.layers.actual) + ' of ' + parseInt(data.print.stats.layers.total[0]));	
			}
		}
		
		$('.total-lines').html(data.print.lines);
		$('.current-line').html(data.print.stats.line_number);
		$('.pid').html(data.print.pid);
		$('.temperature').html(data.print.stats.extruder);
		$('.position').html(data.print.stats.position);
		
		//speed labels
		$(".label-velocity").html(data.print.stats.speed + '%');
		var speed_percent = (parseInt(data.print.stats.speed)/500) * 100;
		$('.speed-progress').attr('style', 'width:' + parseFloat(speed_percent) + '%');
		
		//flow rate labels
		$(".label-flow-rate").html('' + data.print.stats.flow_rate + '%');
	  	var flow_percent =  (parseInt(data.print.stats.flow_rate) / 500) * 100;	
	   	$('.flow-rate-progress').attr('style', 'width:' + parseInt(flow_percent) + '%');
		
		<?php if($type == 'additive'): ?>
			document.getElementById('act-ext-temp').noUiSlider.set([parseInt(data.print.stats.extruder)]);
			document.getElementById('act-bed-temp').noUiSlider.set([parseInt(data.print.stats.bed)]);
			
			var fan_percent = (parseFloat(data.print.stats.fan) / 255) * 100;
			document.getElementById('fan').noUiSlider.set([parseInt(fan_percent)]);
			
			$(".label-fan").html('' + parseInt(fan_percent) + '%');
			$('.fan-progress').attr('style', 'width:' + parseInt(fan_percent) + '%');
			
		<?php endif; ?>
		
		$('#lines-progress').attr('style', 'width:' + parseFloat(data.print.stats.percent) + '%');
		$('#lines-progress').attr('aria-valuetransitiongoal', parseFloat(data.print.stats.percent));
		$('#lines-progress').attr('aria-valuenow', parseFloat(data.print.stats.percent));
		
		$('.progress-status').html(number_format(parseFloat(data.print.stats.percent), 2, ',', '.') + ' %');

		$('#label-progress').html('(' + number_format(parseFloat(data.print.stats.percent), 2, ',', '.') + ' % )');
		
		$("#label-temp1").html(parseInt(data.print.stats.extruder) + '&deg;C');
		$(".nozzle-temperature").html(parseInt(data.print.stats.extruder));
		$(".nozzle-target").html(parseInt(data.print.stats.extruder_target));
		$("#label-temp2").html(parseInt(data.print.stats.bed) + '&deg;C');
		$(".bed-temperature").html(parseInt(data.print.stats.bed));
		$(".bed-target").html(parseInt(data.print.stats.bed_target));
	
		extruder_target = parseInt(data.print.stats.extruder_target);
		bed_target = parseInt(data.print.stats.bed_target);
		
		/*_update_task();*/
	
		estimated_time_left = ((elapsed_time / data.print.stats.percent) * 100) - elapsed_time;
		
		if(data.print.hasOwnProperty('tip')){
			tip(data.print.tip.show, data.print.tip.message);
		}
		
		
		/*** GRAPHS ***/
		addNozzleTemperature(data.print.stats.extruder);
		addNozzleTargetTemperature(data.print.stats.extruder_target);
		addBedTemperature(data.print.stats.bed);
		addBedTargetTemperature(data.print.stats.bed_target);
		
		/*updateNozzleGraph();
		updateBedGraph();*/
			
		
		
				
		
		
		<?php if($type == 'subtractive'): ?>
			var rpm_percent = (parseInt(data.print.stats.rpm)/14000) * 100;
		   	$(".label-rpm").html(parseInt(data.print.stats.rpm));
		   	$('.rpm-progress').attr('style', 'width:' + parseFloat(rpm_percent) + '%');
			
			document.getElementById('rpm').noUiSlider.set([parseInt(data.print.stats.rpm)]);
		<?php endif; ?>
		
		$(".z_override").html(data.print.stats.z_override);
		z_override = data.print.stats.z_override;
		
		/******* TOP BAR *********************/
		$("#top-bar-nozzle-actual").html(parseInt(data.print.stats.extruder));
		$("#top-bar-nozzle-target").html(parseInt(extruder_target));
		$("#top-bar-bed-actual").html(parseInt(data.print.stats.bed));
		$("#top-bar-bed-target").html(parseInt(bed_target));
		
		if(document.getElementById("top-ext-target-temp") != null){
			document.getElementById('top-ext-target-temp').noUiSlider.set([parseInt(extruder_target)]);
		}
		if(document.getElementById("top-act-ext-temp") != null){
			document.getElementById('top-act-ext-temp').noUiSlider.set([parseInt(extruder_target)]);
		}
		
		
		document.getElementById('top-act-bed-temp').noUiSlider.set([parseInt(data.print.stats.bed)]);
		document.getElementById('top-bed-target-temp').noUiSlider.set([parseInt(bed_target)]);
	}
	
	function addNozzleTemperature(temp){
		var obj = {'temp': parseFloat(temp), 'time': new Date().getTime()};
		if(nozzle_temperatures.length == max_plot) nozzle_temperatures.shift();
		nozzle_temperatures.push(obj);
		if ( typeof (Storage) !== "undefined") localStorage.setItem('nozzle_temperatures', JSON.stringify(nozzle_temperatures));
	}
	
	function addNozzleTargetTemperature(temp){
		var obj = {'temp': parseFloat(temp), 'time': new Date().getTime()};
		if(nozzle_target_temperatures.length == max_plot) nozzle_target_temperatures.shift();
		nozzle_target_temperatures.push(obj);
		if ( typeof (Storage) !== "undefined") localStorage.setItem('nozzle_target_temperatures', JSON.stringify(nozzle_target_temperatures));
	}
	
	function addBedTemperature(temp){
		var obj = {'temp': parseFloat(temp), 'time': new Date().getTime()};
		if(bed_temperatures.length == max_plot) bed_temperatures.shift();
		bed_temperatures.push(obj);
		if ( typeof (Storage) !== "undefined") localStorage.setItem('bed_temperatures', JSON.stringify(bed_temperatures));
	}
	
	function addBedTargetTemperature(temp){
		var obj = {'temp': parseFloat(temp), 'time': new Date().getTime()};
		if(bed_target_temperatures.length == max_plot) bed_target_temperatures.shift();
		bed_target_temperatures.push(obj);
		if ( typeof (Storage) !== "undefined") localStorage.setItem('bed_target_temperatures', JSON.stringify(bed_target_temperatures));
	}
	
	function getNozzlePlotTemperatures(){
		var res1 = [];
		var res2 = [];
		for (var i = 0; i < nozzle_temperatures.length; ++i) {
			var obj = nozzle_temperatures[i];
			res1.push([obj.time, obj.temp]);
		}
		for (var i = 0; i < nozzle_target_temperatures.length; ++i) {
			var obj = nozzle_target_temperatures[i];
			res2.push([obj.time, obj.temp]);
		}
		return [{ label: "Actual", data: res1 },
			    { label: "Target", data: res2 }];
	}
	
	function getBedPlotTemperatures(){
		var res1 = [];
		var res2 = [];
		for (var i = 0; i < bed_temperatures.length; ++i) {
			var obj = bed_temperatures[i];
			res1.push([obj.time, obj.temp]);
		}
		for (var i = 0; i < bed_target_temperatures.length; ++i) {
			var obj = bed_target_temperatures[i];
			res2.push([obj.time, obj.temp]);
		}
		return [{ label: "Actual", data: res1 },
			    { label: "Target", data: res2 }];
	}
	
	//init graphs
	function  initGraphs(){
		nozzlePlot = $.plot("#nozzle-chart", [ getNozzlePlotTemperatures() ], {
	        	series : {
					lines : {
						show : true,
						lineWidth : 1,
						fill : true,
						fillColor : {
							colors : [{
								opacity : 0.1
							}, {
								opacity : 0.15
							}]
						}
					}
				},
				xaxis: {
				    mode: "time",
				    show: true,
				    tickFormatter: function (val, axis) {
					    var d = new Date(val);
					    return d.getHours() + ":" + d.getMinutes();
					}
				},
				yaxis: {
			        min: 0,
			        max: 300,    
			        tickFormatter: function (v, axis) {
			            return v + "&deg;C";
			        }
	        
	    		},
				tooltip : true,
				tooltipOpts : {
					content : "%s: %y &deg;C",
					defaultTheme : false
				},
				colors : ["#FF0000", "#57889c", "#0000FF"],
				legend: {
					show : true
				},
				grid : {
						hoverable : true,
						clickable : true,
						borderWidth : 0
					},
	
								
				});
			bedPlot = $.plot("#bed-chart", [ getBedPlotTemperatures() ], {
	        	series : {
					lines : {
						show : true,
						lineWidth : 1,
						fill : true,
						fillColor : {
							colors : [{
								opacity : 0.1
							}, {
								opacity : 0.15
							}]
						}
					},
					shadowSize : 0
				},
				xaxis: {
				    mode: "time",
				    show: true,
				    tickFormatter: function (val, axis) {
					    var d = new Date(val);
					    return d.getHours() + ":" + d.getMinutes();
					}
				},
				yaxis: {
			        min: 0,
			        max: 100,
			        tickSize: 20,        
			        tickFormatter: function (v, axis) {
			            return v + "&deg;C";
			        }
	    		},
				tooltip : true,
				tooltipOpts : {
					content : "%s: %y &deg;C",
					defaultTheme : false
				},
				colors : ["#FF0000", "#57889c", "#0000FF"],
				grid : {
						hoverable : true,
						clickable : true,
						borderWidth : 0
				},
								
		});
		interval_charts = setInterval(updateCharts, 1000);
	}
	
	//update graphs
	function updateCharts(){
		updateNozzleGraph();
		updateBedGraph();
	}
	
	//update nozzle temperatures graph
	function updateNozzleGraph(){
		try{
			if(typeof nozzlePlot == "object" ){
				var data = 	getNozzlePlotTemperatures();
				nozzlePlot.setData(data);
				nozzlePlot.draw();
				nozzlePlot.setupGrid();
			}
		}catch(e){}
	}
	
	//update bed temperatures graph
	function updateBedGraph(){
		try{
			if(typeof bedPlot == "object" ){
				var data = getBedPlotTemperatures()	
				bedPlot.setData(data);
				bedPlot.draw();
				bedPlot.setupGrid();
			}
			
		}catch(e){}
	}
	
	//resume process
	function resume(){
		$.is_task_on = true;
		
		//first get monitor and trace
		getTrace(true);
		getMonitor(true);
		
		interval_monitor = setInterval(getMonitor, 5000);
		interval_timer   = setInterval(timer, 1000);
		interval_trace   = setInterval(getTrace, 1000);
		
		if(print_started){
			$(".controls-tab").removeClass("disabled");
			$(".controls-tab").find("a").attr("data-toggle", "tab");
			print_started = true;
			enableControlsTab();
		}
		
		if(print_type == 'additive'){
			initGraphs();
			$(".subtractive-print").hide();
			var layer_percent = (parseInt(layer_actual) / parseInt(layer_total) ) * 100;
			$('.progress-layer').attr('style', 'width:' + parseFloat(layer_percent) + '%');
			$('.layer-percent').html('('+number_format(parseFloat(layer_percent), 2, ',', '.') +'%)');
			
			if ( typeof (Storage) !== "undefined") {	
				if(localStorage.getItem("nozzle_temperatures") !== null){			
					nozzle_temperatures =  JSON.parse(localStorage.getItem("nozzle_temperatures"));
				}
				if(localStorage.getItem("nozzle_target_temperatures") !== null){			
					nozzle_target_temperatures =  JSON.parse(localStorage.getItem("nozzle_target_temperatures"));
				}
				if(localStorage.getItem("bed_temperatures") !== null){			
					bed_temperatures =  JSON.parse(localStorage.getItem("bed_temperatures"));
				}				
				if(localStorage.getItem("bed_target_temperatures") !== null){			
					bed_target_temperatures =  JSON.parse(localStorage.getItem("bed_target_temperatures"));
				}
			}

		}else{
			$(".speed-well").removeClass("col-sm-4").addClass("col-sm-6");
			$(".stats-well").removeClass("col-sm-4").addClass("col-sm-12");
			$(".additive-print").hide();
		}
	
		$(".steps >li").removeClass("complete");
		if(document.getElementById("top-ext-target-temp") != null){
			document.getElementById("top-ext-target-temp").setAttribute('disabled', true);
		}
		document.getElementById("top-bed-target-temp").setAttribute('disabled', true);
		$(".jog").addClass('disabled');
		
		
	}
	
	//get trace
	function getTrace(force){
		force = force || false;
		if(force == true || $.socket_connected == false){
			$.get('/temp/task_trace',{ "_": $.now() }, function(data, status){
				$(".console").html(data).scrollTop(1E10);
			});
		}
	}
	
	//get monitor
	function getMonitor(force){
		force = force || false;
		if(force == true || $.socket_connected == false){
			$.get('/temp/task_monitor.json',{ "_": $.now() }, function(data, status){
				monitor(data);
			});
		}
	}
	
	//manage times
	function timer(){
		elapsed_time = (parseInt(elapsed_time) + 1);
		$('.elapsed-time').html(_time_to_string(elapsed_time));
		if (!isNaN(estimated_time_left)) {
			estimated_time_left = (parseInt(estimated_time_left) - 1);
			if (estimated_time_left >= 0) {
				$('.estimated-time-left').html(_time_to_string(estimated_time_left));
			}
		}
	}
	// refresh page
	function refreshPage() {
		waitContent('Refreshing page');
		document.location.href = '<?php echo site_url("make/".strtolower($label)); ?>';
	}
	// reset objects table
	function resetTableObjects(){
		$( "#objects_table tbody > tr > td" ).find('input').each(function() {
			$(this).prop('checked', false);
		});
		$( "#objects_table tbody > tr " ).each(function() {
			$(this).removeClass('success');
		});
		object = undefined;
	}
	
</script>