<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<?php if(isset($alert['type'])): ?>
	<div class="row">
		<div class="col-sm-3 hidden-xs hidden-sm"></div>
		<div class="col-sm-6">
			<div class="alert <?php echo $alert['type'] ?> animated bounce">
				<?php echo $alert['message']; ?>
			</div>
		</div>
		<div class="col-sm-3 hidden-xs hidden-sm"></div>
	</div>
<?php endif; ?>
<div class="row">
	<div class="col-sm-3 hidden-xs hidden-sm"></div>
	<div class="col-sm-6 col-xs-12">
		<div class="well no-padding">
			<form action="<?php echo site_url('login/do'); ?>" method="POST" id="login-form" class="smart-form client-form">
				<header><i class="fa fa-play fa-rotate-90"></i> <?php echo _("Sign In");?></header>
				<fieldset>
					<section>
						<label class="label"><?php echo _("Email");?></label>
						<label class="input"> <i class="icon-append fa fa-user"></i>
							<input type="email" name="email">
							<b class="tooltip tooltip-top-right"><i class="fa fa-user txt-color-teal"></i> <?php echo _("Please enter your email address");?></b></label>
					</section>
					<section>
						<label class="label"><?php echo _("Password");?></label>
						<label class="input"> <i class="icon-append fa fa-lock"></i>
							<input type="password" name="password">
							<b class="tooltip tooltip-top-right"><i class="fa fa-lock txt-color-teal"></i> <?php echo _("Enter your password");?></b> </label>
						<div class="note">
							<a href="#" id="forgot-password"><?php echo _("Forgot password?");?></a>
						</div>
					</section>
					<section>
						<label class="checkbox">
							<input type="checkbox" name="remember">
							<i></i><?php echo _("Stay signed in");?></label>
					</section>
				</fieldset>
				<footer>
					<button type="submit"  class="btn btn-primary"><?php echo _("Sign In");?></button>
				</footer>
				<input type="hidden" name="browser-date" id="browser-date">
			</form>
		</div>
	</div>
	<div class="col-sm-3 hidden-xs hidden-sm"></div>
</div>


<div class="modal fade" id="password-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
		<?php if(isInternetAvaialable()): ?>
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
					&times;
				</button>
				<h4 class="modal-title" id="myModalLabel"><?php echo _("Don't panic");?></h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">
						<p>
							<?php echo _("Enter the email address you used when creating the account and click <strong>Send Email</strong>.<br>A message will be sent to that address containing a link to reset your password");?>
						</p>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div class="form-group">
							<input id="mail-for-reset" type="email" class="form-control invalid" placeholder="example@fabtotum.com" required />
							<em id="error-message" style="margin-top:5px; color:#D56161; display: none;"><?php echo _("This email is not recognized by the printer, please insert a valid email");?></em>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _("Cancel");?></button>
				<button type="button" id="send-mail" class="btn btn-primary"><?php echo _("Send Email");?></button>
			</div>
		<?php else: ?>
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
					&times;
				</button>
				<h4 class="modal-title" id="myModalLabel"><?php echo _("Don't panic");?></h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">
						<p>
							<?php echo _("To be able to send an email with a reset link you need to be connected to the internet.");?>
						</p>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _("Cancel");?></button>
				<button type="button" id="reload-page" class="btn btn-primary">M<?php echo _("Reload");?></button>
			</div>
		<?php endif; ?>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

