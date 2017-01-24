<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>

<hr class="simple">
<ul id="createFeed" class="nav nav-tabs bordered">
	<li class="active"><a href="#live-feeds-tab" data-toggle="tab">Live feeds</a></li>
	<li><a href="#controls-tab" data-toggle="tab"><i class="fa fa-sliders"></i> Controls</a></li>
</ul>
<div id="createFeedContent" class="tab-content padding-10">
	<br>
	<div class="tab-pane fade in active" id="live-feeds-tab">
		<div class="row">
			<?php if($type == 'print'): ?>
			<div class="col-sm-6">
				
				<div class="row">
					<div class="col-xs-6 col-sm-6 col-md-12 col-lg-6">
						<button type="button" class="btn btn-default btn-block dropdown-toggle" data-toggle="dropdown">
							<span><i class="fab-lg fab-fw icon-fab-term"></i> <span class="hidden-xs">Extruder</span> <span class="hidden-md hidden-sm hidden-lg font-md">E</span> <span class="extruder-temp"></span> / <span class="extruder-target"></span> <span class="pull-right"><i class="fa fa-caret-down"></i></span></span>
						</button>
						<ul class="dropdown-menu">
							<li>
								<div class="checkbox">
									<label><input type="checkbox" class="checkbox graph-line-selector" checked="checked" name="ext-actual"><span>Actual</span></label>
								</div>
							</li>
							<li>
								<div class="checkbox">
									<label><input type="checkbox" class="checkbox graph-line-selector" name="ext-target"><span>Target</span></label>
								</div>
							</li>
						</ul>
					</div>
					<div class="col-xs-6 col-sm-6 col-md-12 col-lg-6">
						<button type="button" class="btn btn-default btn-sm btn-block dropdown-toggle" data-toggle="dropdown">
							<span><i class="fab-lg fab-fw icon-fab-term"></i> <span class="hidden-xs">Bed</span> <span class="hidden-md hidden-sm hidden-lg font-md">B</span> <span class="bed-temp"></span> / <span class="bed-target"></span><span class="pull-right"><i class="fa fa-caret-down"></i></span></span>
						</button>
						<ul class="dropdown-menu">
							<li>
								<div class="checkbox">
									<label><input type="checkbox" class="checkbox graph-line-selector" checked="checked" name="bed-actual"><span>Actual</span></label>
								</div>
							</li>
							<li>
								<div class="checkbox">
									<label><input type="checkbox" class="checkbox graph-line-selector" name="bed-target"><span>Target</span></label>
								</div>
							</li>
						</ul>
					</div>
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
							<div class="progress-bar" id="task-progress-bar"></div>
						</div> </div>
					<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12"> <span class="text"> Speed <span class="pull-right"><span class="task-speed"></span> / 500 %</span> </span>
						<div class="progress">
							<div class="progress-bar" id="task-speed-bar"></div>
						</div> </div>
					<?php if($type == 'print'): ?>
					<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12"> <span class="text"> Flow rate<span class="pull-right"><span class="task-flow-rate"></span> / 500 %</span></span></span>
						<div class="progress">
							<div class="progress-bar" id="task-flow-rate-bar"></div>
						</div> </div>
					<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12"> <span class="text"> Fan <span class="pull-right"><span class="task-fan"></span> %</span> </span>
						<div class="progress">
							<div class="progress-bar" id="task-fan-bar"></div>
						</div> </div>
					<?php endif; ?>
					<?php if($type == 'mill' || $type == 'laser'): ?>
					<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12"> <span class="text"><?php echo isset($rpm_label) ? $rpm_label : 'RPM'; ?> <span class="pull-right"><span class="task-rpm"></span> </span> </span>
						<div class="progress">
							<div class="progress-bar" id="task-rpm-bar"></div>
						</div> </div>
					<?php endif; ?>
					<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12"> <span class="text"> Elapsed time <span class="pull-right"><span class="elapsed-time"></span> </span> </span>
						<div class="fake-progress"></div>
					</div>
					<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12"> <span class="text"> Estimated time left <span class="pull-right"><span class="estimated-time-left"></span> </span> </span>
						<div class="fake-progress"></div>
					</div>
					<span class="show-stat-buttons"> 
						<span class="col-xs-6 col-sm-6 col-md-6 col-lg-6"> 
							<button type="button" data-action="abort"  class="btn btn-default btn-block  action"><i class="fa fa-stop"></i> Abort <?php echo isset($type_label) ? $type_label : ucfirst($type); ?></button> 
						</span> 
						<span class="col-xs-6 col-sm-6 col-md-6 col-lg-6"> 
							<button type="button" data-action="pause"  class="btn btn-default btn-block  action isPaused-button"><i class="fa fa-pause"></i> Pause <?php echo isset($type_label) ? $type_label : ucfirst($type); ?></button> 
						</span> 
					</span>
				</div>
			</div>
		</div>
		<hr class="simple">
		<div class="row">
			<div class="col-sm-12">
				<div class="textarea-div">
					<div class="typearea">
						<div class="custom-scroll trace-console" ></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="tab-pane fade in" id="controls-tab">
		<div class="row margin-bottom-20">
			<div class="col-sm-6 col-xs-12">
				<span class="col-xs-12 col-sm-12 margin-bottom-10">
					<label>Override Z Height: <strong><span class="z-height"></span></strong></label>
				</span>
				<span class="show-stat-buttons"> 
					<span class="col-xs-4 col-sm-4 col-md-4 col-lg-4"> 
						<button data-rel="tooltip" data-original-title="Away from the nozzle" type="button" data-action="zHeight" data-attribute="+" class="btn btn-default btn-block action"><i class="fa fa-arrow-down"></i></button>
					</span> 
					<span class="col-xs-4 col-sm-4 col-md-4 col-lg-4"> 
						<label style="width:100%;">
							<?php echo form_dropdown('zHeight', isset($z_height_values)?$z_height_values:array('0.1' => '0.1', '0.01' => '0.01'), '', 'id="zHeight" class="form-control"'); ?> <i></i>
						</label>
					</span>
					<span class="col-xs-4 col-sm-4 col-md-4 col-lg-4"> 
						<button rel="tooltip" title="Closer to the nozzle" type="button" data-action="zHeight" data-attribute="-" class="btn btn-default btn-block action"><i class="fa fa-arrow-up"></i></button>
					</span>
				</span>
			</div>
		</div>
		<hr class="simple hidden-md hidden-sm hidden-lg">
		<?php if($type == 'print'): ?>
		<div class="row padding-10">
			<div class="col-sm-6 margin-bottom-50">
				<h4><i class="icon-fab-term"></i> <span>Extruder</span> <span class="pull-right"><span rel="tooltip" data-placement="top" data-original-title="Extruder current temperature"  class="extruder-temp"></span> / <strong><span rel="tooltip" data-placement="top" data-original-title="Extruder target temperature" class="slider-extruder-target"></span></strong> &deg;C</span></h4>
				<div id="create-ext-target-slider" class="noUiSlider sliders"></div>
			</div>
			<hr class="simple hidden-md hidden-sm hidden-lg">
			<div class="col-sm-6 margin-bottom-50">
				<h4><i class="icon-fab-term"></i> Bed <span class="pull-right"><span rel="tooltip" data-placement="top" data-original-title="Bed current temperature" class="bed-temp"></span> / <strong><span rel="tooltip" data-placement="top" data-original-title="Bed target temperature" class="slider-bed-target"></span></strong> &deg;C</span></h4>
				<div id="create-bed-target-slider" class="noUiSlider sliders"></div>
			</div>
		</div>
		<hr class="simple">
		<?php endif; ?>
		<div class="row padding-10">
			<div class="col-sm-<?php echo $type == 'print' ?  4 : 6 ?> margin-bottom-50">
				<h4>Speed <span class="pull-right"><strong><span class="slider-task-speed"></span></strong>  %</span></h4>
				<div id="create-speed-slider" class="noUiSlider sliders"></div>
			</div>
			<?php if($type == 'print'): ?>
			<hr class="simple hidden-md hidden-sm hidden-lg">
			<div class="col-sm-4 margin-bottom-50">
				<h4>Flow rate <span class="pull-right"><strong><span class="slider-task-flow-rate"></span></strong> %</span></h4>
				<div id="create-flow-rate-slider" class="noUiSlider sliders"></div>
			</div>
			<hr class="simple hidden-md hidden-sm hidden-lg">
			<div class="col-sm-4 margin-bottom-50">
				<h4>Fan <span class="pull-right"><strong><span class="slider-task-fan"></span></strong> %</span></h4>
				<div id="create-fan-slider" class="noUiSlider sliders"></div>
			</div>
			<?php endif;?>
			<?php if($type == 'mill' || $type == 'laser'):?>
			<hr class="simple hidden-md hidden-sm hidden-lg">
			<div class="col-sm-6 margin-bottom-50">
				<h4><?php echo isset($rpm_label) ? $rpm_label : 'RPM'; ?> <span class="pull-right"><strong><span class="slider-task-rpm"></span></strong> </span></h4>
				<div id="create-rpm-slider" class="noUiSlider sliders"></div>
			</div>
			<?php endif;?>
		</div>
	</div>
</div>
