<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>
<div class="row">
	<div class="col-sm-12">
		<form class="smart-form" id="user-form" method="post">
			<fieldset>
				<legend><?php echo _("Personal info"); ?></legend>
				<div class="row">
					<section class="col col-6">
						<label class="label"><?php echo _("First name"); ?></label>
						<label class="input">
							<i class="icon-prepend fa fa-user"></i>
							<input type="text" placeholder="<?php echo _("First name"); ?>" name="first_name" id="first_name" value="<?php echo $this->session->user['first_name'] ?>">
						</label>
					</section>
					<section class="col col-6">
						<label class="label"><?php echo _("Last name"); ?></label>
						<label class="input">
							<i class="icon-prepend fa fa-user"></i>
							<input type="text" placeholder="<?php echo _("Last name"); ?>" name="last_name" id="last_name" value="<?php echo $this->session->user['last_name'] ?>">
						</label>
					</section>
				</div>
				<section >
					<label class="label"><?php echo _("Email")?></label>
					<label class="input">
						<i class="icon-prepend fa fa fa-envelope-o"></i>
						<input type="text" name="email" id="email" value="<?php echo $this->session->user['email'] ?>">
					</label>
				</section>
				<?php if($user['role'] == 'administrator'):?>
				<section>
					<label class="label"><?php echo _("Language")?></label>
					<label class="select">
						<?php echo langauges_menu('form-control', 'settings-locale', 'id="settings-locale"');?> <i></i>
					</label>
				</section>
				<?php endif;?>
			</fieldset>
		</form>	
	</div>
</div>
<?php if($user['role'] == 'administrator' && $fabid_active):?>
<div class="row">
	<div class="col-sm-12 margin-bottom-10">
		<?php if(!isset($user['settings']['fabid']['email'])):?>
			<div style="padding:25px 14px 5px;">
				<span>
					<button id="fabidModalButton" class="btn btn-default"><i class="fa fa-link"></i> <?php echo _("Connect to your FABID account"); ?></button>
					<a target="_blank" style="margin-left:10px;" href="https://my.fabtotum.com/myfabtotum" class="no-ajax"><?php echo _("Need an account?"); ?></a>
				</span>
			</div>
		<?php else: ?>
			<div style="padding:25px 14px 5px;">
				<span>
					<button class="btn btn-success"><i class="fa fa-check"></i> <?php echo _("Connected via FABID"); ?> (<?php echo $user['settings']['fabid']['email'] ?>)</button>
					<a style="margin-left:10px;" href="javascript:void(0);" id="fabid-disconnect-button" ><?php echo _("Disconnect"); ?></a>
				</span>
			</div>
		<?php endif;?>
	</div>
</div>
<!-- FABID MODAL -->
<div class="modal fade" id="fabidModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><?php echo _("Connect to your FABID account") ?></h4>
			</div>
			<div class="modal-body" >
				<div class="row">
					<div class="col-sm-12">
						<form class="smart-form" id="fabid-form">
							<fieldset>
								<section>
									<label class="label"><?php echo _("Email"); ?></label>
									<label class="input">
										<input type="email" id="fabid_email" name="fabid_email">
									</label>
								</section>
								<section>
									<label class="label"><?php echo _("Password"); ?></label>
									<label class="input">
										<input type="password" id="fabid_password" name="fabid_password">
									</label>
								</section>
								<section>
									<div class="note">
										<a target="_blank" href="https://my.fabtotum.com/myfabtotum" class="no-ajax"><?php echo _("Need an account?"); ?></a>
									</div>
								</section>
							</fieldset>
						</form>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _('Cancel')?></button>
				<button type="button" class="btn btn-primary" id="fabid-connect-button"><i class="fa fa-link"></i> <?php echo _('Connect')?> </button>
			</div>
		</div>
	</div>
</div>
<?php endif;?>