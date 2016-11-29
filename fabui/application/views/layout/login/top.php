<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>
<header id="header">
	<div id="logo-group">
		<span id="logo"> <img src="/assets/img/fabui_v1.png" alt="FABUI"> </span>
	</div>
	<?php if($mode == 'login'): ?>
	<span id="extr-page-header-space"> <span class="hidden-mobile hiddex-xs">Need an account?</span> <a href="<?php echo site_url('login/new-account') ?>" class="btn btn-primary">Create account</a> </span>
	<?php elseif($mode == 'register'): ?>
	<span id="extr-page-header-space"> <span class="hidden-mobile hiddex-xs">Already registered?</span> <a href="<?php echo site_url('login') ?>" class="btn btn-danger">Sign In</a> </span>
	<?php endif; ?>
</header>