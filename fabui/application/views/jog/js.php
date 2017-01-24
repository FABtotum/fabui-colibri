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

		$("#clearButton").on('click', clearJogResponse);
		$("#mdiCommands").on('keyup', handleMdiInputs);
	});

	/**
	* clear jog response file
	*/
	function clearJogResponse()
	{
		$(".consoleContainer").html('');
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
				if( ! e.shiftKey) fabApp.jogMdi($("#mdiCommands").val());
			}
		}
	}
	    
</script>
