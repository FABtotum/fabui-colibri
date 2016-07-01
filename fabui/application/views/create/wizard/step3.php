<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<div class="step-pane <?php echo $runningTask ? 'active' : ''; ?>" id="step3">
	<hr class="simple">
	<ul id="createFeed" class="nav nav-tabs bordered">
		<li class="active"><a href="#live-feeds-tab" data-toggle="tab">Live feeds</a></li>
		<li><a href="#controls-tab" data-toggle="tab">Controls</a></li>
	</ul>
	<div id="createFeedContent" class="tab-content padding-10">
		<div class="tab-pane fade in active" id="live-feeds-tab">
			<div class="row">
				<div class="col-sm-6 show-stats">
					<div class="row">
						<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12"> <span class="text"> Progress <span class="pull-right task-progress"></span> </span>
							<div class="progress">
								<div class="progress-bar bg-color-blue" id="task-progress-bar"></div>
							</div> </div>
						<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12"> <span class="text"> Speed <span class="pull-right"><span class="task-speed"></span> / 500 %</span> </span>
							<div class="progress">
								<div class="progress-bar bg-color-blue" id="task-speed-bar"></div>
							</div> </div>
						<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12"> <span class="text"> Flow rate<span class="pull-right"><span class="task-flow-rate"></span> / 500 %</span></span></span>
							<div class="progress">
								<div class="progress-bar bg-color-blue" id="task-flow-rate-bar"></div>
							</div> </div>
						<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12"> <span class="text"> Fan <span class="pull-right"><span class="task-fan"></span></span> </span>
							<div class="progress">
								<div class="progress-bar bg-color-blue" id="task-fan-bar"></div>
							</div> </div>
						<span class="show-stat-buttons"> 
							<span class="col-xs-12 col-sm-6 col-md-6 col-lg-6"> 
								<button type="button" data-action="abort"  class="btn btn-default btn-block hidden-xs action">Abort Print</button> 
							</span> 
							<span class="col-xs-12 col-sm-6 col-md-6 col-lg-6"> 
								<button type="button" data-action="pause"  class="btn btn-default btn-block hidden-xs action">Pause Print</button> 
							</span> 
						</span>
					</div>
				</div>
				<div class="col-sm-6">
					<div id="temperatures-chart" class="chart"> </div>
				</div>
			</div>
		</div>
		<div class="tab-pane fade in active" id="controls-tab"></div>
	</div>
</div>