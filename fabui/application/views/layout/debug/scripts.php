<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>
<!--<script data-pace-options='{"restartOnRequestAfter":true}' src="assets/js/plugin/pace/pace.min.js"></script>-->
<script type="text/javascript" src="/assets/js/<?php echo ENVIRONMENT ?>/app.config.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/<?php echo ENVIRONMENT ?>/fab.app.config.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/plugin/jquery-touch/jquery.ui.touch-punch.min.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/bootstrap/bootstrap.min.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/notification/SmartNotification.min.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/notification/FabtotumNotification.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/smartwidgets/jarvis.widget.min.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/plugin/msie-fix/jquery.mb.browser.min.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/plugin/fastclick/fastclick.min.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/plugin/noUiSlider.8.2.1/nouislider.min.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/plugin/wNumb/wNumb.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/fabwebsocket.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/app.min.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/fab.app.js?v=<?php echo FABUI_VERSION ?>"></script>
<?php if(ENVIRONMENT == 'development'): //only for development purpose ?>
	<script type="text/javascript" src="/assets/js/demo.min.js?v=<?php echo FABUI_VERSION ?>"></script>
<?php endif; ?>
<script type="text/javascript" src="/assets/js/fabtotum.js?v=<?php echo FABUI_VERSION ?>"></script>
<?php echo $jsScripts; ?>
<script type="text/javascript">
	$(document).ready(function() {
		pageSetUp();
		fabApp.webSocket();
		fabApp.FabActions();
		fabApp.domReadyMisc();
		//start intervals
		temperatures_interval = setInterval(fabApp.getTemperatures, temperatures_interval_timer);
	});
</script>
<?php echo $jsInLine; ?>
