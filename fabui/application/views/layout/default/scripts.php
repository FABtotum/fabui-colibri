<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>
<!--<script data-pace-options='{"restartOnRequestAfter":true}' src="<?php echo base_url(); ?>/assets/js/plugin/pace/pace.min.js"></script>-->
<script type="text/javascript" src="<?php echo base_url(); ?>/assets/js/<?php echo ENVIRONMENT ?>/app.config.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>/assets/js/<?php echo ENVIRONMENT ?>/fabApp.config.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>/assets/js/plugin/jquery-touch/jquery.ui.touch-punch.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>/assets/js/bootstrap/bootstrap.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>/assets/js/notification/SmartNotification.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>/assets/js/notification/FabtotumNotification.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>/assets/js/smartwidgets/jarvis.widget.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>/assets/js/plugin/msie-fix/jquery.mb.browser.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>/assets/js/plugin/fastclick/fastclick.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>/assets/js/plugin/noUiSlider.8.2.1/nouislider.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>/assets/js/plugin/wNumb/wNumb.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>/assets/js/fabwebsocket.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>/assets/js/app.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>/assets/js/fab.app.js"></script>
<?php if(ENVIRONMENT == 'development'): //only for development purpose ?>
	<script type="text/javascript" src="<?php echo base_url(); ?>/assets/js/demo.min.js"></script>
<?php endif; ?>
<script type="text/javascript" src="<?php echo base_url(); ?>/assets/js/fabtotum.js"></script>
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
