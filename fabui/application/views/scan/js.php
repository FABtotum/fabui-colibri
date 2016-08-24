<?php
/**
 * 
 * @author Krios Mane
 * @author Development Team
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<script type="text/javascript">
	var wizard; //wizard object
	var scanQualites = new Array();
	var probingQualities = new Array();
	<?php foreach($scanQualities as $quality):?>
	scanQualites.push(<?php echo $quality['values'];?>);
	<?php endforeach;?>
	<?php foreach($probingQualities as $quality):?>
	probingQualities.push(<?php echo $quality['values'];?>);
	<?php endforeach;?>
	var scanMode = 0;
	var scanModeInstructions = 0;
</script>  