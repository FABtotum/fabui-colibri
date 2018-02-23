<form class="lockscreen animated flipInY" action="<?php echo site_url()?>">
	<div class="logo">
		<img src="/assets/img/fabui_v1.png" style="width:30%;" /> 
	</div>
	<div>
		<?php if(isset($this->session->user['settings']['image']['url']) && $this->session->user['settings']['image']['url'] != ''):?>
			<img src="<?php echo $this->session->user['settings']['image']['url']; ?>" alt="me"  width="120" height="120" />
		<?php else:?>
			<img src="/assets/img/avatars/male.png" alt="me" width="120" height="120" />
		<?php endif;?>
		<div>
			<h1><i class="fa fa-user fa-3x text-muted air air-top-right hidden-mobile"></i><?php echo $this->session->user['first_name'].' '.$this->session->user['last_name']; ?> <small><i class="fa fa-lock text-muted"></i> &nbsp;<?php echo _("Locked")?></small></h1>
			<button class="btn btn-primary  btn-block margin-top-30" type="submit"><i class="fa fa-unlock"></i> <?php echo _("Unlock")?></button>
		</div>
	</div>
</form>
