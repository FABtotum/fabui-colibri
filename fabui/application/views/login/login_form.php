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
			<form action="<?php echo site_url('login/do'); ?>" method="POST" id="login-form" class="smart-form client-form">
				<header><i class="fa fa-play fa-rotate-90"></i> Sign In</header>
				<fieldset>
					<section>
						<label class="label">E-mail</label>
						<label class="input"> <i class="icon-append fa fa-user"></i>
							<input type="email" name="email">
							<b class="tooltip tooltip-top-right"><i class="fa fa-user txt-color-teal"></i> Please enter email address/username</b></label>
					</section>
					<section>
						<label class="label">Password</label>
						<label class="input"> <i class="icon-append fa fa-lock"></i>
							<input type="password" name="password">
							<b class="tooltip tooltip-top-right"><i class="fa fa-lock txt-color-teal"></i> Enter your password</b> </label>
						<div class="note">
							<a href="/forgotpassword.php">Forgot password?</a>
						</div>
					</section>
					<section>
						<label class="checkbox">
							<input type="checkbox" name="remember" checked="">
							<i></i>Stay signed in</label>
					</section>
					
				</fieldset>
				<footer>
					<button type="submit" class="btn btn-primary">Sign in</button>
				</footer>
			</form>
		</div>
	</div>
	<div class="col-sm-3 hidden-xs hidden-sm"></div>
</div>