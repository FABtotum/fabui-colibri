<?php
/**
 * 
 * @author Krios Mane
 * @author Fabtotum Development Team
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>
<script type="text/javascript">
	var mode;
	var filament;
	var is_pro_head = <?php echo isset($head['feeder']) ? 'true' : 'false' ?>;
	
	$(document).ready(function() {
		fabApp.checkSafety('print', 'yes', '.fuelux');
		$(".mode-choise").on('click', clickSetMode);
		$("#restart-button").on('click', restartAction);
		$("input[name='filament-type']").on('click', filamentButtonClick);
		loadShopFilaments();

		
	});
	
	/**
	*
	**/
	function handleStep()
	{

		var step = $('.wizard').wizard('selectedItem').step;
		if(step == 2){
			if(is_pro_head && mode == 'load'){
				heatsNozzle();
				return false;
			}
		}else if(step == 3)
		{
			doSpoolAction();
			return false;
		}

		return true;
	}
	
	/**
	*
	**/
	function checkWizard()
	{
		var step = $('.wizard').wizard('selectedItem').step;
		switch(step){
			case 1: // Choose mode
				disableButton('.button-prev');
				disableButton('.button-next');
				$("#main-widget-spool-management").find('header').find('h2').html(_("Spool management"));
				break;
			case 2: // Filament
				enableButton('.button-prev');
				enableButton('.button-next');
				break;
			case 3: // Get ready
				enableButton('.button-prev');
				enableButton('.button-next');
				break;
			case 4: // Finish
				disableButton('.button-prev');
				disableButton('.button-next');
				break;
		}
	}
	
	/**
	*
	**/
	function clickSetMode()
	{
		var action = $(this).attr('data-action');
		setMode(action);
	}
	/**
	*
	**/
	function setMode(action)
	{	
		mode = action;
		$(".get-ready-row").hide();
		$("#"+mode+"-row").show();
		
		switch(action)
		{
			case 'load':
				$("#filament-title").html('<?php echo _('Select filament to load')?>');
				$("#main-widget-spool-management").find('header').find('h2').html(_("Spool management") + " > " + _("Load spool"));
				break;
			case 'unload':
				$("#filament-title").html('<?php echo _('What filament are you going to unload?')?>');
				$("#main-widget-spool-management").find('header').find('h2').html(_("Spool management") + " > " + _("Unload spool"));
				break;
		}
		gotoWizardStep(2);
	}
	/**
	*
	**/
	function  filamentButtonClick()
	{
		var input = $(this); 
		var type = input.attr("data-type");
		var temperature = input.attr("data-temperature");
		$("#extrusion-temperature").val(temperature);
		$("#details").attr("href", input.attr("data-details"));
		
	}
	
	/**
	*
	**/
	function doSpoolAction()
	{
		openWait("<i class='fa fa-circle-o-notch fa-spin'></i> <?php echo _("Please wait");?>");
		var filament = $("input[name='filament-type']:checked").val();
		var temperature = $("#extrusion-temperature").val();
		$.ajax({
			type: "POST",
			url: "<?php echo site_url("spool") ?>/" + mode + '/' + filament + '/0/' + temperature,
			dataType: 'json'
		}).done(function( response ) {
		closeWait();
		if(response.response == 'success'){
			if(mode == 'unload'){
				if(is_pro_head == true){
					$(".printing-head-pro-unload-final-step").show();
					$("#pro_head_unload_spool_gif").attr('src', '/assets/img/controllers/spool/pro_head_unload_filament_2.gif');
				}
				$("#restart-button").removeClass('hidden');
			}
			gotoWizardFinish();
		}else{
			fabApp.showErrorAlert(response.message);
		}
	  });
	}
	/**
	*
	*/
	function restartAction()
	{
		setMode('load')
	}
	/**
	*
	**/
	function heatsNozzle()
	{
		openWait("<i class='fa fa-gear-notch fa-spin'></i> <?php echo _("Heating nozzle");?>");
		var filament = $("input[name='filament-type']:checked").val();
		var temperature = $("#extrusion-temperature").val()
		$.ajax({
			type: "POST",
			url: "<?php echo site_url("spool") ?>/heatsNozzle/" + filament + '/' + temperature,
			dataType: 'json'
		}).done(function( response ) {
			closeWait();
			if(response.response == 'success'){
				gotoWizardStep(3);
				$("#pro_head_load_spool_gif").attr('src', '/assets/img/controllers/spool/pro_head_load_filament.gif');
			}else{
				fabApp.showErrorAlert(response.message);
			}
	  });
	}
	/**
	*
	**/
	function loadShopFilaments()
	{
		$.get("/fabui/shop/filaments", function(data, status){
			if(data){
				var html = '';
				counter = 0;
				var currency = getCurrency(data.store);
				$.each(data.items, function(i, item) {
					if(item.is_saleable){
						counter++;
						var cssclass = counter == 1 ? 'active' : '';

						html += '<div class="panel panel-default ' + item.sku.split("-")[0].toLowerCase() + ' ">'+
									'<div class="panel-body status">'+
										'<div class="image padding-10">'+
											'<a rel="tooltip" title="'+item.short_description+'" target="_blank" href="'+item.url+'"><img src="'+item.image_url+'"></a>'+
										'</div>'+
										'<div style="padding:5px;">'+
											'<p class="text-center">'+item.name.trim()+'</p>'+
										'</div>'+
									'</div>'+
								'</div>';
					}
				});
				$(".owl-carousel").html(html);
				
				var owl = $('.owl-carousel').owlCarousel({
		        	loop: true,
		         	margin: 10,
		         	dots: false,
		         	navText : ["<i class='fa fw-lg fa-chevron-left'></i>","<i class='fa fw-lg fa-chevron-right'></i>"],
		         	onInitialized: fixNavBars,
		         	onChange : fixNavBars,
		            responsiveClass: true,
		            	responsive: {
		                	0: {
		                    	items: 1,
		                    	nav: true
		                  	},
		                  	600: {
		                    	items: 5,
		                    	nav: true
		                  	},
		                  	1000: {
		                    	items: 6,
		                    	nav: true,
		                    	loop: false,
		                    	margin: 20
		                  	}
		                }
				});
				

				$('.filters-button').on('click', function(e) {
					var filter_data = $(this).data('filter');
								
					/* return if current */
					if($(this).hasClass('btn-info')) return;

					/* active current */
					$(this).addClass('btn-info').siblings().removeClass('btn-info');

					/* animate filter */
					var owlAnimateFilter = function(even) {
						$(this)
						.addClass('__loading')
						.delay(70 * $(this).parent().index())
						.queue(function() {
							$(this).dequeue().removeClass('__loading')
						})
					}

					/* Filter 
					owl.owlFilter(filter_data);*/
					owl.owlFilter(filter_data, function(_owl) { 
						$(_owl).find('.item').each(owlAnimateFilter); 
						fixNavBars();
					});
				});
				$(".spool-slider").removeClass("hidden");

				function fixNavBars()
				{
					var mainContentHeight = $("#content").height();
					//center arrows
					var carouselHeight = $(".owl-stage .owl-item:first-child").height();
					//var carouselHeight = $(".owl-stage-outer").height();
					var prevHeight = $(".owl-prev").height();
					var nextHeight = $(".owl-next").height();
					$(".owl-prev").css("top", (carouselHeight-prevHeight)/2);
					$(".owl-next").css("top", (carouselHeight-prevHeight)/2);

					if(carouselHeight > mainContentHeight) fixNavBars();
				}

				
			}
		});
		/**
		*
		**/
		function getCurrency(store, website)
		{
			switch(store){
				case 'eu': 
				case 'it':
					return '&euro;';
					break; 
				case 'intl':
					return '&dollar;';
			}
		}
	}
</script>
