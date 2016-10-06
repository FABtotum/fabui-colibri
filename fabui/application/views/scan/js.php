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
	<?php if(isset($scanQualities)): ?>
	<?php foreach($scanQualities as $quality):?>
	scanQualites.push(<?php echo json_encode($quality);?>);
	<?php endforeach;?>
	<?php endif; ?>
	<?php if(isset($probingQualities)): ?>
	<?php foreach($probingQualities as $quality):?>
	probingQualities.push(<?php echo json_encode($quality);?>);
	<?php endforeach;?>
	<?php endif; ?>
	var scanMode = 0;
	var scanModeInstructions = 0;
	var elapsedTime = 0;
	var objectMode = 'new';
	var isRunning = false;
	var isCompleting = false;
	var isCompleted = false;
	var isAborting = false;
	var isAborted = false;
</script>  
