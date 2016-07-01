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
<script type="text/javascript" src="/assets/js/<?php echo ENVIRONMENT ?>/app.config.js"></script>
<script type="text/javascript" src="/assets/js/<?php echo ENVIRONMENT ?>/fabApp.config.js"></script>
<script type="text/javascript" src="/assets/js/plugin/jquery-touch/jquery.ui.touch-punch.min.js"></script>
<script type="text/javascript" src="/assets/js/bootstrap/bootstrap.min.js"></script>
<script type="text/javascript" src="/assets/js/notification/SmartNotification.min.js"></script>
<script type="text/javascript" src="/assets/js/notification/FabtotumNotification.js"></script>
<script type="text/javascript" src="/assets/js/smartwidgets/jarvis.widget.min.js"></script>
<script type="text/javascript" src="/assets/js/plugin/msie-fix/jquery.mb.browser.min.js"></script>
<script type="text/javascript" src="/assets/js/plugin/fastclick/fastclick.min.js"></script>
<script type="text/javascript" src="/assets/js/plugin/noUiSlider.8.2.1/nouislider.min.js"></script>
<script type="text/javascript" src="/assets/js/plugin/wNumb/wNumb.js"></script>
<script type="text/javascript" src="/assets/js/fabwebsocket.js"></script>
<script type="text/javascript" src="/assets/js/app.min.js"></script>
<script type="text/javascript" src="/assets/js/fab.app.js"></script>
<?php if(ENVIRONMENT == 'development'): //only for development purpose ?>
	<script type="text/javascript" src="/assets/js/demo.min.js"></script>
<?php endif; ?>
<script type="text/javascript" src="/assets/js/fabtotum.js"></script>
<?php echo $jsScripts; ?>
<script type="text/javascript">
	$(document).ready(function() {
		pageSetUp();
		fabApp.webSocket();
		fabApp.FabActions();
		fabApp.domReadyMisc();
		//start intervals
		status_interval = setInterval(fabApp.getStatus, status_interval_timer);
	});
</script>
<?php echo $jsInLine; ?>
