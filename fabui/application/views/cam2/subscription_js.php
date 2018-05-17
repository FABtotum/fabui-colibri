<?php
/**
 *
 * @author FABteam
 * @version 0.0.1
 * @license https://opensource.org/licenses/GPL-3.0
 *
 */
?>

<script type="text/javascript">
	
	var today   = moment("<?php echo date("Y-m-d H:i") ?>");
	
	$(document).ready(function() {
		$("#add-subscription-button").on('click', showSubscriptionModal);
		$("#remove-subscription-button").on('click', removeSubscription);
		
		initCodeVisibilityHandler();
	});
	
	/**
	*
	**/
	function showSubscriptionModal()
	{
		enableButton("#modal-active-subscription");
		initModalSubscriptionFormValidator();
		$('#subscriptionModal').modal({
			keyboard: false,
			backdrop: 'static'
		});
	}
	
	/**
	*
	**/
	function initModalSubscriptionFormValidator()
	{
		$("#modal-subscription-form").validate({
			// Rules for form validation
			rules : {
				modal_subscription : {
					required : true
				}
			},
			// Messages for form validation
			messages : {
				modal_subscription : {
					required : "<?php echo _("Please enter subscription code")?>"
				}
			},
			// Do not change code below
			errorPlacement : function(error, element) {
				error.insertAfter(element.parent());
			}
		});
	}
	/**
	*
	**/
	function activeSubscription(type)
	{
		if($("#"+type+"-subscription-form").valid()){

			var code = $("#"+type+"-subscription").val().trim();
			disableButton("#"+type+"-active-subscription");
			$.ajax({
				type: "POST",
				url: "<?php echo site_url('cam2/subscription/active') ?>/"+code,
				dataType: 'json',
			}).done(function( response ) {
				
				if(response.status==false){
					fabApp.showErrorAlert("<?php echo _("Activation failed"); ?>", response.message);
					enableButton("#"+type+"-active-subscription");
					handleDropzone(camDropZone, 'disable');
				}else{
					fabApp.showInfoAlert(response.message);
					$('#subscriptionModal').modal('hide');
					handleDropzone(camDropZone, 'enable');
					createSubscriptionCodesTable(response.subscription);
					$("#settings-code-container").removeClass("hidden");
					$("#settings-add-new-code").addClass("hidden");
					handleDropzone(camDropZone, 'enable');
				}
			});

		}
	}
	
	/**
	*
	**/
	function removeSubscription()
	{
		$.SmartMessageBox({
			title: "<i class='fa fa-trash'></i> <span class='txt-color-orangeDark'><strong><?php echo _("Warning");?></strong></span> ",
			content: "<span class='font-md'><?php echo _("You need a valid subscription code to use CAM Toolbox");?><br><?php echo _("Are you sure you want remove it?")?></span>",
			buttons: "[<?php echo _("No");?>][<?php echo _("Yes");?>]"
		}, function(ButtonPressed) {
		   if(ButtonPressed == "<?php echo _("Yes");?>"){
			   $.ajax({
				type: "POST",
				url: "<?php echo site_url('cam/subscription/remove') ?>",
				dataType: 'json',
			}).done(function( response ) {
				if(response.status == true){
					$("#settings-code-container").addClass("hidden");
					$("#settings-add-new-code").removeClass("hidden");
					handleDropzone(camDropZone, 'disable');
				}else{
					
				}
			});
		   }
	   });
	}
	/**
	*
	**/
	function createSubscriptionCodesTable(subscription)
	{
		//subscription-codes-table
		//var info = jQuery.parseJSON(subscription.target);
		var statusClass = subscription.status == 'active' ? 'success' : 'danger';
		var expirationDate = moment(subscription.exp_date);
		var remainingDays = expirationDate.diff(today, 'days');
		var password_symbol = "*";
		
		var html = '<thead>\
						<tr>\
							<th><?php echo _("Code");?></th>\
							<th><?php echo _("Status");?></th>\
							<th><?php echo _("Credits");?></th>\
							<th><span class="hidden-xs"><?php echo _("Expiration date");?></span><span class="visible-xs"><?php echo _("Exp. date"); ?></span></th>\
							<th width="20"></th>\
						<tr>\
					<thed>\
					<tbody>\
						<tr>\
							<td width="300"><span class="visible-code hidden"><strong>'+subscription.code+'</strong></span> <span class="hidden-code">'+password_symbol.repeat(subscription.code.length)+'</span>  <span class="pull-right"><i style="cursor:pointer;" class="fa fa-eye code-visible-button"></i></span></td>\
							<td><span class="center-block padding-5 label label-'+statusClass+'">'+subscription.status+'</span></td>\
							<td id="subscription-credits">'+subscription.credits+'</td>\
							<td>'+expirationDate.format("DD/MM/YYYY")+' ('+_("{0} remaining days").replace("{0}", remainingDays)+')</td>\
							<td class="text-center"><button title="<?php echo _("Remove"); ?>" id="remove-subscription-button" class="btn btn-danger"><i class="fa fa-trash"></i></button></td>\
						</tr>\
					</tbody>';
		$("#subscription-codes-table").html(html);
		$("#remove-subscription-button").on('click', removeSubscription);
		initCodeVisibilityHandler();
	}
	
	/**
	*
	**/
	function initCodeVisibilityHandler()
	{
		$(".code-visible-button").mouseup(function() {
			$(".visible-code").addClass('hidden');
			$(".hidden-code").removeClass('hidden');
			
		}).mousedown(function() {
			$(".visible-code").removeClass('hidden');
			$(".hidden-code").addClass('hidden');
		});
	}

</script>
