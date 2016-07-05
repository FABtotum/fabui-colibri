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
		<li><a href="#controls-tab" data-toggle="tab"><i class="fa fa-sliders"></i> Controls</a></li>
	</ul>
	<div id="createFeedContent" class="tab-content padding-10">
		<div class="tab-pane fade in active" id="live-feeds-tab">
			<div class="row">
				<?php if($type == 'print'): ?>
				<div class="col-sm-6">
					<div class="row">
						<span class="col-xs-6 col-sm-6 col-md-12 col-lg-6 text-center">
							<span>Extruder <span class="extruder-temp"></span> / <span class="extruder-target"></span> </span>
						</span>
						<span class="col-xs-6 col-sm-6 col-md-12 col-lg-6 text-center">
							<span>Bed <span class="bed-temp"></span> / <span class="bed-target"></span></span>
						</span>
					</div>
					<div class="row">
						<div id="temperatures-chart" class="chart"> </div>			
					</div>
				</div>
				<?php endif; ?>
				<div class="col-sm-<?php echo $type == 'print' ? '6' : '12' ?> show-stats">
					<div class="row ">
						<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12"> <span class="text"> Progress <span class="pull-right task-progress"></span> </span>
							<div class="progress">
								<div class="progress-bar bg-color-blue" id="task-progress-bar"></div>
							</div> </div>
						<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12"> <span class="text"> Speed <span class="pull-right"><span class="task-speed"></span> / 500 %</span> </span>
							<div class="progress">
								<div class="progress-bar bg-color-blue" id="task-speed-bar"></div>
							</div> </div>
						<?php if($type == 'print'): ?>
						<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12"> <span class="text"> Flow rate<span class="pull-right"><span class="task-flow-rate"></span> / 500 %</span></span></span>
							<div class="progress">
								<div class="progress-bar bg-color-blue" id="task-flow-rate-bar"></div>
							</div> </div>
						<?php endif; ?>
						<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12"> <span class="text"> Fan <span class="pull-right"><span class="task-fan"></span> %</span> </span>
							<div class="progress">
								<div class="progress-bar bg-color-blue" id="task-fan-bar"></div>
							</div> </div>
						<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12"> <span class="text"> Elapsed time <span class="pull-right"><span class="elapsed-time"></span> </span> </span>
							<div class="fake-progress"></div>
						</div>
						<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12"> <span class="text"> Estimated time left <span class="pull-right"><span class="estimated-time-left"></span> </span> </span>
							<div class="fake-progress"></div>
						</div>
						<span class="show-stat-buttons"> 
							<span class="col-xs-6 col-sm-6 col-md-6 col-lg-6"> 
								<button type="button" data-action="abort"  class="btn btn-default btn-block  action"><i class="fa fa-stop"></i> Abort <?php echo ucfirst($type) ?></button> 
							</span> 
							<span class="col-xs-6 col-sm-6 col-md-6 col-lg-6"> 
								<button type="button" data-action="pause"  class="btn btn-default btn-block  action isPaused-button"><i class="fa fa-pause"></i> Pause <?php echo ucfirst($type) ?></button> 
							</span> 
						</span>
					</div>
				</div>
				
			</div>
		</div>
		<div class="tab-pane fade in" id="controls-tab">
			<div class="row">
				<div class="col-sm-6">
					<div class="smart-form">
						<fieldset>
							<section>
								<label class="label text-center">Change Z Height</label>
							</section>
							<div class="row">
								<section class="col col-4">
									<button type="button" data-action="zHeight" data-attribute="+" class="btn btn-default btn-block action"  style="padding:6px 10px 5px"><i class="fa fa-minus"></i></button>
								</section>
								<section class="col col-4">
									<label class="select">
										<?php echo form_dropdown('zHeight', $zHeightOptions, '', 'id="zHeight"'); ?> <i></i>
									</label>
								</section>
								<section class="col col-4">
									<button type="button" data-action="zHeight" data-attribute="-" class="btn btn-default btn-block action"  style="padding:6px 10px 5px"><i class="fa fa-plus"></i></button>
								</section>
							</div>
						</fieldset>
					</div>
				</div>
			</div>
			<?php if($type == 'print'): ?>
			<div class="row padding-10">
				<div class="col-sm-6 margin-bottom-50">
					<h4><i class="icon-fab-term"></i> Extruder <span class="pull-right"><span class="extruder-temp"></span> / <strong><span class="slider-extruder-target"></span></strong></span></h4>
					<div id="create-ext-target-slider" class="noUiSlider sliders"></div>
				</div>
				<div class="col-sm-6 margin-bottom-50">
					<h4><i class="icon-fab-term"></i> Bed <span class="pull-right"><span class="bed-temp"></span> / <strong><span class="slider-bed-target"></span></strong></span></h4>
					<div id="create-bed-target-slider" class="noUiSlider sliders"></div>
				</div>
			</div>
			<hr class="simple">
			<?php endif; ?>
			<div class="row padding-10">
				<div class="col-sm-4 margin-bottom-50">
					<h4>Speed <span class="pull-right"><strong><span class="task-speed"></span></strong>  %</span></h4>
					<div id="create-speed-slider" class="noUiSlider sliders"></div>
				</div>
				<div class="col-sm-4 margin-bottom-50">
					<h4>Flow rate <span class="pull-right"><strong><span class="task-flow-rate"></span></strong> %</span></h4>
					<div id="create-flow-rate-slider" class="noUiSlider sliders"></div>
				</div>
				<div class="col-sm-4 margin-bottom-50">
					<h4>Fan <span class="pull-right"><strong><span class="task-fan"></span></strong> %</span></h4>
					<div id="create-fan-slider" class="noUiSlider sliders"></div>
				</div>
			</div>
		</div>
	</div>
</div>