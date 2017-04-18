<?php
/**
 * 
 * @author Krios Mane, Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<div class="row">
					
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
		<div class="well no-padding">

			<form action="<?php echo site_url('login/doReset') ?>" id="form-register" class="smart-form client-form" method="POST" >
				<header>
					<?php echo pyformat( _("Hi {0}, below you can reset your password"), array($user['first_name']) );?>
				</header>

				<fieldset>
					
					<section>
						<label class="input"> <i class="icon-append fa fa-lock"></i>
							<input type="password" name="password" placeholder="New password" id="password">
							<b class="tooltip tooltip-bottom-right"><?php echo _("Don't forget your password");?></b> </label>
					</section>

					<section>
						<label class="input"> <i class="icon-append fa fa-lock"></i>
							<input type="password" name="passwordConfirm" placeholder="Confirm new password">
							<b class="tooltip tooltip-bottom-right"><?php echo _("Don't forget your password");?></b> </label>
					</section>
				</fieldset>
				<footer>
					<button id="register-button" type="button" class="btn btn-primary">
						<?php echo _("Reset");?>
					</button>
				</footer>
			
			
				<input type="hidden" name="token" value="<?php echo $token; ?>">
				
			</form>

		</div>
		
</div>
