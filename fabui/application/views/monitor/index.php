<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<div class="tab-content">
	<div class="tab-pane fade in active" id="dashboard-tab">
	
		<div class="row padding-10">
			<div class="col-sm-3 text-center">
				<img class="" src="/assets/img/controllers/monitor/front.png">
			</div>
			<div class="col-sm-8">
				<div class="row" style="margin-top:10px;margin-left:-50px">
					<div class="col-sm-12">
						<div class="panel panel-default">
							<div class="panel-body status">
								<div class="who clearfix">
									<span class="icon"><i class="fa fa-play fa-rotate-90 fa-border fa-2x"></i></span>
									<span class="name"><b>Core 1</b>
									<span class="pull-right"></span></span>
									<span class="from">169.254.1.2</span>
								</div>
								<div class="text" id="unit-1-content">
								</div>
								<ul class="links">
									<li><button type="button" data-unit="1" class="btn btn-default view-unit"><i class="fa fa-eye"></i> View </button></li>
									<li><button type="button" class="btn btn-default"><i class="fa fa-lock"></i> Lock </button></li>
									<li><button type="button" class="btn btn-default"><i class="fa fa-unlock"></i> Unlock </button></li>
									<li><button type="button" class="btn btn-default"><i class="fa fa-refresh"></i> Reboot </button></li>
									<li><button type="button" class="btn btn-default"><i class="fa fa-power-off"></i> Off </button></li>
									<li class="pull-right"><button type="button" data-unit="1" class="btn btn-default abort-unit"><i class="fa fa-stop"></i> Abort </button></li>
									<li class="pull-right"><button id="play-resume-unit-1" data-unit="1" type="button" data-action="play" class="btn btn-default play-resume-unit "><i class="fa fa-play"></i> Play </button></li>
								</ul>
							</div>
						</div>
					</div>
				</div>			
				<div class="row" style="margin-top:40px;margin-left:-50px">
					<div class="col-sm-12">
						<div class="panel panel-default">
							<div class="panel-body status">
								<div class="who clearfix">
									<span class="icon"><i class="fa fa-play fa-rotate-90 fa-border fa-2x"></i></span>
									<span class="name"><b>Core 2</b>
									<span class="pull-right"></span></span>
									<span class="from">169.254.1.3</span>
								</div>
								<div class="text">
									<div id="json-unit-2"></div>
								</div>
								<ul class="links">
									<li><button type="button" data-unit="2" class="btn btn-default view-unit"><i class="fa fa-eye"></i> View </button></li>
									<li><button type="button" class="btn btn-default"><i class="fa fa-lock"></i> Lock </button></li>
									<li><button type="button" class="btn btn-default"><i class="fa fa-unlock"></i> Unlock </button></li>
									<li><button type="button" class="btn btn-default"><i class="fa fa-refresh"></i> Reboot </button></li>
									<li><button type="button" class="btn btn-default"><i class="fa fa-power-off"></i> Off </button></li>
									<li class="pull-right"><button type="button" data-unit="2" class="btn btn-default abort-unit"><i class="fa fa-stop"></i> Abort </button></li>
									<li class="pull-right"><button id="play-resume-unit-2" data-unit="2" type="button" data-action="play" class="btn btn-default play-resume-unit "><i class="fa fa-play"></i> Play </button></li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	<!--
<?php $count = 1; ?>
<?php foreach($units as $unit): ?>
			<div class="row padding-20">
				<div class="col-sm-10">
					<h2>Core <?php echo $count; ?></h2>
					<div id="json-unit-<?php echo $count?>"></div>
				</div>
			</div>
		<hr class="simple">
<?php $count++; ?>
<?php endforeach; ?>
  -->
	</div>
<?php $count = 1; ?>
<?php foreach($units as $unit): ?>
	<div class="tab-pane fade in" id="unit-<?php echo $count; ?>">
		<div class="row">
			<div class="col-sm-12">
				<iframe id="unit-<?php echo $count; ?>" class="google_maps unit-container hidden" src="http://<?php echo $unit;?>"></iframe>
			</div>
		</div>
	</div>
<?php $count++; ?>
<?php endforeach; ?>
</div>
