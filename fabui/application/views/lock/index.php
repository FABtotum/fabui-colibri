<form class="lockscreen animated flipInY" action="<?php echo site_url()?>">
	<div class="logo">
		<img src="/assets/img/fabui_v1.png" style="width:30%;" /> 
	</div>
	<div>
		<img src="/assets/img/avatars/male.png" alt="" width="120" height="120" />
		<div>
			<h1><i class="fa fa-user fa-3x text-muted air air-top-right hidden-mobile"></i><?php echo $this->session->user['first_name'].' '.$this->session->user['last_name']; ?> <small><i class="fa fa-lock text-muted"></i> &nbsp;Locked</small></h1>
			<button class="btn btn-primary  btn-block margin-top-30" type="submit"><i class="fa fa-unlock"></i> Unlock</button>
		</div>
	</div>
</form>
