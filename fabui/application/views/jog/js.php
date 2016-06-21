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
	});
</script>