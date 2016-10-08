<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 * 
 */
 
?>
<div class="step-pane <?php echo $runningTask ? 'active' : ''; ?>" id="step4" data-step="4">
	<hr class="simple">
	<ul id="step4-tab" class="nav nav-tabs bordered">
		<li class="active">
			<a href="#live-feed" data-toggle="tab"> Live Feed</a>
		</li>
	</ul>
	<div id="myTabContent1" class="tab-content padding-10">
		<div class="tab-pane fade in active" id="live-feed">
			<div class="row">
				<div class="col-sm-6 show-stats">
					<div class="row">
						<div class="col-sm-12"> <span class="text"> Progress <span class="pull-right task-progress"></span> </span>
							<div class="progress">
								<div class="progress-bar" id="task-progress-bar"></div>
							</div> 
						</div>
						<div class="col-sm-12 postprocessing" style="display:none;"> <span class="text"> Post processing <span class="pull-right postprocessing-progress"></span> </span>
							<div class="progress">
								<div class="progress-bar" id="postprocessing-progress-bar"></div>
							</div> 
						</div>
					</div>
				</div>
				<div class="col-sm-6 show-stats">
					
					<!--  <div class="row">
						<div class="col-sm-12">
							<h5>Rotatory Scan</h5>
						</div>
					</div>-->
					<div class="row">
						<div class="col-sm-12"> <span class="text"> Slice <span class="pull-right"> <span class="current-scan"></span> of <span class="total-scan"></span> </span> </span>
							<div class="fake-progress"></div>
						</div>
						<div class="col-sm-12"> <span class="text"> Image resolution <span class="pull-right"> <span class="resolution-width"></span> x <span class="resolution-height"></span> </span> </span>
							<div class="fake-progress"></div>
						</div>
						<div class="col-sm-12"> <span class="text"> Iso <span class="pull-right"> <span class="iso"></span>  </span> </span>
							<div class="fake-progress"></div>
						</div>
						<hr class="simple">
						<div class="pointcloudinfo" style="display:none">
							<div class="col-sm-12"> <span class="text"> Cloud points <span class="pull-right"> # <span class="cloud-points"></span>  </span> </span>
								<div class="fake-progress"></div>
							</div>
							<div class="col-sm-12"> <span class="text"> Cloud size <span class="pull-right">  <span class="cloud-size"></span>  </span> </span>
								<div class="fake-progress"></div>
							</div>
						</div>
						<hr class="simple">
						<div class="col-sm-12"> <span class="text"> Elapsed time <span class="pull-right"><span class="elapsed-time"></span> </span> </span>
							<div class="fake-progress"></div>
						</div>
					</div>
					<hr class="simple">
					<div class="row">
						<div class="col-sm-6">
							<button type="button" class="btn btn-default btn-sm btn-block abort"><i class="fa fa-stop"></i> Abort scan</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
