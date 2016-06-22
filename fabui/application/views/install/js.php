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
		initWizard();
	});
	
	function initWizard()
	{
		$('#bootstrap-wizard-1').bootstrapWizard({
		    'tabClass': 'form-wizard',
		    'onNext': function (tab, navigation, index) {
		      var $valid = $("#wizard-1").valid();
		      if (!$valid) {
		      	$validator.focusInvalid();
		      	return false;
		      } else {
		        $('#bootstrap-wizard-1').find('.form-wizard').children('li').eq(index - 1).addClass('complete');
		        $('#bootstrap-wizard-1').find('.form-wizard').children('li').eq(index - 1).find('.step').html('<i class="fa fa-check"></i>');
			  }
			}
		});
	}
</script>