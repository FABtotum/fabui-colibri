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

	//var $validator;
	
	$(document).ready(function() {
		initValidate();
		initWizard();
	});
	/**
	 * 
	 */
	function initValidate()
	{

	}
	/**
	 * 
	 */
	function initWizard()
	{
		$('#bootstrap-wizard-1').bootstrapWizard({
			'tabClass': 'form-wizard',
			'onNext': function (tab, navigation, index) {
				var $valid = $("#restore-form").valid();
					if (!$valid) {
						$validator.focusInvalid();
						return false;
					} else {
						
						$('#bootstrap-wizard-1').find('.form-wizard').children('li').eq(index - 1).addClass('complete');
						$('#bootstrap-wizard-1').find('.form-wizard').children('li').eq(index - 1).find('.step').html('<i class="fa fa-check"></i>');
						
						if(index == 2){
							$(".next").find('a').attr('style', 'cursor: pointer !important;');
							$(".next").find('a').html(_("Restore"));
						}
						
						if(index == 3){
							install();
						}
						
					}
			},
			'onPrevious': function(tab, navigation, index){
				$(".next").find('a').html( _("Next") );
			},
			'onLast': function(tab, navigation, inde){
			},
			'onFinish': function(tab, navigation, inde){
			}
			
		});
	}
	
	/**
	 * 
	 */
	function install()
	{
		$(".next").find('a').html( _("Restoring...") );
		$(".wizard-button").disable(true);
		$("#browser-date").val(moment().format('YYYY-MM-DD HH:mm:ss'));
		$("#restore-form").submit();
	}

</script>
