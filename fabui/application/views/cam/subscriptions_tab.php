<?php
/**
 * 
 * @author FABteam
 * @version 0.0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
$remainingDays = 0;
if($subscription_exists){
	$expirationDate = new DateTime(date('Y-m-d H:i', strtotime($subscription_code['expiration_date'])));
	$today          = new DateTime(date('Y-m-d H:i'));
	$remainingDays = $expirationDate->diff($today)->days;
}
?>
<!-- SUBSCRIPTIONS TAB -->
<div class="tab-pane fade in" id="subscriptions-tab">
	<div id="settings-add-new-code" class="row <?php echo $subscription_exists ? 'hidden' : '';?>">
		<div class="col-sm-12">
			<p><?php echo _("You must enter a valid subscription code in order to use CAM Toolbox"); ?> <button id="add-subscription-button" class="btn btn-default"><i class="fa fa-key"></i> <?php echo _("Add");?></button></p>
			<p class="margin-top-10"><?php echo _("Need a subscription code"); ?>? <a target="_blank" class="no-ajax" href="https://www.fabtotum.com/software/cam-toolbox-cloud-based-cam-suite/"><?php echo _("Get it here"); ?></a> </p>
		</div>
	</div>
	<div id="settings-code-container" class="row <?php echo !$subscription_exists ? 'hidden' : '';?>">
		<div class="col-sm-12">
			<h5><?php echo _("Subscription code");?></h5>
			<table class="table table-bordered" id="subscription-codes-table">
				<?php if($subscription_exists):?>
				<thead>
					<tr>
						<th><?php echo _("Code");?></th>
						<th><?php echo _("Status");?></th>
						<th><span class="hidden-xs"><?php echo _("Expiration date");?></span><span class="visible-xs"><?php echo _("Exp. date");?></span></th>
						<th width="20"></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td width="300"><strong><span class="visible-code hidden"><?php echo $subscription_code['code']; ?></span> <span class="hidden-code"><?php echo str_repeat( "*", strlen( $subscription_code['code'] ) );?></span> <span class="pull-right"><i title="<?php echo _("Press to view code");?>" style="cursor:pointer;" class="fa fa-eye code-visible-button"></i></span></strong></td>
						<td><span class="center-block padding-5 label label-<?php echo $subscription_code['status'] == 'active' ? 'success' : 'danger';?>"><strong><?php echo $subscription_code['status']; ?></strong></span></td>
						<td><?php echo date('d/m/Y', strtotime($subscription_code['expiration_date'])) ; ?>  (<?php echo str_replace("{0}", $remainingDays, _("{0} days remaining"));?>)</td>
						<td class="text-center"><button title="<?php echo _("Remove"); ?>" id="remove-subscription-button" class="btn btn-danger"><i class="fa fa-trash"></i></button></td>
					</tr>
				</tbody>
				<?php endif;?>
			</table>
		</div>
	</div>
</div>
<!-- END SETTINGS TAB -->
<!-- SUBSCRIPTION MODAL -->
<div class="modal fade" id="subscriptionModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><i class="fabui-edit-file"></i> <?php echo _("CAM toolbox"); ?></h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-12">
						<p><?php echo _("You must enter a valid subscription code in order to use CAM toolbox"); ?></p>
						<form id="modal-subscription-form">
							<div class="smart-form" style="margin-top:20px;margin-bottom:30px;">
								<label class="input">
									<input name="modal_subscription" id="modal-subscription" type="text" style="padding-left:5px!important;" placeholder="<?php echo _("Type here your subscription code"); ?>">
								</label>
							</div>
						</form>
						<p class="margin-top-10"><?php echo _("Need a subscription code"); ?>? <a class="no-ajax" target="_blank" href="https://www.fabtotum.com/software/cam-toolbox-cloud-based-cam-suite/"><?php echo _("Get it here"); ?></a> </p>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _("Close");?></button>
				<button type="button" class="btn btn-default action-button" data-type="modal" data-action="active-subscription" id="modal-active-subscription"><i class="fa fa-star"></i> <?php echo _("Activate");?></button>
			</div>
		</div>
	</div>
</div>
<!-- END SUBSCRIPTION MODAL -->