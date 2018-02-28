<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
$is_admin = $this->session->user['role'] == 'administrator';
?>

<div class="row">
	<div class="col-sm-12">
		<div>
			<table id="users-table" class="table table-responsive table-striped table-bordered table-hover" width="100%">
				<thead>
					<tr>
						<th width="20"></th>
						<th width="100"><?php echo _("Role") ?></th>
						<th><?php echo _("Name") ?></th>
						<th><?php echo _("Email");?></th>
						<th><?php echo _("FABID");?></th>
						<th><?php echo _("Last login");?></th>
						<th width="20"></th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</div>
</div>
<?php if($is_admin):?>
<!-- DELETE USER MODAL -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><?php echo _("User deletion"); ?></h4>
			</div>
			<div class="modal-body">
				<h5><?php echo _("Before deleting this user, you can transfer data associated with it to a new owner.");?></h5>
				<div class="smart-form">
					<fieldset>
						<section>
							<label class="checkbox"><input id="trasnfer-data" type="checkbox"> <i></i><?php echo _("Transfer data");?> </label>
						</section>
						<section class="list-users-container" style="display:none;">
							<label class="label"><?php echo _("Select new owner");?></label>
							<label class="select">
								<select id="users"></select> <i></i>
							</label>
						</section>
					</fieldset>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _("Cancel");?></button>
				<button type="button" class="btn btn-danger" id="delete-user-button"> <?php echo _("Delete");?></button>
			</div>
		</div>
	</div>
</div>
<!-- END DELETE USER MODAL -->
<?php endif;?>