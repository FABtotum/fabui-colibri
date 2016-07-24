<!--
<?php foreach($feeds as $feed):?>
	<div class="row">
		<div class="col-md-4">
			<img class="img-responsive" src="<?php echo $feed['img_src'];?>" />
			<ul class="list-inline padding-10">
				<li><i class="fa fa-calendar"></i> <?php echo $feed['date'];?></li>
			</ul>
		</div>
		<div class="col-md-8 padding-left-0">
			<h3 class="margin-top-0"><a href="javascript:void(0);"><?php echo $feed['title'];?></a></h3>
			<p><?php echo $feed['text'];?></p>
			<a class="btn btn-primary" href="javascript:void(0);"> Read more </a>
		</div>
	</div>
	<hr>
<?php endforeach;?>
  -->
<?php foreach($feeds as $feed): ?>
<div class="panel panel-default">
	<div class="panel-body status">
		<div class="who clearfix">
			<img src="<?php echo $feed['img_src'];?>" />
			<span class="name font-sm">
				<a href="#"><?php echo $feed['title'];?></a>
				<br>
				<span class="text-muted"><?php echo $feed['date'];?></span>
			</span>
			
		</div>
		<div class="text">
			<p><?php echo $feed['text'];?></p>
		</div>
		<ul class="links text-right">
			<li class="">
				<a target="_blank" href="<?php echo $feed['link'];?>"> Read More</a>
			</li>
		</ul>
	</div>
</div>
<?php endforeach;?>