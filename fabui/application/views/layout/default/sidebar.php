<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>

<aside id="left-panel">
	<!-- User info -->
	<div class="login-info">
		<span>
			<a href="<?php echo site_url('user'); ?>">
				<img src="/assets/img/avatars/male.png" alt="me" class="online" />
				<span><?php echo $this->session->user['first_name'].' '.$this->session->user['last_name'] ?></span>
			</a>
		</span>
	</div>
	<nav><?php echo $menu; ?></nav>
	<span class="minifyme" data-action="minifyMenu"> <i class="fa fa-arrow-circle-left hit"></i> </span>
</aside>