<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<script src="/assets/js/<?php echo ENVIRONMENT ?>/app.config.js"></script>
<script src="/assets/js/plugin/jquery-touch/jquery.ui.touch-punch.min.js"></script>
<script src="/assets/js/bootstrap/bootstrap.min.js"></script>
<script src="/assets/js/notification/SmartNotification.min.js"></script>
<script src="/assets/js/smartwidgets/jarvis.widget.min.js"></script>
<script src="/assets/js/plugin/msie-fix/jquery.mb.browser.min.js"></script>
<script src="/assets/js/plugin/fastclick/fastclick.min.js"></script>
<script src="/assets/js/plugin/jquery-validate/jquery.validate.min.js"></script>
<script src="/assets/js/app.min.js"></script>
<?php if(ENVIRONMENT == 'development'): //only for development purpose ?>
<?php endif; ?>
<?php echo $jsScripts; ?>
<?php echo $jsInLine; ?>
	
