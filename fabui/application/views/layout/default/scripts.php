<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>
<!--<script data-pace-options='{"restartOnRequestAfter":true}' src="/assets/js/plugin/pace/pace.min.js"></script> -->
<script type="text/javascript" src="/assets/js/<?php echo ENVIRONMENT ?>/app.config.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/<?php echo ENVIRONMENT ?>/fab.app.config.js?v=<?php echo FABUI_VERSION ?>"></script>
<?php if(ENVIRONMENT == 'production' && file_exists(FCPATH.'/assets/js/mandatory.js')): ?>
	<script type="text/javascript" src="/assets/js/mandatory.js?v=<?php echo FABUI_VERSION ?>"></script>
<?php else: ?>
	<?php foreach($this->js_mandatory as $js):?>
		<script type="text/javascript" src="<?php echo $js;?>?v=<?php echo FABUI_VERSION ?>"></script>
	<?php endforeach; ?>
<?php endif; ?>
<?php if(isset($tours)) echo $tours;?>
<?php echo jScriptsInclusion($this->js); ?>
<?php if($this->fab_app_init):?>
<script type="text/javascript">
	<?php if(isset($heads)): ?>
	var heads = <?php echo json_encode($heads)?>;
	<?php endif; ?>
	$(document).ready(function() {
		pageSetUp();
		fabApp.initFromLocalStorage();
		fabApp.webSocket();
		fabApp.FabActions();
		fabApp.domReadyMisc();
		fabApp.getState(true);
		fabApp.getSettings();
		fabApp.getNetworkInfo();
		fabApp.getUpdates();
		fabApp.getFeeds();
		//start intervals
		temperatures_interval = setInterval(fabApp.getTemperatures, temperatures_interval_timer);
	});
</script>
<?php endif;?>
<?php echo $this->jsInLine; ?>
