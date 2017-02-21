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
	<div class="col-sm-3 hidden-xs hidden-sm"></div>
	<div class="col-sm-6">
		<div class="well no-padding">
			<form action="<?php echo site_url('login/doNewAccount'); ?>" method="POST" id="register-form" class="smart-form client-form">
				<header><i class="fa fa-play fa-rotate-90"></i> <?php echo _("Register new account");?></header>
				<fieldset>
					<section>
						<label class="input"> <i class="icon-append fa fa-user"></i>
							<input type="email" name="email" placeholder="Email">
							<b class="tooltip tooltip-top-right"><i class="fa fa-user txt-color-teal"></i> <?php echo _("Needed to enter the fabui");?></b></label>
					</section>
					<section>
						<label class="input"> <i class="icon-append fa fa-lock"></i>
							<input type="password" name="password" placeholder="Password" id="password">
							<b class="tooltip tooltip-top-right"><i class="fa fa-lock txt-color-teal"></i> <?php echo _("Don't forget your password");?></b> </label>
					</section>
					<section>
						<label class="input"> <i class="icon-append fa fa-lock"></i>
							<input type="password" name="passwordConfirm" placeholder="Confirm password">
							<b class="tooltip tooltip-top-right"><i class="fa fa-lock txt-color-teal"></i> <?php echo _("Don't forget your password");?></b> </label>
					</section>
				</fieldset>
				<fieldset>
					<div class="row">
						<section class="col col-6">
							<label class="input"> 
								<input type="text" placeholder="<?php echo _("First name");?>" name="first_name">
							</label>
						</section>
						<section class="col col-6">
							<label class="input"> 
								<input type="text" placeholder="<?php echo _("Last name");?>" name="last_name">
							</label>
						</section>
					</div>
					<section>
						<label class="checkbox">
							<input type="checkbox" name="terms" id="terms">
							<i></i><?php echo _("I agree with the");?> <a href="#" data-toggle="modal" data-target="#myModal"> <?php echo _("Terms and Conditions");?> </a>
						</label>
					</section>
				</fieldset>
				<footer>
					<button type="submit" class="btn btn-primary"><?php echo _("Register");?></button>
				</footer>
			</form>
		</div>
	</div>
	<div class="col-sm-3 hidden-xs hidden-sm"></div>
</div>
<!-- TERMS & CONDITIONS MODAL -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="myModalLabel">Terms & Conditions</h4>
			</div>
				<div class="modal-body custom-scroll terms-body">
					<div><?php echo termsAndConditions(); ?></div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _("Cancel");?></button>
					<button type="button" class="btn btn-primary" id="i-agree"><i class="fa fa-check"></i> <?php echo _("I agree");?> </button>
					
					<button type="button" class="btn btn-danger pull-left" id="print"><i class="fa fa-print"></i> <?php echo _("Print");?></button>
				</div>
			</div>
		</div>
	</div>
