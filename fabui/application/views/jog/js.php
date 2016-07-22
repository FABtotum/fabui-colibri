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
		
		$(".directions").on("click", function(){
        	fabApp.jogMoveXY($(this).attr("data-attribute-direction"));
        });
        $(".jog-axisz").on("click", function(event){
        	fabApp.jogAxisZ($(this).attr("data-attribute-function"), $(this).attr("data-attribute-value"));
        	event.preventDefault();
        });
        $(".extruder").on("click", function(event){
        	fabApp.jogExtrude($(this).attr('data-attribute-type'));
        });
        $("#mdiButton").on("click", function(event){
            fabApp.jogMdi($("#mdiCommands").val());
        });
		initJogResponseConsole();
        setInterval(getJogResponse, 500);
        $("#clearButton").on('click', clearJogResponse);
	});

	/**
	* init console
	*/
	function initJogResponseConsole()
	{
		fabApp.getJogResponse();
	}
	/**
	* get jog response if not connected to ws
	*/
	function getJogResponse()
	{
		if(!socket_connected) fabApp.getJogResponse();
	}
	/**
	* clear jog response file
	*/
	function clearJogResponse()
	{
		$.ajax({
			type: "POST",
			url: '<?php echo site_url('jog/clear') ?>',
			dataType: 'json'
		}).done(function( data ) {
			$(".consoleContainer").html('');
		});
	}

</script>