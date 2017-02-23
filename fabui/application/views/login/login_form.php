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
							<b class="tooltip tooltip-top-right"><i class="fa fa-user txt-color-teal"></i> <?php echo _("Please enter email address");?></b></label>
					</section>
					<section>
						<label class="label"><?php echo _("Password");?></label>
						<label class="input"> <i class="icon-append fa fa-lock"></i>
							<input type="password" name="password">
							<b class="tooltip tooltip-top-right"><i class="fa fa-lock txt-color-teal"></i> <?php echo _("Enter your password");?></b> </label>
						<div class="note">
							<a href="#"><?php echo _("Forgot password");?>?</a>
						</div>
					</section>
					<section>
						<label class="checkbox">
							<input type="checkbox" name="remember" checked="">
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