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
			<a href="<?php echo site_url('#account'); ?>">
				<?php if(isset($this->session->user['settings']['image']['url']) && $this->session->user['settings']['image']['url'] != ''):?>
					<img src="<?php echo $this->session->user['settings']['image']['url']; ?>" alt="me" class="online" />
				<?php else:?>
					<img src="/assets/img/avatars/male.png" alt="me" />
				<?php endif;?>
				<span id="user-name"><?php echo isset($this->session->user['first_name']) ?  $this->session->user['first_name'] : '' ?> <?php echo isset($this->session->user['last_name']) ?  $this->session->user['last_name'] : '' ?></span>
			</a>
		</span>
	</div>
	<nav><?php echo buildMenu($this->menu); ?></nav>
	<span class="minifyme" data-action="minifyMenu"> <i class="fa fa-arrow-circle-left hit"></i> </span>
</aside>