<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<script type="text/javascript">

	$(document).ready(function() {
		initUI();
		initEvents();
	});
	
	//
	function initUI()
	{
		
		noUiSlider.create(document.getElementById('red'), {
			start: <?php echo $defaultSettings['color']['r'] != '' ? $defaultSettings['color']['r'] : 0 ?>,
			connect: "lower",
			range: {'min': 0, 'max' : 255},
			serialization: {
				format: wNumb({
					decimals: 0
				})
			}
		});
		
		noUiSlider.create(document.getElementById('green'), {
			start: <?php echo $defaultSettings['color']['g'] != '' ? $defaultSettings['color']['g'] : 0 ?>,
			connect: "lower",
			range: {'min': 0, 'max' : 255},
			serialization: {
				format: wNumb({
					decimals: 0
				})
			}
		});
		
		noUiSlider.create(document.getElementById('blue'), {
			start: <?php echo $defaultSettings['color']['b'] != '' ? $defaultSettings['color']['b'] : 0 ?>,
			connect: "lower",
			range: {'min': 0, 'max' : 255},
			serialization: {
				format: wNumb({
					decimals: 0
				})
			}
		});
		
		var resultElement = document.getElementById('result'),
			sliders = document.getElementsByClassName('standby-color');

		for ( var i = 0; i < sliders.length; i++ ) {
			sliders[i].noUiSlider.on('slide', showColor);
			sliders[i].noUiSlider.on('change', setColor);
		}
		
	}
	
	//
	function initEvents()
	{
		$(':radio[name="settings_type"]').change(function() {
			var type = $(this).filter(':checked').val();
			if(type == 'custom'){
				$(".custom_settings").slideDown();
			}else{
				$(".custom_settings").slideUp();
			}
		});
		
		$("#save").on('click', saveSettings);
	}
	
	function showColor()
	{
		var sliders = document.getElementsByClassName('standby-color');

		var red = parseInt(sliders[0].noUiSlider.get()),
			green = parseInt(sliders[1].noUiSlider.get()),
			blue = parseInt(sliders[2].noUiSlider.get());

		var color = 'rgb(' +
			red + ',' +
			green + ',' +
			blue + ')';

		$("#color-r").val(red);
		$("#color-g").val(green);
		$("#color-b").val(blue);

		$(".result").css({
			"background": color,
			"color:": color
		});
	}
	
	function setColor()
	{
		var sliders = document.getElementsByClassName('standby-color');
		
		$.ajax({
		url : '<?php echo site_url('settings/setColor'); ?>',
		  dataType : 'json',
		  type: 'post',
		  async : true,
		  data: {red : parseInt(sliders[0].noUiSlider.get()), green: parseInt(sliders[1].noUiSlider.get()), blue: parseInt(sliders[2].noUiSlider.get())}
		}).done(function(response) {
		  
		  
		});
	}
	
	//
	function saveSettings()
	{
		openWait("<i class='fa fa-spin fa-spinner'></i> <?php echo _("Applying new settings"); ?>", "<?php echo _("Please wait"); ?>...", false );
		var button = $(this);
		button.addClass('disabled');
		button.html('<i class="fa fa-save"></i> <?php echo _('Saving')?>..');
		var data = {};
		$(".tab-content :input").each(function (index, value) {
			if($(this).is('input:text') || $(this).is('textarea') || $(this).is('select') || $(this).is(':input[type="number"]') || ($(this).is('input:radio') && $(this).is(':checked')) ){
				data[$(this).attr('id')] = $(this).val();
			}
		});
		
		$.ajax({
			type: 'post',
			url: '<?php echo site_url('settings/saveSettings'); ?>',
			data : data,
			dataType: 'json'
		}).done(function(response) {
			closeWait();
			button.html('<i class="fa fa-save"></i> <?php echo _('Save')?>');
			button.removeClass('disabled');
			fabApp.analizeMenu(response);
			$.smallBox({
				title : "<?php echo _('Settings')?>",
				content : '<?php echo _('Hardware settings saved')?>',
				color : "#5384AF",
				timeout: 3000,
				icon : "fa fa-check bounce animated"
			});
			
		});
	}
</script>
