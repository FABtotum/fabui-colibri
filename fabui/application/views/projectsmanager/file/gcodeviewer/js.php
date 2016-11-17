<script type="text/javascript">
	
	
	$(function () {
		console.log('js init loaded');
		GCODE.ui.initHandlers();
		
		var download_url = "<?php echo $gcode_url; ?>";
		
		$.get( download_url, function( data ) {
			GCODE.ui.loadFromString(data);
		 });
			 
		
		//GCODE.ui.loadFileFromURL("<?php echo $gcode_url; ?>");
	});
	
</script>
