<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>
<div class="tab-content padding-10">
	<!-- ACCOUNT TAB -->
	<div class="tab-pane fade in active" id="account-tab">
		<div class="row">
			<div class="col-sm-12">
				<form class="smart-form"  enctype="multipart/form-data"  id="user-form" method="post" action="<?php echo site_url('account/saveUser/'.$this->session->user['id']); ?>">
					<fieldset>
						<div class="row">
							<section class="col col-6">
								<label class="label"><?php echo _("First name"); ?></label> <label
									class="input"> <i class="icon-prepend fa fa-user"></i> <input
									type="text" placeholder="<?php echo _("First name"); ?>"
									name="first_name" id="first_name"
									value="<?php echo $user['first_name'] ?>">
								</label>
							</section>
							<section class="col col-6">
								<label class="label"><?php echo _("Last name"); ?></label> <label
									class="input"> <i class="icon-prepend fa fa-user"></i> <input
									type="text" placeholder="<?php echo _("Last name"); ?>"
									name="last_name" id="last_name"
									value="<?php echo $user['last_name'] ?>">
								</label>
							</section>
						</div>
						<div class="row">
    						<section class="col col-6">
    							<label class="label"><?php echo _("Email")?></label> <label
    								class="input"> <i class="icon-prepend fa fa-envelope"></i>
    								<input type="text" name="email" id="email"
    								value="<?php echo $user['email'] ?>">
    							</label>
    						</section>
        				</div>
        				<div class="row">
        					<section class="col col-6">
								<label class="label"><?php echo _("Profile picture"); ?></label>
								<label for="file" class="input input-file">
									<div class="button"><input type="file" name="profile-image" id="file" accept="image/*"><?php echo _("Browse");?></div> 
									<i class="icon-prepend far fa-smile"></i>
									<input type="text" id="image-name" readonly="readonly" placeholder="<?php echo $has_image ? _("Change image") : _("Load a nice pic");?>" >
								</label>
								<div class="margin-top-20 preview">
									<?php if(isset($user['settings']['image']['url']) && $user['settings']['image']['url'] =! ''):?>
										<img width="100" src="<?php echo $this->session->user['settings']['image']['url']; ?>">
									<?php endif;?>
								</div>
							</section>
        				</div>
        			</fieldset>
				</form>
			</div>
		</div>
        <?php if($user['role'] == 'administrator' && $fabid_active):?>
        <div class="row">
			<div class="col-sm-12 margin-bottom-10">
        		<?php if(!isset($user['settings']['fabid']['logged_in']) || $user['settings']['fabid']['logged_in'] == false):?>
        			<div style="padding: 25px 14px 5px;">
					<span>
						<button id="fabidModalButton" class="btn btn-default">
							<i class="fa fa-link"></i> <?php echo _("Connect to your FABID account"); ?></button>
						<a target="_blank" style="margin-left: 10px;"
						href="https://my.fabtotum.com/myfabtotum" class="no-ajax"><?php echo _("Need an account?"); ?></a>
					</span>
				</div>
        		<?php else: ?>
        			<div style="padding: 25px 14px 5px;">
					<span>
						<button class="btn btn-success">
							<i class="fa fa-check"></i> <?php echo _("FABID connected"); ?> (<?php echo $user['settings']['fabid']['email'] ?>)</button>
						<a style="margin-left: 10px;" href="javascript:void(0);"
						id="fabid-disconnect-button"><?php echo _("Disconnect"); ?></a>
					</span>
				</div>
        		<?php endif;?>
        	</div>
		</div>
		<!-- FABID MODAL -->
		<div class="modal fade" id="fabidModal" tabindex="-1" role="dialog"
			aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title"><?php echo _("Connect to your FABID account") ?></h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-sm-12">
								<form class="smart-form" id="fabid-form">
									<fieldset>
										<section>
											<label class="label"><?php echo _("Email"); ?></label> <label
												class="input"> <input type="email" id="fabid_email"
												name="fabid_email">
											</label>
										</section>
										<section>
											<label class="label"><?php echo _("Password"); ?></label> <label
												class="input"> <input type="password" id="fabid_password"
												name="fabid_password">
											</label>
										</section>
										<section>
											<div class="note">
												<a target="_blank" href="https://my.fabtotum.com/myfabtotum"
													class="no-ajax"><?php echo _("Need an account?"); ?></a>
											</div>
										</section>
									</fieldset>
								</form>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _('Cancel')?></button>
						<button type="button" class="btn btn-primary"
							id="fabid-connect-button">
							<i class="fa fa-link"></i> <?php echo _('Connect')?> </button>
					</div>
				</div>
			</div>
		</div>
        <?php endif;?>
    </div>
	<!-- END ACCOUNT TAB -->

	<!-- PASSWORD TAB -->
	<div class="tab-pane fade in" id="password-tab">
		<form class="smart-form" id="password-form">
			<fieldset>
				<section>
					<label class="label"><?php echo _("Enter your old password");?></label>
					<label class="input"> 
						<i class="icon-prepend fa fa-lock"></i>
						<input type="password" name="old_password" id="old_password"> 
					</label>
				</section>
				<section>
					<label class="label"><?php echo _("Enter your new password");?></label>
					<label class="input"> 
						<i class="icon-prepend fa fa-lock"></i>
						<input type="password" name="new_password" id="new_password"> 
					</label>
				</section>
				<section>
					<label class="label"><?php echo _("Confirm new password");?></label>
					<label class="input"> 
						<i class="icon-prepend fa fa-lock"></i>
						<input type="password" name="confirm_new_password" id="confirm_new_password"> 
					</label>
				</section>
			</fieldset>
		</form>
	</div>
	<!-- END PASSWORD TAB -->

	<!-- NOTIFICATIONS TAB -->
	<div class="tab-pane fade in" id="notifications-tab">
		<form class="smart-form" id="notifications-form">
			<fieldset>
				<div class="row">
					<section class="col col-6">
						<label class="label"><?php echo _("Tasks notification");?></label>
						<label class="toggle">
							<input type="checkbox" name="tasks-finish" id="tasks-finish" <?php echo isset($user['settings']['notifications']['tasks']['finish']) && $user['settings']['notifications']['tasks']['finish'] == 'true' ? 'checked="checked"' : '' ?>>
							<i data-swchon-text="ON" data-swchoff-text="OFF"></i><?php echo _("Send me a notification email when task is finished");?>
						</label>
						
						<label class="toggle">
							<input type="checkbox" name="tasks-pause" id="tasks-pause" <?php echo isset($user['settings']['notifications']['tasks']['pause']) && $user['settings']['notifications']['tasks']['pause'] == 'true' ? 'checked="checked"' : '' ?>>
							<i data-swchon-text="ON" data-swchoff-text="OFF"></i><?php echo _("Send me a notification email when task is paused");?>
						</label>
					</section>
				</div>
			</fieldset>
		</form>
	</div>
	<!-- END NOTIFICATIONS TAB -->
</div>