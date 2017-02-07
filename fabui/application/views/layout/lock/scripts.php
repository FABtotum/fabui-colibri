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
<script type="text/javascript" src="/assets/js/plugin/jquery-touch/jquery.ui.touch-punch.min.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/bootstrap/bootstrap.min.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/plugin/msie-fix/jquery.mb.browser.min.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/plugin/fastclick/fastclick.min.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/std/modernizr-touch.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/app.min.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/fab.app.js?v=<?php echo FABUI_VERSION ?>"></script>
<?php if(ENVIRONMENT == 'development'): //only for development purpose ?>
	<!--script type="text/javascript" src="/assets/js/demo.min.js?v=<?php echo FABUI_VERSION ?>"></script-->
<?php endif; ?>
<script type="text/javascript" src="/assets/js/fabtotum.js?v=<?php echo FABUI_VERSION ?>"></script>
<?php echo $jsScripts; ?>
<script type="text/javascript">
	$(document).ready(function() {
		pageSetUp();
	});
</script>
<?php echo $jsInLine; ?>
