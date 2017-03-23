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
<script type="text/javascript" src="/assets/js/plugin/jquery-websocket/jquery.WebSocket.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/std/raphael.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/std/modernizr-touch.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/std/jogcontrols.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/app.min.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/fab.app.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/fabtotum.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/demo.js?v=<?php echo FABUI_VERSION ?>"></script>
<!--script type="text/javascript" src="/assets/js/plugin/introjs/intro.js?v=<?php echo FABUI_VERSION ?>"></script-->
<script type="text/javascript" src="/assets/js/plugin/bootstrap-tour/bootstrap-tour.min.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/intro.js?v=<?php echo FABUI_VERSION ?>"></script>
<?php echo $jsScripts; ?>
<script type="text/javascript">
	var heads = <?php echo json_encode($heads)?>;
	$(document).ready(function() {
		pageSetUp();
		fabApp.initFromLocalStorage();
		fabApp.webSocket();
		fabApp.FabActions();
		fabApp.domReadyMisc();
		fabApp.getState();
		fabApp.getSettings();
		fabApp.getNetworkInfo();
		fabApp.getUpdates();
		//start intervals
		temperatures_interval = setInterval(fabApp.getTemperatures, temperatures_interval_timer);
		
		/*setTimeout(function(){
			startIntroduction();
		}, 2000);*/
		
	});
</script>
<?php echo $jsInLine; ?>
