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
	<span id="extr-page-header-space"><span> </span> <a href="javascript:void(0);" class="btn btn-danger power-off"><i class="fa fa-power-off"></i></a> </span>
	<?php if($mode == 'login'): ?>
	<span id="extr-page-header-space"> <button style="font-weight: 400; text-transform: none;" id="local-access" type="button" class="btn btn-default"><?php echo _("Local access");?></button> </span>
	<?php elseif($mode == 'register'): ?>
	<span id="extr-page-header-space"> <span class="hidden-mobile hiddex-xs"><?php echo _("Already registered?");?></span> <a href="<?php echo site_url('login') ?>" class="btn btn-primary"><?php echo _("Sign in");?></a> </span>
	<?php endif; ?>
</header>
