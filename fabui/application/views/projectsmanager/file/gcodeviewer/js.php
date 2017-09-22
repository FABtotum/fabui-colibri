<script type="text/javascript">
	
	
	$(function () {
		GCODE.ui.initHandlers();
		
		var download_url = "<?php echo $gcode_url; ?>";
		
		$.get( download_url, function( data ) {
			GCODE.ui.loadFromString(data);
		 });
	});
	
</script>
