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
	<span id="extr-page-header-space"> <span class="hidden-mobile hiddex-xs"><?php echo _("Need an account");?>?</span> <a href="<?php echo site_url('login/new-account') ?>" class="btn btn-primary"><?php echo _("Create account");?></a> </span>
	<?php elseif($mode == 'register'): ?>
	<span id="extr-page-header-space"> <span class="hidden-mobile hiddex-xs"><?php echo _("Already registered");?>?</span> <a href="<?php echo site_url('login') ?>" class="btn btn-danger"><?php echo _("Sign in");?></a> </span>
	<?php endif; ?>
</header>